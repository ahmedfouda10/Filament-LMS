<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number', 'user_id', 'promo_code_id', 'subtotal', 'discount',
        'total', 'payment_method', 'status', 'billing_street', 'billing_city',
        'billing_state', 'billing_country', 'billing_postal_code', 'paid_at',
        'paymob_order_id',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Phase 2 relationships
    public function paymentLogs() { return $this->hasMany(PaymentLog::class); }
    public function refundRequest() { return $this->hasOne(RefundRequest::class); }
    public function installmentPlan() { return $this->hasOne(InstallmentPlan::class); }
}
