<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseShare;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseShareSeeder extends Seeder
{
    public function run(): void
    {
        $platforms = ['copy_link', 'copy_link', 'copy_link', 'copy_link', 'whatsapp', 'whatsapp', 'whatsapp', 'facebook', 'facebook', 'twitter', 'linkedin'];
        $courses = Course::where('is_published', true)->pluck('id')->toArray();
        $students = User::where('role', 'student')->pluck('id')->toArray();

        for ($i = 0; $i < rand(30, 50); $i++) {
            CourseShare::create([
                'course_id' => $courses[array_rand($courses)],
                'user_id' => rand(0, 1) ? $students[array_rand($students)] : null,
                'platform' => $platforms[array_rand($platforms)],
                'ip_address' => '41.34.' . rand(1, 255) . '.' . rand(1, 255),
                'created_at' => now()->subDays(rand(0, 30)),
            ]);
        }
    }
}
