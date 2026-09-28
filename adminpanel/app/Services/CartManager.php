<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartManager
{
    private const MAX_ITEM_QUANTITY = 3;

    public function resolve(Request $request, bool $create = false): ?Cart
    {
        if ($user = $request->user()) {
            return $this->mergeForUser(
                $user,
                $request->header('X-Guest-Cart-Token'),
                $create,
            );
        }

        $token = $this->guestToken($request, $create);
        if (! $token) {
            return null;
        }

        $query = Cart::query()->whereNull('user_id')->where('guest_token', $token);

        return $create
            ? $query->firstOrCreate(['user_id' => null, 'guest_token' => $token])
            : $query->first();
    }

    public function mergeForUser(User $user, ?string $guestToken, bool $create = true): ?Cart
    {
        return DB::transaction(function () use ($user, $guestToken, $create): ?Cart {
            $userCart = Cart::query()
                ->where('user_id', $user->id)
                ->latest('updated_at')
                ->lockForUpdate()
                ->first();

            $guestCart = $guestToken && Str::isUuid($guestToken)
                ? Cart::query()
                    ->whereNull('user_id')
                    ->where('guest_token', $guestToken)
                    ->lockForUpdate()
                    ->first()
                : null;

            if (! $guestCart) {
                if ($userCart || ! $create) {
                    return $userCart;
                }

                return Cart::query()->create([
                    'user_id' => $user->id,
                    'guest_token' => null,
                ]);
            }

            if (! $userCart) {
                $guestCart->update(['user_id' => $user->id]);

                return $guestCart->fresh();
            }

            $this->moveGuestItemsToUserCart($guestCart, $userCart);
            $userCart->touch();

            if (! $userCart->coupon_id && $guestCart->coupon_id) {
                $userCart->update([
                    'coupon_id' => $guestCart->coupon_id,
                    'coupon_applied_at' => $guestCart->coupon_applied_at,
                ]);
            }

            $guestCart->delete();

            return $userCart->fresh();
        });
    }

    private function moveGuestItemsToUserCart(Cart $guestCart, Cart $userCart): void
    {
        $guestCart->items()->lockForUpdate()->get()->each(function (CartItem $guestItem) use ($userCart): void {
            $userItemQuery = CartItem::query()
                ->where('cart_id', $userCart->id)
                ->where('product_id', $guestItem->product_id);

            $userItem = $guestItem->product_variant_id
                ? $userItemQuery->where('product_variant_id', $guestItem->product_variant_id)->lockForUpdate()->first()
                : $userItemQuery->whereNull('product_variant_id')->lockForUpdate()->first();

            if (! $userItem) {
                $guestItem->update(['cart_id' => $userCart->id]);

                return;
            }

            $userItem->update([
                'quantity' => min(self::MAX_ITEM_QUANTITY, $userItem->quantity + $guestItem->quantity),
            ]);

            $guestItem->delete();
        });
    }

    private function guestToken(Request $request, bool $required): ?string
    {
        $token = $request->header('X-Guest-Cart-Token');

        if (! $token && ! $required) {
            return null;
        }

        if (! $token || ! Str::isUuid($token)) {
            throw ValidationException::withMessages([
                'guest_token' => 'A valid guest cart token is required.',
            ]);
        }

        return $token;
    }
}
