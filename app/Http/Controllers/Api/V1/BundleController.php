<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseBundleItem;
use App\Models\Review;
use Illuminate\Http\Request;

class BundleController extends Controller
{
    public function show(string $slug)
    {
        $bundle = Course::where('slug', $slug)->where('is_bundle', true)->where('is_published', true)
            ->with(['bundledCourses' => function ($q) {
                $q->select('courses.id', 'title', 'slug', 'image', 'price', 'original_price', 'instructor_id')
                  ->with('instructor:id,name,avatar');
            }])
            ->with('instructor:id,name,avatar')
            ->with('category:id,name,slug')
            ->with('reviews', fn ($q) => $q->where('is_approved', true)->with('user:id,name,avatar')->latest()->limit(10))
            ->firstOrFail();

        $totalIndividualPrice = $bundle->bundledCourses->sum('price');

        // Collect unique instructors from bundled courses (#13)
        $instructors = $bundle->bundledCourses
            ->pluck('instructor')->filter()->unique('id')->values()
            ->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'avatar' => $i->avatar]);

        // Bundled courses with lessons_count + total_duration (#14)
        $coursesWithStats = $bundle->bundledCourses->map(function ($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'image' => $course->image,
                'price' => (float) $course->price,
                'original_price' => $course->original_price ? (float) $course->original_price : null,
                'instructor' => $course->instructor ? ['id' => $course->instructor->id, 'name' => $course->instructor->name] : null,
                'lessons_count' => $course->getTotalLessonsCount(),
                'total_duration' => $course->total_duration,
            ];
        });

        return response()->json(['data' => [
            'id' => $bundle->id,
            'title' => $bundle->title,
            'slug' => $bundle->slug,
            'short_description' => $bundle->short_description,
            'description' => $bundle->description,
            'image' => $bundle->image,
            'price' => (float) $bundle->price,
            'original_price' => (float) $bundle->original_price,
            'total_individual_price' => (float) $totalIndividualPrice,
            'you_save' => (float) ($totalIndividualPrice - $bundle->price),
            'is_bundle' => true,
            'average_rating' => $bundle->average_rating,
            'reviews_count' => $bundle->reviews->count(),
            'students_count' => $bundle->students_count,
            'total_duration' => $bundle->total_duration,
            'requirements' => $bundle->requirements,
            'learning_outcomes' => $bundle->learning_outcomes,
            'instructor' => $bundle->instructor,
            'instructors' => $instructors,
            'category' => $bundle->category,
            'courses' => $coursesWithStats,
            'reviews' => $bundle->reviews->map(fn ($r) => [
                'id' => $r->id,
                'user' => ['name' => $r->user->name, 'avatar' => $r->user->avatar],
                'rating' => $r->rating,
                'comment' => $r->comment,
                'created_at' => $r->created_at,
            ]),
        ]]);
    }

    public function addCourse(Request $request, Course $course)
    {
        abort_unless($course->is_bundle, 422, 'Not a bundle.');
        abort_unless($course->instructor_id === $request->user()->id, 403);
        $validated = $request->validate(['course_id' => 'required|exists:courses,id']);
        abort_if((int) $validated['course_id'] === $course->id, 422, 'Cannot add bundle to itself.');
        CourseBundleItem::firstOrCreate(['bundle_id' => $course->id, 'course_id' => $validated['course_id']], ['sort_order' => $course->bundleItems()->count()]);
        return response()->json(['message' => 'Course added to bundle.'], 201);
    }

    public function removeCourse(Request $request, CourseBundleItem $bundleItem)
    {
        abort_unless($bundleItem->bundle->instructor_id === $request->user()->id, 403);
        $bundleItem->delete();
        return response()->json(['message' => 'Course removed from bundle.']);
    }

    public function reorder(Request $request, Course $course)
    {
        abort_unless($course->instructor_id === $request->user()->id, 403);
        $validated = $request->validate(['items' => 'required|array', 'items.*.id' => 'required|exists:course_bundle_items,id', 'items.*.sort_order' => 'required|integer|min:0']);
        foreach ($validated['items'] as $item) { CourseBundleItem::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]); }
        return response()->json(['message' => 'Bundle reordered.']);
    }
}
