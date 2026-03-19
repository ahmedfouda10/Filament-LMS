<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\InstructorTransaction;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class InstructorDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $courseIds = $user->courses()->pluck('id');

        // Total revenue: sum net_amount from instructor_transactions where type='sale' and status in ('cleared','completed')
        $totalRevenue = InstructorTransaction::where('instructor_id', $user->id)
            ->where('type', 'sale')
            ->whereIn('status', ['cleared', 'completed'])
            ->sum('net_amount');

        // New students this month
        $newStudentsThisMonth = Enrollment::whereIn('course_id', $courseIds)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Average rating across all instructor's courses
        $averageRating = Review::whereIn('course_id', $courseIds)
            ->avg('rating');

        // Active courses count
        $activeCourses = $user->courses()
            ->where('status', 'published')
            ->count();

        // Revenue growth: percentage change vs previous month
        $currentMonthRevenue = InstructorTransaction::where('instructor_id', $user->id)
            ->where('type', 'sale')
            ->whereIn('status', ['cleared', 'completed'])
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('net_amount');

        $lastMonthRevenue = InstructorTransaction::where('instructor_id', $user->id)
            ->where('type', 'sale')
            ->whereIn('status', ['cleared', 'completed'])
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('net_amount');

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 2)
            : 0;

        // Recent reviews: latest 5 reviews for instructor's courses
        $recentReviews = Review::whereIn('course_id', $courseIds)
            ->with('user:id,name', 'course:id,title')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($review) {
                return [
                    'id' => $review->id,
                    'student_name' => $review->user->name ?? 'Unknown',
                    'course_title' => $review->course->title ?? 'Unknown',
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'date' => $review->created_at->toDateTimeString(),
                ];
            });

        return response()->json([
            'data' => [
                'total_revenue' => round($totalRevenue, 2),
                'new_students_this_month' => $newStudentsThisMonth,
                'average_rating' => $averageRating ? round($averageRating, 2) : null,
                'active_courses' => $activeCourses,
                'revenue_growth' => $revenueGrowth,
                'current_month_revenue' => round($currentMonthRevenue, 2),
                'last_month_revenue' => round($lastMonthRevenue, 2),
                'recent_reviews' => $recentReviews,
            ],
        ]);
    }
}
