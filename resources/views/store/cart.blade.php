@extends('layouts.store')
@section('title', 'Your basket · '.$site['business_name'])
@section('content')
<div class="wrap">
    <div class="breadcrumb"><a href="{{ route('home') }}">Home</a> / Basket</div>
    <div class="section-heading"><div><span class="eyebrow">YOUR GOOD THINGS</span><h1 class="page-title">Your basket</h1></div><a class="text-link" href="{{ route('shop.index') }}">Keep browsing →</a></div>
    @if($summary['lines']->isEmpty())
        <div class="empty-state" style="margin-bottom:55px"><h2>Your basket is waiting for something lovely</h2><p>Pick a few favourites and we’ll take it from here.</p><a class="button" href="{{ route('shop.index') }}">Explore the shop →</a></div>
    @else
        <div class="basket-layout">
            <section class="basket-card">
                <form action="{{ route('cart.update') }}" method="POST" data-cart-form>@csrf @method('PATCH')
                    @foreach($summary['lines'] as $line)
                        <div class="basket-row" data-basket-line data-product-id="{{ $line['product']->id }}">
                            <span class="basket-image basket-image-fallback" aria-hidden="true">{{ strtoupper(substr($line['product']->category?->name ?? 'SSG',0,1)) }}</span>
                            @if($line['product']->image_url)<img class="basket-image basket-image-overlay" src="{{ $line['product']->image_url }}" alt="{{ $line['product']->name }}" onerror="this.remove()">@endif
                            <div><a class="basket-name" href="{{ route('products.show', $line['product']->slug) }}">{{ $line['product']->name }}<small>{{ $line['product']->unit }}</small></a></div>
                            <label class="quantity"><button type="button" data-quantity="-1" aria-label="Decrease {{ $line['product']->name }} quantity">−</button><input type="number" min="1" max="{{ min(99, $line['product']->stock) }}" name="quantities[{{ $line['product']->id }}]" value="{{ $line['quantity'] }}" data-saved-quantity="{{ $line['quantity'] }}" aria-label="Quantity for {{ $line['product']->name }}"><button type="button" data-quantity="1" aria-label="Increase {{ $line['product']->name }} quantity">+</button></label>
                            <div class="basket-actions"><span class="price">₹{{ number_format($line['line_total'], 2) }}</span><button class="remove-link" type="submit" form="remove-{{ $line['product']->id }}" style="border:0;background:none">Remove</button></div>
                        </div>
                    @endforeach
                    <div style="display:flex;justify-content:flex-end;padding-top:16px"><button class="button button-light button-small">Update basket</button></div>
                </form>
                <p class="cart-update-feedback" data-cart-feedback role="status" aria-live="polite" hidden></p>
                @foreach($summary['lines'] as $line)<form id="remove-{{ $line['product']->id }}" method="POST" action="{{ route('cart.remove', $line['product']) }}" hidden>@csrf @method('DELETE')</form>@endforeach
            </section>
            <aside class="summary-card">
                <h2>A little basket maths</h2>
                <div class="summary-line"><span>Good things</span><strong data-basket-subtotal>₹{{ number_format($summary['subtotal'], 2) }}</strong></div>
                <div class="summary-line" data-basket-discount-row @if(!$summary['coupon']) hidden @endif><span>Offer · <span data-basket-coupon-code>{{ $summary['coupon']?->code }}</span></span><strong data-basket-discount>−₹{{ number_format($summary['discount'], 2) }}</strong></div>
                <div class="summary-line"><span>Delivery</span><strong data-basket-delivery>{{ $summary['delivery_fee'] ? '₹'.number_format($summary['delivery_fee'], 2) : 'On us' }}</strong></div>
                <p class="muted" data-basket-delivery-note style="font-size:11px">{{ !$summary['delivery_fee'] ? 'Lovely! Your delivery is on us.' : 'Free delivery on baskets over ₹'.number_format($summary['free_delivery_minimum'], 0).'.' }}</p>
                <form class="coupon-form" method="POST" action="{{ route('cart.coupon') }}">@csrf<input class="field" name="code" placeholder="Offer code" value="{{ $summary['coupon']?->code }}"><button class="button button-light button-small">Apply</button></form>
                <div class="summary-line summary-total"><span>Total</span><strong>₹{{ number_format($summary['total'], 2) }}</strong></div>
                @auth<a class="button" style="width:100%;margin-top:13px" href="{{ route('checkout') }}">On to checkout →</a>@else<a class="button" style="width:100%;margin-top:13px" href="{{ route('login') }}">Sign in to checkout →</a>@endauth
                <p class="muted" style="font-size:10px;margin:12px 0 0;text-align:center">A safe, secure checkout · Good things guaranteed</p>
            </aside>
        </div>
    @endif
