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

        // Total revenue
        $totalRevenue = InstructorTransaction::where('instructor_id', $user->id)
            ->where('type', 'sale')
            ->whereIn('status', ['cleared', 'completed'])
            ->sum('net_amount');

        // New students this month
        $newStudentsThisMonth = Enrollment::whereIn('course_id', $courseIds)
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Students last month (#31)
        $studentsLastMonth = Enrollment::whereIn('course_id', $courseIds)
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();

        $studentsGrowth = $studentsLastMonth > 0
            ? round((($newStudentsThisMonth - $studentsLastMonth) / $studentsLastMonth) * 100, 1)
            : 0;

        // Average rating
        $averageRating = Review::whereIn('course_id', $courseIds)->where('is_approved', true)->avg('rating');

        // Active courses
        $activeCourses = $user->courses()->where('is_published', true)->count();

        // Revenue growth
        $currentMonthRevenue = InstructorTransaction::where('instructor_id', $user->id)
            ->where('type', 'sale')->whereIn('status', ['cleared', 'completed'])
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('net_amount');

        $lastMonthRevenue = InstructorTransaction::where('instructor_id', $user->id)
            ->where('type', 'sale')->whereIn('status', ['cleared', 'completed'])
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('net_amount');

        $revenueGrowth = $lastMonthRevenue > 0
            ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        // Revenue chart data - daily for last 30 days (#30)
        $revenueChart = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayRevenue = InstructorTransaction::where('instructor_id', $user->id)
                ->where('type', 'sale')
                ->whereDate('created_at', $date)
                ->sum('net_amount');
            $revenueChart[] = [
                'date' => $date->toDateString(),
                'amount' => round((float) $dayRevenue, 2),
            ];
        }

        // Recent reviews
        $recentReviews = Review::whereIn('course_id', $courseIds)
            ->where('is_approved', true)
            ->with('user:id,name,avatar', 'course:id,title')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'student_name' => $r->user->name ?? 'Unknown',
                'student_avatar' => $r->user->avatar ?? null,
                'course_title' => $r->course->title ?? 'Unknown',
                'rating' => $r->rating,
                'comment' => $r->comment,
                'date' => $r->created_at->toDateTimeString(),
            ]);

        return response()->json([
            'data' => [
                'total_revenue' => round($totalRevenue, 2),
                'new_students_this_month' => $newStudentsThisMonth,
                'students_growth' => $studentsGrowth,
                'average_rating' => $averageRating ? round($averageRating, 2) : 0,
                'active_courses' => $activeCourses,
                'revenue_growth' => $revenueGrowth,
                'current_month_revenue' => round($currentMonthRevenue, 2),
                'last_month_revenue' => round($lastMonthRevenue, 2),
                'revenue_chart' => $revenueChart,
                'recent_reviews' => $recentReviews,
            ],
        ]);
    }
}
