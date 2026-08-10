<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('countdown_beep_seconds')->default(10)
                ->after('pre_prayer_beep_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->dropColumn('countdown_beep_seconds');
        });
    }
};
