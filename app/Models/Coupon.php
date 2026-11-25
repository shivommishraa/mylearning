<?php

namespace App\Models;

use App\Models\Concerns\HasStoreImage;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasStoreImage;

    protected $fillable = [
        'code', 'description', 'image', 'type', 'value', 'minimum_order',
        'maximum_discount', 'usage_limit', 'usage_count', 'starts_at', 'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'minimum_order' => 'decimal:2',
            'maximum_discount' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function discountFor(float $subtotal): float
    {
        if ($this->type === 'percent') {
            $discount = $subtotal * ((float) $this->value / 100);

            if ($this->maximum_discount !== null) {
                $discount = min($discount, (float) $this->maximum_discount);
            }

            return round($discount, 2);
        }

        return min((float) $this->value, $subtotal);
    }

    public function isAvailable(float $subtotal): bool
    {
        return $this->is_active
            && (! $this->starts_at || $this->starts_at->isPast())
            && (! $this->expires_at || $this->expires_at->isFuture())
            && (! $this->usage_limit || $this->usage_count < $this->usage_limit)
            && $subtotal >= (float) $this->minimum_order;
    }
}
