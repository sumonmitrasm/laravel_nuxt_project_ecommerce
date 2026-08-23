<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('coupon_customer_rules');

        Schema::table('coupons', function (Blueprint $table) {
            $table->decimal('minimum_lifetime_spend', 12, 2)->nullable()->after('minimum_order_amount');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('minimum_lifetime_spend');
        });

        Schema::create('coupon_customer_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('rule_type', 50);
            $table->string('operator', 10)->default('gte');
            $table->decimal('value', 12, 2);
            $table->timestamps();
            $table->index(['rule_type', 'operator']);
        });
    }
};
