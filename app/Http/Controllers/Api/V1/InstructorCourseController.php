<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Http\Resources\CourseDetailResource;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\InstructorTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstructorCourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->courses()->with('category')
            ->withCount('enrollments as students_count')
            ->withAvg('reviews as average_rating', 'rating');

        // #32 - Published/Draft filter
        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            }
        }

        // #32 - Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $courses = $query->orderByDesc('updated_at')
            ->paginate($request->input('per_page', 10));

        // #33 - Add revenue per course
        $courseData = $courses->through(function ($course) use ($request) {
            $data = (new CourseResource($course))->toArray(request());
            $data['revenue'] = (float) InstructorTransaction::where('instructor_id', $request->user()->id)
                ->where('course_id', $course->id)
                ->where('type', 'sale')
                ->sum('net_amount');
            $data['updated_at'] = $course->updated_at;
            return $data;
        });

        return response()->json([
            'data' => $courseData,
            'meta' => [
                'current_page' => $courses->currentPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
                'last_page' => $courses->lastPage(),
            ],
        ]);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $course = $request->user()->courses()
            ->with(['modules.lessons.quiz', 'category', 'reviews.user'])
            ->withCount('enrollments as students_count')
            ->withAvg('reviews as average_rating', 'rating')
            ->findOrFail($id);

        // #34 - Additional stats
        $revenue = (float) InstructorTransaction::where('instructor_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->where('type', 'sale')
            ->sum('net_amount');

        $totalEnrollments = $course->enrollments()->count();
        $completedEnrollments = $course->enrollments()->whereNotNull('completed_at')->count();
        $completionRate = $totalEnrollments > 0
            ? round(($completedEnrollments / $totalEnrollments) * 100, 1)
            : 0;

        $reviewsCount = $course->reviews()->where('is_approved', true)->count();

        // New enrollments this month
        $newEnrollments = $course->enrollments()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $courseData = (new CourseDetailResource($course))->toArray(request());
        $courseData['revenue'] = round($revenue, 2);
        $courseData['completion_rate'] = $completionRate;
        $courseData['reviews_count'] = $reviewsCount;
        $courseData['new_enrollments_this_month'] = $newEnrollments;
        $courseData['is_published'] = $course->is_published;
        $courseData['updated_at'] = $course->updated_at;

        return response()->json(['data' => $courseData]);
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
        $course = $request->user()->courses()->findOrFail($id);
        $course->update($request->validated());
        $course->load('category');
        $course->loadCount('enrollments as students_count');
        $course->loadAvg('reviews as average_rating', 'rating');

        return response()->json([
            'data' => new CourseResource($course),
            'message' => 'Course updated successfully.',
        ]);
    }

    public function students(int $id, Request $request): JsonResponse
    {
        $course = $request->user()->courses()->findOrFail($id);

        $students = $course->enrollments()
            ->with('user:id,name,email,avatar')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json([
            'data' => $students->through(fn ($e) => [
                'id' => $e->id,
                'student' => [
                    'id' => $e->user->id,
                    'name' => $e->user->name,
                    'email' => $e->user->email,
                    'avatar' => $e->user->avatar,
                ],
                'progress_percentage' => (float) $e->progress_percentage,
                'enrolled_at' => $e->enrolled_at ?? $e->created_at,
                'completed_at' => $e->completed_at,
            ]),
            'meta' => [
                'current_page' => $students->currentPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'last_page' => $students->lastPage(),
            ],
        ]);
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $request->user()->courses()->findOrFail($id)->delete();
        return response()->json(['message' => 'Course deleted successfully.']);
    }
}
