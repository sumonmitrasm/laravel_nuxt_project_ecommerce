<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('products_attributes');
    }

    public function down(): void
    {
        // The legacy table is intentionally not recreated. Product variants now use
        // product_variants and attribute_value_product_variant.
    }
};
