<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('home_sliders', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow', 100)->nullable();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->string('offer_label', 50)->nullable();
            $table->string('offer_text', 50)->nullable();
            $table->string('offer_note', 100)->nullable();
            $table->string('button_text', 50)->nullable();
            $table->string('button_url', 500)->nullable();
            $table->string('image')->nullable();
            $table->string('background_color', 20)->default('#f4f6ed');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('home_sliders'); }
};
