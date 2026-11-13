<section class="wrap section">
    <div class="section-heading">
        <div><span class="eyebrow">{{ $section->eyebrow }}</span><h2>{{ $section->title }}</h2>@if($section->subtitle)<p>{{ $section->subtitle }}</p>@endif</div>
        <a class="text-link" href="{{ $section->type === 'category' && $section->category ? route('shop.index', ['category' => $section->category->slug]) : route('shop.index') }}">Shop this collection →</a>
    </div>
    <div class="product-grid">
        @foreach($section->products as $product)
            @include('components.product-card', ['product' => $product])
        @endforeach
    </div>
</section>
