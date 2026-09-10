@extends('admin.layout.layout')

@section('content')
@php
    $statusColors=['pending'=>'warning','confirmed'=>'primary','processing'=>'info','shipped'=>'secondary','delivered'=>'success','cancelled'=>'danger'];
    $payment=$order->payments->first();
    $address=$order->address;
@endphp
<div class="app-content main-content"><div class="side-app"><div class="container-fluid main-container">
    <div class="page-header"><div class="page-leftheader"><h4 class="page-title">Order details</h4></div><div class="page-rightheader"><a href="{{ route('admin-orders.index') }}" class="btn btn-light" data-ajax-page>← Back to orders</a></div></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(isset($errors) && $errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="row">
        <div class="col-xl-8">
            <div class="card"><div class="card-header justify-content-between"><div><h3 class="card-title mb-1">{{ $order->order_number }}</h3><small class="text-muted">Placed {{ optional($order->placed_at)->format('d M Y, h:i A') }}</small></div><span class="badge bg-{{ $statusColors[$order->order_status] ?? 'secondary' }} fs-12">{{ ucfirst((string) ($order->order_status ?: 'unknown')) }}</span></div>
                <div class="card-body"><h5 class="mb-3">Products ({{ $order->items->sum('quantity') }})</h5>
                    @foreach($order->items as $item)
                    <div class="d-flex align-items-center border-bottom py-3">
                        <div class="avatar avatar-xl bg-light me-3">@if($item->image)<img src="{{ asset('admin/productimage/'.basename($item->image)) }}" alt="{{ $item->product_name }}" class="w-100 h-100" style="object-fit:contain">@else<i class="fe fe-box"></i>@endif</div>
                        <div class="flex-grow-1"><div class="fw-semibold">{{ $item->product_name }}</div><small class="text-muted">SKU: {{ $item->sku ?: '-' }} &middot; Qty: {{ $item->quantity }}</small>@if($item->options)<small class="d-block text-muted">{{ collect($item->options)->map(function ($option, $key) { return is_array($option) ? (($option['name'] ?? ucfirst((string) $key)).': '.($option['value'] ?? '')) : (ucfirst((string) $key).': '.$option); })->implode(' &middot; ') }}</small>@endif</div>
                        <div class="text-end"><div>৳{{ number_format((float)$item->line_total,2) }}</div><small class="text-muted">৳{{ number_format((float)$item->unit_price,2) }} each</small></div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="card"><div class="card-header"><h3 class="card-title">Status history</h3></div><div class="card-body">
                @forelse($order->statusHistories as $history)<div class="d-flex border-bottom py-3"><span class="badge bg-{{ $statusColors[$history->status] ?? 'secondary' }} me-3 align-self-start">{{ ucfirst($history->status) }}</span><div><div>{{ $history->note ?: 'Status updated.' }}</div><small class="text-muted">{{ $history->created_at->format('d M Y, h:i A') }} · {{ ucfirst($history->changed_by_type) }}</small></div></div>@empty<p class="text-muted mb-0">No history found.</p>@endforelse
            </div></div>
        </div>

        <div class="col-xl-4">
            <div class="card"><div class="card-header"><h3 class="card-title">Update status</h3></div><div class="card-body">
                @if(count($nextStatuses))
                <form method="POST" action="{{ route('admin-orders.status', $order) }}" data-order-status-form>@csrf @method('PATCH')<div class="alert alert-danger d-none js-order-status-errors"></div>
                    <label class="form-label">Next status</label><select name="status" class="form-select mb-3" required><option value="">Select status</option>@foreach($nextStatuses as $status)<option value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach</select>
                    <label class="form-label">Note @if(in_array('cancelled',$nextStatuses))(required for cancellation)@endif</label><textarea name="note" class="form-control mb-3" rows="3" maxlength="500" placeholder="Write a short update or cancellation reason"></textarea>
                    <button class="btn btn-primary w-100" data-order-status-submit><span class="spinner-border spinner-border-sm me-2 d-none" data-order-status-spinner aria-hidden="true"></span><span data-order-status-label>Update order</span></button>
                </form>
                @else<p class="text-muted mb-0">This order has reached its final status.</p>@endif
                @if($order->payment_method === 'sslcommerz' && $order->payment_status !== 'paid' && $order->order_status !== 'cancelled')<div class="alert alert-warning mt-3 mb-0">Online payment is not verified. This order cannot move forward yet.</div>@endif
            </div></div>

            <div class="card"><div class="card-header"><h3 class="card-title">Payment summary</h3></div><div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>৳{{ number_format((float)$order->subtotal,2) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Discount</span><span>−৳{{ number_format((float)$order->discount_amount,2) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Shipping</span><span>৳{{ number_format((float)$order->shipping_charge,2) }}</span></div>
                <hr><div class="d-flex justify-content-between fs-18 fw-bold"><span>Total</span><span>৳{{ number_format((float)$order->grand_total,2) }}</span></div>
                <hr><div><strong>{{ $order->payment_method === 'cod' ? 'Cash on delivery' : 'SSLCommerz' }}</strong><span class="badge bg-light text-dark float-end">{{ ucwords(str_replace('_',' ',(string) ($order->payment_status ?: 'unknown'))) }}</span></div>
                @if($payment?->transaction_id)<small class="text-muted d-block mt-2">Transaction: {{ $payment->transaction_id }}</small>@endif
            </div></div>

            <div class="card"><div class="card-header"><h3 class="card-title">Customer & delivery</h3></div><div class="card-body">
                <div class="fw-semibold">{{ $address?->recipient_name ?: $order->user?->name }}</div><div>{{ $address?->phone }}</div><div class="text-muted">{{ $order->user?->email }}</div><hr>
                <div>{{ $address?->address_line }}</div>@if($address?->area)<div>{{ $address->area }}</div>@endif<div>{{ collect([$address?->upazila_name,$address?->district_name,$address?->division_name,$address?->postal_code])->filter()->implode(', ') }}</div>
                <hr><strong>Shipping:</strong> {{ $order->shipping_method_name }}
                @if($order->customer_note)<hr><strong>Customer note</strong><p class="mb-0 mt-1">{{ $order->customer_note }}</p>@endif
                @if($order->cancellation_reason)<hr><strong class="text-danger">Cancellation reason</strong><p class="mb-0 mt-1">{{ $order->cancellation_reason }}</p>@endif
            </div></div>
        </div>
    </div>
</div></div></div>
@endsection
