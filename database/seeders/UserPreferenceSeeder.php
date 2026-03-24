<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Seeder;

class UserPreferenceSeeder extends Seeder
{
    public function run(): void
    {
        foreach (User::all() as $user) {
            $isArabic = rand(1, 100) <= 60;
            UserPreference::firstOrCreate(['user_id' => $user->id], [
                'theme' => rand(1, 100) <= 60 ? 'light' : 'dark',
                'language' => $isArabic ? 'ar' : 'en',
                'direction' => $isArabic ? 'rtl' : 'ltr',
            ]);
        }
    }
}
