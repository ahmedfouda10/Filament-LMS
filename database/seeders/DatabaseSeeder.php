<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            CategorySeeder::class,
            UserSeeder::class,
            InstructorProfileSeeder::class,
            CourseSeeder::class,
            EnrollmentSeeder::class,
            ReviewSeeder::class,
            PromoCodeSeeder::class,
            SubscriptionPlanSeeder::class,
            SubscriptionSeeder::class,
            OrderSeeder::class,
            InstructorTransactionSeeder::class,
            QuizAttemptSeeder::class,
            CertificateSeeder::class,
            ContactMessageSeeder::class,
        ]);
    }
}
