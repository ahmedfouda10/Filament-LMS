<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('provider', ['valu', 'sympl']);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('monthly_amount', 10, 2);
            $table->unsignedTinyInteger('months');
            $table->unsignedTinyInteger('paid_months')->default(0);
            $table->enum('status', ['active', 'completed', 'defaulted'])->default('active');
            $table->date('next_payment_date')->nullable();
            $table->string('provider_reference', 255)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void { Schema::dropIfExists('installment_plans'); }
};
