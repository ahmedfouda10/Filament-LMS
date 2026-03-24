<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstructorQuizController extends Controller
{
    /**
     * List quizzes for instructor's courses
     */
    public function index(Request $request): JsonResponse
    {
        $courseIds = $request->user()->courses()->pluck('id');

        $quizzes = Quiz::whereIn('course_id', $courseIds)
            ->with(['course:id,title', 'lesson:id,title'])
            ->withCount('questions')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 15));

        return response()->json($quizzes);
    }

    /**
     * Show quiz with questions and options
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $quiz = Quiz::with(['questions.options', 'course:id,title', 'lesson:id,title'])
            ->findOrFail($id);

        $this->verifyCourseOwnership($quiz->course_id, $request);

        return response()->json(['data' => $quiz]);
    }

    /**
     * Create quiz for a course
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'nullable|exists:lessons,id',
            'title' => 'required|string|max:255',
            'passing_score' => 'sometimes|integer|min:1|max:100',
            'time_limit_minutes' => 'sometimes|integer|min:1',
            'max_attempts' => 'sometimes|integer|min:1',
            'questions' => 'sometimes|array|min:1',
            'questions.*.question_text' => 'required_with:questions|string',
            'questions.*.explanation' => 'nullable|string',
            'questions.*.options' => 'required_with:questions|array|size:4',
            'questions.*.options.*.label' => 'required|string|in:A,B,C,D',
            'questions.*.options.*.text' => 'required|string',
            'questions.*.options.*.is_correct' => 'required|boolean',
        ]);

        $this->verifyCourseOwnership($validated['course_id'], $request);

        // If lesson_id provided, verify it belongs to the same course
        if (!empty($validated['lesson_id'])) {
            $lesson = Lesson::with('module')->findOrFail($validated['lesson_id']);
            if ($lesson->module->course_id !== (int) $validated['course_id']) {
                return response()->json(['message' => 'Lesson does not belong to this course.'], 422);
            }
        }

        $quiz = DB::transaction(function () use ($validated) {
            $quiz = Quiz::create([
                'course_id' => $validated['course_id'],
                'lesson_id' => $validated['lesson_id'] ?? null,
                'title' => $validated['title'],
                'passing_score' => $validated['passing_score'] ?? 70,
                'time_limit_minutes' => $validated['time_limit_minutes'] ?? 15,
                'max_attempts' => $validated['max_attempts'] ?? 3,
            ]);

            if (!empty($validated['questions'])) {
                $this->createQuestions($quiz, $validated['questions']);
            }

            return $quiz->load('questions.options');
        });

        return response()->json([
            'data' => $quiz,
            'message' => 'Quiz created successfully.',
        ], 201);
    }

    /**
     * Update quiz
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $quiz = Quiz::findOrFail($id);
        $this->verifyCourseOwnership($quiz->course_id, $request);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'lesson_id' => 'nullable|exists:lessons,id',
            'passing_score' => 'sometimes|integer|min:1|max:100',
            'time_limit_minutes' => 'sometimes|integer|min:1',
            'max_attempts' => 'sometimes|integer|min:1',
        ]);

        $quiz->update($validated);

        return response()->json([
            'data' => $quiz->load('questions.options'),
            'message' => 'Quiz updated successfully.',
        ]);
    }

    /**
     * Delete quiz
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $quiz = Quiz::findOrFail($id);
        $this->verifyCourseOwnership($quiz->course_id, $request);

        $quiz->delete();

        return response()->json(['message' => 'Quiz deleted successfully.']);
    }

    // ==========================================
    // Question Management
    // ==========================================

    /**
     * Add question to quiz
     */
    public function storeQuestion(int $quizId, Request $request): JsonResponse
    {
        $quiz = Quiz::findOrFail($quizId);
        $this->verifyCourseOwnership($quiz->course_id, $request);

        $validated = $request->validate([
            'question_text' => 'required|string',
            'explanation' => 'nullable|string',
            'sort_order' => 'sometimes|integer|min:0',
            'options' => 'required|array|size:4',
            'options.*.label' => 'required|string|in:A,B,C,D',
            'options.*.text' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ]);

        // Validate exactly one correct answer
        $correctCount = collect($validated['options'])->where('is_correct', true)->count();
        if ($correctCount !== 1) {
            return response()->json([
                'message' => 'Each question must have exactly one correct answer.',
            ], 422);
        }

        $question = DB::transaction(function () use ($quiz, $validated) {
            $question = $quiz->questions()->create([
                'question_text' => $validated['question_text'],
                'explanation' => $validated['explanation'] ?? null,
                'sort_order' => $validated['sort_order'] ?? $quiz->questions()->count(),
            ]);

            foreach ($validated['options'] as $opt) {
                $question->options()->create([
                    'option_label' => $opt['label'],
                    'option_text' => $opt['text'],
                    'is_correct' => $opt['is_correct'],
                ]);
            }

            return $question->load('options');
        });

        return response()->json([
            'data' => $question,
            'message' => 'Question added successfully.',
        ], 201);
    }

    /**
     * Update question
     */
    public function updateQuestion(int $questionId, Request $request): JsonResponse
    {
        $question = QuizQuestion::with('quiz')->findOrFail($questionId);
        $this->verifyCourseOwnership($question->quiz->course_id, $request);

        $validated = $request->validate([
            'question_text' => 'sometimes|string',
            'explanation' => 'nullable|string',
            'sort_order' => 'sometimes|integer|min:0',
            'options' => 'sometimes|array|size:4',
            'options.*.label' => 'required_with:options|string|in:A,B,C,D',
            'options.*.text' => 'required_with:options|string',
            'options.*.is_correct' => 'required_with:options|boolean',
        ]);

        if (!empty($validated['options'])) {
            $correctCount = collect($validated['options'])->where('is_correct', true)->count();
            if ($correctCount !== 1) {
                return response()->json([
                    'message' => 'Each question must have exactly one correct answer.',
                ], 422);
            }
        }

        DB::transaction(function () use ($question, $validated) {
            $question->update(collect($validated)->except('options')->toArray());

            if (!empty($validated['options'])) {
                $question->options()->delete();
                foreach ($validated['options'] as $opt) {
                    $question->options()->create([
                        'option_label' => $opt['label'],
                        'option_text' => $opt['text'],
                        'is_correct' => $opt['is_correct'],
                    ]);
                }
            }
        });

        return response()->json([
            'data' => $question->load('options'),
            'message' => 'Question updated successfully.',
        ]);
    }

    /**
     * Delete question
     */
    public function destroyQuestion(int $questionId, Request $request): JsonResponse
    {
        $question = QuizQuestion::with('quiz')->findOrFail($questionId);
        $this->verifyCourseOwnership($question->quiz->course_id, $request);

        $question->delete();

        return response()->json(['message' => 'Question deleted successfully.']);
    }

    // ==========================================
    // Helpers
    // ==========================================

    private function verifyCourseOwnership(int $courseId, Request $request): void
    {
        $course = Course::findOrFail($courseId);
        if ($course->instructor_id !== $request->user()->id) {
            abort(403, 'You do not have permission to manage this quiz.');
        }
    }

    private function createQuestions(Quiz $quiz, array $questions): void
    {
        foreach ($questions as $index => $q) {
            $question = $quiz->questions()->create([
                'question_text' => $q['question_text'],
                'explanation' => $q['explanation'] ?? null,
                'sort_order' => $index + 1,
            ]);

            foreach ($q['options'] as $opt) {
                $question->options()->create([
                    'option_label' => $opt['label'],
                    'option_text' => $opt['text'],
                    'is_correct' => $opt['is_correct'],
                ]);
            }
        }
    }
}
