<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        dd($request->all());
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make(
                $validated['password']
            ),
        ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return response()->json([
            'status' => true,
            'message' => 'Account created successfully.',
            'user' => $user->only([
                'id',
                'name',
                'email',
                'email_verified_at',
            ]),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                'email',
            ],
            'password' => [
                'required',
                'string',
            ],
            'remember' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $remember = (bool) ($credentials['remember'] ?? false);

        unset($credentials['remember']);

        if (! Auth::guard('web')->attempt(
            $credentials,
            $remember
        )) {
            throw ValidationException::withMessages([
                'email' => 'Email address or password is incorrect.',
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'status' => true,
            'message' => 'Signed in successfully.',
            'user' => $request->user()->only([
                'id',
                'name',
                'email',
                'email_verified_at',
            ]),
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'user' => $request->user()->only([
                'id',
                'name',
                'email',
                'email_verified_at',
            ]),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => true,
            'message' => 'Signed out successfully.',
        ]);
    }
}
