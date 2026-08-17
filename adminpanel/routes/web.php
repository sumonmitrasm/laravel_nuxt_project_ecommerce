<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductAttributeController;

Route::get('/clear-cache', function() {
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    return "All cache cleared successfully!";
});
// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });
Route::redirect('/admin/login', '/admin/login');
Route::namespace('App\Http\Controllers\Admin')->prefix('/admin')->group(function() {
    Route::match(['get', 'post'], 'login', [AdminController::class, 'login'])->name('admin.login');
    Route::middleware(['admin.auth', 'admin.permission'])->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('logout', [AdminController::class, 'logout'])->name('logout-admin');
        //>>>>>>>>>>>>>>>>>>>>>>>>User activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        Route::get('users', [AdminController::class, 'users'])->name('admin-user');
        Route::post('users', [AdminController::class, 'storeUser'])->name('admin-user.store');
        Route::get('users/{user}', [AdminController::class, 'showUser'])->name('admin-user.show');
        Route::put('users/{user}', [AdminController::class, 'updateUser'])->name('admin-user.update');
        Route::patch('users/{user}/status', [AdminController::class, 'updateUserStatus'])->name('admin-user.status');
        Route::delete('users/{user}', [AdminController::class, 'deleteUser'])->name('admin-user.delete');
        //++++++++++++++++++++++++++User Permission++++++++++++++++++++++++++++++++++++++++++++++++
        Route::get('admin/users/{id}/permission', [AdminController::class, 'permissionUser'])->name('admin-user.permission');
        Route::post('admin/users/{id}/permission', [AdminController::class, 'updatePermissionUser'])->name('admin-user.permission.update');
        //>>>>>>>>>>>>>>>>>>>>>>>>User activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        //>>>>>>>>>>>>>>>>>>>>>>>>Section activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        Route::get('section', [SectionController::class, 'section'])->name('section');
        Route::post('section', [SectionController::class, 'store'])->name('admin-section.store');
        Route::get('sections/{section}', [SectionController::class, 'show'])->name('admin-section.show');
        Route::put('sections/{section}', [SectionController::class, 'update'])->name('admin-section.update');
        Route::patch('sections/{section}/status', [SectionController::class, 'updateStatus'])->name('admin-section.status');
        Route::delete('sections/{section}', [SectionController::class, 'destroy'])->name('admin-section.delete');
        //>>>>>>>>>>>>>>>>>>>>>>>>Section activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        //>>>>>>>>>>>>>>>>>>>>>>>>Category activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        Route::get('category', [CategoryController::class, 'category'])->name('category');
        Route::post('category', [CategoryController::class, 'store'])->name('admin-category.store');
        Route::get('categories/{category}', [CategoryController::class, 'show'])->name('admin-category.show');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('admin-category.update');
        Route::patch('categories/{category}/status', [CategoryController::class, 'updateStatus'])->name('admin-category.status');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('admin-category.delete');
        //>>>>>>>>>>>>>>>>>>>>>>>>Category activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        //>>>>>>>>>>>>>>>>>>>>>>>>General Settings<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        Route::get('settings', [SettingController::class, 'settings'])->name('settings');
        Route::post('settings', [SettingController::class, 'store'])->name('admin-setting.store');
        Route::get('setting/{setting}', [SettingController::class, 'show'])->name('admin-setting.show');
        Route::put('setting/{setting}', [SettingController::class, 'update'])->name('admin-setting.update');
        Route::patch('setting/{setting}/status', [SettingController::class, 'updateStatus'])->name('admin-setting.status');
        Route::delete('setting/{setting}', [SettingController::class, 'destroy'])->name('admin-setting.delete');
        //>>>>>>>>>>>>>>>>>>>>>>>>General Settings<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        //>>>>>>>>>>>>>>>>>>>>>>>>Tags activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        Route::get('tags', [TagController::class, 'index'])->name('tags');
        Route::post('tag', [TagController::class, 'store'])->name('admin-tag.store');
        Route::get('tag/{tag}', [TagController::class, 'show'])->name('admin-tag.show');
        Route::put('tag/{tag}', [TagController::class, 'update'])->name('admin-tag.update');
        Route::patch('tag/{tag}/status', [TagController::class, 'updateStatus'])->name('admin-tag.status');
        Route::delete('tag/{tag}', [TagController::class, 'destroy'])->name('admin-tag.delete');
        //>>>>>>>>>>>>>>>>>>>>>>>>Tags activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        //>>>>>>>>>>>>>>>>>>>>>>>>Brand activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        Route::get('brands', [BrandController::class, 'index'])->name('brands');
        Route::post('brand', [BrandController::class, 'store'])->name('admin-brand.store');
        Route::get('brand/{brand}', [BrandController::class, 'show'])->name('admin-brand.show');
        Route::put('brand/{brand}', [BrandController::class, 'update'])->name('admin-brand.update');
        Route::patch('brand/{brand}/status', [BrandController::class, 'updateStatus'])->name('admin-brand.status');
        Route::delete('brand/{brand}', [BrandController::class, 'destroy'])->name('admin-brand.delete');
        //>>>>>>>>>>>>>>>>>>>>>>>>Brand activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        //>>>>>>>>>>>>>>>>>>>>>>>>Product activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        Route::get('products', [ProductController::class, 'index'])->name('products');
        Route::post('product', [ProductController::class, 'store'])->name('admin-product.store');
        Route::get('product/{product}', [ProductController::class, 'show'])->name('admin-product.show');
        Route::put('product/{product}', [ProductController::class, 'update'])->name('admin-product.update');
        Route::patch('product/{product}/status', [ProductController::class, 'updateStatus'])->name('admin-product.status');
        Route::delete('product-images/{productImage}', [ProductController::class, 'destroyImage'])->name('admin-product.image.delete');
        Route::delete('product/{product}', [ProductController::class, 'destroy'])->name('admin-product.delete');
        Route::get('product-attributes', [ProductAttributeController::class, 'index'])->name('product-attributes');
        Route::post('product-attributes', [ProductAttributeController::class, 'store'])->name('product-attributes.store');
        Route::post('product-attributes/{attribute}/values', [ProductAttributeController::class, 'storeValue'])->name('product-attributes.values.store');
        Route::delete('product-attributes/{attribute}', [ProductAttributeController::class, 'destroy'])->name('product-attributes.destroy');
        Route::delete('product-attribute-values/{value}', [ProductAttributeController::class, 'destroyValue'])->name('product-attribute-values.destroy');
        //>>>>>>>>>>>>>>>>>>>>>>>>Product activity<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

    });
});
require __DIR__.'/auth.php';
