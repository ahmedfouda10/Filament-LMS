<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'items' => CartItemResource::collection($this->whenLoaded('items')),
            'promo_code' => $this->whenLoaded('promoCode', fn() => [
                'code' => $this->promoCode->code,
                'discount_percentage' => (float) $this->promoCode->discount_percentage,
            ]),
            'subtotal' => $this->whenLoaded('items', fn() => (float) $this->items->sum('price')),
            'discount' => $this->whenLoaded('items', function () {
                $subtotal = $this->items->sum('price');
                if ($this->promoCode) {
                    return round($subtotal * ($this->promoCode->discount_percentage / 100), 2);
                }
                return 0;
            }),
            'total' => $this->whenLoaded('items', function () {
                $subtotal = $this->items->sum('price');
                $discount = $this->promoCode ? round($subtotal * ($this->promoCode->discount_percentage / 100), 2) : 0;
                return round($subtotal - $discount, 2);
            }),
        ];
    }
}
