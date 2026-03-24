<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\User;
use Illuminate\Http\Request;

class RefundRequestController extends Controller
{
    public function store(Request $request, Order $order)
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->status === 'completed', 422, 'Only completed orders can be refunded.');
        abort_if($order->refundRequest()->exists(), 422, 'Refund already requested.');
        if ($order->paid_at && $order->paid_at->diffInDays(now()) > 30) {
            abort(422, 'Refund window (30 days) has expired.');
        }

        $validated = $request->validate(['reason' => 'required|string|max:1000']);
        $refund = RefundRequest::create(['order_id' => $order->id, 'user_id' => $request->user()->id, 'reason' => $validated['reason'], 'status' => 'pending', 'requested_at' => now()]);

        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            Notification::create(['user_id' => $admin->id, 'title' => 'New Refund Request', 'body' => "Refund requested for order {$order->order_number}", 'type' => 'system', 'data' => ['order_id' => $order->id, 'refund_id' => $refund->id]]);
        }

        return response()->json(['data' => $refund, 'message' => 'Refund request submitted.'], 201);
    }
}
