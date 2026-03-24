<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    public function run(): void
    {
        $studentMessages = [
            'Dr., I have a question about the case study in Module 3. Can you explain the differential diagnosis?',
            'Thank you for the detailed explanation in the last lecture. Very helpful!',
            'Could you recommend additional reading for the cardiology section?',
            'I am having trouble understanding the ECG interpretation part. Can you help?',
            'When will the next module be available? I am really enjoying the course.',
        ];

        $instructorReplies = [
            'Great question! The key differential diagnosis here involves considering both cardiac and pulmonary causes.',
            'Thank you for your feedback! I am glad the content was helpful.',
            'I recommend Harrison\'s Principles of Internal Medicine for a deeper understanding.',
            'Sure! Let me explain the key points. First, look at the P waves and QRS complexes.',
            'The next module will be available by next week. Stay tuned!',
        ];

        $enrollments = Enrollment::with('user', 'course.instructor')->limit(10)->get();

        foreach ($enrollments as $enrollment) {
            if (!$enrollment->course->instructor) continue;

            $studentMsg = $studentMessages[array_rand($studentMessages)];
            $instructorMsg = $instructorReplies[array_rand($instructorReplies)];

            Message::create([
                'sender_id' => $enrollment->user_id,
                'receiver_id' => $enrollment->course->instructor_id,
                'course_id' => $enrollment->course_id,
                'subject' => 'Question about ' . $enrollment->course->title,
                'body' => $studentMsg,
                'is_read' => rand(0, 1),
                'read_at' => rand(0, 1) ? now()->subDays(rand(0, 5)) : null,
                'created_at' => now()->subDays(rand(1, 15)),
            ]);

            Message::create([
                'sender_id' => $enrollment->course->instructor_id,
                'receiver_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
                'body' => $instructorMsg,
                'is_read' => rand(0, 1),
                'read_at' => rand(0, 1) ? now()->subDays(rand(0, 3)) : null,
                'created_at' => now()->subDays(rand(0, 14)),
            ]);
        }
    }
}
