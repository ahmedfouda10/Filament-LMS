<?php

namespace Database\Seeders;

use App\Models\InstructorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class InstructorProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'email' => 'ahmed@spc-academy.com',
                'bio' => 'Dr. Ahmed Hassan is a board-certified internist with over 15 years of clinical experience. He specializes in teaching complex clinical case analysis and diagnostic reasoning.',
                'specialization' => 'Internal Medicine',
                'years_of_experience' => 15,
                'qualifications' => ['MBBS', 'MD Internal Medicine', 'MRCP (UK)'],
                'education' => [
                    ['degree' => 'MBBS', 'institution' => 'Cairo University', 'year' => 2005],
                    ['degree' => 'MD Internal Medicine', 'institution' => 'Ain Shams University', 'year' => 2010],
                    ['degree' => 'MRCP', 'institution' => 'Royal College of Physicians, UK', 'year' => 2012],
                ],
                'expertise' => ['Internal Medicine', 'Clinical Diagnosis', 'Case-Based Learning', 'Board Preparation'],
                'social_links' => ['linkedin' => 'https://linkedin.com/in/dr-ahmed-hassan', 'twitter' => 'https://twitter.com/dr_ahmed'],
            ],
            [
                'email' => 'mona@spc-academy.com',
                'bio' => 'Dr. Mona Ibrahim is a pediatric specialist known for her innovative teaching methods. She has trained hundreds of residents in pediatric emergency care.',
                'specialization' => 'Pediatrics',
                'years_of_experience' => 12,
                'qualifications' => ['MBBS', 'MD Pediatrics', 'Fellowship in Pediatric Emergency'],
                'education' => [
                    ['degree' => 'MBBS', 'institution' => 'Alexandria University', 'year' => 2008],
                    ['degree' => 'MD Pediatrics', 'institution' => 'Cairo University', 'year' => 2013],
                ],
                'expertise' => ['Pediatrics', 'PALS', 'Neonatal Care', 'Pediatric Emergency', 'Medical Education'],
                'social_links' => ['linkedin' => 'https://linkedin.com/in/dr-mona-ibrahim'],
            ],
            [
                'email' => 'khaled@spc-academy.com',
                'bio' => 'Dr. Khaled Mostafa is a renowned cardiologist and ECG interpretation expert. He has authored multiple publications in cardiac electrophysiology.',
                'specialization' => 'Cardiology',
                'years_of_experience' => 18,
                'qualifications' => ['MBBS', 'MD Cardiology', 'FACC', 'Fellowship in Interventional Cardiology'],
                'education' => [
                    ['degree' => 'MBBS', 'institution' => 'Ain Shams University', 'year' => 2003],
                    ['degree' => 'MD Cardiology', 'institution' => 'Cairo University', 'year' => 2008],
                    ['degree' => 'Fellowship', 'institution' => 'Cleveland Clinic', 'year' => 2011],
                ],
                'expertise' => ['Cardiology', 'ECG Interpretation', 'ACLS', 'Interventional Cardiology', 'Cardiac Imaging'],
                'social_links' => ['linkedin' => 'https://linkedin.com/in/dr-khaled-mostafa', 'twitter' => 'https://twitter.com/dr_khaled'],
            ],
            [
                'email' => 'sara@spc-academy.com',
                'bio' => 'Dr. Sara El-Sayed is a dermatology consultant with special interest in clinical dermatology and dermatoscopy. She is passionate about visual learning in medicine.',
                'specialization' => 'Dermatology',
                'years_of_experience' => 10,
                'qualifications' => ['MBBS', 'MD Dermatology', 'Diploma in Dermatoscopy'],
                'education' => [
                    ['degree' => 'MBBS', 'institution' => 'Mansoura University', 'year' => 2010],
                    ['degree' => 'MD Dermatology', 'institution' => 'Cairo University', 'year' => 2015],
                ],
                'expertise' => ['Dermatology', 'Dermatoscopy', 'Clinical Procedures', 'Medical Photography'],
                'social_links' => ['linkedin' => 'https://linkedin.com/in/dr-sara-elsayed'],
            ],
            [
                'email' => 'omar@spc-academy.com',
                'bio' => 'Dr. Omar Farouk is a general surgeon with extensive experience in both academic surgery and surgical skills training. He specializes in case-based surgical education.',
                'specialization' => 'Surgery',
                'years_of_experience' => 20,
                'qualifications' => ['MBBS', 'MS General Surgery', 'FRCS (Edinburgh)', 'Fellowship in Laparoscopic Surgery'],
                'education' => [
                    ['degree' => 'MBBS', 'institution' => 'Cairo University', 'year' => 2000],
                    ['degree' => 'MS General Surgery', 'institution' => 'Ain Shams University', 'year' => 2006],
                    ['degree' => 'FRCS', 'institution' => 'Royal College of Surgeons, Edinburgh', 'year' => 2009],
                ],
                'expertise' => ['General Surgery', 'Laparoscopic Surgery', 'Surgical Skills', 'Case Studies', 'Trauma Surgery'],
                'social_links' => ['linkedin' => 'https://linkedin.com/in/dr-omar-farouk', 'twitter' => 'https://twitter.com/dr_omar'],
            ],
        ];

        foreach ($profiles as $profileData) {
            $user = User::where('email', $profileData['email'])->first();
            if (!$user) {
                continue;
            }

            $data = collect($profileData)->except('email')->toArray();
            InstructorProfile::updateOrCreate(
                ['user_id' => $user->id],
                $data
            );
        }
    }
}
