<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('rotation_after_prayer_minutes')->default(15)
                ->after('rotation_near_prayer_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->dropColumn('rotation_after_prayer_minutes');
        });
    }
};
