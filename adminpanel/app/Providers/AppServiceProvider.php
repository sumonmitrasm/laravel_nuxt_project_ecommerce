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
use App\Observers\BrandObserver;
use App\Observers\CategoryObserver;
use App\Observers\ProductObserver;
use App\Observers\ProductAttributeObserver;
use App\Observers\SectionObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
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
        Product::observe(ProductObserver::class);
        ProductAttributeDefinition::observe(ProductAttributeObserver::class);
        ProductAttributeValue::observe(ProductAttributeObserver::class);
        Brand::observe(BrandObserver::class);
        Category::observe(CategoryObserver::class);
        Section::observe(SectionObserver::class);

        Paginator::useBootstrap();
        $generalSetting = Cache::remember(
            'general_setting.v2',
            now()->addHours(6),
            fn () => Setting::query()->where('status', true)->latest('id')->first()?->toArray(),
        );

        View::share('generalSetting', $generalSetting ? (object) $generalSetting : null);

        View::composer('admin.layout.header', function ($view) {
            $query = AdminNotification::query()->where('admin_id', Auth::guard('admin')->id());
            $view->with('adminNotifications', (clone $query)->latest()->limit(5)->get())
                ->with('unreadAdminNotificationCount', (clone $query)->whereNull('read_at')->count());
        });
    }
}
