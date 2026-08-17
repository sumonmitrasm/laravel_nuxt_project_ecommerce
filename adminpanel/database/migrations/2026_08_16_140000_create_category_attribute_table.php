<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_attribute', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->boolean('is_filterable')->default(true);
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->primary(['category_id', 'attribute_id']);
            $table->index(['category_id', 'is_filterable']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_attribute');
    }
};
