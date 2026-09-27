<?php

namespace Tests\Feature;

use App\Http\Controllers\ProductController;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use Tests\TestCase;

class AdminPerformanceTest extends TestCase
{
    use DatabaseMigrations;

    protected function migrateFreshUsing(): array
    {
        // Only the catalog tables are needed; legacy cart migrations fail on SQLite.
        return ['--path' => [
            'database/migrations/2026_07_28_091827_create_sections_table.php',
            'database/migrations/2026_07_29_100009_create_categories_table.php',
            'database/migrations/2026_08_16_130000_create_catalog_variant_tables.php',
            'database/migrations/2026_08_16_140000_create_category_attribute_table.php',
            'database/migrations/2026_08_20_120000_separate_variant_and_product_attributes.php',
        ]];
    }

    public function test_public_cache_clear_route_is_not_registered(): void
    {
        foreach (Route::getRoutes() as $route) {
            $this->assertNotSame('clear-cache', $route->uri());
        }
    }

    public function test_category_attributes_keep_inheritance_with_only_two_queries(): void
    {
        $section = DB::table('sections')->insertGetId(['name' => 'Test']);
        $parent = DB::table('categories')->insertGetId([
            'section_id' => $section, 'category_name' => 'Parent', 'status' => 0,
        ]);
        $child = DB::table('categories')->insertGetId([
            'section_id' => $section, 'category_name' => 'Child', 'parent_id' => $parent,
        ]);
        $own = DB::table('categories')->insertGetId([
            'section_id' => $section, 'category_name' => 'Own attributes', 'parent_id' => $parent,
        ]);
        $empty = DB::table('categories')->insertGetId([
            'section_id' => $section, 'category_name' => 'Empty',
        ]);
        $attribute = DB::table('attributes')->insertGetId(['name' => 'Test Size', 'slug' => 'test-size']);
        DB::table('category_attribute')->insert([
            ['category_id' => $parent, 'attribute_id' => $attribute, 'is_required' => true],
            ['category_id' => $own, 'attribute_id' => $attribute, 'is_required' => false],
        ]);

        $categories = Category::whereIn('id', [$child, $own, $empty])->get();
        $controller = app(ProductController::class);
        DB::enableQueryLog();
        DB::flushQueryLog();
        $map = (new ReflectionMethod($controller, 'categoryAttributeMap'))->invoke($controller, $categories);
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertCount(2, $queries);
        $this->assertTrue($map[$child][$attribute]['is_required']);
        $this->assertFalse($map[$own][$attribute]['is_required']);
        $this->assertSame([], $map[$empty]);
    }
}
