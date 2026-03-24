<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\VideoProgress;
use Illuminate\Http\Request;

class VideoProgressController extends Controller
{
    public function update(Request $request, Lesson $lesson)
    {
        $validated = $request->validate([
            'progress_percentage' => 'required|numeric|min:0|max:100',
            'last_position_seconds' => 'required|integer|min:0',
        ]);

        $courseId = $lesson->module->course_id;
        $enrollment = Enrollment::where('user_id', $request->user()->id)->where('course_id', $courseId)->firstOrFail();

        $progress = VideoProgress::updateOrCreate(
            ['user_id' => $request->user()->id, 'lesson_id' => $lesson->id],
            ['progress_percentage' => $validated['progress_percentage'], 'last_position_seconds' => $validated['last_position_seconds'], 'updated_at' => now()]
        );

        if ($validated['progress_percentage'] >= 75) {
            LessonCompletion::firstOrCreate(['user_id' => $request->user()->id, 'lesson_id' => $lesson->id], ['completed_at' => now()]);
            $this->recalculateProgress($enrollment);
        }

        return response()->json(['data' => $progress]);
    }

    public function show(Request $request, Lesson $lesson)
    {
        $progress = VideoProgress::where('user_id', $request->user()->id)->where('lesson_id', $lesson->id)->first();
        return response()->json(['data' => $progress ? ['progress_percentage' => (float) $progress->progress_percentage, 'last_position_seconds' => $progress->last_position_seconds] : ['progress_percentage' => 0, 'last_position_seconds' => 0]]);
    }

    private function recalculateProgress(Enrollment $enrollment): void
    {
        $course = $enrollment->course;
        $totalLessons = $course->modules()->withCount('lessons')->get()->sum('lessons_count');
        $completedLessons = LessonCompletion::where('user_id', $enrollment->user_id)
            ->whereIn('lesson_id', Lesson::whereIn('module_id', $course->modules()->pluck('id'))->pluck('id'))->count();
        $enrollment->update(['progress_percentage' => $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0]);
    }
}
