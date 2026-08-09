<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->string('public_slug')->nullable()->unique()->after('public_id');
            $table->string('donation_qr_url')->nullable()->after('logo_url');
            $table->json('committee')->nullable()->after('address');
        });

        DB::table('mosque_settings')->orderBy('id')->each(function (object $masjid): void {
            $base = Str::slug($masjid->name) ?: 'masjid';
            $slug = $base;
            $suffix = 2;

            while (DB::table('mosque_settings')->where('public_slug', $slug)->exists()) {
                $slug = $base.'-'.$suffix++;
            }

            DB::table('mosque_settings')->where('id', $masjid->id)->update(['public_slug' => $slug]);
        });

        Schema::table('mosque_settings', fn (Blueprint $table) => $table->string('public_slug')->nullable(false)->change());

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('mosque_setting_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('mosque_setting_id'));
        Schema::table('mosque_settings', fn (Blueprint $table) => $table->dropColumn(['public_slug', 'donation_qr_url', 'committee']));
    }
};
