<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstructorResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class InstructorController extends Controller
{
    public function show(int $id): JsonResponse
    {
        $instructor = User::where('id', $id)
            ->where('role', 'instructor')
            ->with([
                'instructorProfile',
                'courses' => function ($query) {
                    $query->where('status', 'published')
                        ->withCount('enrollments as students_count')
                        ->withAvg('reviews as average_rating', 'rating');
                },
            ])
            ->firstOrFail();

        return response()->json([
            'data' => new InstructorResource($instructor),
        ]);
    }
}
