<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FrontController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\AuthController;

Route::get('/menu', [FrontController::class, 'menu'])->name('api.menu');
Route::get('/products', [FrontController::class, 'products'])->name('api.products');
Route::get('/listing/{url}', [FrontController::class, 'listing'])->name('api.listing');
Route::get('/detail/{id}', [FrontController::class, 'details'])->whereNumber('id')->name('api.detail');
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<Cart Routes>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('api.cart.index');
    Route::post('/items', [CartController::class, 'store'])->name('api.cart.items.store');
    Route::patch('/items/{item}', [CartController::class, 'update'])->name('api.cart.items.update');
    Route::delete('/items/{item}', [CartController::class, 'destroy'])->name('api.cart.items.destroy');
    Route::delete('/', [CartController::class, 'clear'])->name('api.cart.clear');
});
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class,'register',])->middleware('guest');
    Route::post('/login', [ AuthController::class,'login',])->middleware('guest');
    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class,'user',]);
    Route::post('/logout', [AuthController::class,'logout',]);
    });
});

