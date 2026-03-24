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
            'thumbnail' => $this->thumbnail,
            'content' => $this->content,
            'is_free' => $this->is_free,
            'sort_order' => $this->sort_order,
            'views_count' => $this->views_count ?? 0,
            'quiz' => $this->when($this->relationLoaded('quiz') && $this->quiz, fn () => [
                'id' => $this->quiz->id,
                'title' => $this->quiz->title,
                'questions_count' => $this->quiz->questions()->count(),
                'passing_score' => $this->quiz->passing_score,
                'max_attempts' => $this->quiz->max_attempts,
                'time_limit_minutes' => $this->quiz->time_limit_minutes,
            ]),
        ];
    }
}
