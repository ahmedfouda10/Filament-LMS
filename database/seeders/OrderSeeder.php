<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $courses = Course::where('is_published', true)->with('instructor')->get();
        $paymentMethods = ['credit_card', 'mobile_wallet', 'bank_transfer', 'installment'];

        if ($students->isEmpty() || $courses->isEmpty()) return;

        for ($i = 0; $i < 12; $i++) {
            $student = $students->random();
            $itemCount = rand(1, 3);
            $orderCourses = $courses->random(min($itemCount, $courses->count()));

            $subtotal = $orderCourses->sum('price');
            $discount = 0;
            $total = $subtotal - $discount;

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => $student->id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => collect($paymentMethods)->random(),
                'status' => 'completed',
                'paid_at' => now()->subDays(rand(1, 90)),
            ]);

            foreach ($orderCourses as $course) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'course_id' => $course->id,
                    'title' => $course->title,
                    'instructor_name' => $course->instructor?->name,
                    'price' => $course->price,
                    'original_price' => $course->original_price,
                ]);
            }
        }
    }
}
