<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 8);

        $reviews = Review::where('is_approved', true)
            ->where('rating', '>=', 4)
            ->whereNotNull('comment')
            ->with(['user:id,name,avatar,title', 'course:id,title'])
            ->inRandomOrder()
            ->limit(min($limit, 20))
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'comment' => $r->comment,
                'rating' => $r->rating,
                'user' => [
                    'name' => $r->user->name,
                    'avatar' => $r->user->avatar,
                    'title' => $r->user->title ?? null,
                ],
                'course' => [
                    'title' => $r->course->title,
                ],
            ]);

        return response()->json(['data' => $reviews]);
    }
}
