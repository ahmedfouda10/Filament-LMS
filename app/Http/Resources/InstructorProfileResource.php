<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstructorProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'bio' => $this->bio,
            'specialization' => $this->specialization,
            'years_of_experience' => $this->years_of_experience,
            'qualifications' => $this->qualifications,
            'education' => $this->education,
            'expertise' => $this->expertise,
            'social_links' => $this->social_links,
        ];
    }
}
