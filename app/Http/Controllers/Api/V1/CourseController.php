<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Http\Resources\CourseDetailResource;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 9);

        $query = Course::query()
            ->where('is_published', true)
            ->with(['instructor', 'category'])
            ->withCount('enrollments as students_count')
            ->withAvg('reviews as average_rating', 'rating');

        // Filter by category slug
        if ($request->filled('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Search by title or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by level
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by minimum rating
        if ($request->filled('rating')) {
            $query->having('average_rating', '>=', $request->rating);
        }

        // Filter by bundle
        if ($request->filled('is_bundle')) {
            $query->where('is_bundle', $request->boolean('is_bundle'));
        }

        // Sorting
        switch ($request->input('sort_by', 'newest')) {
            case 'popular':
                $query->orderByDesc('students_count');
                break;
            case 'highest_rated':
                $query->orderByDesc('average_rating');
                break;
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'newest':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        $courses = $query->paginate($perPage);

        return response()->json(
            CourseResource::collection($courses)->response()->getData(true)
        );
    }

    public function featured(): JsonResponse
    {
        $courses = Course::query()
            ->where('is_published', true)
            ->where('is_featured', true)
            ->with(['instructor', 'category'])
            ->withCount('enrollments as students_count')
            ->withAvg('reviews as average_rating', 'rating')
            ->limit(8)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => CourseResource::collection($courses),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $course = Course::where('slug', $slug)
            ->where('is_published', true)
            ->with([
                'modules.lessons',
                'instructor.instructorProfile',
                'category',
                'reviews.user',
            ])
            ->withCount('enrollments as students_count')
            ->withAvg('reviews as average_rating', 'rating')
            ->firstOrFail();

        return response()->json([
            'data' => new CourseDetailResource($course),
        ]);
    }
}
