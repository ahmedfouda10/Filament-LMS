<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseAnalytic;
use Illuminate\Database\Seeder;

class CourseAnalyticSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Course::where('is_published', true)->get() as $course) {
            for ($i = 30; $i >= 0; $i--) {
                $views = rand(10, 200);
                CourseAnalytic::updateOrCreate(
                    ['course_id' => $course->id, 'date' => now()->subDays($i)->toDateString()],
                    ['views' => $views, 'unique_visitors' => (int)($views * rand(60, 80) / 100), 'enrollments' => max(0, (int)($views * rand(1, 5) / 100))]
                );
            }
        }
    }
}
