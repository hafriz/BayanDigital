<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->boolean('prayer_alerts_enabled')->default(true)->after('iqamah_minutes');
            $table->unsignedSmallInteger('pre_prayer_beep_minutes')->default(5)->after('prayer_alerts_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->dropColumn(['prayer_alerts_enabled', 'pre_prayer_beep_minutes']);
        });
    }
};
