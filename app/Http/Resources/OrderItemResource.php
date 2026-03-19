<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'instructor_name' => $this->instructor_name,
            'price' => (float) $this->price,
            'original_price' => $this->original_price ? (float) $this->original_price : null,
        ];
    }
}
