<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'duration_months', 'price_per_month',
        'total_price', 'savings_percentage', 'features', 'is_popular', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price_per_month' => 'decimal:2',
            'total_price' => 'decimal:2',
            'savings_percentage' => 'decimal:2',
            'is_popular' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }
}
