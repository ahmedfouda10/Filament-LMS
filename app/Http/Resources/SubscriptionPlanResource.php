<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'duration_months' => $this->duration_months,
            'price_per_month' => (float) $this->price_per_month,
            'total_price' => (float) $this->total_price,
            'savings_percentage' => (float) $this->savings_percentage,
            'features' => $this->features,
            'is_popular' => $this->is_popular,
        ];
    }
}
