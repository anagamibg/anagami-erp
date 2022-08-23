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
        if (!Schema::hasColumn('global_settings', 'custom_css')) {
            Schema::table('global_settings', function (Blueprint $table) {
                $table->longText('custom_css')->nullable()->after('last_license_verified_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('global_settings', 'custom_css')) {
            Schema::table('global_settings', function (Blueprint $table) {
                $table->dropColumn('custom_css');
            });
        }
    }
};
