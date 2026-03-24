<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained();
            $table->string('transaction_id', 255)->nullable();
            $table->string('payment_method', 100);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('EGP');
            $table->enum('status', ['success', 'failed', 'pending', 'refunded']);
            $table->json('gateway_response')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
