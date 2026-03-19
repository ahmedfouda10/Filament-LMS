<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'SPC Online Academy'],
            ['key' => 'logo', 'value' => '/images/logo-spc.png'],
            ['key' => 'contact_phone', 'value' => '+20 100 123 4567'],
            ['key' => 'contact_email', 'value' => 'support@spc-academy.com'],
            ['key' => 'primary_color', 'value' => '#236bba'],
            ['key' => 'secondary_color', 'value' => '#0f172a'],
            ['key' => 'platform_fee_percentage', 'value' => '20'],
            ['key' => 'certificate_validity_years', 'value' => '2'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
