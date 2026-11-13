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
                <form action="{{ route('cart.update') }}" method="POST">@csrf @method('PATCH')
                    @foreach($summary['lines'] as $line)
                        <div class="basket-row">
                            <span class="basket-image basket-image-fallback" aria-hidden="true">{{ strtoupper(substr($line['product']->category?->name ?? 'SSG',0,1)) }}</span>
                            @if($line['product']->image_url)<img class="basket-image basket-image-overlay" src="{{ $line['product']->image_url }}" alt="{{ $line['product']->name }}" onerror="this.remove()">@endif
                            <div><a class="basket-name" href="{{ route('products.show', $line['product']->slug) }}">{{ $line['product']->name }}<small>{{ $line['product']->unit }}</small></a></div>
                            <label class="quantity" aria-label="Quantity"><button type="button" data-quantity="-1">−</button><input type="number" min="0" max="{{ min(99, $line['product']->stock) }}" name="quantities[{{ $line['product']->id }}]" value="{{ $line['quantity'] }}"><button type="button" data-quantity="1">+</button></label>
                            <div class="basket-actions"><span class="price">₹{{ number_format($line['line_total'], 2) }}</span><button class="remove-link" type="submit" form="remove-{{ $line['product']->id }}" style="border:0;background:none">Remove</button></div>
                        </div>
                    @endforeach
                    <div style="display:flex;justify-content:flex-end;padding-top:16px"><button class="button button-light button-small">Update basket</button></div>
                </form>
                @foreach($summary['lines'] as $line)<form id="remove-{{ $line['product']->id }}" method="POST" action="{{ route('cart.remove', $line['product']) }}" hidden>@csrf @method('DELETE')</form>@endforeach
            </section>
            <aside class="summary-card">
                <h2>A little basket maths</h2>
                <div class="summary-line"><span>Good things</span><strong>₹{{ number_format($summary['subtotal'], 2) }}</strong></div>
                @if($summary['coupon'])<div class="summary-line"><span>Offer · {{ $summary['coupon']->code }}</span><strong>−₹{{ number_format($summary['discount'], 2) }}</strong></div>@endif
                <div class="summary-line"><span>Delivery</span><strong>{{ $summary['delivery_fee'] ? '₹'.number_format($summary['delivery_fee'], 2) : 'On us' }}</strong></div>
                @if(!$summary['delivery_fee'])<p class="muted" style="font-size:11px">Lovely! Your delivery is on us.</p>@else<p class="muted" style="font-size:11px">Free delivery on baskets over ₹{{ number_format($summary['free_delivery_minimum'], 0) }}.</p>@endif
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
document.querySelectorAll('[data-quantity]').forEach((button) => button.addEventListener('click', () => {
    const input = button.closest('.quantity').querySelector('input');
    input.value = Math.max(Number(input.min), Math.min(Number(input.max), Number(input.value) + Number(button.dataset.quantity)));
}));
</script>
@endpush
