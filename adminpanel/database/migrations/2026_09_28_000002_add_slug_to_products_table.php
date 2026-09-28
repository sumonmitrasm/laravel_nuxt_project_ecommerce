<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'slug')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('slug')->nullable()->after('product_name');
            });
        }

        $used = [];
        DB::table('products')->orderBy('id')->select('id', 'product_name', 'slug')->each(function ($product) use (&$used) {
            $base = Str::slug($product->product_name) ?: 'product';
            $slug = $base;
            $number = 2;
            while (isset($used[$slug])) $slug = $base.'-'.$number++;
            $used[$slug] = true;
            if ($product->slug !== $slug) DB::table('products')->where('id', $product->id)->update(['slug' => $slug]);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
