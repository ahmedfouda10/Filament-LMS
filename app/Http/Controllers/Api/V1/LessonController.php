<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLessonRequest;
use App\Models\Lesson;
use App\Models\CourseModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function store(int $moduleId, StoreLessonRequest $request): JsonResponse
    {
        $module = CourseModule::with('course')->findOrFail($moduleId);

        $this->verifyOwnership($module, $request);

        $lesson = $module->lessons()->create($request->validated());

        return response()->json([
            'data' => $lesson,
            'message' => 'Lesson created successfully.',
        ], 201);
    }

    public function update(int $id, StoreLessonRequest $request): JsonResponse
    {
        $lesson = Lesson::with('module.course')->findOrFail($id);

        $this->verifyOwnership($lesson->module, $request);

        $lesson->update($request->validated());

        return response()->json([
            'data' => $lesson,
            'message' => 'Lesson updated successfully.',
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $lesson = Lesson::with('module.course')->findOrFail($id);

        $this->verifyOwnership($lesson->module, $request);

        $lesson->delete();

        return response()->json([
            'message' => 'Lesson deleted successfully.',
        ]);
    }

    public function reorder(int $moduleId, Request $request): JsonResponse
    {
        $module = CourseModule::with('course')->findOrFail($moduleId);
        $this->verifyOwnership($module, $request);

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:lessons,id',
        ]);

        foreach ($validated['order'] as $index => $lessonId) {
            Lesson::where('id', $lessonId)
                ->where('module_id', $module->id)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'message' => 'Lessons reordered successfully.',
        ]);
    }

    private function verifyOwnership(CourseModule $module, Request $request): void
    {
        if ($module->course->instructor_id !== $request->user()->id) {
            abort(403, 'You do not have permission to modify this resource.');
        }
    }
}
