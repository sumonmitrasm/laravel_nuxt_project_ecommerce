<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiGuestMiddleware
{
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        if (Auth::guard('web')->check()) {
            return response()->json([
                'status' => false,
                'message' => 'You are already signed in. Please sign out before using another account.',
            ], 409);
        }

        return $next($request);
    }
}
