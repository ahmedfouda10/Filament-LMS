<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar' => $this->avatar,
            'profile' => new InstructorProfileResource($this->whenLoaded('instructorProfile')),
            'courses_count' => $this->courses()->published()->count(),
            'total_students' => $this->courses()->withCount('enrollments')->get()->sum('enrollments_count'),
            'average_rating' => round($this->courses()->with('reviews')->get()->flatMap->reviews->avg('rating') ?? 0, 1),
        ];
    }
}
