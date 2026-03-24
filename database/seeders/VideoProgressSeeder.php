<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\VideoProgress;
use Illuminate\Database\Seeder;

class VideoProgressSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Enrollment::where('progress_percentage', '>', 0)->with('course.modules.lessons')->get() as $enrollment) {
            $videos = $enrollment->course->modules->flatMap->lessons->where('type', 'video');
            $ratio = $enrollment->progress_percentage / 100;
            foreach ($videos->take((int)ceil($videos->count() * $ratio)) as $lesson) {
                $pct = rand(20, 95);
                VideoProgress::firstOrCreate(
                    ['user_id' => $enrollment->user_id, 'lesson_id' => $lesson->id],
                    ['progress_percentage' => $pct, 'last_position_seconds' => (int)($lesson->duration_minutes * 60 * $pct / 100), 'updated_at' => now()->subDays(rand(0, 14))]
                );
            }
        }
    }
}
