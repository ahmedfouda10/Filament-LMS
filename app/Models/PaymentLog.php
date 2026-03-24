<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentLog extends Model
{
    protected $fillable = ['order_id', 'transaction_id', 'payment_method', 'amount', 'currency', 'status', 'gateway_response', 'ip_address'];
    protected $hidden = ['gateway_response', 'ip_address'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'gateway_response' => 'array'];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
