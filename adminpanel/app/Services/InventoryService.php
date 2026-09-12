<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\AdminNotification;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function decreaseForOrder(
        ProductVariant $variant,
        int $quantity,
        Order $order,
        int $userId,
    ): void {
        $this->changeStock(
            variantId: $variant->id,
            quantity: -$quantity,
            reason: 'order_placed',
            order: $order,
            changedBy: 'user',
            changedById: $userId,
        );
    }

    public function restoreForCancelledOrder(
        int $variantId,
        int $quantity,
        Order $order,
        string $cancelledBy,
        int $cancelledById,
    ): void {
        $this->changeStock(
            variantId: $variantId,
            quantity: $quantity,
            reason: 'order_cancelled',
            order: $order,
            changedBy: $cancelledBy,
            changedById: $cancelledById,
        );
    }

    public function manualAdjust(
        int $variantId,
        int $quantity,
        string $reason,
        int $adminId,
        ?string $note = null,
    ): void {
        $this->changeStock(
            variantId: $variantId,
            quantity: $quantity,
            reason: $reason,
            changedBy: 'admin',
            changedById: $adminId,
            note: $note,
        );
    }

    private function changeStock(
        int $variantId,
        int $quantity,
        string $reason,
        ?Order $order = null,
        string $changedBy = 'system',
        ?int $changedById = null,
        ?string $note = null,
    ): void {
        DB::transaction(function () use (
            $variantId, $quantity, $reason, $order,
            $changedBy, $changedById, $note
        ) {
            $variant = ProductVariant::with('product:id,product_name')
                ->lockForUpdate()
                ->findOrFail($variantId);

            $stockBefore = (int) $variant->stock;
            $stockAfter = $stockBefore + $quantity;

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'stock' => "Only {$stockBefore} item(s) are available.",
                ]);
            }

            $variant->update(['stock' => $stockAfter]);

            $variant->stockAdjustments()->create([
                'change_quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $reason,
                'reference_type' => $order?->getMorphClass(),
                'reference_id' => $order?->id,
                'changed_by_type' => $changedBy,
                'changed_by_id' => $changedById,
                'note' => $note,
            ]);

            $justBecameLow = $stockBefore > $variant->low_stock_threshold
                && $stockAfter <= $variant->low_stock_threshold;
            $justBecameEmpty = $stockBefore > 0 && $stockAfter === 0;

            if ($justBecameLow || $justBecameEmpty) {
                $this->sendStockNotification($variant, $justBecameEmpty);
            }
        }, 3);
    }

    private function sendStockNotification(ProductVariant $variant, bool $isOutOfStock): void
    {
        $adminIds = Admin::query()
            ->where('status', true)
            ->where(function ($query) {
                $query->where('type', 'superadmin')
                    ->orWhereHas('roles', function ($roles) {
                        $roles->where('module', 'product')
                            ->where('view_access', true)
                            ->where('no_access', false);
                    });
            })
            ->pluck('id');

        $now = now();
        $notifications = $adminIds->map(fn ($adminId) => [
            'admin_id' => $adminId,
            'product_variant_id' => $variant->id,
            'type' => $isOutOfStock ? 'stock_out' : 'stock_low',
            'title' => $isOutOfStock ? 'Product out of stock' : 'Low stock alert',
            'message' => ($variant->product?->product_name ?? 'Product')
                ." ({$variant->sku}) has {$variant->stock} item(s) left.",
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if ($notifications !== []) {
            AdminNotification::insert($notifications);
        }
    }
}