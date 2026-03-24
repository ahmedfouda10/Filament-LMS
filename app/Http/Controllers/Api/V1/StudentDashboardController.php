<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LessonCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $enrolledCourses = $user->enrollments()->count();
        $activeSubscriptions = $user->subscriptions()->where('status', 'active')->count();
        $completedCourses = $user->enrollments()->whereNotNull('completed_at')->count();
        $certificatesEarned = $user->certificates()->count();

        // #27 - Lessons completed this week
        $lessonsThisWeek = LessonCompletion::where('user_id', $user->id)
            ->where('completed_at', '>=', now()->startOfWeek())
            ->count();

        // #28 - Continue learning with instructor avatar
        $continueLearning = $user->enrollments()
            ->whereNull('completed_at')
            ->with(['course:id,title,slug,image,short_description,instructor_id', 'course.instructor:id,name,avatar'])
            ->orderByDesc('updated_at')
            ->limit(3)
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'progress_percentage' => (float) $e->progress_percentage,
                'enrolled_at' => $e->enrolled_at,
                'last_accessed_lesson_id' => $e->last_accessed_lesson_id,
                'course' => [
                    'id' => $e->course->id,
                    'title' => $e->course->title,
                    'slug' => $e->course->slug,
                    'image' => $e->course->image,
                    'short_description' => $e->course->short_description,
                    'instructor' => $e->course->instructor ? [
                        'id' => $e->course->instructor->id,
                        'name' => $e->course->instructor->name,
                        'avatar' => $e->course->instructor->avatar,
                    ] : null,
                ],
            ]);

        return response()->json([
            'data' => [
                'enrolled_courses' => $enrolledCourses,
                'active_subscriptions' => $activeSubscriptions,
                'completed_courses' => $completedCourses,
                'certificates_earned' => $certificatesEarned,
                'lessons_completed_this_week' => $lessonsThisWeek,
                'continue_learning' => $continueLearning,
            ],
        ]);
    }
}
