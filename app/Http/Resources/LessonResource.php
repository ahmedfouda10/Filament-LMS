<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'duration_minutes' => $this->duration_minutes,
            'video_url' => $this->video_url,
            'content' => $this->content,
            'is_free' => $this->is_free,
            'sort_order' => $this->sort_order,
        ];
    }
}
