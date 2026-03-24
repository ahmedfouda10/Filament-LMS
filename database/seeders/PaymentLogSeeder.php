<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\PaymentLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PaymentLogSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Order::where('status', 'completed')->get() as $order) {
            PaymentLog::firstOrCreate(
                ['order_id' => $order->id, 'status' => 'success'],
                ['transaction_id' => 'TXN_' . strtoupper(Str::random(12)), 'payment_method' => $order->payment_method ?? 'credit_card', 'amount' => $order->total, 'currency' => 'EGP', 'gateway_response' => ['success' => true], 'ip_address' => '41.34.' . rand(1, 255) . '.' . rand(1, 255), 'created_at' => $order->paid_at ?? $order->created_at]
            );
        }
        for ($i = 0; $i < 3; $i++) {
            PaymentLog::create(['transaction_id' => 'TXN_FAIL_' . strtoupper(Str::random(8)), 'payment_method' => ['credit_card', 'mobile_wallet', 'bank_transfer'][rand(0, 2)], 'amount' => rand(500, 3000), 'currency' => 'EGP', 'status' => 'failed', 'gateway_response' => ['success' => false, 'message' => 'Insufficient funds'], 'ip_address' => '41.34.' . rand(1, 255) . '.' . rand(1, 255), 'created_at' => now()->subDays(rand(1, 30))]);
        }
    }
}
