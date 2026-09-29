<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_variants', fn (Blueprint $table) => $table->decimal('cost_price', 14, 2)->default(0)->after('price'));
        Schema::table('order_items', fn (Blueprint $table) => $table->decimal('cost_price', 14, 2)->default(0)->after('unit_price'));
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('category', 40);
            $table->string('title', 150);
            $table->decimal('amount', 14, 2);
            $table->date('expense_date');
            $table->string('note', 500)->nullable();
            $table->timestamps();
            $table->index(['expense_date', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('cost_price'));
        Schema::table('product_variants', fn (Blueprint $table) => $table->dropColumn('cost_price'));
    }
};
