<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FrontController;

Route::get('/sections', [FrontController::class, 'sections'])->name('api.sections');
Route::get('/menu', [FrontController::class, 'menu'])->name('api.menu');
Route::get('/manu', [FrontController::class, 'menu']);
Route::get('/listing/{url}', [FrontController::class, 'listing'])->name('api.listing');
Route::get('/detail/{id}', [FrontController::class, 'details'])->whereNumber('id')->name('api.detail');
