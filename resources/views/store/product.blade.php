@extends('layouts.store')
@section('title', $product->name.' · '.$site['business_name'])
@section('meta_description', Str::limit(strip_tags($product->description), 155))
@section('content')
<div class="wrap">
    <div class="breadcrumb"><a href="{{ route('home') }}">Home</a> / <a href="{{ route('shop.index') }}">Everyday essentials</a> / {{ $product->name }}</div>
    <div class="product-detail">
        <div class="detail-image">@include('components.product-art', ['product' => $product])@if($product->image_url)<img src="{{ $product->image_url }}" alt="{{ $product->name }}" fetchpriority="high" onerror="this.remove()">@endif</div>
        <div class="detail-copy">
            <span class="eyebrow">{{ $product->brand?->name ?: $product->category?->name }}</span><h1>{{ $product->name }}</h1>
            <span class="muted">{{ $product->unit }} · {{ $product->stock > 0 ? ($storefrontFeatures['enable_add_to_cart'] ? 'In stock and ready for your basket' : 'In stock') : 'Currently out of stock' }}</span>
            <div class="product-rating"><span aria-hidden="true">☆☆☆☆☆</span><span>New product</span></div>
            @if($storefrontFeatures['show_product_prices'])
                <div class="detail-price">₹{{ number_format((float) $product->price, 2) }} @if($product->compare_at_price && $product->compare_at_price > $product->price)<span class="old-price">₹{{ number_format((float) $product->compare_at_price, 2) }}</span><span class="detail-saving">{{ (int) round((1 - $product->price / $product->compare_at_price) * 100) }}% off</span>@endif</div>
            @endif
            <p class="detail-description">{{ $product->description }}</p>
            @if($storefrontFeatures['enable_add_to_cart'] && $product->stock > 0)
                <form class="detail-buy" method="POST" action="{{ route('cart.add', $product) }}">@csrf<div class="quantity"><button type="button" data-quantity="-1" aria-label="Decrease quantity">−</button><input name="quantity" type="number" value="1" min="1" max="{{ min(99, $product->stock) }}" aria-label="Quantity"><button type="button" data-quantity="1" aria-label="Increase quantity">+</button></div><button class="button">Add to basket @if($storefrontFeatures['show_product_prices']) · ₹{{ number_format((float) $product->price, 0) }}@endif</button></form>
            @elseif(!$storefrontFeatures['enable_add_to_cart'])
                <p class="muted" style="font-size:12px">Online ordering is currently unavailable.</p>
            @endif
            <div class="info-note">A trusted everyday pick from SSG MART. Need a hand? <a class="text-link" href="tel:{{ preg_replace('/\s+/', '', $site['phone']) }}">Give us a call.</a></div>
        </div>
    </div>
    @if($related->isNotEmpty())<section class="section"><div class="section-heading"><h2>A few more from this aisle</h2><a class="text-link" href="{{ route('shop.index', ['category' => $product->category?->slug]) }}">See the aisle →</a></div><div class="product-grid">@foreach($related as $product)@include('components.product-card', ['product' => $product])@endforeach</div></section>@endif
</div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('[data-quantity]').forEach((button) => button.addEventListener('click', () => {
    const input = button.closest('.quantity').querySelector('input');
    input.value = Math.max(1, Math.min(Number(input.max), Number(input.value) + Number(button.dataset.quantity)));
}));
</script>
@endpush
