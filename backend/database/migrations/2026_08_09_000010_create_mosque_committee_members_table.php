<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mosque_committee_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mosque_setting_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('position', 150);
            $table->string('photo_path')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('show_phone_publicly')->default(false);
            $table->boolean('show_email_publicly')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['mosque_setting_id', 'is_active', 'display_order'], 'committee_public_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mosque_committee_members');
    }
};
