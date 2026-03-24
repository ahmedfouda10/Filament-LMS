<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_text' => $this->question_text,
            'sort_order' => $this->sort_order,
            'options' => QuizOptionResource::collection($this->whenLoaded('options')),
            // Note: explanation is NOT included here (shown after attempt)
        ];
    }
}
