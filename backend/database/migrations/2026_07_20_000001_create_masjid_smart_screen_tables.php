<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mosque_settings', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 20)->unique();
            $table->string('type', 10)->default('masjid');
            $table->string('name');
            $table->string('zone_code', 10)->index();
            $table->string('status', 20)->default('pending')->index();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('address')->nullable();
            $table->json('prayer_offsets')->nullable();
            $table->json('iqamah_minutes')->nullable();
            $table->unsignedSmallInteger('silent_mode_minutes')->default(15);
            $table->timestamps();
        });

        Schema::create('prayer_times', function (Blueprint $table) {
            $table->id();
            $table->string('zone_code', 10)->index();
            $table->date('prayer_date');
            $table->string('hijri_date')->nullable();
            $table->json('times');
            $table->timestamp('fetched_at');
            $table->timestamps();
            $table->unique(['zone_code', 'prayer_date']);
        });

        Schema::create('screen_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mosque_setting_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type', 30)->index();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('media_path')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screen_contents');
        Schema::dropIfExists('prayer_times');
        Schema::dropIfExists('mosque_settings');
    }
};
