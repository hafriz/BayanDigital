<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->string('screen_sleep_mode', 20)->default('fixed')->after('screen_sleep_enabled');
            $table->unsignedSmallInteger('sleep_after_isyak_minutes')->default(30)->after('screen_sleep_time');
        });
    }

    public function down(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->dropColumn(['screen_sleep_mode', 'sleep_after_isyak_minutes']);
        });
    }
};
