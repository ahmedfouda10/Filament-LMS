<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Enrollment;
use App\Models\InstallmentPlan;
use App\Models\InstructorTransaction;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function process(CheckoutRequest $request): JsonResponse
    {
        $user = $request->user();

        $order = DB::transaction(function () use ($user, $request) {
            // 1. Get user's cart with items
            $cart = Cart::where('user_id', $user->id)
                ->with('items.course.instructor', 'promoCode')
                ->first();

            // 2. Validate cart is not empty
            if (!$cart || $cart->items->isEmpty()) {
                abort(422, 'Your cart is empty.');
            }

            // 3. Validate user is not already enrolled in any cart courses
            $courseIds = $cart->items->pluck('course_id')->toArray();
            $existingEnrollments = Enrollment::where('user_id', $user->id)
                ->whereIn('course_id', $courseIds)
                ->exists();

            if ($existingEnrollments) {
                abort(422, 'You are already enrolled in one or more courses in your cart.');
            }

            // 4. Calculate subtotal
            $subtotal = $cart->items->sum('price');

            // 5. Apply promo code if exists
            $discount = 0;
            if ($cart->promoCode) {
                $promo = $cart->promoCode;
                $discount = round($subtotal * ($promo->discount_percentage / 100), 2);
                $promo->increment('used_count');
            }

            // 6. Calculate total
            $total = max(0, $subtotal - $discount);

            // 7. Generate order_number
            $orderNumber = 'ORD-' . strtoupper(Str::random(8));

            // 8. Create Order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'status' => 'completed',
                'payment_method' => $request->input('payment_method', 'card'),
                'paid_at' => now(),
                'promo_code_id' => $cart->promo_code_id,
            ]);

            // 8b. Create OrderItems with snapshot data
            foreach ($cart->items as $cartItem) {
                $course = $cartItem->course;
                $instructorName = $course->instructor->name ?? 'Unknown';

                OrderItem::create([
                    'order_id' => $order->id,
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'instructor_name' => $instructorName,
                    'price' => $cartItem->price,
                ]);

                // 10. Create Enrollment
                Enrollment::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'progress_percentage' => 0,
                    'enrolled_at' => now(),
                ]);

                // 11. Create InstructorTransaction for each course with instructor
                if ($course->instructor_id) {
                    $amount = $cartItem->price;
                    $platformFee = round($amount * 0.20, 2);
                    $netAmount = round($amount - $platformFee, 2);

                    InstructorTransaction::create([
                        'instructor_id' => $course->instructor_id,
                        'order_id' => $order->id,
                        'course_id' => $course->id,
                        'type' => 'sale',
                        'amount' => $amount,
                        'platform_fee' => $platformFee,
                        'net_amount' => $netAmount,
                        'status' => 'pending',
                        'transaction_number' => 'TXN-' . strtoupper(Str::random(5)),
                    ]);
                }
            }

            // 11b. Create installment plan if payment_method is installment
            if ($request->input('payment_method') === 'installment') {
                $installmentMonths = (int) $request->input('installment_months', 6);
                $installmentMonths = in_array($installmentMonths, [3, 6, 12]) ? $installmentMonths : 6;

                InstallmentPlan::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'provider' => $request->input('installment_provider', 'valu'),
                    'total_amount' => $total,
                    'monthly_amount' => round($total / $installmentMonths, 2),
                    'months' => $installmentMonths,
                    'paid_months' => 0,
                    'status' => 'active',
                    'next_payment_date' => now()->addMonth()->toDateString(),
                ]);
            }

            // 12. Clear cart
            $cart->items()->delete();
            $cart->update(['promo_code_id' => null]);

            return $order;
        });

        // 13. Return OrderResource with items
        $order->load('items.course');

        return response()->json([
            'data' => new OrderResource($order),
            'message' => 'Checkout completed successfully.',
        ], 201);
    }
}
