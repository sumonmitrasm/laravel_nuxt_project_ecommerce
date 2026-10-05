<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\JsonResponse;
use App\Support\ContentCache;

class ShippingMethodController extends Controller
{
    public function index(): JsonResponse
    {
        $methods = ContentCache::remember('shipping', 'methods', fn () =>
            ShippingMethod::query()->where('status', true)->orderBy('position')->orderBy('id')
                ->get(['id', 'name', 'code', 'description', 'charge', 'delivery_time', 'icon'])->toArray()
        );

        return response()->json(['status' => true, 'shipping_methods' => $methods]);
    }
}
