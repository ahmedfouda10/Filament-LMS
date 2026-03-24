<?php

namespace Database\Seeders;

use App\Models\PromoCode;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    public function run(): void
    {
        $codes = [
            ['code' => 'SPC25', 'description' => '25% off any course', 'discount_percentage' => 25, 'max_uses' => 100, 'used_count' => 12, 'valid_from' => '2024-01-01', 'valid_until' => '2026-12-31', 'is_active' => true],
            ['code' => 'WELCOME10', 'description' => 'Welcome discount for new students', 'discount_percentage' => 10, 'max_uses' => 500, 'used_count' => 45, 'valid_from' => '2024-01-01', 'valid_until' => '2026-12-31', 'is_active' => true],
            ['code' => 'BUNDLE50', 'description' => '50% off bundle courses', 'discount_percentage' => 50, 'max_uses' => 50, 'used_count' => 8, 'valid_from' => '2024-01-01', 'valid_until' => '2026-06-30', 'is_active' => true],
        ];

        foreach ($codes as $code) {
            PromoCode::updateOrCreate(['code' => $code['code']], $code);
        }
    }
}
