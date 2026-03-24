<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'sort_order' => $this->sort_order,
            'lessons_count' => $this->whenCounted('lessons', $this->lessons_count ?? $this->lessons()->count()),
            'total_duration_minutes' => $this->relationLoaded('lessons') ? $this->lessons->sum('duration_minutes') : $this->lessons()->sum('duration_minutes'),
            'lessons' => LessonResource::collection($this->whenLoaded('lessons')),
        ];
    }
}
