<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CourseDetailResource extends CourseResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'description' => $this->description,
            'requirements' => $this->requirements,
            'learning_outcomes' => $this->learning_outcomes,
            'tags' => $this->tags,
            'modules' => ModuleResource::collection($this->whenLoaded('modules')),
            'reviews_count' => $this->reviews()->count(),
            'instructor' => new InstructorResource($this->whenLoaded('instructor')),
        ]);
    }
}
