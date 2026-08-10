<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->string('language', 5)->default('ms')->after('clock_style');
        });
    }

    public function down(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->dropColumn('language');
        });
    }
};
