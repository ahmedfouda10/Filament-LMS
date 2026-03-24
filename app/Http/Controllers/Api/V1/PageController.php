<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $allowedPages = ['terms', 'privacy', 'faq', 'about'];
        abort_unless(in_array($slug, $allowedPages), 404, 'Page not found.');

        // About returns structured data from settings
        if ($slug === 'about') {
            return response()->json([
                'data' => [
                    'slug' => 'about',
                    'title' => Setting::get('about_title') ?? 'About SPC Online Academy',
                    'description' => Setting::get('about_description') ?? '',
                    'mission' => Setting::get('about_mission') ?? '',
                    'vision' => Setting::get('about_vision') ?? '',
                    'values' => json_decode(Setting::get('about_values') ?? '[]', true),
                    'last_updated' => Setting::get('about_updated') ?? null,
                ],
            ]);
        }

        // FAQ returns structured data from faqs table
        if ($slug === 'faq') {
            $faqs = Faq::where('is_active', true)
                ->orderBy('category')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('category')
                ->map(fn ($items) => $items->map(fn ($f) => [
                    'id' => $f->id,
                    'question' => $f->question,
                    'answer' => $f->answer,
                ])->values());

            return response()->json([
                'data' => [
                    'slug' => 'faq',
                    'title' => 'Frequently Asked Questions',
                    'categories' => $faqs,
                ],
            ]);
        }

        // Terms / Privacy return HTML content from settings
        $content = Setting::get("page_{$slug}");
        $updated = Setting::get("page_{$slug}_updated");

        $titles = [
            'terms' => 'Terms of Service',
            'privacy' => 'Privacy Policy',
        ];

        return response()->json([
            'data' => [
                'slug' => $slug,
                'title' => $titles[$slug] ?? $slug,
                'content' => $content ?? '',
                'last_updated' => $updated ?? null,
            ],
        ]);
    }
}
