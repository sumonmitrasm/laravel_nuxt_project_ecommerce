<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class CouponService
{
    public function calculate(
        Coupon $coupon,
        Collection $items,
        float $subtotal,
        ?User $user,
        string $guestToken,
    ): array {
        $this->validateAvailability($coupon);
        $this->validateCustomer($coupon, $user, $guestToken);

        if ($subtotal < (float) $coupon->minimum_order_amount) {
            $minimum = number_format((float) $coupon->minimum_order_amount, 2);
            $this->fail("A minimum order of ৳{$minimum} is required for this coupon.");
        }

        $eligibleItems = $this->eligibleItems($coupon, $items);
        $eligibleSubtotal = round((float) $eligibleItems->sum('line_total'), 2);

        if ($eligibleSubtotal <= 0) {
            $this->fail('This coupon does not apply to the items in your cart.');
        }

        $discount = match ($coupon->discount_type) {
            'fixed' => min((float) $coupon->discount_value, $eligibleSubtotal),
            'percentage' => $eligibleSubtotal * ((float) $coupon->discount_value / 100),
            'free_shipping' => 0,
            default => 0,
        };

        if ($coupon->discount_type === 'percentage' && $coupon->maximum_discount !== null) {
            $discount = min($discount, (float) $coupon->maximum_discount);
        }

        return [
            'discount' => round(max(0, $discount), 2),
            'free_shipping' => $coupon->discount_type === 'free_shipping',
            'eligible_subtotal' => $eligibleSubtotal,
        ];
    }

    private function validateAvailability(Coupon $coupon): void
    {
        if (! $coupon->is_active) {
            $this->fail('This coupon is not active.');
        }

        if ($coupon->starts_at?->isFuture()) {
            $this->fail('This coupon is not available yet.');
        }

        if ($coupon->expires_at?->isPast()) {
            $this->fail('This coupon has expired.');
        }

        if ($coupon->usage_limit !== null && $coupon->usages()->count() >= $coupon->usage_limit) {
            $this->fail('This coupon has reached its usage limit.');
        }
    }

    private function validateCustomer(Coupon $coupon, ?User $user, string $guestToken): void
    {
        if ($coupon->customer_scope === 'selected') {
            if (! $user || ! $coupon->users()->whereKey($user->id)->exists()) {
                $this->fail('This coupon is not available for your account.');
            }
        }

        if (in_array($coupon->customer_scope, ['first_order', 'lifetime_spend'], true) && ! $user) {
            $this->fail('Please sign in to use this coupon.');
        }

        if ($coupon->customer_scope === 'first_order' && CouponUsage::query()->where('user_id', $user?->id)->exists()) {
            $this->fail('This coupon is valid only for your first order.');
        }

        if ($coupon->customer_scope === 'lifetime_spend') {
            // Until the real orders table is connected, no lifetime spend can be proven safely.
            $this->fail('This loyalty coupon is not available yet.');
        }

        if ($coupon->usage_limit_per_user !== null) {
            $used = $user
                ? $coupon->usages()->where('user_id', $user->id)->count()
                : $coupon->usages()->where('guest_token', $guestToken)->count();

            if ($used >= $coupon->usage_limit_per_user) {
                $this->fail('You have already used this coupon the maximum number of times.');
            }
        }
    }

    private function eligibleItems(Coupon $coupon, Collection $items): Collection
    {
        $productIds = $coupon->scope === 'products' ? $coupon->products()->pluck('products.id') : collect();
        $categoryIds = $coupon->scope === 'categories' ? $coupon->categories()->pluck('categories.id') : collect();
        $brandIds = $coupon->scope === 'brands' ? $coupon->brands()->pluck('brands.id') : collect();

        return $items->filter(function (array $item) use ($coupon, $productIds, $categoryIds, $brandIds) {
            if ($coupon->exclude_discounted_products && (float) $item['discount_percentage'] > 0) {
                return false;
            }

            return match ($coupon->scope) {
                'all' => true,
                'products' => $productIds->contains($item['product_id']),
                'categories' => $categoryIds->contains($item['category_id']),
                'brands' => $brandIds->contains($item['brand_id']),
                default => false,
            };
        });
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['coupon' => $message]);
    }
}
