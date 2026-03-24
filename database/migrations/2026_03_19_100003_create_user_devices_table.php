<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_name', 255);
            $table->string('browser', 255)->nullable();
            $table->string('ip_address', 45);
            $table->string('location', 255)->nullable();
            $table->timestamp('last_active_at');
            $table->boolean('is_current')->default(false);
            $table->string('token', 255)->unique()->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
