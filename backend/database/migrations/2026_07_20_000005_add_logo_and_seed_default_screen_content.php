<?php

use App\Models\MosqueSetting;
use App\Services\DefaultScreenContentService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->string('logo_url')->nullable()->after('time_format');
        });

        MosqueSetting::query()
            ->whereRaw('LOWER(name) LIKE ?', ['%surau abdullah sol%'])
            ->each(fn (MosqueSetting $masjid) => app(DefaultScreenContentService::class)->seed($masjid));
    }

    public function down(): void
    {
        Schema::table('mosque_settings', function (Blueprint $table) {
            $table->dropColumn('logo_url');
        });
    }
};
