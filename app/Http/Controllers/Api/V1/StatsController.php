<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    public function index(): JsonResponse
    {
        $stats = Cache::remember('public_stats', 3600, function () {
            return [
                'active_students' => User::where('role', 'student')->where('is_active', true)->count(),
                'total_courses' => Course::where('is_published', true)->count(),
                'total_instructors' => User::where('role', 'instructor')->where('is_active', true)->count(),
                'average_rating' => round(Review::where('is_approved', true)->avg('rating') ?? 0, 1),
            ];
        });

        return response()->json(['data' => $stats]);
    }
}
