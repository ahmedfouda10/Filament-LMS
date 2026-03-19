<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class QuizAttemptSeeder extends Seeder
{
    public function run(): void
    {
        // Get enrollments with progress > 0 (students who started learning)
        $enrollments = Enrollment::where('progress_percentage', '>', 0)
            ->with(['course.quizzes.questions.options', 'user'])
            ->get();

        foreach ($enrollments as $enrollment) {
            $quizzes = $enrollment->course->quizzes;

            if ($quizzes->isEmpty()) continue;

            // Student attempts some quizzes based on their progress
            $progressRatio = $enrollment->progress_percentage / 100;
            $quizzesToAttempt = $quizzes->take((int) ceil($quizzes->count() * $progressRatio));

            foreach ($quizzesToAttempt as $quiz) {
                if ($quiz->questions->isEmpty()) continue;

                // Already attempted? skip
                $existing = QuizAttempt::where('user_id', $enrollment->user_id)
                    ->where('quiz_id', $quiz->id)
                    ->exists();
                if ($existing) continue;

                // Build answers - simulate mostly correct answers
                $answers = [];
                $correctCount = 0;
                $totalQuestions = $quiz->questions->count();

                foreach ($quiz->questions as $question) {
                    $correctOption = $question->options->firstWhere('is_correct', true);
                    if (!$correctOption) continue;

                    // 75% chance of correct answer
                    if (rand(1, 100) <= 75) {
                        $answers[$question->id] = $correctOption->option_label;
                        $correctCount++;
                    } else {
                        // Pick a random wrong answer
                        $wrongOptions = $question->options->where('is_correct', false);
                        $wrongOption = $wrongOptions->isNotEmpty() ? $wrongOptions->random() : $correctOption;
                        $answers[$question->id] = $wrongOption->option_label;
                    }
                }

                $score = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 2) : 0;
                $passed = $score >= $quiz->passing_score;

                QuizAttempt::create([
                    'user_id' => $enrollment->user_id,
                    'quiz_id' => $quiz->id,
                    'score' => $score,
                    'passed' => $passed,
                    'answers' => $answers,
                    'attempt_number' => 1,
                    'started_at' => now()->subDays(rand(1, 30)),
                    'completed_at' => now()->subDays(rand(0, 29)),
                ]);

                // If failed first time, some students retry and pass
                if (!$passed && rand(1, 100) <= 60) {
                    $retryAnswers = [];
                    $retryCorrect = 0;

                    foreach ($quiz->questions as $question) {
                        $correctOption = $question->options->firstWhere('is_correct', true);
                        if (!$correctOption) continue;

                        // 90% chance correct on retry
                        if (rand(1, 100) <= 90) {
                            $retryAnswers[$question->id] = $correctOption->option_label;
                            $retryCorrect++;
                        } else {
                            $wrongOptions = $question->options->where('is_correct', false);
                            $wrongOption = $wrongOptions->isNotEmpty() ? $wrongOptions->random() : $correctOption;
                            $retryAnswers[$question->id] = $wrongOption->option_label;
                        }
                    }

                    $retryScore = $totalQuestions > 0 ? round(($retryCorrect / $totalQuestions) * 100, 2) : 0;

                    QuizAttempt::create([
                        'user_id' => $enrollment->user_id,
                        'quiz_id' => $quiz->id,
                        'score' => $retryScore,
                        'passed' => $retryScore >= $quiz->passing_score,
                        'answers' => $retryAnswers,
                        'attempt_number' => 2,
                        'started_at' => now()->subDays(rand(0, 15)),
                        'completed_at' => now()->subDays(rand(0, 14)),
                    ]);
                }
            }
        }
    }
}
