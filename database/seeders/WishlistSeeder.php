<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->get();
        $courseIds = Course::where('is_published', true)->pluck('id')->toArray();

        foreach ($students as $student) {
            $enrolled = Enrollment::where('user_id', $student->id)->pluck('course_id')->toArray();
            $available = array_diff($courseIds, $enrolled);
            if (empty($available)) continue;

            $picks = array_slice(collect($available)->shuffle()->toArray(), 0, rand(2, 4));
            foreach ($picks as $cid) {
                Wishlist::firstOrCreate(['user_id' => $student->id, 'course_id' => $cid], ['created_at' => now()->subDays(rand(1, 30))]);
            }
        }
    }
}
