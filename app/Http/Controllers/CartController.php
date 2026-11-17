<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\SiteSetting;
use App\Services\CartService;
use App\Support\StoreCatalogRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(): View
    {
        return view('store.cart', ['summary' => $this->cart->summary()]);
    }

    public function add(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        abort_unless($this->addToCartEnabled(), 403, 'Adding products to the basket is currently disabled.');

        abort_unless(
            $product->is_active
                && $product->category?->is_active
                && ! StoreCatalogRules::isRestricted($product->name.' '.$product->category->name),
            404,
        );
        $data = $request->validate(['quantity' => ['nullable', 'integer', 'min:1', 'max:99']]);
        $quantity = (int) ($data['quantity'] ?? 1);
        $cart = Session::get('cart', []);

        if (($cart[$product->id] ?? 0) + $quantity > $product->stock) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'There are only '.$product->stock.' available.',
                ], 422);
            }

            return back()->withErrors(['cart' => 'There are only '.$product->stock.' available.']);
        }

        $cart[$product->id] = ($cart[$product->id] ?? 0) + $quantity;
        Session::put('cart', $cart);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $product->name.' added to your basket.',
                'cart_count' => array_sum($cart),
            ]);
        }

        return back()->with('success', $product->name.' added to your basket.');
    }

    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $quantities = $request->validate([
            'quantities' => ['required', 'array'],
            'quantities.*' => ['required', 'integer', 'min:1', 'max:99'],
        ])['quantities'];
        $cart = Session::get('cart', []);

        if (! $this->addToCartEnabled()) {
            foreach ($quantities as $id => $quantity) {
                if ($quantity > (int) ($cart[$id] ?? 0)) {
                    throw ValidationException::withMessages([
                        'cart' => 'Adding products to the basket is currently disabled.',
                    ]);
                }
            }
        }

        foreach ($quantities as $id => $quantity) {
            $product = Product::find($id);

            if (! $product) {
                unset($cart[$id]);

                continue;
            }

            if ($quantity > $product->stock) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => "Only {$product->stock} of {$product->name} are available.",
                    ], 422);
                }

                return back()->withErrors(['cart' => "Only {$product->stock} of {$product->name} are available."]);
            }

            $cart[$id] = $quantity;
        }

        Session::put('cart', $cart);

        if ($request->expectsJson()) {
            $summary = $this->cart->summary();

            return response()->json([
                'message' => 'Basket updated.',
                'cart_count' => array_sum($cart),
                'lines' => $summary['lines']->map(fn (array $line) => [
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'line_total' => $line['line_total'],
                ])->values(),
                'subtotal' => $summary['subtotal'],
                'coupon' => $summary['coupon']?->code,
                'discount' => $summary['discount'],
                'delivery_fee' => $summary['delivery_fee'],
                'free_delivery_minimum' => $summary['free_delivery_minimum'],
                'total' => $summary['total'],
            ]);
        }

        return back()->with('success', 'Basket updated.');
    }

    public function remove(Product $product): RedirectResponse
    {
        $cart = Session::get('cart', []);
        unset($cart[$product->id]);
        Session::put('cart', $cart);

        return back()->with('success', 'Item removed from your basket.');
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string', 'max:40']]);
        $subtotal = $this->cart->summary(null)['subtotal'];
        $coupon = Coupon::where('code', strtoupper(trim($data['code'])))->first();

        if (! $coupon || ! $coupon->isAvailable($subtotal)) {
            return back()->withErrors(['code' => 'That offer code is invalid or does not apply to this basket.']);
        }

        Session::put('coupon_code', $coupon->code);

        return back()->with('success', 'Offer '.$coupon->code.' applied.');
    }

    public function checkout(): View|RedirectResponse
    {
        $summary = $this->cart->summary();

        if ($summary['lines']->isEmpty()) {
            return redirect()->route('shop.index')->with('error', 'Add something delicious to your basket first.');
        }

        return view('store.checkout', [
            'summary' => $summary,
            'addresses' => auth()->user()->addresses()->latest()->get(),
        ]);
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'address_id' => ['required', 'integer'],
            'payment_method' => ['required', 'in:cod,online'],
            'customer_note' => ['nullable', 'string', 'max:500'],
        ]);
        $address = auth()->user()->addresses()->findOrFail($data['address_id']);
        $cart = Session::get('cart', []);

        if (! $cart) {
            return redirect()->route('shop.index')->with('error', 'Your basket is empty.');
        }

        $order = DB::transaction(function () use ($cart, $address, $data) {
            $lines = [];
            $subtotal = 0;

            foreach ($cart as $productId => $quantity) {
                $product = Product::whereKey($productId)->lockForUpdate()->first();

                if (! $product || ! $product->is_active || ! $product->category?->is_active || StoreCatalogRules::isRestricted($product->name.' '.$product->category->name) || $quantity > $product->stock) {
                    throw ValidationException::withMessages([
                        'cart' => 'A basket item is no longer available in the requested quantity. Please review your basket.',
                    ]);
                }

                $quantity = (int) $quantity;
                $lineTotal = round((float) $product->price * $quantity, 2);
                $subtotal += $lineTotal;
                $lines[] = [$product, $quantity, $lineTotal];
            }

            $subtotal = round($subtotal, 2);
            $coupon = null;
            $code = Session::get('coupon_code');

            if ($code) {
                $coupon = Coupon::where('code', $code)->lockForUpdate()->first();

                if (! $coupon || ! $coupon->isAvailable($subtotal)) {
                    throw ValidationException::withMessages(['coupon' => 'Your offer is no longer valid. Remove it and try again.']);
                }
            }

            $discount = $coupon ? $coupon->discountFor($subtotal) : 0;
            $freeDeliveryMinimum = (float) SiteSetting::getValue('free_delivery_minimum', 499);
            $deliveryFee = $subtotal < $freeDeliveryMinimum
                ? (float) SiteSetting::getValue('delivery_fee', 39)
                : 0;

            $order = Order::create([
                'user_id' => auth()->id(),
                'address_id' => $address->id,
                'coupon_id' => $coupon?->id,
                'order_number' => 'SSG-'.now()->format('ymd').'-'.strtoupper(Str::random(6)),
                'delivery_address' => $address->only([
                    'recipient_name', 'phone', 'address_line', 'landmark', 'city', 'state', 'postal_code',
                ]),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'delivery_fee' => $deliveryFee,
                'total' => max(0, round($subtotal - $discount + $deliveryFee, 2)),
                'coupon_code' => $coupon?->code,
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_method'] === 'cod' ? 'pay_on_delivery' : 'pending',
                'status' => 'placed',
                'customer_note' => $data['customer_note'] ?? null,
            ]);

            foreach ($lines as [$product, $quantity, $lineTotal]) {
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'unit' => $product->unit,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'line_total' => $lineTotal,
                ]);
                $product->decrement('stock', $quantity);
            }

            $coupon?->increment('usage_count');

            return $order;
        });

        Session::forget(['cart', 'coupon_code']);

        return redirect()->route('orders.show', $order)->with('success', 'Your order has been placed.');
    }

    public function showOrder(Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 404);

        return view('store.order', ['order' => $order->load('items')]);
    }

    public function invoice(Order $order): View
    {
        abort_unless($order->user_id === auth()->id(), 404);

        return view('store.invoice', ['order' => $order->load('items')]);
    }

    private function addToCartEnabled(): bool
    {
        return SiteSetting::getValue('enable_add_to_cart', config('ssg.defaults.enable_add_to_cart')) === '1';
    }
}
