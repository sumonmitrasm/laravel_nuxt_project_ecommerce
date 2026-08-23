<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            // Coupon admin
            $table->foreignId('created_by_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('code')->unique();
            // Now Just code-based coupon
            $table->string('apply_method')->default('code');

            // all, products, categories, brands
            $table->string('scope')->default('all');

            // fixed, percentage, free_shipping
            $table->string('discount_type');

            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('maximum_discount', 12, 2)->nullable();
            $table->decimal('minimum_order_amount', 12, 2)->default(0);

            // null unlimited
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_user')->nullable();

            $table->boolean('exclude_discounted_products')->default(0);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->boolean('is_active')->default(1);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'starts_at', 'expires_at']);
            $table->index(['scope', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
