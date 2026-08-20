<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FrontController;
use App\Http\Controllers\Api\CartController;

Route::get('/menu', [FrontController::class, 'menu'])->name('api.menu');
Route::get('/products', [FrontController::class, 'products'])->name('api.products');
Route::get('/listing/{url}', [FrontController::class, 'listing'])->name('api.listing');
Route::get('/detail/{id}', [FrontController::class, 'details'])->whereNumber('id')->name('api.detail');
//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<Cart Routes>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>
Route::get('/cart', [CartController::class, 'index']);
Route::post('/cart/items', [CartController::class, 'store'])->name('api.items.store');
Route::patch('/cart/items/{item}', [CartController::class, 'update'])->name('api.items.update');
Route::delete('/cart/items/{item}', [CartController::class, 'destroy'])->name('api.items.destroy');
Route::delete('/cart', [CartController::class, 'clear'])->name('api.cart.clear');
