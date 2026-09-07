<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ShippingMethodController extends Controller
{
    public function index(): JsonResponse
    {
        $methods = Cache::remember('api.shipping-methods.v1', now()->addHours(6), fn () =>
            ShippingMethod::query()->where('status', true)->orderBy('position')->orderBy('id')
                ->get(['id', 'name', 'code', 'description', 'charge', 'delivery_time', 'icon'])->toArray()
        );

        return response()->json(['status' => true, 'shipping_methods' => $methods]);
    }
}
