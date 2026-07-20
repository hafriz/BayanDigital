<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->boolean('screen_sleep_enabled')->default(false)->after('google_calendar_ics_url');
            $table->time('screen_sleep_time')->default('22:00:00')->after('screen_sleep_enabled');
            $table->string('screen_wake_mode', 20)->default('fixed')->after('screen_sleep_time');
            $table->time('screen_wake_time')->default('05:00:00')->after('screen_wake_mode');
            $table->unsignedSmallInteger('wake_before_subuh_minutes')->default(30)->after('screen_wake_time');
        });
    }

    public function down(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->dropColumn([
                'screen_sleep_enabled',
                'screen_sleep_time',
                'screen_wake_mode',
                'screen_wake_time',
                'wake_before_subuh_minutes',
            ]);
        });
    }
};
