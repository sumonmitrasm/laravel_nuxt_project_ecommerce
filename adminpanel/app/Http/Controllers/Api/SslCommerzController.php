<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Library\SslCommerz\SslCommerzNotification;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use App\Models\OrderPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SslCommerzController extends Controller
{
    public function initiate(Request $request, string $orderNumber): JsonResponse
    {
        $order = $request->user()->orders()
            ->with(['items', 'address', 'payments' => fn ($query) => $query->latest('id')])
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if ($order->payment_method !== 'sslcommerz' || $order->payment_status === 'paid') {
            throw ValidationException::withMessages(['payment' => 'This order is not available for online payment.']);
        }
        if (in_array($order->order_status, ['cancelled', 'canceled', 'delivered'], true)) {
            throw ValidationException::withMessages(['payment' => 'This order can no longer be paid.']);
        }

        $payment = $order->payments->firstOrFail();
        $transactionId = 'SSL'.$order->id.Str::upper(Str::random(12));
        $payment->update(['transaction_id' => $transactionId, 'status' => 'initiated']);

        $address = $order->address;
        $data = [
            'total_amount' => $order->grand_total,
            'currency' => $order->currency,
            'tran_id' => $transactionId,
            'product_category' => 'ecommerce',
            'product_name' => Str::limit($order->items->pluck('product_name')->join(', '), 250, ''),
            'product_profile' => 'physical-goods',
            'shipping_method' => 'YES',
            'num_of_item' => $order->items->sum('quantity'),
            'cus_name' => Str::limit($address?->recipient_name ?? $request->user()->name, 50, ''),
            'cus_email' => $request->user()->email,
            'cus_add1' => Str::limit($address?->address_line ?? 'Bangladesh', 50, ''),
            'cus_add2' => Str::limit($address?->area ?? '', 50, ''),
            'cus_city' => Str::limit($address?->district_name ?? 'Dhaka', 50, ''),
            'cus_state' => Str::limit($address?->division_name ?? '', 50, ''),
            'cus_postcode' => $address?->postal_code ?? '0000',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $address?->phone ?? '',
            'ship_name' => Str::limit($address?->recipient_name ?? $request->user()->name, 50, ''),
            'ship_add1' => Str::limit($address?->address_line ?? 'Bangladesh', 50, ''),
            'ship_add2' => Str::limit($address?->area ?? '', 50, ''),
            'ship_city' => Str::limit($address?->district_name ?? 'Dhaka', 50, ''),
            'ship_state' => Str::limit($address?->division_name ?? '', 50, ''),
            'ship_postcode' => $address?->postal_code ?? '0000',
            'ship_country' => 'Bangladesh',
            'value_a' => $order->order_number,
        ];

        $response = json_decode((new SslCommerzNotification())->makePayment($data), true);
        if (($response['status'] ?? null) !== 'success' || empty($response['data'])) {
            $payment->update(['status' => 'failed']);
            return response()->json([
                'status' => false,
                'message' => $response['message'] ?? 'SSLCommerz payment session could not be created.',
            ], 502);
        }

        return response()->json(['status' => true, 'gateway_url' => $response['data']]);
    }

    public function success(Request $request): RedirectResponse
    {
        $orderNumber = $this->completePayment($request);
        return $this->frontendRedirect($orderNumber ? 'success' : 'invalid', $orderNumber);
    }

    public function ipn(Request $request): JsonResponse
    {
        $orderNumber = $this->completePayment($request);
        return response()->json([
            'status' => (bool) $orderNumber,
            'message' => $orderNumber ? 'Payment verified.' : 'Payment validation failed.',
        ], $orderNumber ? 200 : 422);
    }

    public function fail(Request $request): RedirectResponse
    {
        return $this->frontendRedirect('failed', $this->orderNumber($request));
    }

    public function cancel(Request $request): RedirectResponse
    {
        return $this->frontendRedirect('cancelled', $this->orderNumber($request));
    }

    private function completePayment(Request $request): ?string
    {
        $transactionId = (string) $request->input('tran_id');
        $validationId = (string) $request->input('val_id');
        if ($transactionId === '' || $validationId === '') return null;

        $payment = OrderPayment::with('order')->where('transaction_id', $transactionId)->first();
        if (! $payment || ! $payment->order || $payment->method !== 'sslcommerz') return null;
        if ($payment->status === 'paid') return $payment->order->order_number;

        $gateway = new SslCommerzNotification();
        if (! $gateway->orderValidate($request->all(), $transactionId, (float) $payment->amount, $payment->currency)) {
            Log::warning('SSLCommerz validation failed.', ['transaction_id' => $transactionId]);
            return null;
        }

        $validation = $gateway->getValidationData();
        if ((int) ($validation?->risk_level ?? 0) === 1) {
            $payment->update(['status' => 'review']);
            $payment->order->update(['payment_status' => 'review']);
            return null;
        }

        [$order, $newlyPaid] = DB::transaction(function () use ($payment, $validation, $request) {
            $lockedPayment = OrderPayment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($lockedPayment->order_id)->lockForUpdate()->firstOrFail();
            if ($lockedPayment->status === 'paid') return [$order, false];

            $lockedPayment->update([
                'status' => 'paid',
                'gateway_reference' => $validation?->bank_tran_id ?? $request->input('bank_tran_id'),
                'paid_at' => now(),
                'gateway_response' => [
                    'status' => $validation?->status,
                    'validation_id' => $validation?->val_id,
                    'bank_transaction_id' => $validation?->bank_tran_id,
                    'card_type' => $validation?->card_type,
                ],
            ]);
            $order->update(['payment_status' => 'paid', 'order_status' => 'confirmed']);
            if (! $order->statusHistories()->where('status', 'confirmed')->exists()) {
                $order->statusHistories()->create([
                    'status' => 'confirmed',
                    'note' => 'SSLCommerz payment verified.',
                    'changed_by_type' => 'system',
                ]);
            }
            return [$order, true];
        }, 3);

        try {
            if ($newlyPaid) {
                Mail::to($order->user->email)->send(new OrderPlacedMail($order));
            }
        } catch (\Throwable $exception) {
            Log::error('Paid order email could not be sent.', ['order_id' => $order->id, 'exception' => $exception->getMessage()]);
        }

        return $order->order_number;
    }

    private function orderNumber(Request $request): ?string
    {
        $payment = OrderPayment::with('order')->where('transaction_id', (string) $request->input('tran_id'))->first();
        return $payment?->order?->order_number;
    }

    private function frontendRedirect(string $state, ?string $orderNumber): RedirectResponse
    {
        $frontend = rtrim((string) config('app.frontend_url', 'http://localhost:3000'), '/');
        $query = http_build_query(['order' => $orderNumber, 'payment' => $state]);
        return redirect()->away($frontend.'/account/order-details?'.$query);
    }
}