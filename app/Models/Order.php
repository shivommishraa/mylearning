<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'address_id', 'coupon_id', 'order_number', 'delivery_address',
        'subtotal', 'discount', 'delivery_fee', 'total', 'coupon_code',
        'payment_method', 'payment_status', 'status', 'customer_note',
    ];

    protected function casts(): array
    {
        return [
            'delivery_address' => 'array',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
