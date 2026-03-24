<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseAnalytic;
use Illuminate\Http\Request;

class CourseAnalyticsController extends Controller
{
    public function show(Request $request, Course $course)
    {
        abort_unless($course->instructor_id === $request->user()->id, 403);
        $days = $request->input('period', 30);
        $analytics = CourseAnalytic::where('course_id', $course->id)->where('date', '>=', now()->subDays($days))->orderBy('date')->get();

        $modules = $course->modules()->withCount('lessons')
            ->with(['lessons' => fn ($q) => $q->withCount('completions')])->get()
            ->map(fn ($m) => ['id' => $m->id, 'title' => $m->title, 'lessons_count' => $m->lessons_count, 'completions' => $m->lessons->sum('completions_count')]);

        return response()->json(['data' => [
            'daily' => $analytics,
            'totals' => ['total_views' => $analytics->sum('views'), 'total_unique_visitors' => $analytics->sum('unique_visitors'), 'total_enrollments' => $analytics->sum('enrollments')],
            'modules' => $modules,
        ]]);
    }
}
