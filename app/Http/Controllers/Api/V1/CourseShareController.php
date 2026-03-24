<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseShare;
use Illuminate\Http\Request;

class CourseShareController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $validated = $request->validate(['platform' => 'required|in:facebook,twitter,linkedin,whatsapp,copy_link']);

        CourseShare::create(['course_id' => $course->id, 'user_id' => $request->user()?->id, 'platform' => $validated['platform'], 'ip_address' => $request->ip(), 'created_at' => now()]);

        return response()->json(['message' => 'Share tracked.'], 201);
    }

    public function analytics(Request $request, Course $course)
    {
        abort_unless($course->instructor_id === $request->user()->id, 403);
        $days = $request->input('period', 30);

        $shares = CourseShare::where('course_id', $course->id)->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('platform, COUNT(*) as count')->groupBy('platform')->get();

        return response()->json(['data' => ['total_shares' => $shares->sum('count'), 'by_platform' => $shares, 'period_days' => (int) $days]]);
    }
}
