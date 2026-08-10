<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('rotation_duration_seconds')->nullable()->after('rotation_duration_minutes');
        });

        DB::table('mosque_settings')->update([
            'rotation_duration_seconds' => DB::raw('GREATEST(rotation_duration_minutes * 60, 1)'),
        ]);

        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('rotation_duration_seconds')->default(180)->change();
            $table->dropColumn('rotation_duration_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->unsignedSmallInteger('rotation_duration_minutes')->default(3)->after('rotation_views');
        });

        DB::table('mosque_settings')->update([
            'rotation_duration_minutes' => DB::raw('GREATEST(ROUND(rotation_duration_seconds / 60), 1)'),
        ]);

        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->dropColumn('rotation_duration_seconds');
        });
    }
};
