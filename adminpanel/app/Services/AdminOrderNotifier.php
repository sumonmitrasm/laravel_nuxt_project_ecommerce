<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AdminNotification;
use App\Models\Order;

class AdminOrderNotifier
{
    public function send(Order $order, string $event): void
    {
        $admins = Admin::query()
            ->where('status', true)
            ->where(function ($query) {
                $query->where('type', 'superadmin')
                    ->orWhereHas('roles', fn ($roles) => $roles
                        ->where('module', 'order')
                        ->where('view_access', true)
                        ->where('no_access', false));
            })->pluck('id');

        if ($admins->isEmpty()) return;

        $cancelled = $event === 'cancelled';
        $now = now();
        $rows = $admins->map(fn ($adminId) => [
            'admin_id' => $adminId,
            'order_id' => $order->id,
            'type' => $cancelled ? 'order_cancelled' : 'order_placed',
            'title' => $cancelled ? 'Order cancelled' : 'New order received',
            'message' => $cancelled
                ? $order->order_number.' was cancelled by the customer.'
                : $order->order_number.' was placed for BDT '.number_format((float) $order->grand_total, 2).'.',
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        AdminNotification::insert($rows);
    }
}
