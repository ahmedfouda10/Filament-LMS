<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Enrollment;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentReviewController extends Controller
{
    /**
     * List student's reviews
     */
    public function index(Request $request): JsonResponse
    {
        $reviews = Review::where('user_id', $request->user()->id)
            ->with('course:id,title,slug,image')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => ReviewResource::collection($reviews),
        ]);
    }

    /**
     * Create or update review for a course
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Verify student is enrolled in the course
        $enrolled = Enrollment::where('user_id', $request->user()->id)
            ->where('course_id', $validated['course_id'])
            ->exists();

        if (!$enrolled) {
            return response()->json([
                'message' => 'You must be enrolled in this course to leave a review.',
            ], 403);
        }

        // One review per course per user (update if exists)
        $review = Review::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'course_id' => $validated['course_id'],
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'is_approved' => true,
            ]
        );

        $review->load('course:id,title,slug,image');

        return response()->json([
            'data' => new ReviewResource($review),
            'message' => $review->wasRecentlyCreated ? 'Review submitted successfully.' : 'Review updated successfully.',
        ], $review->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * Delete own review
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $review = Review::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully.',
        ]);
    }
}
