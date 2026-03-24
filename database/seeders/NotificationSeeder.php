<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            'enrollment' => ['Enrollment Successful', 'You are now enrolled in a new course.'],
            'payment' => ['Payment Confirmed', 'Your payment has been processed successfully.'],
            'certificate' => ['Certificate Earned!', 'Congratulations! You earned a new certificate.'],
            'quiz' => ['Quiz Passed!', 'You successfully passed the quiz.'],
            'subscription' => ['Subscription Active', 'Your subscription is now active.'],
            'system' => ['Welcome to SPC Academy', 'Welcome! Start exploring our courses.'],
        ];
        $types = array_keys($templates);

        foreach (User::where('role', 'student')->get() as $student) {
            for ($i = 0; $i < rand(3, 5); $i++) {
                $type = $types[array_rand($types)];
                Notification::create([
                    'user_id' => $student->id, 'title' => $templates[$type][0], 'body' => $templates[$type][1],
                    'type' => $type, 'read_at' => rand(0, 1) ? now()->subDays(rand(0, 10)) : null,
                    'created_at' => now()->subDays(rand(0, 30)), 'updated_at' => now(),
                ]);
            }
        }
    }
}
