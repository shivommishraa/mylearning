@extends('layouts.store')

@section('content')
    <section class="wrap hero {{ $banners->count() > 1 ? 'hero-slider' : '' }}" @if($banners->count() > 1) data-banner-slider aria-label="Homepage banners" @endif>
        @forelse($banners as $banner)
            <article class="hero-slide {{ $loop->first ? 'is-active' : '' }}" data-banner-slide aria-hidden="{{ $loop->first ? 'false' : 'true' }}" @if($banners->count() > 1) role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $banners->count() }}" @endif>
                <div class="hero-media" style="background-image:url('{{ $banner->image_url ?? asset('images/hero-daily-needs.svg') }}')"></div>
                <div class="hero-copy">
                    <span class="eyebrow">{{ $banner->eyebrow ?: 'YOUR NEIGHBOURHOOD DAILY-NEEDS STORE' }}</span>
                    <h1>{{ $site['hero_title'] ?: $banner->title }}</h1>
                    <p>{{ $site['hero_subtitle'] ?: ($banner->subtitle ?: 'Atta, chawal, dal, chai and household essentials from trusted Indian brands.') }}</p>
                    <a class="button" href="{{ $banner->button_url ?: route('shop.index') }}">{{ $banner->button_text ?: 'Shop daily essentials' }} <span>→</span></a>
                    <div class="hero-local-note">Proudly serving Greater Noida, Uttar Pradesh</div>
                </div>
            </article>
        @empty
            <div class="hero-media" style="background-image:url('{{ asset('images/hero-daily-needs.svg') }}')"></div>
            <div class="hero-copy">
                <span class="eyebrow">YOUR NEIGHBOURHOOD DAILY-NEEDS STORE</span>
                <h1>{{ $site['hero_title'] ?: 'Everyday essentials, closer to home.' }}</h1>
                <p>{{ $site['hero_subtitle'] ?: 'Atta, chawal, dal, chai and household essentials from trusted Indian brands.' }}</p>
                <a class="button" href="{{ route('shop.index') }}">Shop daily essentials <span>→</span></a>
                <div class="hero-local-note">Proudly serving Greater Noida, Uttar Pradesh</div>
            </div>
        @endforelse
        @if($banners->count() > 1)
            <div class="hero-slider-controls" aria-label="Banner controls">
                <button type="button" class="hero-slider-button" data-banner-prev aria-label="Previous banner">‹</button>
                <div class="hero-slider-dots">
                    @foreach($banners as $index => $banner)
                        <button type="button" class="hero-slider-dot {{ $loop->first ? 'is-active' : '' }}" data-banner-dot="{{ $index }}" aria-label="Show banner {{ $loop->iteration }}" aria-current="{{ $loop->first ? 'true' : 'false' }}"></button>
                    @endforeach
                </div>
                <button type="button" class="hero-slider-button" data-banner-next aria-label="Next banner">›</button>
                <button type="button" class="hero-slider-button" data-banner-toggle aria-label="Pause automatic banner rotation">Pause</button>
            </div>
        @endif
    </section>

    <section class="wrap section">
        <div class="section-heading"><div><span class="eyebrow">EVERYDAY NEEDS, ALL IN ONE PLACE</span><h2>Shop by category</h2><p>The trusted aisles for every Indian home.</p></div><a class="text-link" href="{{ route('shop.index') }}">See everything →</a></div>
        <div class="category-grid">
            @foreach ($categories as $category)
                <a class="category-card" href="{{ route('shop.index', ['category' => $category->slug]) }}">
                    <div class="category-image">
                        <span class="category-symbol" aria-hidden="true">{{ match($category->slug) {'atta-rice' => '▤','dal-pulses' => '◉','oils-ghee' => '◍','snacks-namkeen' => '▧','biscuits-chocolates' => '▣','tea-coffee' => '♨','cold-drinks' => '◌','personal-care' => '✦','home-cleaning' => '⌂','toys' => '✣','stationery' => '✎',default => '▦'} }}</span>
                        @if($category->image_url)<img src="{{ $category->image_url }}" alt="{{ $category->name }}" loading="lazy" onerror="this.remove()">@endif
                    </div>
                    <strong>{{ $category->name }}</strong>
                </a>
            @endforeach
        </div>
    </section>

    @if($promotion)
        <section class="wrap wide-promo">
            <div class="wide-promo-copy"><span class="eyebrow">{{ $promotion->code }} · STORE OFFER</span><h2>{{ $promotion->type === 'percent' ? number_format((float) $promotion->value, 0).'%' : '₹'.number_format((float) $promotion->value, 0) }} off your basket.</h2><p>{{ $promotion->description ?: 'Use code '.$promotion->code.' at checkout on eligible orders.' }}</p><a href="{{ route('shop.index') }}" class="button">Shop the offer →</a></div>
            <div class="promo-art" aria-hidden="true"><img src="{{ asset('images/hero-daily-needs.svg') }}" alt="" loading="lazy"></div>
        </section>
    @endif

    @foreach($sections as $section)
        @include('components.home-product-section', ['section' => $section])
    @endforeach

    @if($brands->isNotEmpty())
        <section class="wrap section">
            <div class="section-heading"><div><span class="eyebrow">TRUSTED INDIAN BRANDS</span><h2>Good company on every shelf</h2><p>Familiar favourites for your household and pantry.</p></div></div>
            <div class="brand-list">@foreach($brands as $brand)<a class="brand-chip" href="{{ route('shop.index', ['brand' => $brand->slug]) }}">{{ $brand->name }}</a>@endforeach</div>
        </section>
    @endif

    @if ($testimonials->isNotEmpty())
    <section class="wrap section">
        <div class="section-heading"><div><span class="eyebrow">KIND WORDS FROM CLOSE TO HOME</span><h2>Good neighbours, good service</h2></div></div>
        <div class="testimonial-grid">@foreach ($testimonials as $review)
            <article class="quote-card"><div class="stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div><blockquote>“{{ $review->quote }}”</blockquote><div class="quote-person"><span class="avatar">{{ strtoupper(substr($review->name, 0, 1)) }}</span><div><strong>{{ $review->name }}</strong><small>{{ $review->location }}</small></div></div></article>
        @endforeach</div>
    </section>
    @endif
@endsection
