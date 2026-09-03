<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\FrontController;
use Illuminate\Support\Facades\Route;

Route::get('/menu', [FrontController::class, 'menu'])->name('api.menu');
Route::get('/products', [FrontController::class, 'products'])->name('api.products');
Route::get('/listing/{url}', [FrontController::class, 'listing'])->name('api.listing');
Route::get('/detail/{id}', [FrontController::class, 'details'])->whereNumber('id')->name('api.detail');

Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('api.cart.index');
    Route::post('/items', [CartController::class, 'store'])->name('api.cart.items.store');
    Route::patch('/items/{item}', [CartController::class, 'update'])->name('api.cart.items.update');
    Route::delete('/items/{item}', [CartController::class, 'destroy'])->name('api.cart.items.destroy');
    Route::delete('/', [CartController::class, 'clear'])->name('api.cart.clear');
});

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('api.verification.verify');

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware(['api.guest', 'throttle:5,1']);
    Route::post('/login', [AuthController::class, 'login'])->middleware(['api.guest', 'throttle:10,1']);
    Route::post('/email/resend', [AuthController::class, 'resendVerification'])->middleware('throttle:6,1');

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::get('/user', [AuthController::class, 'user']);
        Route::patch('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
