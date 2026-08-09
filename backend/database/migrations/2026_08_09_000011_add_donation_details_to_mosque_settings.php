<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->string('donation_qr_image')->nullable()->after('logo_url');
            $table->string('donation_caption', 200)->nullable()->after('donation_qr_image');
            $table->string('donation_account', 150)->nullable()->after('donation_caption');
        });
    }

    public function down(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->dropColumn(['donation_qr_image', 'donation_caption', 'donation_account']);
        });
    }
};
