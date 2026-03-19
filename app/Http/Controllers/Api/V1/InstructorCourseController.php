<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\CourseDetailResource;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstructorCourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $courses = $request->user()
            ->courses()
            ->with('category')
            ->withCount('enrollments as students_count')
            ->withAvg('reviews as average_rating', 'rating')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 10));

        return response()->json(
            CourseResource::collection($courses)->response()->getData(true)
        );
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $course = $request->user()
            ->courses()
            ->with([
                'modules.lessons',
                'category',
                'reviews.user',
            ])
            ->withCount('enrollments as students_count')
            ->withAvg('reviews as average_rating', 'rating')
            ->findOrFail($id);

        return response()->json([
            'data' => new CourseDetailResource($course),
        ]);
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['instructor_id'] = $request->user()->id;

        $course = Course::create($data);
        $course->load('category');

        return response()->json([
            'data' => new CourseResource($course),
            'message' => 'Course created successfully.',
        ], 201);
    }

    public function update(int $id, UpdateCourseRequest $request): JsonResponse
    {
        $course = $request->user()
            ->courses()
            ->findOrFail($id);

        $course->update($request->validated());
        $course->load('category');
        $course->loadCount('enrollments as students_count');
        $course->loadAvg('reviews as average_rating', 'rating');

        return response()->json([
            'data' => new CourseResource($course),
            'message' => 'Course updated successfully.',
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $course = $request->user()
            ->courses()
            ->findOrFail($id);

        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully.',
        ]);
    }
}
