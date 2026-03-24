<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentPlan extends Model
{
    protected $fillable = ['order_id', 'user_id', 'provider', 'total_amount', 'monthly_amount', 'months', 'paid_months', 'status', 'next_payment_date', 'provider_reference'];

    protected function casts(): array
    {
        return ['total_amount' => 'decimal:2', 'monthly_amount' => 'decimal:2', 'next_payment_date' => 'date'];
    }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function getRemainingMonthsAttribute(): int { return $this->months - $this->paid_months; }
    public function getRemainingAmountAttribute(): float { return $this->monthly_amount * $this->remaining_months; }
    public function scopeActive(Builder $query): Builder { return $query->where('status', 'active'); }
}
