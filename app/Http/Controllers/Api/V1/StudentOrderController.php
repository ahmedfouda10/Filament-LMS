<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with('items.course')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 10));

        return response()->json(
            OrderResource::collection($orders)->response()->getData(true)
        );
    }

    public function show(string $orderNumber, Request $request): JsonResponse
    {
        $order = $request->user()
            ->orders()
            ->where('order_number', $orderNumber)
            ->with('items.course')
            ->firstOrFail();

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    public function receipt(string $orderNumber, Request $request): JsonResponse
    {
        $order = $request->user()
            ->orders()
            ->where('order_number', $orderNumber)
            ->with('items.course', 'user')
            ->firstOrFail();

        return response()->json([
            'data' => [
                'receipt_number' => $order->order_number,
                'date' => $order->paid_at ?? $order->created_at,
                'student' => [
                    'name' => $order->user->name,
                    'email' => $order->user->email,
                ],
                'items' => $order->items->map(function ($item) {
                    return [
                        'course_title' => $item->course_title,
                        'instructor_name' => $item->instructor_name,
                        'price' => $item->price,
                    ];
                }),
                'subtotal' => $order->subtotal,
                'discount' => $order->discount,
                'total' => $order->total,
                'payment_method' => $order->payment_method,
                'status' => $order->status,
            ],
        ]);
    }
}
