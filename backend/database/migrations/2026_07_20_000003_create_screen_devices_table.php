<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('screen_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mosque_setting_id')->constrained()->cascadeOnDelete();
            $table->uuid('request_id')->unique();
            $table->string('pairing_code', 6);
            $table->string('device_name')->nullable();
            $table->string('status', 20)->default('pending')->index();
            $table->text('device_token')->nullable();
            $table->char('token_hash', 64)->nullable()->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screen_devices');
    }
};
