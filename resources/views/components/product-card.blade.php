<article class="product-card">
    <div class="product-photo">
        <a href="{{ route('products.show', $product->slug) }}" aria-label="View {{ $product->name }}">
            @include('components.product-art', ['product' => $product])
            @if($product->image_url)
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy" onerror="this.remove()">
            @endif
        </a>
        @if ($storefrontFeatures['show_product_prices'] && $product->compare_at_price && $product->compare_at_price > $product->price)<span class="product-badge">{{ (int) round((1 - $product->price / $product->compare_at_price) * 100) }}% off</span>@endif
        @auth
            <form method="POST" action="{{ route('wishlist.toggle', $product) }}">@csrf<button class="product-wish" title="Save to wishlist" aria-label="Add {{ $product->name }} to wishlist">♡</button></form>
        @endauth
    </div>
    <div class="product-info">
        <span class="product-category">{{ $product->brand?->name ?: $product->category?->name }}</span>
        <a href="{{ route('products.show', $product->slug) }}" class="product-title">{{ $product->name }}</a>
        <div class="product-unit">{{ $product->unit }}</div>
        <div class="product-rating" aria-label="Rating not available"><span aria-hidden="true">☆☆☆☆☆</span><span>New</span></div>
        @if($storefrontFeatures['show_product_prices'] || $storefrontFeatures['enable_add_to_cart'])
            <div class="product-buy">
                @if($storefrontFeatures['show_product_prices'])
                    <div class="price">₹{{ number_format((float) $product->price, 2) }} @if ($product->compare_at_price && $product->compare_at_price > $product->price)<span class="old-price">₹{{ number_format((float) $product->compare_at_price, 2) }}</span>@endif</div>
                @endif
                @if($storefrontFeatures['enable_add_to_cart'])
                    @if ($product->stock > 0)
                        <form method="POST" action="{{ route('cart.add', $product) }}">@csrf<input type="hidden" name="quantity" value="1"><button class="add-button" aria-label="Add {{ $product->name }} to basket">+</button></form>
                    @else<span class="muted" style="font-size:11px">Sold out</span>@endif
                @endif
            </div>
        @endif
    </div>
</article>
