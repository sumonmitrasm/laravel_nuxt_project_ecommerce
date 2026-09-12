<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::table('product_variants', fn(Blueprint $t) => $t->unsignedInteger('low_stock_threshold')->default(5)->after('stock'));
  Schema::create('stock_adjustments', function(Blueprint $t){$t->id();$t->foreignId('product_variant_id')->constrained()->cascadeOnDelete();$t->integer('change_quantity');$t->unsignedInteger('stock_before');$t->unsignedInteger('stock_after');$t->string('reason',100);$t->nullableMorphs('reference');$t->string('changed_by_type',20)->default('system');$t->unsignedBigInteger('changed_by_id')->nullable();$t->text('note')->nullable();$t->timestamps();$t->index(['product_variant_id','created_at']);});
  Schema::table('admin_notifications', function(Blueprint $t){$t->foreignId('product_variant_id')->nullable()->after('order_id')->constrained()->nullOnDelete();});
 }
 public function down(): void { Schema::table('admin_notifications',fn(Blueprint $t)=>$t->dropConstrainedForeignId('product_variant_id'));Schema::dropIfExists('stock_adjustments');Schema::table('product_variants',fn(Blueprint $t)=>$t->dropColumn('low_stock_threshold')); }
};