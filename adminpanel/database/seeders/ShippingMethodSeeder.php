<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            ['name' => 'Free delivery', 'code' => 'free', 'charge' => 0, 'delivery_time' => '4-6 business days', 'icon' => 'bi bi-truck', 'position' => 1],
            ['name' => 'Standard delivery', 'code' => 'standard', 'charge' => 120, 'delivery_time' => '2-4 business days', 'icon' => 'bi bi-box-seam', 'position' => 2],
            ['name' => 'Express delivery', 'code' => 'express', 'charge' => 350, 'delivery_time' => 'Next business day', 'icon' => 'bi bi-lightning-charge', 'position' => 3],
        ];

        foreach ($methods as $method) {
            ShippingMethod::updateOrCreate(['code' => $method['code']], [...$method, 'status' => true]);
        }
    }
}
