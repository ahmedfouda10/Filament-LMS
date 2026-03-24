<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = $request->user()->wishlists()
            ->with(['course' => fn ($q) => $q->select('id', 'title', 'slug', 'image', 'price', 'original_price', 'instructor_id')->with('instructor:id,name')])
            ->latest('created_at')
            ->paginate($request->input('per_page', 9));

        return response()->json([
            'data' => $wishlists->through(fn ($w) => ['id' => $w->id, 'course' => $w->course, 'created_at' => $w->created_at]),
            'meta' => ['current_page' => $wishlists->currentPage(), 'per_page' => $wishlists->perPage(), 'total' => $wishlists->total(), 'last_page' => $wishlists->lastPage()],
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $request->user()->wishlists()->firstOrCreate(['course_id' => $course->id], ['created_at' => now()]);
        return response()->json(['message' => 'Added to wishlist.'], 201);
    }

    public function destroy(Request $request, Course $course)
    {
        $request->user()->wishlists()->where('course_id', $course->id)->delete();
        return response()->json(['message' => 'Removed from wishlist.']);
    }
}
