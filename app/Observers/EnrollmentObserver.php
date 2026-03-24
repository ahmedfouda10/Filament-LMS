<?php

namespace App\Observers;

use App\Models\Enrollment;
use App\Models\Notification;

class EnrollmentObserver
{
    public function created(Enrollment $enrollment): void
    {
        $this->updateCount($enrollment);

        // Notify student
        Notification::create([
            'user_id' => $enrollment->user_id,
            'title' => 'Enrollment Successful',
            'body' => "You are now enrolled in {$enrollment->course->title}",
            'type' => 'enrollment',
            'data' => ['course_id' => $enrollment->course_id],
        ]);
    }

    public function deleted(Enrollment $enrollment): void
    {
        $this->updateCount($enrollment);
    }

    private function updateCount(Enrollment $enrollment): void
    {
        $enrollment->course->update([
            'students_count_cached' => $enrollment->course->enrollments()->count(),
        ]);
    }
}
