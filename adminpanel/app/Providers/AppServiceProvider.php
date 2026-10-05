<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Setting;
use App\Models\AdminNotification;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeDefinition;
use App\Models\ProductAttributeValue;
use App\Models\Section;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $cachedModels = [
            Setting::class, Product::class, Category::class, Section::class,
            Brand::class, ProductAttributeDefinition::class, ProductAttributeValue::class,
            \App\Models\ProductVariant::class, \App\Models\HomeSlider::class,
            \App\Models\AboutPage::class, \App\Models\ShippingMethod::class,
            \App\Models\Division::class, \App\Models\District::class, \App\Models\Upazila::class,
        ];
        foreach ($cachedModels as $model) {
            $model::observe(\App\Observers\ContentCacheObserver::class);
        }

        Paginator::useBootstrap();
        // Read site settings only when a view needs them, not on every API/Artisan call.
        View::composer(['admin.login', 'admin.layout.*', 'emails.order-placed'], function ($view) {
            $generalSetting = \App\Support\SiteSettings::get();
            $view->with('generalSetting', $generalSetting ? (object) $generalSetting : null);
        });

        View::composer('admin.layout.header', function ($view) {
            $query = AdminNotification::query()->where('admin_id', Auth::guard('admin')->id());
            $view->with('adminNotifications', (clone $query)->with('variant:id,sku')->latest()->limit(5)->get())
                ->with('unreadAdminNotificationCount', (clone $query)->whereNull('read_at')->count());
        });
    }
}
