<?php

namespace Database\Seeders;

use App\Models\ContactMessage;
use Illuminate\Database\Seeder;

class ContactMessageSeeder extends Seeder
{
    public function run(): void
    {
        $messages = [
            ['name' => 'Mohamed Ali', 'email' => 'mohamed.ali@gmail.com', 'subject' => 'Enrollment Issue', 'message' => 'I am trying to enroll in the ECG Interpretation Masterclass but the payment is not going through. Can you please help?', 'is_read' => true],
            ['name' => 'Fatma Ahmed', 'email' => 'fatma.ahmed@outlook.com', 'subject' => 'Technical Support', 'message' => 'The video player is not loading on my iPad. I have tried different browsers but the issue persists.', 'is_read' => true],
            ['name' => 'Hassan Mahmoud', 'email' => 'hassan.m@yahoo.com', 'subject' => 'Billing Question', 'message' => 'I was charged twice for my subscription. Please review my account and issue a refund for the duplicate charge.', 'is_read' => false],
            ['name' => 'Nour El-Din', 'email' => 'nour.eldin@gmail.com', 'subject' => 'Course Inquiry', 'message' => 'Do you offer any courses on radiology or medical imaging? I am a second-year resident looking for comprehensive radiology training.', 'is_read' => false],
            ['name' => 'Layla Karim', 'email' => 'layla.k@hotmail.com', 'subject' => 'Certificate Request', 'message' => 'I completed the Surgical Skills Fundamentals course last month but have not received my certificate yet. My enrollment shows 100% progress.', 'is_read' => false],
        ];

        foreach ($messages as $msg) {
            ContactMessage::updateOrCreate(
                ['email' => $msg['email'], 'subject' => $msg['subject']],
                $msg
            );
        }
    }
}
