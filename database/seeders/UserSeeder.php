<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::updateOrCreate(['email' => 'admin@spc-academy.com'], [
            'name' => 'Admin',
            'role' => 'admin',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // 5 Instructors
        $instructors = [
            ['name' => 'Dr. Ahmed Hassan', 'email' => 'ahmed@spc-academy.com'],
            ['name' => 'Dr. Mona Ibrahim', 'email' => 'mona@spc-academy.com'],
            ['name' => 'Dr. Khaled Mostafa', 'email' => 'khaled@spc-academy.com'],
            ['name' => 'Dr. Sara El-Sayed', 'email' => 'sara@spc-academy.com'],
            ['name' => 'Dr. Omar Farouk', 'email' => 'omar@spc-academy.com'],
        ];

        foreach ($instructors as $instructor) {
            User::updateOrCreate(['email' => $instructor['email']], [
                'name' => $instructor['name'],
                'role' => 'instructor',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }

        // 20 Students via factory (only create if we don't already have 20 students)
        $existingStudents = User::where('role', 'student')->count();
        $needed = 20 - $existingStudents;
        if ($needed > 0) {
            User::factory($needed)->create();
        }
    }
}
