<?php

namespace Database\Seeders;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->inRandomOrder()->take(7)->get();
        $plans = SubscriptionPlan::all();

        if ($plans->isEmpty()) return;

        foreach ($students as $student) {
            $plan = $plans->random();
            $startDate = now()->subDays(rand(0, 60));

            Subscription::updateOrCreate(
                ['user_id' => $student->id],
                [
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'start_date' => $startDate,
                    'end_date' => $startDate->copy()->addMonths($plan->duration_months),
                    'auto_renew' => rand(0, 1) ? true : false,
                ]
            );
        }
    }
}
