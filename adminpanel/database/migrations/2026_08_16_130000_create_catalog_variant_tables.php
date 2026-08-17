<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['text', 'color'])->default('text');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->string('value');
            $table->string('color_code', 20)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->unique(['attribute_id', 'value']);
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->decimal('price', 12, 2)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->index(['product_id', 'status']);
        });

        Schema::create('attribute_value_product_variant', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();
            $table->primary(['product_variant_id', 'attribute_value_id'], 'variant_attribute_value_primary');
        });

        $now = now();
        $sizeId = DB::table('attributes')->insertGetId([
            'name' => 'Size', 'slug' => 'size', 'type' => 'text', 'position' => 1,
            'status' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $colorId = DB::table('attributes')->insertGetId([
            'name' => 'Color', 'slug' => 'color', 'type' => 'color', 'position' => 2,
            'status' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);

        foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL'] as $position => $size) {
            DB::table('attribute_values')->insert([
                'attribute_id' => $sizeId, 'value' => $size, 'position' => $position + 1,
                'status' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_value_product_variant');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('attribute_values');
        Schema::dropIfExists('attributes');
    }
};
