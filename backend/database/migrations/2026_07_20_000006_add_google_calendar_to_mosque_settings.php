<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->string('google_calendar_ics_url', 1000)->nullable()->after('logo_url');
        });
    }

    public function down(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->dropColumn('google_calendar_ics_url');
        });
    }
};
