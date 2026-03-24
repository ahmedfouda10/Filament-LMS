<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            ['category' => 'Subscriptions', 'question' => 'What happens when I cancel my subscription?', 'answer' => 'When you cancel, you retain access until the end of your billing period. After that, you lose access to subscription-only content but keep any individually purchased courses.', 'sort_order' => 1],
            ['category' => 'Subscriptions', 'question' => 'Do I get access to Clinical Cases and EXAM prep with a subscription?', 'answer' => 'Yes! All subscription plans include full access to our clinical case library and exam preparation materials.', 'sort_order' => 2],
            ['category' => 'Subscriptions', 'question' => 'Can I switch between subscription plans?', 'answer' => 'Yes, you can upgrade or downgrade your plan at any time. Changes take effect at the start of your next billing cycle.', 'sort_order' => 3],
            ['category' => 'Courses & Content', 'question' => 'How often is new content added?', 'answer' => 'We add new clinical cases and course materials weekly. Major course updates happen monthly based on the latest medical guidelines.', 'sort_order' => 1],
            ['category' => 'Courses & Content', 'question' => 'Can I download course materials?', 'answer' => 'Yes, PDFs and presentation slides are downloadable. Video content is available for offline viewing through our app.', 'sort_order' => 2],
            ['category' => 'Certificates', 'question' => 'Do I receive a certificate after completing a course?', 'answer' => 'Yes! Upon completing all lessons and passing the course quizzes, you automatically receive a certificate of completion valid for 2 years.', 'sort_order' => 1],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }
    }
}
