<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FrontController;

Route::get('/menu', [FrontController::class, 'menu'])->name('api.menu');
Route::get('/products', [FrontController::class, 'products'])->name('api.products');
Route::get('/listing/{url}', [FrontController::class, 'listing'])->name('api.listing');
Route::get('/detail/{id}', [FrontController::class, 'details'])->whereNumber('id')->name('api.detail');
