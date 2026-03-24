<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('provider', ['google', 'facebook']);
            $table->string('provider_id', 255);
            $table->text('provider_token')->nullable();
            $table->text('provider_refresh_token')->nullable();
            $table->string('avatar_url', 500)->nullable();
            $table->timestamps();
            $table->unique(['provider', 'provider_id']);
            $table->index('user_id');
        });
    }

    public function down(): void { Schema::dropIfExists('social_accounts'); }
};
