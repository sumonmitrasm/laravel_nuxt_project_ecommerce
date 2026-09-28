<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('settings', 'meta_image')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('meta_image')->nullable()->after('meta_description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('settings', 'meta_image')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->dropColumn('meta_image');
            });
        }
    }
};
