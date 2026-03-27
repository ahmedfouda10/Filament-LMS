<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Enrollment;
use App\Models\InstallmentPlan;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\PaymobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function process(CheckoutRequest $request): JsonResponse
    {
        $user = $request->user();

        try {
            $result = DB::transaction(function () use ($user, $request) {
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

                // 5. Apply promo code if exists (do NOT increment used_count yet - wait for payment success)
                $discount = 0;
                if ($cart->promoCode) {
                    $promo = $cart->promoCode;
                    $discount = round($subtotal * ($promo->discount_percentage / 100), 2);
                }

                // 6. Calculate total
                $total = max(0, $subtotal - $discount);

                // 7. Generate order_number
                $orderNumber = 'ORD-' . strtoupper(Str::random(8));

                // 8. Create Order with status = 'pending' (NOT 'completed')
                $order = Order::create([
                    'user_id' => $user->id,
                    'order_number' => $orderNumber,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total' => $total,
                    'status' => 'pending',
                    'payment_method' => $request->input('payment_method', 'card'),
                    'paid_at' => null,
                    'promo_code_id' => $cart->promo_code_id,
                    'billing_street' => $request->input('billing_street'),
                    'billing_city' => $request->input('billing_city'),
                    'billing_state' => $request->input('billing_state'),
                    'billing_country' => $request->input('billing_country'),
                    'billing_postal_code' => $request->input('billing_postal_code'),
                ]);

                // 9. Create OrderItems with snapshot data
                $paymobItems = [];
                foreach ($cart->items as $cartItem) {
                    $course = $cartItem->course;
                    $instructorName = $course->instructor->name ?? 'Unknown';

                    OrderItem::create([
                        'order_id' => $order->id,
                        'course_id' => $course->id,
                        'title' => $course->title,
                        'instructor_name' => $instructorName,
                        'price' => $cartItem->price,
                    ]);

                    // Build Paymob items array
                    $paymobItems[] = [
                        'name' => $course->title,
                        'amount_cents' => (int) round($cartItem->price * 100),
                        'description' => "Course: {$course->title} by {$instructorName}",
                        'quantity' => 1,
                    ];
                }

                // 10. Create installment plan if payment_method is installment
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

                // 11. Call Paymob to get payment URL
                $amountCents = (int) round($total * 100);

                $billingData = [
                    'apartment' => 'NA',
                    'email' => $user->email,
                    'floor' => 'NA',
                    'first_name' => $user->name ? explode(' ', $user->name)[0] : 'NA',
                    'street' => $request->input('billing_street', 'NA') ?: 'NA',
                    'building' => 'NA',
                    'phone_number' => $user->phone ?? 'NA',
                    'shipping_method' => 'NA',
                    'postal_code' => $request->input('billing_postal_code', 'NA') ?: 'NA',
                    'city' => $request->input('billing_city', 'NA') ?: 'NA',
                    'country' => $request->input('billing_country', 'EG') ?: 'EG',
                    'last_name' => $user->name ? (explode(' ', $user->name)[1] ?? 'NA') : 'NA',
                    'state' => $request->input('billing_state', 'NA') ?: 'NA',
                ];

                $paymobService = new PaymobService();
                $paymentResult = $paymobService->processPayment(
                    $orderNumber,
                    $amountCents,
                    $paymobItems,
                    $billingData
                );

                // 12. Save paymob_order_id on the order
                $order->update(['paymob_order_id' => (string) $paymentResult['paymob_order_id']]);

                // 13. Clear cart
                $cart->items()->delete();
                $cart->update(['promo_code_id' => null]);

                return [
                    'order' => $order,
                    'payment_url' => $paymentResult['payment_url'],
                ];
            });

            return response()->json([
                'data' => [
                    'order_number' => $result['order']->order_number,
                    'payment_url' => $result['payment_url'],
                    'status' => 'pending',
                ],
                'message' => 'Proceed to payment.',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Checkout process failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Checkout failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
