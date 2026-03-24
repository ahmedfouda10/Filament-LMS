<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CourseResource;
use App\Models\Category;
use App\Models\CategorySubscription;
use App\Models\Enrollment;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::withCount('courses')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    /**
     * GET /categories/{slug}
     * Category detail with stats and courses
     */
    public function show(string $slug): JsonResponse
    {
        $category = Category::where('slug', $slug)
            ->withCount('courses')
            ->firstOrFail();

        $courses = $category->courses()
            ->where('is_published', true)
            ->with('instructor:id,name,avatar')
            ->withCount('enrollments')
            ->get();

        $totalStudents = $courses->sum('enrollments_count');

        // Count free video lessons across all courses in this category
        $courseIds = $courses->pluck('id');
        $featuredVideosCount = Lesson::whereHas('module', function ($q) use ($courseIds) {
            $q->whereIn('course_id', $courseIds);
        })->where('type', 'video')->where('is_free', true)->count();

        return response()->json([
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'description' => $category->description,
                'courses_count' => $category->courses_count,
                'total_students' => $totalStudents,
                'featured_videos_count' => $featuredVideosCount,
                'courses' => CourseResource::collection($courses),
            ],
        ]);
    }

    /**
     * GET /categories/{slug}/videos
     * Featured/free video lessons in this category
     */
    public function videos(string $slug, Request $request): JsonResponse
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $courseIds = $category->courses()
            ->where('is_published', true)
            ->pluck('id');

        $query = Lesson::whereHas('module', function ($q) use ($courseIds) {
            $q->whereIn('course_id', $courseIds);
        })
            ->where('type', 'video')
            ->where('is_free', true)
            ->with([
                'module.course:id,title,slug,instructor_id',
                'module.course.instructor:id,name,avatar',
            ]);

        // Search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Sort
        $sortBy = $request->input('sort_by', 'newest');
        switch ($sortBy) {
            case 'most_viewed':
                $query->orderByDesc('views_count');
                break;
            case 'duration':
                $query->orderByDesc('duration_minutes');
                break;
            default:
                $query->latest('created_at');
        }

        $lessons = $query->paginate($request->input('per_page', 8));

        return response()->json([
            'data' => $lessons->through(function ($lesson) {
                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'type' => $lesson->type,
                    'duration_minutes' => $lesson->duration_minutes,
                    'video_url' => $lesson->video_url,
                    'thumbnail' => $lesson->thumbnail ?? null,
                    'views_count' => $lesson->views_count ?? 0,
                    'is_free' => $lesson->is_free,
                    'instructor' => $lesson->module?->course?->instructor ? [
                        'id' => $lesson->module->course->instructor->id,
                        'name' => $lesson->module->course->instructor->name,
                        'avatar' => $lesson->module->course->instructor->avatar,
                    ] : null,
                    'course' => $lesson->module?->course ? [
                        'id' => $lesson->module->course->id,
                        'title' => $lesson->module->course->title,
                        'slug' => $lesson->module->course->slug,
                    ] : null,
                ];
            }),
            'meta' => [
                'current_page' => $lessons->currentPage(),
                'per_page' => $lessons->perPage(),
                'total' => $lessons->total(),
                'last_page' => $lessons->lastPage(),
            ],
        ]);
    }

    public function subscribe(string $slug, Request $request): JsonResponse
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        CategorySubscription::firstOrCreate(
            ['user_id' => $request->user()->id, 'category_id' => $category->id],
            ['created_at' => now()]
        );
        return response()->json(['message' => 'Subscribed to category.'], 201);
    }

    public function unsubscribe(string $slug, Request $request): JsonResponse
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        CategorySubscription::where('user_id', $request->user()->id)
            ->where('category_id', $category->id)
            ->delete();
        return response()->json(['message' => 'Unsubscribed from category.']);
    }
}
