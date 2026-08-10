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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            // Foreign Keys
            $table->foreignId('section_id')->nullable()->constrained('sections')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('cascade');
            $table->integer('vendor_id')->nullable();
            $table->string('admin_type')->nullable();
            // Product Details
            $table->string('product_name');
            $table->string('product_code')->unique();
            $table->string('product_color')->nullable();
            // Prices & Weight
            $table->decimal('product_price', 10, 2)->default(0);
            $table->decimal('product_discount', 8, 2)->default(0);
            $table->decimal('product_weight', 8, 2)->nullable();
            // Media & Content
            $table->string('product_image')->nullable();
            $table->string('product_video')->nullable();
            $table->text('description')->nullable();
            // SEO Fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_image')->nullable();
            $table->string('url_structure')->nullable();
            $table->string('heading_tag')->nullable();
            $table->text('schema_markup')->nullable();
            $table->text('meta_data')->nullable();
            $table->string('meta_robot')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('canonical_tag')->nullable();
            // Status & Flags
            $table->enum('is_featured', ['No', 'Yes'])->default('No');
            $table->tinyInteger('status')->default(1)->comment('1=Active, 0=Inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
