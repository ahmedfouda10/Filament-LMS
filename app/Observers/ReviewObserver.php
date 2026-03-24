<?php

namespace App\Observers;

use App\Models\Review;

class ReviewObserver
{
    public function created(Review $review): void { $this->updateCourseStats($review); }
    public function updated(Review $review): void { $this->updateCourseStats($review); }
    public function deleted(Review $review): void { $this->updateCourseStats($review); }

    private function updateCourseStats(Review $review): void
    {
        $course = $review->course;
        if ($course) {
            $course->update([
                'average_rating_cached' => $course->reviews()->where('is_approved', true)->avg('rating') ?? 0,
            ]);
        }
    }
}
