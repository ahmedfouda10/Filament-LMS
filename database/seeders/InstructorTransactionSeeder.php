<?php

namespace Database\Seeders;

use App\Models\InstructorTransaction;
use App\Models\Order;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InstructorTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::where('status', 'completed')->with('items.course')->get();
        $statuses = ['pending', 'cleared', 'completed'];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                if (!$item->course || !$item->course->instructor_id) continue;

                $amount = $item->price;
                $platformFee = round($amount * 0.20, 2);
                $netAmount = round($amount - $platformFee, 2);

                InstructorTransaction::create([
                    'transaction_number' => 'TXN-' . strtoupper(Str::random(5)),
                    'instructor_id' => $item->course->instructor_id,
                    'type' => 'sale',
                    'order_id' => $order->id,
                    'course_id' => $item->course_id,
                    'amount' => $amount,
                    'platform_fee' => $platformFee,
                    'net_amount' => $netAmount,
                    'status' => collect($statuses)->random(),
                ]);
            }
        }
    }
}
