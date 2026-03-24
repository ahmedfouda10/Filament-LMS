<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('instructor_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 50)->unique();
            $table->foreignId('instructor_id')->constrained('users');
            $table->enum('type', ['sale', 'payout']);
            $table->foreignId('order_id')->nullable()->constrained('orders');
            $table->foreignId('course_id')->nullable()->constrained('courses');
            $table->decimal('amount', 10, 2);
            $table->decimal('platform_fee', 10, 2)->default(0.00);
            $table->decimal('net_amount', 10, 2);
            $table->enum('status', ['pending', 'cleared', 'completed'])->default('pending');
            $table->string('payout_method', 100)->nullable();
            $table->timestamps();

            $table->index(['instructor_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_transactions');
    }
};
