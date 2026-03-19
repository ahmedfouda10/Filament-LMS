<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course' => new CourseBriefResource($this->whenLoaded('course')),
            'progress_percentage' => (float) $this->progress_percentage,
            'enrolled_at' => $this->enrolled_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'last_accessed_lesson_id' => $this->last_accessed_lesson_id,
        ];
    }
}
