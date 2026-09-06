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
        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 30)->default('Home');
            $table->string('recipient_name', 100);
            $table->string('phone', 20);
            $table->string('alternative_phone', 20)->nullable();
            $table->string('division', 100);
            $table->string('district', 100);
            $table->string('upazila', 100);
            $table->string('area', 150)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->text('address_line');
            $table->boolean('is_default')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'is_default']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
