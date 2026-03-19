<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificateResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'certificate_number' => $this->certificate_number,
            'student_name' => $this->student_name,
            'certificate_url' => $this->certificate_url,
            'course' => new CourseBriefResource($this->whenLoaded('course')),
            'issued_at' => $this->issued_at?->toISOString(),
            'valid_until' => $this->valid_until?->toISOString(),
        ];
    }
}
