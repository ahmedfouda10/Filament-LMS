<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key');

        // Only expose public settings (not business/payment internals)
        $public = [
            'site_name' => $settings['site_name'] ?? null,
            'logo' => $settings['logo'] ?? null,
            'site_description' => $settings['site_description'] ?? null,
            'contact_phone' => $settings['contact_phone'] ?? null,
            'contact_email' => $settings['contact_email'] ?? null,
            'address' => $settings['address'] ?? null,
            'working_hours' => $settings['working_hours'] ?? null,
            'primary_color' => $settings['primary_color'] ?? null,
            'secondary_color' => $settings['secondary_color'] ?? null,
            'facebook_url' => $settings['facebook_url'] ?? null,
            'twitter_url' => $settings['twitter_url'] ?? null,
            'instagram_url' => $settings['instagram_url'] ?? null,
            'linkedin_url' => $settings['linkedin_url'] ?? null,
            'youtube_url' => $settings['youtube_url'] ?? null,
            'meta_title' => $settings['meta_title'] ?? null,
            'meta_description' => $settings['meta_description'] ?? null,
            'meta_keywords' => $settings['meta_keywords'] ?? null,
            'currency' => $settings['currency'] ?? 'EGP',
            // Announcements
            'announcement_enabled' => ($settings['announcement_enabled'] ?? 'false') === 'true',
            'announcement_text' => $settings['announcement_text'] ?? null,
            'announcement_color' => $settings['announcement_color'] ?? null,
            'hero_video_url' => $settings['hero_video_url'] ?? null,
            // Maintenance
            'maintenance_mode' => in_array($settings['maintenance_mode'] ?? '0', ['true', '1'], true),
            'maintenance_message' => $settings['maintenance_message'] ?? null,
        ];

        return response()->json(['data' => $public]);
    }
}
