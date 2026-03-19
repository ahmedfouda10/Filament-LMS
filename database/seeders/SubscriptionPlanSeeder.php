<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            'Full access to 150+ Clinical Study Cases',
            'Downloadable PDFs and presentations',
            'Interactive quizzes with explanations',
            'Priority instructor support',
            'Certificates of completion',
            'Monthly new case additions',
        ];

        $plans = [
            ['name' => 'Monthly', 'description' => 'Perfect for trying out the platform', 'duration_months' => 1, 'price_per_month' => 450, 'total_price' => 450, 'savings_percentage' => 0, 'features' => $features, 'is_popular' => false, 'is_active' => true],
            ['name' => '3 Months', 'description' => 'Great for focused study periods', 'duration_months' => 3, 'price_per_month' => 390, 'total_price' => 1170, 'savings_percentage' => 13, 'features' => $features, 'is_popular' => false, 'is_active' => true],
            ['name' => '6 Months', 'description' => 'Our most popular plan', 'duration_months' => 6, 'price_per_month' => 350, 'total_price' => 2100, 'savings_percentage' => 22, 'features' => $features, 'is_popular' => true, 'is_active' => true],
            ['name' => 'Annual', 'description' => 'Best value for committed learners', 'duration_months' => 12, 'price_per_month' => 300, 'total_price' => 3600, 'savings_percentage' => 33, 'features' => $features, 'is_popular' => false, 'is_active' => true],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