</div>
@endsection
@push('scripts')
<script>
const cartForm = document.querySelector('[data-cart-form]');

if (cartForm) {
    const feedback = document.querySelector('[data-cart-feedback]');
    const quantityInputs = Array.from(cartForm.querySelectorAll('input[name^="quantities["]'));
    let updateInProgress = false;

    const formatPrice = (amount, fractionDigits = 2) => `₹${Number(amount).toLocaleString('en-IN', {
        minimumFractionDigits: fractionDigits,
        maximumFractionDigits: fractionDigits,
    })}`;

    const updateBasket = async () => {
        if (updateInProgress) {
            return;
        }

        updateInProgress = true;
        const formData = new FormData(cartForm);
        cartForm.querySelectorAll('button[data-quantity], input[name^="quantities["]').forEach((control) => {
            control.disabled = true;
        });

        try {
            const response = await fetch(cartForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            let result;

            try {
                result = await response.json();
            } catch {
                throw new Error('Unable to update your basket. Please try again.');
            }

            if (!response.ok) {
                throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Unable to update your basket.');
            }

            result.lines.forEach((line) => {
                const row = cartForm.querySelector(`[data-product-id="${line.product_id}"]`);
                const input = row.querySelector('input[name^="quantities["]');
                input.value = line.quantity;
                input.dataset.savedQuantity = line.quantity;
                row.querySelector('.price').textContent = formatPrice(line.line_total);
            });

            document.querySelector('.cart-count').textContent = result.cart_count;
            document.querySelector('[data-basket-subtotal]').textContent = formatPrice(result.subtotal);
            document.querySelector('[data-basket-delivery]').textContent = result.delivery_fee
                ? formatPrice(result.delivery_fee)
                : 'On us';
            document.querySelector('.summary-total strong').textContent = formatPrice(result.total);

            const discountRow = document.querySelector('[data-basket-discount-row]');
            discountRow.hidden = !result.coupon;
            document.querySelector('[data-basket-coupon-code]').textContent = result.coupon || '';
            document.querySelector('[data-basket-discount]').textContent = `−${formatPrice(result.discount)}`;
            document.querySelector('[data-basket-delivery-note]').textContent = result.delivery_fee
                ? `Free delivery on baskets over ${formatPrice(result.free_delivery_minimum, 0)}.`
                : 'Lovely! Your delivery is on us.';
            feedback.textContent = result.message;
            feedback.hidden = false;
        } catch (error) {
            quantityInputs.forEach((input) => {
                input.value = input.dataset.savedQuantity;
            });
            feedback.textContent = error.message || 'Unable to update your basket. Please try again.';
            feedback.hidden = false;
        } finally {
            cartForm.querySelectorAll('button[data-quantity], input[name^="quantities["]').forEach((control) => {
                control.disabled = false;
            });
            updateInProgress = false;
        }
    };

    const clampQuantity = (input) => {
        const minimum = Number(input.min) || 1;
        const maximum = Number(input.max);
        const quantity = Number(input.value);

        input.value = Math.max(minimum, Math.min(maximum, Number.isFinite(quantity) ? quantity : minimum));
    };

    cartForm.addEventListener('click', (event) => {
        const button = event.target.closest('button[data-quantity]');

        if (!button) {
            return;
        }

        const input = button.closest('.quantity').querySelector('input');
        input.value = Math.max(1, Math.min(Number(input.max), Number(input.value) + Number(button.dataset.quantity)));
        updateBasket();
    });

    quantityInputs.forEach((input) => input.addEventListener('change', () => {
        clampQuantity(input);
        updateBasket();
    }));

    cartForm.addEventListener('submit', (event) => {
        event.preventDefault();
        updateBasket();
    });
}
</script>
@endpush
