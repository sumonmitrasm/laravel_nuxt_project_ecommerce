<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\FrontController;
use App\Support\ContentCache;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class FilterCachePerformanceTest extends TestCase
{
    public function test_many_variants_are_counted_in_sql_and_repeat_reads_use_cache(): void
    {
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        (require database_path('migrations/2026_10_04_000001_create_content_cache_versions_table.php'))->up();
        Schema::create('attributes', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug');
            $table->string('type'); $table->integer('position'); $table->boolean('status');
        });
        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id(); $table->integer('attribute_id'); $table->string('value');
            $table->string('color_code')->nullable(); $table->integer('position'); $table->boolean('status');
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id(); $table->integer('category_id'); $table->boolean('status');
        });
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id(); $table->integer('product_id'); $table->boolean('status');
        });
        Schema::create('attribute_value_product_variant', function (Blueprint $table) {
            $table->integer('attribute_value_id'); $table->integer('product_variant_id');
        });
        Schema::create('attribute_value_product', function (Blueprint $table) {
            $table->integer('attribute_value_id'); $table->integer('product_id');
        });
        DB::table('attributes')->insert(['id' => 1, 'name' => 'Colour', 'slug' => 'colour', 'type' => 'color', 'position' => 0, 'status' => true]);
        DB::table('attribute_values')->insert(['id' => 1, 'attribute_id' => 1, 'value' => 'Red', 'color_code' => '#ff0000', 'position' => 0, 'status' => true]);

        $products = $variants = $pivots = $specifications = [];
        for ($id = 1; $id <= 2000; $id++) {
            $products[] = ['id' => $id, 'category_id' => 1, 'status' => $id <= 1000];
            $specifications[] = ['product_id' => $id, 'attribute_value_id' => 1];
            for ($offset = 0; $offset < 2; $offset++) {
                $variantId = $id * 2 + $offset;
                $variants[] = ['id' => $variantId, 'product_id' => $id, 'status' => true];
                $pivots[] = ['product_variant_id' => $variantId, 'attribute_value_id' => 1];
            }
        }
        foreach (['products' => $products, 'product_variants' => $variants,
            'attribute_value_product_variant' => $pivots, 'attribute_value_product' => $specifications] as $table => $rows) {
            foreach (array_chunk($rows, 200) as $chunk) {
                DB::table($table)->insert($chunk);
            }
        }

        $method = new ReflectionMethod(FrontController::class, 'availableAttributeFilters');
        $controller = app(FrontController::class);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $filters = $method->invoke($controller, [1], [1]);
        $this->assertCount(2, DB::getQueryLog());
        $this->assertStringContainsString('COUNT(DISTINCT product_id)', DB::getQueryLog()[1]['query']);
        $this->assertCount(1, $filters);
        $this->assertCount(1, $filters[0]['values']);
        $this->assertSame(1000, $filters[0]['values'][0]['product_count']);

        DB::flushQueryLog();
        $this->assertSame($filters, $method->invoke($controller, [1], [1]));
        $this->assertCount(1, DB::getQueryLog());
        $this->assertStringContainsString('content_cache_versions', DB::getQueryLog()[0]['query']);
        DB::disableQueryLog();

        // A bulk import does not emit model events, so it clears explicitly.
        DB::table('products')->where('id', 1001)->update(['status' => true]);
        ContentCache::forget('shop-filters');
        $updated = $method->invoke($controller, [1], [1]);
        $this->assertSame(1001, $updated[0]['values'][0]['product_count']);
    }
}
