<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('manual');
            $table->string('status')->default('pending');
            $table->string('database_file')->nullable();
            $table->string('storage_file')->nullable();
            $table->string('google_drive_id')->nullable();
            $table->string('google_drive_link')->nullable();
            $table->bigInteger('database_size')->default(0);
            $table->bigInteger('storage_size')->default(0);
            $table->bigInteger('total_size')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
