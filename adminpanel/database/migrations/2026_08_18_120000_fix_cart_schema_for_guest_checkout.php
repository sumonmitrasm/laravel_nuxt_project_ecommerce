<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'session_id']);
            $table->foreignId('user_id')->nullable()->change();
            $table->uuid('guest_token')->nullable()->change();
            $table->unique('guest_token', 'carts_guest_token_unique');
        });

        Schema::table('cart_items', function (Blueprint $table) {
            $table->foreignId('product_variant_id')->nullable()->change();
            $table->unsignedInteger('quantity')->default(1)->change();
            $table->unique(['cart_id', 'product_id', 'product_variant_id'], 'cart_item_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('cart_item_unique');
            $table->foreignId('product_variant_id')->nullable(false)->change();
            $table->integer('quantity')->default(0)->change();
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->dropUnique('carts_guest_token_unique');
            $table->string('session_id')->nullable();
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete();
        });
    }
};
