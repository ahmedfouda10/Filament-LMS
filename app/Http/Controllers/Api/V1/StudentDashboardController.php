<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EnrollmentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $enrolledCourses = $user->enrollments()->count();

        $activeSubscriptions = $user->subscriptions()
            ->where('status', 'active')
            ->count();

        $completedCourses = $user->enrollments()
            ->whereNotNull('completed_at')
            ->count();

        $certificatesEarned = $user->certificates()->count();

        $continueLearning = $user->enrollments()
            ->whereNull('completed_at')
            ->with('course.instructor', 'course.category')
            ->orderByDesc('updated_at')
            ->limit(3)
            ->get();

        return response()->json([
            'data' => [
                'enrolled_courses' => $enrolledCourses,
                'active_subscriptions' => $activeSubscriptions,
                'completed_courses' => $completedCourses,
                'certificates_earned' => $certificatesEarned,
                'continue_learning' => EnrollmentResource::collection($continueLearning),
            ],
        ]);
    }
}
