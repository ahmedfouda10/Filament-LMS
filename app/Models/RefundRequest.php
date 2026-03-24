<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundRequest extends Model
{
    protected $fillable = ['order_id', 'user_id', 'reason', 'status', 'admin_notes', 'requested_at', 'resolved_at'];

    protected function casts(): array
    {
        return ['requested_at' => 'datetime', 'resolved_at' => 'datetime'];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }
}
