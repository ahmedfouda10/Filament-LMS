<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\InstructorResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class InstructorController extends Controller
{
    public function index(): JsonResponse
    {
        $instructors = User::where('role', 'instructor')
            ->where('is_active', true)
            ->with('instructorProfile')
            ->get()
            ->map(function ($instructor) {
                $courses = $instructor->courses()->where('is_published', true);
                return [
                    'id' => $instructor->id,
                    'name' => $instructor->name,
                    'avatar' => $instructor->avatar,
                    'specialization' => $instructor->instructorProfile?->specialization,
                    'courses_count' => $courses->count(),
                    'total_students' => $courses->withCount('enrollments')->get()->sum('enrollments_count'),
                    'average_rating' => round($instructor->courses()->with('reviews')->get()->flatMap->reviews->avg('rating') ?? 0, 1),
                ];
            });

        return response()->json(['data' => $instructors]);
    }

    public function show(int $id): JsonResponse
    {
        $instructor = User::where('id', $id)
            ->where('role', 'instructor')
            ->with([
                'instructorProfile',
                'courses' => function ($query) {
                    $query->where('is_published', true);
                },
            ])
            ->firstOrFail();

        return response()->json([
            'data' => new InstructorResource($instructor),
        ]);
    }
}
