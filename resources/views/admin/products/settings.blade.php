@extends('layouts.admin')
@section('title', 'Product storefront settings')
@section('content')
@php
    $enableAddToCart = (string) old('enable_add_to_cart', $settings['enable_add_to_cart'] ?? config('ssg.defaults.enable_add_to_cart')) === '1';
    $showProductPrices = (string) old('show_product_prices', $settings['show_product_prices'] ?? config('ssg.defaults.show_product_prices')) === '1';
@endphp
<div class="admin-title">
    <div><h1>Product storefront settings</h1><p>Choose what customers can see and do with products.</p></div>
    <a class="text-link" href="{{ route('admin.products.index') }}">← Products</a>
</div>
<form class="admin-panel" method="POST" action="{{ route('admin.products.settings.update') }}">
    @csrf
    @method('PUT')
    <h2>Customer product options</h2>
    <div class="admin-field">
        <div class="product-setting-heading">
            <label class="product-setting-option">
                <input type="hidden" name="enable_add_to_cart" value="0">
                <input type="checkbox" name="enable_add_to_cart" value="1" data-setting-toggle @checked($enableAddToCart)>
                Enable add to cart
            </label>
            <span class="product-setting-status {{ $enableAddToCart ? 'is-enabled' : 'is-disabled' }}" data-setting-status aria-live="polite">{{ $enableAddToCart ? 'ENABLED' : 'DISABLED' }}</span>
        </div>
        <small>When disabled, add-to-cart controls are hidden and new items cannot be added to baskets.</small>
    </div>
    <div class="admin-field">
        <div class="product-setting-heading">
            <label class="product-setting-option">
                <input type="hidden" name="show_product_prices" value="0">
                <input type="checkbox" name="show_product_prices" value="1" data-setting-toggle @checked($showProductPrices)>
                Show product prices
            </label>
            <span class="product-setting-status {{ $showProductPrices ? 'is-enabled' : 'is-disabled' }}" data-setting-status aria-live="polite">{{ $showProductPrices ? 'ENABLED' : 'DISABLED' }}</span>
        </div>
        <small>When disabled, prices and price filters are hidden from product listings and product pages. Basket and order totals remain visible.</small>
    </div>
    <button class="button">Save product settings</button>
</form>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.product-setting-heading').forEach((heading) => {
    const toggle = heading.querySelector('[data-setting-toggle]');
    const status = heading.querySelector('[data-setting-status]');

    toggle.addEventListener('change', () => {
        status.textContent = toggle.checked ? 'ENABLED' : 'DISABLED';
        status.classList.toggle('is-enabled', toggle.checked);
        status.classList.toggle('is-disabled', !toggle.checked);
    });
});
</script>
@endpush
