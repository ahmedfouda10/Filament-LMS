<?php

namespace Database\Seeders;

use App\Models\InstallmentPlan;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InstallmentPlanSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::where('status', 'completed')->limit(4)->get();

        $configs = [
            ['provider' => 'valu', 'months' => 6, 'status' => 'active', 'paid_months' => 2],
            ['provider' => 'valu', 'months' => 12, 'status' => 'active', 'paid_months' => 4],
            ['provider' => 'sympl', 'months' => 3, 'status' => 'completed', 'paid_months' => 3],
            ['provider' => 'sympl', 'months' => 6, 'status' => 'defaulted', 'paid_months' => 1],
        ];

        foreach ($orders as $i => $order) {
            if (!isset($configs[$i])) break;
            $c = $configs[$i];

            InstallmentPlan::firstOrCreate(['order_id' => $order->id], [
                'user_id' => $order->user_id,
                'provider' => $c['provider'],
                'total_amount' => $order->total,
                'monthly_amount' => round($order->total / $c['months'], 2),
                'months' => $c['months'],
                'paid_months' => $c['paid_months'],
                'status' => $c['status'],
                'next_payment_date' => $c['status'] === 'active' ? now()->addMonth() : null,
                'provider_reference' => strtoupper($c['provider']) . '-' . strtoupper(Str::random(8)),
            ]);
        }
    }
}
