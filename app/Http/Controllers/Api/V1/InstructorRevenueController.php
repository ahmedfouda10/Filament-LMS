<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayoutRequest;
use App\Http\Resources\TransactionResource;
use App\Models\InstructorTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class InstructorRevenueController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Available balance: sum net_amount where status='cleared' and type='sale'
        // MINUS sum amount where type='payout' and status in ('pending','completed')
        $clearedSales = InstructorTransaction::where('instructor_id', $userId)
            ->where('type', 'sale')
            ->where('status', 'cleared')
            ->sum('net_amount');

        $payoutsDeducted = InstructorTransaction::where('instructor_id', $userId)
            ->where('type', 'payout')
            ->whereIn('status', ['pending', 'completed'])
            ->sum('amount');

        $availableBalance = round($clearedSales - $payoutsDeducted, 2);

        // Pending clearance: sum net_amount where status='pending' and type='sale'
        $pendingClearance = InstructorTransaction::where('instructor_id', $userId)
            ->where('type', 'sale')
            ->where('status', 'pending')
            ->sum('net_amount');

        // Lifetime earnings: sum net_amount where type='sale'
        $lifetimeEarnings = InstructorTransaction::where('instructor_id', $userId)
            ->where('type', 'sale')
            ->sum('net_amount');

        return response()->json([
            'data' => [
                'available_balance' => round($availableBalance, 2),
                'pending_clearance' => round($pendingClearance, 2),
                'lifetime_earnings' => round($lifetimeEarnings, 2),
            ],
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $query = InstructorTransaction::where('instructor_id', $userId)
            ->with('course:id,title');

        // Filter by type
        $filter = $request->input('filter', 'all');
        switch ($filter) {
            case 'sales':
                $query->where('type', 'sale');
                break;
            case 'payouts':
                $query->where('type', 'payout');
                break;
            case 'all':
            default:
                break;
        }

        // Filter by period
        $period = $request->input('period');
        switch ($period) {
            case 'current_month':
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
                break;
            case 'last_month':
                $query->whereMonth('created_at', Carbon::now()->subMonth()->month)
                    ->whereYear('created_at', Carbon::now()->subMonth()->year);
                break;
            case 'year_to_date':
                $query->whereYear('created_at', Carbon::now()->year);
                break;
        }

        $transactions = $query->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return response()->json(
            TransactionResource::collection($transactions)->response()->getData(true)
        );
    }

    public function requestPayout(PayoutRequest $request): JsonResponse
    {
        $userId = $request->user()->id;

        // Calculate available balance
        $clearedSales = InstructorTransaction::where('instructor_id', $userId)
            ->where('type', 'sale')
            ->where('status', 'cleared')
            ->sum('net_amount');

        $payoutsDeducted = InstructorTransaction::where('instructor_id', $userId)
            ->where('type', 'payout')
            ->whereIn('status', ['pending', 'completed'])
            ->sum('amount');

        $availableBalance = round($clearedSales - $payoutsDeducted, 2);

        // Validate amount <= available_balance
        if ($request->amount > $availableBalance) {
            return response()->json([
                'message' => 'Requested amount exceeds your available balance.',
                'available_balance' => $availableBalance,
            ], 422);
        }

        // Create payout transaction
        $transaction = InstructorTransaction::create([
            'instructor_id' => $userId,
            'type' => 'payout',
            'amount' => $request->amount,
            'platform_fee' => 0,
            'net_amount' => $request->amount,
            'status' => 'pending',
            'transaction_number' => 'TXN-' . strtoupper(Str::random(5)),
        ]);

        return response()->json([
            'data' => new TransactionResource($transaction),
            'message' => 'Payout request submitted successfully.',
        ], 201);
    }
}
