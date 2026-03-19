<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitQuizRequest;
use App\Http\Resources\QuizResource;
use App\Models\Enrollment;
use App\Models\LessonCompletion;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Lesson;
use App\Models\Certificate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function show(int $id, Request $request): JsonResponse
    {
        $user = $request->user();

        $quiz = Quiz::with(['questions.options' => function ($query) {
            // Exclude is_correct from the response
            $query->select('id', 'question_id', 'option_label', 'option_text');
        }])->findOrFail($id);

        // Verify student is enrolled in the quiz's course
        $this->verifyEnrollment($user, $quiz);

        return response()->json([
            'data' => new QuizResource($quiz),
        ]);
    }

    public function attempt(int $id, SubmitQuizRequest $request): JsonResponse
    {
        $user = $request->user();

        $result = DB::transaction(function () use ($id, $user, $request) {
            // 1. Get quiz with questions and options
            $quiz = Quiz::with('questions.options')->findOrFail($id);

            // 2. Validate student is enrolled in quiz's course
            $this->verifyEnrollment($user, $quiz);

            // 3. Check attempt count < max_attempts
            $previousAttempts = QuizAttempt::where('user_id', $user->id)
                ->where('quiz_id', $quiz->id)
                ->count();

            if ($quiz->max_attempts && $previousAttempts >= $quiz->max_attempts) {
                abort(422, 'You have reached the maximum number of attempts for this quiz.');
            }

            // 4. Grade each answer
            $answers = $request->input('answers', []);
            $correctCount = 0;
            $totalQuestions = $quiz->questions->count();
            $gradedAnswers = [];

            foreach ($quiz->questions as $question) {
                $selectedLabel = $answers[$question->id] ?? null;
                $correctOption = $question->options->firstWhere('is_correct', true);
                $isCorrect = $correctOption && $selectedLabel === $correctOption->option_label;

                if ($isCorrect) {
                    $correctCount++;
                }

                $gradedAnswers[] = [
                    'question_id' => $question->id,
                    'question_text' => $question->question_text,
                    'selected_answer' => $selectedLabel,
                    'correct_answer' => $correctOption->option_label ?? null,
                    'is_correct' => $isCorrect,
                    'explanation' => $question->explanation,
                ];
            }

            // 5. Calculate score
            $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 2) : 0;

            // 6. Determine if passed
            $passed = $score >= $quiz->passing_score;

            // 7. Save QuizAttempt
            $attemptNumber = $previousAttempts + 1;
            QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'answers' => collect($answers)->mapWithKeys(fn ($label, $qId) => [$qId => $label])->toArray(),
                'score' => $score,
                'passed' => $passed,
                'attempt_number' => $attemptNumber,
                'started_at' => now(),
                'completed_at' => now(),
            ]);

            // 8. If passed AND quiz has a lesson_id: mark that lesson as completed
            if ($passed && $quiz->lesson_id) {
                $lesson = Lesson::with('module.course')->find($quiz->lesson_id);

                if ($lesson) {
                    LessonCompletion::firstOrCreate([
                        'user_id' => $user->id,
                        'lesson_id' => $lesson->id,
                    ], [
                        'completed_at' => now(),
                    ]);

                    // Recalculate enrollment progress
                    $course = $lesson->module->course;
                    $enrollment = Enrollment::where('user_id', $user->id)
                        ->where('course_id', $course->id)
                        ->first();

                    if ($enrollment) {
                        $totalLessons = Lesson::whereHas('module', function ($q) use ($course) {
                            $q->where('course_id', $course->id);
                        })->count();

                        $completedLessons = LessonCompletion::where('user_id', $user->id)
                            ->whereHas('lesson.module', function ($q) use ($course) {
                                $q->where('course_id', $course->id);
                            })->count();

                        $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;
                        $enrollment->update(['progress_percentage' => $progress]);

                        // Check if course is completed
                        if ($progress >= 100) {
                            $courseQuizIds = Lesson::whereHas('module', function ($q) use ($course) {
                                $q->where('course_id', $course->id);
                            })
                                ->where('type', 'quiz')
                                ->whereHas('quiz')
                                ->with('quiz')
                                ->get()
                                ->pluck('quiz.id')
                                ->filter();

                            $allQuizzesPassed = true;
                            foreach ($courseQuizIds as $quizId) {
                                $hasPassed = QuizAttempt::where('user_id', $user->id)
                                    ->where('quiz_id', $quizId)
                                    ->where('passed', true)
                                    ->exists();

                                if (!$hasPassed) {
                                    $allQuizzesPassed = false;
                                    break;
                                }
                            }

                            if ($allQuizzesPassed) {
                                Certificate::firstOrCreate([
                                    'user_id' => $user->id,
                                    'course_id' => $course->id,
                                ], [
                                    'certificate_number' => 'CERT-' . strtoupper(Str::random(8)),
                                    'student_name' => $user->name,
                                    'issued_at' => now(),
                                    'valid_until' => now()->addYears(2),
                                ]);

                                $enrollment->update(['completed_at' => now()]);
                            }
                        }
                    }
                }
            }

            return [
                'score' => $score,
                'passed' => $passed,
                'attempt_number' => $attemptNumber,
                'total_questions' => $totalQuestions,
                'correct_count' => $correctCount,
                'answers' => $gradedAnswers,
            ];
        });

        return response()->json([
            'data' => $result,
        ]);
    }

    private function verifyEnrollment($user, Quiz $quiz): void
    {
        // Find the course through the quiz's lesson -> module -> course relationship
        $courseId = null;

        if ($quiz->lesson_id) {
            $lesson = Lesson::with('module')->find($quiz->lesson_id);
            if ($lesson && $lesson->module) {
                $courseId = $lesson->module->course_id;
            }
        }

        if (!$courseId) {
            $courseId = $quiz->course_id ?? null;
        }

        if (!$courseId) {
            abort(404, 'Quiz is not associated with any course.');
        }

        $isEnrolled = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->exists();

        if (!$isEnrolled) {
            abort(403, 'You are not enrolled in this course.');
        }
    }
}
