<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('global_settings', 'custom_js')) {
            Schema::table('global_settings', function (Blueprint $table) {
                $table->longText('custom_js')->nullable()->after('custom_css');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('global_settings', 'custom_js')) {
            Schema::table('global_settings', function (Blueprint $table) {
                $table->dropColumn('custom_js');
            });
        }
    }
};
