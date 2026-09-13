<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title')->nullable();
            $table->string('hero_highlight')->nullable();
            $table->text('hero_text')->nullable();
            $table->string('intro_title')->nullable();
            $table->text('intro_text')->nullable();
            $table->string('promise_title')->nullable();
            $table->text('promise_text')->nullable();
            $table->string('cta_title')->nullable();
            $table->unsignedInteger('return_days')->default(7);
            $table->string('value_1_title')->nullable();
            $table->text('value_1_text')->nullable();
            $table->string('value_2_title')->nullable();
            $table->text('value_2_text')->nullable();
            $table->string('value_3_title')->nullable();
            $table->text('value_3_text')->nullable();
            $table->string('value_4_title')->nullable();
            $table->text('value_4_text')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_pages');
    }
};