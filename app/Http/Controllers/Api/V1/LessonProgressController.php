<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\QuizAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LessonProgressController extends Controller
{
    public function complete(int $lessonId, Request $request): JsonResponse
    {
        $user = $request->user();

        $lesson = Lesson::with('module.course')->findOrFail($lessonId);
        $course = $lesson->module->course;

        // Validate student is enrolled in the lesson's course
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->firstOrFail();

        // Create or find LessonCompletion record
        $completion = LessonCompletion::firstOrCreate([
            'user_id' => $user->id,
            'lesson_id' => $lessonId,
        ], [
            'completed_at' => now(),
        ]);

        // Recalculate enrollment progress
        $totalLessons = Lesson::whereHas('module', function ($query) use ($course) {
            $query->where('course_id', $course->id);
        })->count();

        $completedLessons = LessonCompletion::where('user_id', $user->id)
            ->whereHas('lesson.module', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->count();

        $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;

        // Update enrollment progress
        $enrollment->update(['progress_percentage' => $progress]);

        // Check if course is completed
        if ($progress >= 100) {
            // Check all quizzes in the course are passed by user
            $courseQuizIds = Lesson::whereHas('module', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
                ->where('type', 'quiz')
                ->whereHas('quiz')
                ->with('quiz')
                ->get()
                ->pluck('quiz.id')
                ->filter();

            $allQuizzesPassed = true;
            foreach ($courseQuizIds as $quizId) {
                $passed = QuizAttempt::where('user_id', $user->id)
                    ->where('quiz_id', $quizId)
                    ->where('passed', true)
                    ->exists();

                if (!$passed) {
                    $allQuizzesPassed = false;
                    break;
                }
            }

            if ($allQuizzesPassed) {
                // Generate Certificate
                Certificate::firstOrCreate([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                ], [
                    'certificate_number' => 'CERT-' . strtoupper(Str::random(8)),
                    'issued_at' => now(),
                    'valid_until' => now()->addYears(2),
                ]);

                // Mark enrollment as completed
                $enrollment->update(['completed_at' => now()]);
            }
        }

        return response()->json([
            'data' => [
                'lesson_id' => $lessonId,
                'is_completed' => true,
                'progress_percentage' => $progress,
                'total_lessons' => $totalLessons,
                'completed_lessons' => $completedLessons,
                'course_completed' => $enrollment->fresh()->completed_at !== null,
            ],
        ]);
    }

    public function show(int $courseId, Request $request): JsonResponse
    {
        $user = $request->user();

        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->firstOrFail();

        $completedLessons = LessonCompletion::where('user_id', $user->id)
            ->whereHas('lesson.module', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->with('lesson:id,title,module_id')
            ->get();

        $totalLessons = Lesson::whereHas('module', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->count();

        return response()->json([
            'data' => [
                'course_id' => $courseId,
                'progress_percentage' => $enrollment->progress_percentage,
                'total_lessons' => $totalLessons,
                'completed_count' => $completedLessons->count(),
                'completed_at' => $enrollment->completed_at,
                'lesson_completions' => $completedLessons->map(function ($completion) {
                    return [
                        'lesson_id' => $completion->lesson_id,
                        'lesson_title' => $completion->lesson->title ?? null,
                        'completed_at' => $completion->completed_at,
                    ];
                }),
            ],
        ]);
    }
}
