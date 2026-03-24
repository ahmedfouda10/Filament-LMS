<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\InstallmentPlan;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function index(Request $request)
    {
        $installments = InstallmentPlan::where('user_id', $request->user()->id)
            ->with('order:id,order_number,total')->with('order.items:id,order_id,title')->latest()->paginate(10);

        return response()->json([
            'data' => $installments->through(fn ($i) => [
                'id' => $i->id, 'order' => $i->order, 'provider' => $i->provider,
                'total_amount' => (float) $i->total_amount, 'monthly_amount' => (float) $i->monthly_amount,
                'months' => $i->months, 'paid_months' => $i->paid_months, 'remaining_months' => $i->remaining_months,
                'remaining_amount' => $i->remaining_amount, 'status' => $i->status,
                'next_payment_date' => $i->next_payment_date, 'created_at' => $i->created_at,
            ]),
            'meta' => ['current_page' => $installments->currentPage(), 'per_page' => $installments->perPage(), 'total' => $installments->total()],
        ]);
    }

    public function show(Request $request, InstallmentPlan $installment)
    {
        abort_unless($installment->user_id === $request->user()->id, 403);
        $installment->load('order.items', 'order.user:id,name,email');

        return response()->json(['data' => [
            'id' => $installment->id, 'provider' => $installment->provider,
            'total_amount' => (float) $installment->total_amount, 'monthly_amount' => (float) $installment->monthly_amount,
            'months' => $installment->months, 'paid_months' => $installment->paid_months,
            'remaining_months' => $installment->remaining_months, 'remaining_amount' => $installment->remaining_amount,
            'status' => $installment->status, 'next_payment_date' => $installment->next_payment_date,
            'provider_reference' => $installment->provider_reference, 'order' => $installment->order,
            'created_at' => $installment->created_at,
        ]]);
    }
}
