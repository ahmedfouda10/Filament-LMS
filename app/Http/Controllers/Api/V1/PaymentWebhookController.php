<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentLog;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $obj = $payload['obj'] ?? [];
        $order = ($obj['order']['merchant_order_id'] ?? null) ? Order::where('order_number', $obj['order']['merchant_order_id'])->first() : null;

        PaymentLog::create([
            'order_id' => $order?->id, 'transaction_id' => $obj['id'] ?? null,
            'payment_method' => $obj['source_data']['type'] ?? 'unknown', 'amount' => ($obj['amount_cents'] ?? 0) / 100,
            'status' => ($obj['success'] ?? false) ? 'success' : 'failed', 'gateway_response' => $payload, 'ip_address' => $request->ip(),
        ]);

        if (($obj['success'] ?? false) && $order && $order->status === 'pending') {
            $order->update(['status' => 'completed', 'paid_at' => now()]);
        }

        return response()->json(['status' => 'received']);
    }
}
