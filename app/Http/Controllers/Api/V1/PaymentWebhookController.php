<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\InstructorTransaction;
use App\Models\Order;
use App\Models\PaymentLog;
use App\Models\Setting;
use App\Services\PaymobService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentWebhookController extends Controller
{
    /**
     * Handle Paymob webhook (POST) - called by Paymob server-to-server.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $obj = $payload['obj'] ?? [];
        $hmac = $request->query('hmac') ?? $request->header('hmac') ?? ($payload['hmac'] ?? null);

        // Build flat data array for HMAC verification
        $hmacData = [
            'amount_cents' => $obj['amount_cents'] ?? '',
            'created_at' => $obj['created_at'] ?? '',
            'currency' => $obj['currency'] ?? '',
            'error_occured' => $obj['error_occured'] ?? '',
            'has_parent_transaction' => $obj['has_parent_transaction'] ?? '',
            'id' => $obj['id'] ?? '',
            'integration_id' => $obj['integration_id'] ?? '',
            'is_3d_secure' => $obj['is_3d_secure'] ?? '',
            'is_auth' => $obj['is_auth'] ?? '',
            'is_capture' => $obj['is_capture'] ?? '',
            'is_refunded' => $obj['is_refunded'] ?? '',
            'is_standalone_payment' => $obj['is_standalone_payment'] ?? '',
            'is_voided' => $obj['is_voided'] ?? '',
            'order.id' => $obj['order']['id'] ?? '',
            'owner' => $obj['owner'] ?? '',
            'pending' => $obj['pending'] ?? '',
            'source_data.pan' => $obj['source_data']['pan'] ?? '',
            'source_data.sub_type' => $obj['source_data']['sub_type'] ?? '',
            'source_data.type' => $obj['source_data']['type'] ?? '',
            'success' => $obj['success'] ?? '',
        ];

        // Verify HMAC signature
        $paymobService = new PaymobService();
        if ($hmac && !$paymobService->verifyHmac($hmacData, $hmac)) {
            Log::warning('Paymob webhook HMAC verification failed', [
                'received_hmac' => $hmac,
                'payload' => $payload,
            ]);
            return response()->json(['status' => 'invalid_hmac'], 403);
        }

        // Find the order
        $merchantOrderId = $obj['order']['merchant_order_id'] ?? null;
        $order = $merchantOrderId ? Order::where('order_number', $merchantOrderId)->first() : null;

        // Log the payment
        PaymentLog::create([
            'order_id' => $order?->id,
            'transaction_id' => $obj['id'] ?? null,
            'payment_method' => $obj['source_data']['type'] ?? 'unknown',
            'amount' => ($obj['amount_cents'] ?? 0) / 100,
            'status' => ($obj['success'] ?? false) ? 'success' : 'failed',
            'gateway_response' => $payload,
            'ip_address' => $request->ip(),
        ]);

        $success = filter_var($obj['success'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($success && $order && $order->status === 'pending') {
            $this->completeOrder($order);
        } elseif (!$success && $order && $order->status === 'pending') {
            $order->update(['status' => 'failed']);
            Log::info('Payment failed for order', ['order_number' => $order->order_number]);
        }

        return response()->json(['status' => 'received']);
    }

    /**
     * Handle Paymob browser callback (GET) - user redirected here after payment.
     */
    public function callback(Request $request)
    {
        $data = $request->all();
        $hmac = $request->query('hmac');

        // Build flat data for HMAC verification from query params
        $hmacData = [
            'amount_cents' => $data['amount_cents'] ?? '',
            'created_at' => $data['created_at'] ?? '',
            'currency' => $data['currency'] ?? '',
            'error_occured' => $data['error_occured'] ?? '',
            'has_parent_transaction' => $data['has_parent_transaction'] ?? '',
            'id' => $data['id'] ?? '',
            'integration_id' => $data['integration_id'] ?? '',
            'is_3d_secure' => $data['is_3d_secure'] ?? '',
            'is_auth' => $data['is_auth'] ?? '',
            'is_capture' => $data['is_capture'] ?? '',
            'is_refunded' => $data['is_refunded'] ?? '',
            'is_standalone_payment' => $data['is_standalone_payment'] ?? '',
            'is_voided' => $data['is_voided'] ?? '',
            'order.id' => $data['order'] ?? '',
            'owner' => $data['owner'] ?? '',
            'pending' => $data['pending'] ?? '',
            'source_data.pan' => $data['source_data_pan'] ?? ($data['source_data.pan'] ?? ''),
            'source_data.sub_type' => $data['source_data_sub_type'] ?? ($data['source_data.sub_type'] ?? ''),
            'source_data.type' => $data['source_data_type'] ?? ($data['source_data.type'] ?? ''),
            'success' => $data['success'] ?? '',
        ];

        $verified = false;
        if ($hmac) {
            $paymobService = new PaymobService();
            $verified = $paymobService->verifyHmac($hmacData, $hmac);
        }

        $success = filter_var($data['success'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // Find the order by merchant_order_id if available
        $merchantOrderId = $data['merchant_order_id'] ?? null;
        $order = $merchantOrderId ? Order::where('order_number', $merchantOrderId)->first() : null;

        // If callback reports success and HMAC is valid, also complete the order
        // (as a fallback in case webhook hasn't arrived yet)
        if ($verified && $success && $order && $order->status === 'pending') {
            $this->completeOrder($order);
        }

        // Redirect to frontend
        $frontendUrl = Setting::get('frontend_url') ?? 'https://spc.a2za1.com';
        $orderNumber = $order?->order_number ?? '';

        if ($success) {
            return redirect("{$frontendUrl}/checkout/success?order_number={$orderNumber}");
        }

        return redirect("{$frontendUrl}/checkout/failed?order_number={$orderNumber}");
    }

    /**
     * Manually verify payment status with Paymob (for cases where webhook doesn't reach us).
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'order_number' => 'required|string',
        ]);

        $order = Order::where('order_number', $request->input('order_number'))
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($order->status === 'completed') {
            return response()->json([
                'data' => [
                    'order_number' => $order->order_number,
                    'status' => 'completed',
                    'already_completed' => true,
                ],
                'message' => 'Order is already completed.',
            ]);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'data' => [
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                ],
                'message' => 'Order is not in a pending state.',
            ]);
        }

        try {
            $paymobService = new PaymobService();
            $authToken = $paymobService->authenticate();

            // Query Paymob orders endpoint to find transactions for this order
            $paymobOrderId = $order->paymob_order_id;

            if (!$paymobOrderId) {
                return response()->json([
                    'message' => 'No Paymob order ID found for this order.',
                ], 422);
            }

            // Get order transactions from Paymob
            $response = \Illuminate\Support\Facades\Http::withToken($authToken)
                ->get("https://accept.paymob.com/api/ecommerce/orders/{$paymobOrderId}");

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Failed to inquire order from Paymob.',
                ], 502);
            }

            $paymobOrder = $response->json();
            $paid = $paymobOrder['paid_amount_cents'] ?? 0;
            $expectedAmount = (int) round($order->total * 100);

            if ($paid >= $expectedAmount) {
                $this->completeOrder($order);

                return response()->json([
                    'data' => [
                        'order_number' => $order->order_number,
                        'status' => 'completed',
                    ],
                    'message' => 'Payment verified and order completed.',
                ]);
            }

            return response()->json([
                'data' => [
                    'order_number' => $order->order_number,
                    'status' => 'pending',
                    'paid_amount' => $paid / 100,
                    'expected_amount' => $order->total,
                ],
                'message' => 'Payment not yet confirmed.',
            ]);

        } catch (\Exception $e) {
            Log::error('Payment verification failed', [
                'order_number' => $order->order_number,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Payment verification failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete an order: create enrollments, instructor transactions, and increment promo code.
     */
    protected function completeOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // Reload to make sure we have the latest status (prevent double processing)
            $order = Order::lockForUpdate()->find($order->id);

            if ($order->status !== 'pending') {
                return;
            }

            // Update order status
            $order->update([
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            // Load order items with course and instructor
            $order->load('items.course.instructor');

            // Get platform fee percentage from settings (default 20%)
            $platformFeePercentage = (float) Setting::get('platform_fee_percentage', 20);

            foreach ($order->items as $orderItem) {
                $course = $orderItem->course;

                if (!$course) {
                    continue;
                }

                // Create Enrollment
                Enrollment::firstOrCreate(
                    [
                        'user_id' => $order->user_id,
                        'course_id' => $course->id,
                    ],
                    [
                        'progress_percentage' => 0,
                        'enrolled_at' => now(),
                    ]
                );

                // Create InstructorTransaction
                if ($course->instructor_id) {
                    $amount = $orderItem->price;
                    $platformFee = round($amount * ($platformFeePercentage / 100), 2);
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

            // Increment promo code used_count if applicable
            if ($order->promo_code_id) {
                $order->promoCode?->increment('used_count');
            }
        });
    }
}
