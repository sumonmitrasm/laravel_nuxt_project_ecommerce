<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Older databases used product_id and session_id directly on carts.
        // New installations already use the cart_items table, so no change is needed.
        if (! Schema::hasColumn('carts', 'product_id')) {
            return;
        }

        Schema::table('carts', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'session_id']);
            $table->foreignId('user_id')->nullable()->change();
            $table->uuid('guest_token')->nullable()->change();
            $table->unique('guest_token', 'carts_guest_token_unique');
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
