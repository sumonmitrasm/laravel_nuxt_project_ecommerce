<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $colorAttributeId = DB::table('attributes')->where('slug', 'color')->value('id');

        if (! $colorAttributeId) {
            $colorAttributeId = DB::table('attributes')->insertGetId([
                'name' => 'Color', 'slug' => 'color', 'type' => 'color', 'position' => 2,
                'status' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        if (Schema::hasTable('colors')) {
            foreach (DB::table('colors')->orderBy('id')->get() as $position => $color) {
                DB::table('attribute_values')->updateOrInsert(
                    ['attribute_id' => $colorAttributeId, 'value' => $color->name],
                    ['color_code' => $color->color_code, 'position' => $position + 1,
                        'status' => (bool) $color->status, 'updated_at' => $now, 'created_at' => $now]
                );
            }
        }

        if (Schema::hasColumn('products', 'product_color')) {
            foreach (DB::table('products')->whereNotNull('product_color')->where('product_color', '!=', '')->get(['id', 'product_code', 'product_color']) as $product) {
                $valueId = DB::table('attribute_values')
                    ->where('attribute_id', $colorAttributeId)
                    ->where('color_code', $product->product_color)
                    ->value('id');

                if (! $valueId) {
                    $valueId = DB::table('attribute_values')->insertGetId([
                        'attribute_id' => $colorAttributeId,
                        'value' => $product->product_color,
                        'color_code' => $product->product_color,
                        'position' => 999,
                        'status' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $variantIds = DB::table('product_variants')->where('product_id', $product->id)->pluck('id');
                if ($variantIds->isEmpty()) {
                    $baseSku = $product->product_code.'-DEFAULT';
                    $sku = $baseSku;
                    $suffix = 1;
                    while (DB::table('product_variants')->where('sku', $sku)->exists()) $sku = $baseSku.'-'.$suffix++;
                    $variantIds = collect([DB::table('product_variants')->insertGetId([
                        'product_id' => $product->id, 'sku' => $sku, 'price' => null,
                        'stock' => 0, 'status' => true, 'created_at' => $now, 'updated_at' => $now,
                    ])]);
                }

                foreach ($variantIds as $variantId) {
                    $hasColor = DB::table('attribute_value_product_variant as pivot')
                        ->join('attribute_values as value', 'value.id', '=', 'pivot.attribute_value_id')
                        ->where('pivot.product_variant_id', $variantId)
                        ->where('value.attribute_id', $colorAttributeId)
                        ->exists();
                    if (! $hasColor) {
                        DB::table('attribute_value_product_variant')->insertOrIgnore([
                            'product_variant_id' => $variantId,
                            'attribute_value_id' => $valueId,
                        ]);
                    }
                }
            }

            Schema::table('products', fn (Blueprint $table) => $table->dropColumn('product_color'));
        }

        if (Schema::hasTable('admin_roles')) DB::table('admin_roles')->where('module', 'color')->delete();
        Schema::dropIfExists('colors');
    }

    public function down(): void
    {
        // Legacy duplicate color storage is intentionally not recreated.
    }
};
