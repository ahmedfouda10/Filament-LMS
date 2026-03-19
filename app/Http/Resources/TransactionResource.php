<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_number' => $this->transaction_number,
            'type' => $this->type,
            'course' => new CourseBriefResource($this->whenLoaded('course')),
            'amount' => (float) $this->amount,
            'platform_fee' => (float) $this->platform_fee,
            'net_amount' => (float) $this->net_amount,
            'status' => $this->status,
            'payout_method' => $this->payout_method,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
