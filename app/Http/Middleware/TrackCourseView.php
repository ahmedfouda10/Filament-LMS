<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\CourseAnalytic;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackCourseView
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->isMethod('get') && $request->route('slug')) {
            $course = Course::where('slug', $request->route('slug'))->first();
            if ($course) {
                CourseAnalytic::updateOrCreate(
                    ['course_id' => $course->id, 'date' => today()],
                    ['views' => DB::raw('views + 1'), 'unique_visitors' => DB::raw('unique_visitors + 1')]
                );
            }
        }

        return $response;
    }
}
