<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $comments = [
            'Excellent course with real clinical cases',
            'Very detailed and well-structured content',
            'Dr. explained everything clearly',
            'Great for board preparation',
            'Highly recommend for medical students',
            'Good content but could use more cases',
            'Perfect for clinical rotation preparation',
            'Outstanding teaching methodology',
            'Comprehensive and practical approach',
            'Best medical course I have taken online',
            'The quizzes really helped reinforce the material',
            'Wish there were more video demonstrations',
        ];

        $courses = Course::where('is_published', true)->get();

        foreach ($courses as $course) {
            $enrolledUserIds = Enrollment::where('course_id', $course->id)->pluck('user_id');
            if ($enrolledUserIds->isEmpty()) continue;

            $reviewCount = min(rand(3, 8), $enrolledUserIds->count());
            $reviewerIds = $enrolledUserIds->random($reviewCount);

            foreach ($reviewerIds as $userId) {
                Review::updateOrCreate(
                    ['user_id' => $userId, 'course_id' => $course->id],
                    [
                        'rating' => collect([3, 4, 4, 4, 5, 5, 5, 5])->random(),
                        'comment' => collect($comments)->random(),
                        'is_approved' => true,
                    ]
                );
            }
        }
    }
}
