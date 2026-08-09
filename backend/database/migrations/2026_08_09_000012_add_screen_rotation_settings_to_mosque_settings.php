<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->boolean('screen_rotation_enabled')->default(false)->after('wake_before_subuh_minutes');
            $table->json('rotation_views')->nullable()->after('screen_rotation_enabled');
            $table->unsignedSmallInteger('rotation_duration_minutes')->default(3)->after('rotation_views');
            $table->unsignedSmallInteger('rotation_near_prayer_minutes')->default(30)->after('rotation_duration_minutes');
            $table->string('clock_style', 20)->default('standard')->after('rotation_near_prayer_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->dropColumn([
                'screen_rotation_enabled',
                'rotation_views',
                'rotation_duration_minutes',
                'rotation_near_prayer_minutes',
                'clock_style',
            ]);
        });
    }
};
