<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\LessonResource;
use Illuminate\Database\Seeder;

class LessonResourceSeeder extends Seeder
{
    public function run(): void
    {
        $titles = ['Module Summary PDF', 'Clinical Guidelines', 'Study Notes', 'Reference Sheet', 'Practice Questions'];

        foreach (Course::with('modules.lessons')->where('is_published', true)->get() as $course) {
            foreach ($course->modules as $module) {
                $lesson = $module->lessons->first();
                if (!$lesson) continue;
                for ($i = 0; $i < rand(1, 3); $i++) {
                    LessonResource::firstOrCreate(
                        ['lesson_id' => $lesson->id, 'title' => $titles[array_rand($titles)] . ' - ' . $module->title],
                        ['file_url' => "lesson-resources/sample-{$lesson->id}-{$i}.pdf", 'file_type' => 'pdf', 'file_size' => rand(102400, 5242880), 'is_downloadable' => true, 'sort_order' => $i]
                    );
                }
            }
        }
    }
}
