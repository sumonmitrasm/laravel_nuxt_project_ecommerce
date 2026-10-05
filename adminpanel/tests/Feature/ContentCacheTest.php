<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Notifications\StorefrontResetPasswordNotification;
use App\Support\ContentCache;
use App\Support\PageSeo;
use App\Support\SiteSettings;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContentCacheTest extends TestCase
{
    public function test_outage_allows_reads_and_edits_and_recovery_rejects_stale_data(): void
    {
        $store = new FaultingContentStore;
        Cache::swap(new \Illuminate\Cache\Repository($store));
        $setting = Setting::create(['address' => 'Before outage', 'status' => true]);
        $this->assertSame('Before outage', SiteSettings::get()['address']);
        $oldEntry = $store->get('content.v1.settings.active');

        $store->offline = true;
        $this->assertSame('Before outage', SiteSettings::get()['address']);
        $setting->update(['address' => 'Edited while offline']);
        $this->assertSame('Edited while offline', SiteSettings::get()['address']);

        $store->offline = false;
        // Recovery really does contain the stale entry; a new repository/request
        // must use the durable DB version rather than process-local memory.
        $this->assertSame($oldEntry, $store->get('content.v1.settings.active'));
        Cache::swap(new \Illuminate\Cache\Repository($store));
        $this->assertSame('Edited while offline', SiteSettings::get()['address']);
        $this->assertNotSame($oldEntry['version'], $store->get('content.v1.settings.active')['version']);
    }

    public function test_cache_write_failure_returns_loaded_data_once(): void
    {
        $store = new FaultingContentStore;
        $store->failWrites = true;
        Cache::swap(new \Illuminate\Cache\Repository($store));
        $loads = 0;
        $this->assertSame('fresh', ContentCache::remember('write-failure', 'data', function () use (&$loads) {
            $loads++;
            return 'fresh';
        }));
        $this->assertSame(1, $loads);
        $this->assertNull($store->get('content.v1.write-failure.data'));
    }

    public function test_failed_cleanup_does_not_allow_old_data_after_edit(): void
    {
        $store = new FaultingContentStore;
        Cache::swap(new \Illuminate\Cache\Repository($store));
        ContentCache::remember('cleanup', 'data', fn () => 'old');
        $store->failCleanup = true;
        ContentCache::forget('cleanup');
        $this->assertSame('old', $store->get('content.v1.cleanup.data')['value']);
        $this->assertSame('new', ContentCache::remember('cleanup', 'data', fn () => 'new'));
    }

    public function test_old_format_cache_is_not_accepted_without_a_version(): void
    {
        Cache::forever('content.v1.legacy.data', ['value' => 'old']);
        $this->assertSame('new', ContentCache::remember('legacy', 'data', fn () => 'new'));
    }

    public function test_database_failure_is_not_disguised_as_successful_cached_content(): void
    {
        ContentCache::remember('database-down', 'data', fn () => 'old');
        Schema::drop('content_cache_versions');
        $this->expectException(\Illuminate\Database\QueryException::class);
        ContentCache::remember('database-down', 'data', fn () => 'new');
    }

    public function test_busy_rebuild_lock_reads_fresh_without_caching_the_fallback(): void
    {
        Cache::shouldReceive('get')->once()->with('content.v1.busy.data')->andReturn(null);
        $lock = \Mockery::mock();
        Cache::shouldReceive('lock')->once()->with('content.v1.busy.lock', 60)->andReturn($lock);
        $lock->shouldReceive('block')->once()->andThrow(new \Illuminate\Contracts\Cache\LockTimeoutException);
        $this->assertSame('fresh', ContentCache::remember('busy', 'data', fn () => 'fresh'));
    }

    public function test_failed_loader_is_not_cached_and_releases_its_lock(): void
    {
        try {
            ContentCache::remember('failure', 'data', function () { throw new \RuntimeException('Database failed'); });
            $this->fail('Loader exceptions must not be hidden.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Database failed', $exception->getMessage());
        }
        $this->assertSame('recovered', ContentCache::remember('failure', 'data', fn () => 'recovered'));
    }

    public function test_stock_edits_keep_filters_but_price_edits_clear_them(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id(); $table->decimal('price'); $table->integer('stock');
            $table->boolean('status'); $table->timestamps();
        });
        $variant = \App\Models\ProductVariant::create(['price' => 100, 'stock' => 20, 'status' => true]);
        // Reload to reproduce an existing inventory record, not a new variant.
        $variant = $variant->fresh();
        ContentCache::remember('shop-filters', 'test', fn () => 'warm');
        $variant->update(['stock' => 19]);
        $this->assertSame('warm', Cache::get('content.v1.shop-filters.test')['value']);
        $variant->update(['price' => 120]);
        $this->assertNull(Cache::get('content.v1.shop-filters.test'));
        ContentCache::remember('shop-filters', 'test', fn () => 'warm');
        $variant->update(['status' => false]);
        $this->assertNull(Cache::get('content.v1.shop-filters.test'));
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        (require database_path('migrations/2026_10_04_000001_create_content_cache_versions_table.php'))->up();
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('side_name')->nullable();
            $table->string('address')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function test_settings_remain_cached_until_saved_and_all_consumers_agree(): void
    {
        $this->assertNull(SiteSettings::get());
        $setting = Setting::create(['side_name' => 'Shop', 'address' => 'Old address', 'status' => true]);
        $this->assertSame('Old address', SiteSettings::get()['address']);
        $this->travel(30)->days();
        DB::enableQueryLog();
        $this->assertSame('Old address', SiteSettings::get()['address']);
        $this->assertCount(1, DB::getQueryLog());
        $this->assertStringContainsString('content_cache_versions', DB::getQueryLog()[0]['query']);
        DB::disableQueryLog();
        $setting->update(['address' => 'New address']);
        $this->assertSame('New address', app(PageSeo::class)->site()['address']);

        // The mail used to store an Eloquent object under the array cache key.
        $user = new class {
            public string $name = 'Customer';
            public function getEmailForPasswordReset(): string { return 'customer@example.com'; }
        };
        $mail = (new StorefrontResetPasswordNotification('test-token'))->toMail($user);
        $this->assertSame('Shop password reset', $mail->subject);
        $this->assertIsArray(SiteSettings::get());

        $setting->update(['status' => false]);
        $this->assertNull(SiteSettings::get());
        $setting->update(['status' => true]);
        $this->assertNotNull(SiteSettings::get());
        $setting->delete();
        $this->assertNull(SiteSettings::get());
    }

    public function test_rollback_keeps_cache_and_commit_invalidates_it(): void
    {
        $setting = Setting::create(['address' => 'Original', 'status' => true]);
        SiteSettings::get();
        DB::beginTransaction();
        $setting->update(['address' => 'Uncommitted']);
        $this->assertSame('Uncommitted', SiteSettings::get()['address']);
        $this->assertSame('Original', Cache::get('content.v1.settings.active')['value']['address']);
        DB::rollBack();
        $this->assertSame('Original', SiteSettings::get()['address']);
        DB::transaction(fn () => $setting->fresh()->update(['address' => 'Committed']));
        $this->assertSame('Committed', SiteSettings::get()['address']);
    }

    public function test_invalidating_a_group_removes_all_scopes_but_keeps_other_groups(): void
    {
        ContentCache::remember('shop-filters', 'price.all', fn () => [1, 100]);
        ContentCache::remember('shop-filters', 'price.category-2', fn () => [2, 50]);
        ContentCache::remember('shipping', 'methods', fn () => ['Standard']);
        ContentCache::forget('shop-filters');
        $this->assertNull(Cache::get('content.v1.shop-filters.price.all'));
        $this->assertNull(Cache::get('content.v1.shop-filters.price.category-2'));
        $this->assertNull(Cache::get('content.v1.shop-filters.keys'));
        $this->assertSame(['Standard'], ContentCache::remember('shipping', 'methods', fn () => []));
        $this->assertSame([5, 200], ContentCache::remember('shop-filters', 'price.all', fn () => [5, 200]));
    }

    public function test_registered_model_events_clear_their_dependent_groups(): void
    {
        $models = [
            'HomeSlider' => 'sliders', 'AboutPage' => 'about', 'ShippingMethod' => 'shipping',
            'Division' => 'locations', 'District' => 'locations', 'Upazila' => 'locations',
            'Section' => 'menu', 'Category' => 'menu', 'Product' => 'menu',
            'ProductVariant' => 'shop-filters', 'Brand' => 'shop-filters',
            'ProductAttributeDefinition' => 'shop-filters', 'ProductAttributeValue' => 'shop-filters',
        ];
        foreach ($models as $name => $group) {
            $class = 'App\\Models\\'.$name;
            $model = new $class;
            foreach (['saved', 'deleted', 'restored'] as $event) {
                ContentCache::remember($group, 'test', fn () => 'old');
                event('eloquent.'.$event.': '.$class, $model);
                $this->assertNull(Cache::get("content.v1.{$group}.test"), "$name $event");
            }
        }
    }

    public function test_database_store_supports_forever_data_and_invalidation(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value');
            $table->integer('expiration');
        });
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
        config(['cache.default' => 'database']);
        $setting = Setting::create(['address' => 'Database cached', 'status' => true]);
        $this->assertSame('Database cached', SiteSettings::get()['address']);
        $this->assertGreaterThan(0, DB::table('cache')->count());
        $setting->update(['address' => 'Updated']);
        $this->assertSame('Updated', SiteSettings::get()['address']);

        // Exercise real database-store exceptions, not just a mock Redis outage.
        Schema::rename('cache', 'cache_offline');
        $this->assertSame('Updated', SiteSettings::get()['address']);
        $setting->update(['address' => 'Changed during cache outage']);
        $this->assertSame('Changed during cache outage', SiteSettings::get()['address']);
        Schema::rename('cache_offline', 'cache');
        $this->assertSame('Changed during cache outage', SiteSettings::get()['address']);
    }
}

// Fault injection leaves old entries intact, just like a recovered Redis server.
class FaultingContentStore extends \Illuminate\Cache\ArrayStore
{
    public bool $offline = false;
    public bool $failWrites = false;
    public bool $failCleanup = false;

    public function get($key)
    {
        if ($this->offline) { throw new \RuntimeException('Cache offline'); }
        return parent::get($key);
    }

    public function forever($key, $value)
    {
        if ($this->offline || $this->failWrites) { throw new \RuntimeException('Cache write failed'); }
        return parent::forever($key, $value);
    }

    public function lock($name, $seconds = 0, $owner = null)
    {
        if ($this->offline) { throw new \RuntimeException('Cache lock unavailable'); }
        return parent::lock($name, $seconds, $owner);
    }

    public function forget($key)
    {
        if ($this->offline || $this->failCleanup) { throw new \RuntimeException('Cache delete failed'); }
        return parent::forget($key);
    }
}
