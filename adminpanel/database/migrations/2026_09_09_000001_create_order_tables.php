<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 40)->unique();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shipping_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shipping_method_name', 100);
            $table->string('payment_method', 30);
            $table->string('payment_status', 30)->default('unpaid');
            $table->string('order_status', 30)->default('pending');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('shipping_charge', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->string('currency', 3)->default('BDT');
            $table->text('customer_note')->nullable();
            $table->timestamp('placed_at');
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index(['order_status', 'payment_status']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->string('product_code')->nullable();
            $table->string('sku')->nullable();
            $table->string('image')->nullable();
            $table->json('options')->nullable();
            $table->unsignedInteger('quantity');
            $table->decimal('regular_price', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();
        });

        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_address_id')->nullable()->constrained()->nullOnDelete();
            $table->string('label', 30)->nullable();
            $table->string('recipient_name', 100);
            $table->string('phone', 20);
            $table->string('alternative_phone', 20)->nullable();
            $table->unsignedBigInteger('division_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('upazila_id')->nullable();
            $table->string('division_name', 100)->nullable();
            $table->string('district_name', 100)->nullable();
            $table->string('upazila_name', 100)->nullable();
            $table->string('area', 150)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->text('address_line');
            $table->timestamps();
        });

        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('method', 30);
            $table->string('transaction_id', 150)->nullable()->unique();
            $table->string('gateway_reference', 150)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('BDT');
            $table->string('status', 30)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'status']);
        });

        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30);
            $table->text('note')->nullable();
            $table->string('changed_by_type', 20)->default('system');
            $table->unsignedBigInteger('changed_by_id')->nullable();
            $table->timestamps();
            $table->index(['order_id', 'created_at']);
        });

        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
        });
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_payments');
        Schema::dropIfExists('order_addresses');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
