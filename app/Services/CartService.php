<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Support\StoreCatalogRules;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function lines(): Collection
    {
        $cart = Session::get('cart', []);
        $products = StoreCatalogRules::constrainStoreProducts(Product::with('category')
            ->whereIn('id', array_keys($cart))
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true)))
            ->get()
            ->keyBy('id');

        return collect($cart)->map(function ($quantity, $id) use ($products) {
            $product = $products->get($id);

            if (! $product) {
                return null;
            }

            $quantity = min((int) $quantity, (int) $product->stock);

            return $quantity > 0 ? [
                'product' => $product,
                'quantity' => $quantity,
                'line_total' => round((float) $product->price * $quantity, 2),
            ] : null;
        })->filter()->values();
    }

    public function summary(?Coupon $coupon = null): array
    {
        $lines = $this->lines();
        $subtotal = round($lines->sum('line_total'), 2);
        $coupon ??= $this->currentCoupon($subtotal);
        $discount = $coupon && $coupon->isAvailable($subtotal)
            ? $coupon->discountFor($subtotal)
            : 0;
        $freeDeliveryMinimum = (float) SiteSetting::getValue('free_delivery_minimum', 499);
        $deliveryFee = $subtotal > 0 && $subtotal < $freeDeliveryMinimum
            ? (float) SiteSetting::getValue('delivery_fee', 39)
            : 0;

        return [
            'lines' => $lines,
            'subtotal' => $subtotal,
            'coupon' => $coupon,
            'discount' => $discount,
            'delivery_fee' => $deliveryFee,
            'total' => max(0, round($subtotal - $discount + $deliveryFee, 2)),
            'free_delivery_minimum' => $freeDeliveryMinimum,
        ];
    }

    public function currentCoupon(float $subtotal): ?Coupon
    {
        $code = Session::get('coupon_code');

        if (! $code) {
            return null;
        }

        $coupon = Coupon::where('code', $code)->first();

        return $coupon && $coupon->isAvailable($subtotal) ? $coupon : null;
    }
}
