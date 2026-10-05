<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_cache_versions', function (Blueprint $table) {
            $table->string('group')->primary();
            $table->uuid('version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_cache_versions');
    }
};
