<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentSubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $subscriptions = $request->user()
            ->subscriptions()
            ->with('plan')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => SubscriptionResource::collection($subscriptions),
        ]);
    }

    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        $plan = SubscriptionPlan::where('is_active', true)
            ->findOrFail($request->plan_id);

        $subscription = Subscription::create([
            'user_id' => $request->user()->id,
            'plan_id' => $plan->id,
            'start_date' => now(),
            'end_date' => now()->addMonths($plan->duration_months),
            'status' => 'active',
            'auto_renew' => $request->input('auto_renew', false),
        ]);

        $subscription->load('plan');

        return response()->json([
            'data' => new SubscriptionResource($subscription),
            'message' => 'Subscription created successfully.',
        ], 201);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $subscription = $request->user()
            ->subscriptions()
            ->findOrFail($id);

        $subscription->update([
            'auto_renew' => $request->boolean('auto_renew'),
        ]);

        $subscription->load('plan');

        return response()->json([
            'data' => new SubscriptionResource($subscription),
            'message' => 'Subscription updated successfully.',
        ]);
    }

    public function cancel(int $id, Request $request): JsonResponse
    {
        $subscription = $request->user()
            ->subscriptions()
            ->findOrFail($id);

        $subscription->update([
            'status' => 'cancelled',
            'auto_renew' => false,
        ]);

        $subscription->load('plan');

        return response()->json([
            'data' => new SubscriptionResource($subscription),
            'message' => 'Subscription cancelled successfully.',
        ]);
    }
}
