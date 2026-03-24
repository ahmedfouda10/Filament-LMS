<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EnrollmentResource;
use App\Models\Enrollment;
use App\Models\LessonCompletion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentCourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = $user->enrollments()
            ->with('course.instructor', 'course.category');

        // Filter by status
        $filter = $request->input('filter', 'all');
        switch ($filter) {
            case 'in-progress':
                $query->whereNull('completed_at')
                    ->where('progress_percentage', '>', 0);
                break;
            case 'completed':
                $query->whereNotNull('completed_at');
                break;
            case 'all':
            default:
                break;
        }

        // Search by course title
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('course', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $enrollments = $query->orderByDesc('updated_at')
            ->paginate($request->input('per_page', 10));

        return response()->json(
            EnrollmentResource::collection($enrollments)->response()->getData(true)
        );
    }

    public function player(int $id, Request $request): JsonResponse
    {
        $user = $request->user();

        $enrollment = $user->enrollments()
            ->where('course_id', $id)
            ->firstOrFail();

        $course = $enrollment->course()
            ->with(['modules' => function ($query) {
                $query->orderBy('sort_order')
                    ->with(['lessons' => function ($q) {
                        $q->orderBy('sort_order');
                    }]);
            }])
            ->first();

        // Get student's lesson completions for this course
        $completedLessonIds = LessonCompletion::where('user_id', $user->id)
            ->whereHas('lesson.module', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->pluck('lesson_id')
            ->toArray();

        // Build the course data with completion status per lesson
        $modules = $course->modules->map(function ($module) use ($completedLessonIds) {
            $lessons = $module->lessons->map(function ($lesson) use ($completedLessonIds) {
                $lessonData = $lesson->toArray();
                $lessonData['is_completed'] = in_array($lesson->id, $completedLessonIds);

                // Include quiz data for quiz-type lessons
                if ($lesson->type === 'quiz' && $lesson->quiz) {
                    $lessonData['quiz'] = [
                        'id' => $lesson->quiz->id,
                        'title' => $lesson->quiz->title,
                        'passing_score' => $lesson->quiz->passing_score,
                        'max_attempts' => $lesson->quiz->max_attempts,
                        'time_limit' => $lesson->quiz->time_limit,
                    ];
                }

                return $lessonData;
            });

            return [
                'id' => $module->id,
                'title' => $module->title,
                'sort_order' => $module->sort_order,
                'lessons' => $lessons,
            ];
        });

        return response()->json([
            'data' => [
                'course' => [
                    'id' => $course->id,
                    'title' => $course->title,
                    'slug' => $course->slug,
                    'thumbnail' => $course->thumbnail,
                ],
                'enrollment' => [
                    'id' => $enrollment->id,
                    'progress_percentage' => $enrollment->progress_percentage,
                    'completed_at' => $enrollment->completed_at,
                    'enrolled_at' => $enrollment->created_at,
                ],
                'modules' => $modules,
                'completed_lesson_ids' => $completedLessonIds,
            ],
        ]);
    }
}
