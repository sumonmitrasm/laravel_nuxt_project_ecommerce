<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category_attribute', function (Blueprint $table) {
            $table->boolean('is_variant')->default(false)->after('attribute_id');
            $table->index(['category_id', 'is_variant']);
        });

        Schema::create('attribute_value_product', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $table->primary(['product_id', 'attribute_value_id'], 'product_attribute_value_primary');
        });

        $variantAttributeIds = DB::table('attributes')
            ->whereIn('slug', ['color', 'size', 'storage', 'ram', 'memory', 'capacity'])
            ->pluck('id');

        DB::table('category_attribute')->whereIn('attribute_id', $variantAttributeIds)->update(['is_variant' => true]);

        $specificationRows = DB::table('attribute_value_product_variant as pivot')
            ->join('product_variants as variant', 'variant.id', '=', 'pivot.product_variant_id')
            ->join('attribute_values as value', 'value.id', '=', 'pivot.attribute_value_id')
            ->whereNotIn('value.attribute_id', $variantAttributeIds)
            ->get(['variant.product_id', 'pivot.attribute_value_id']);

        foreach ($specificationRows->unique(fn ($row) => $row->product_id.'-'.$row->attribute_value_id) as $row) {
            DB::table('attribute_value_product')->insertOrIgnore([
                'product_id' => $row->product_id,
                'attribute_value_id' => $row->attribute_value_id,
            ]);
        }

        DB::table('attribute_value_product_variant')
            ->whereIn('attribute_value_id', DB::table('attribute_values')->whereNotIn('attribute_id', $variantAttributeIds)->select('id'))
            ->delete();
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_value_product');
        Schema::table('category_attribute', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'is_variant']);
            $table->dropColumn('is_variant');
        });
    }
};
