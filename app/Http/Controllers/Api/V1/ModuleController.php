<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreModuleRequest;
use App\Models\Course;
use App\Models\CourseModule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function store(int $courseId, StoreModuleRequest $request): JsonResponse
    {
        $course = $this->getInstructorCourse($courseId, $request);

        $module = $course->modules()->create($request->validated());

        return response()->json([
            'data' => $module,
            'message' => 'Module created successfully.',
        ], 201);
    }

    public function update(int $id, StoreModuleRequest $request): JsonResponse
    {
        $module = CourseModule::with('course')->findOrFail($id);

        $this->verifyOwnership($module->course, $request);

        $module->update($request->validated());

        return response()->json([
            'data' => $module,
            'message' => 'Module updated successfully.',
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $module = CourseModule::with('course')->findOrFail($id);

        $this->verifyOwnership($module->course, $request);

        $module->delete();

        return response()->json([
            'message' => 'Module deleted successfully.',
        ]);
    }

    public function reorder(int $courseId, Request $request): JsonResponse
    {
        $course = $this->getInstructorCourse($courseId, $request);

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:course_modules,id',
        ]);

        foreach ($validated['order'] as $index => $moduleId) {
            CourseModule::where('id', $moduleId)
                ->where('course_id', $course->id)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'message' => 'Modules reordered successfully.',
        ]);
    }

    private function getInstructorCourse(int $courseId, Request $request): Course
    {
        return $request->user()
            ->courses()
            ->findOrFail($courseId);
    }

    private function verifyOwnership(Course $course, Request $request): void
    {
        if ($course->instructor_id !== $request->user()->id) {
            abort(403, 'You do not have permission to modify this resource.');
        }
    }
}
