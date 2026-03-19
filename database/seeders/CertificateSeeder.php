<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $completedEnrollments = Enrollment::whereNotNull('completed_at')
            ->with(['user', 'course'])
            ->get();

        foreach ($completedEnrollments as $enrollment) {
            Certificate::updateOrCreate(
                ['user_id' => $enrollment->user_id, 'course_id' => $enrollment->course_id],
                [
                    'certificate_number' => 'CERT-' . strtoupper(Str::random(8)),
                    'student_name' => $enrollment->user->name,
                    'issued_at' => $enrollment->completed_at,
                    'valid_until' => $enrollment->completed_at->copy()->addYears(2),
                ]
            );
        }
    }
}
