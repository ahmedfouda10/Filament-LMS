<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $courses = Course::where('is_published', true)->with('modules.lessons')->get();

        if ($courses->isEmpty()) return;

        foreach ($students as $student) {
            $enrollCourses = $courses->random(min(rand(2, 5), $courses->count()));

            foreach ($enrollCourses as $course) {
                $progress = collect([0, 25, 50, 75, 100])->random();
                $allLessons = $course->modules->flatMap->lessons;
                $totalLessons = $allLessons->count();

                if ($totalLessons === 0) continue;

                $completedCount = (int) round($totalLessons * ($progress / 100));
                $completedLessons = $allLessons->take($completedCount);

                $enrollment = Enrollment::updateOrCreate(
                    ['user_id' => $student->id, 'course_id' => $course->id],
                    [
                        'progress_percentage' => $progress,
                        'enrolled_at' => now()->subDays(rand(10, 90)),
                        'completed_at' => $progress === 100 ? now()->subDays(rand(1, 10)) : null,
                        'last_accessed_lesson_id' => $completedLessons->isNotEmpty() ? $completedLessons->last()->id : null,
                    ]
                );

                foreach ($completedLessons as $lesson) {
                    LessonCompletion::updateOrCreate(
                        ['user_id' => $student->id, 'lesson_id' => $lesson->id],
                        ['completed_at' => now()->subDays(rand(1, 60))]
                    );
                }
            }
        }
    }
}
