<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CartManager
{
    public function resolve(Request $request, bool $create = false): ?Cart
    {
        if ($user = $request->user()) {
            $cart = Cart::query()
                ->where('user_id', $user->id)
                ->latest('updated_at')
                ->first();

            return $cart ?? $this->mergeForUser(
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
        if ($guestToken && Str::isUuid($guestToken)) {
            $guestCart = Cart::query()
                ->whereNull('user_id')
                ->where('guest_token', $guestToken)
                ->first();

            if ($guestCart) {
                $guestCart->update(['user_id' => $user->id]);

                return $guestCart->fresh();
            }
        }

        $userCart = Cart::query()
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->first();

        if ($userCart || ! $create) {
            return $userCart;
        }

        return Cart::query()->create([
            'user_id' => $user->id,
            'guest_token' => $guestToken && Str::isUuid($guestToken) ? $guestToken : null,
        ]);
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
