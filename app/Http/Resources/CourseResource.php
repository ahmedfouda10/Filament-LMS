<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'image' => $this->image,
            'price' => (float) $this->price,
            'original_price' => $this->original_price ? (float) $this->original_price : null,
            'level' => $this->level,
            'language' => $this->language,
            'is_bundle' => $this->is_bundle,
            'is_featured' => $this->is_featured,
            'average_rating' => $this->average_rating,
            'students_count' => $this->students_count,
            'total_duration' => $this->total_duration,
            'instructor' => new InstructorBriefResource($this->whenLoaded('instructor')),
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
