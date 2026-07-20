<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->string('screen_theme', 20)->default('emerald')->after('silent_mode_minutes');
            $table->string('time_format', 5)->default('24h')->after('screen_theme');
        });
    }

    public function down(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->dropColumn(['screen_theme', 'time_format']);
        });
    }
};
