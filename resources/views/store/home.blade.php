@extends('layouts.store')

@section('content')
<div class="store-home">
    @php($imageOnlyBanners = $banners->isNotEmpty() && $banners->every(fn ($banner) => ! $banner->hasOverlayContent()))
    <section class="wrap hero {{ $banners->count() > 1 ? 'hero-slider' : '' }} {{ $imageOnlyBanners ? 'hero-image-only' : '' }}" @if($banners->count() > 1) data-banner-slider aria-label="Homepage banners" @endif>
        @forelse($banners as $banner)
            <article class="hero-slide {{ $banner->hasOverlayContent() ? 'hero-slide--content' : 'hero-slide--image-only' }} {{ $loop->first ? 'is-active' : '' }}" data-banner-slide aria-hidden="{{ $loop->first ? 'false' : 'true' }}" @if($banners->count() > 1) role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $banners->count() }}" @endif>
                <div class="hero-media" style="background-image:url('{{ $banner->image_url ?? asset('images/hero-daily-needs.svg') }}')"></div>
                @if(filled($banner->button_url))
                    <a class="hero-click-target" href="{{ $banner->button_url }}" target="{{ $banner->link_target ?: '_self' }}" @if($banner->link_target === '_blank') rel="noopener" @endif aria-label="{{ $banner->title ?: $banner->button_text ?: 'Open banner link' }}"></a>
                @endif
                @if($banner->hasOverlayContent())
                    <div class="hero-copy">
                        @if(filled($banner->eyebrow))<span class="eyebrow">{{ $banner->eyebrow }}</span>@endif
                        @if(filled($banner->title))<h1>{{ $banner->title }}</h1>@endif
                        @if(filled($banner->subtitle))<p>{{ $banner->subtitle }}</p>@endif
                        @if(filled($banner->button_text) && filled($banner->button_url))<span class="button">{{ $banner->button_text }} <span aria-hidden="true">→</span></span>@endif
                    </div>
                @endif
            </article>
        @empty
            <div class="hero-media" style="background-image:url('{{ asset('images/hero-daily-needs.svg') }}')"></div>
            <div class="hero-copy">
                <span class="eyebrow">YOUR NEIGHBOURHOOD DAILY-NEEDS STORE</span>
                <h1>{{ $site['hero_title'] ?: 'Everyday essentials, closer to home.' }}</h1>
                <p>{{ $site['hero_subtitle'] ?: 'Atta, chawal, dal, chai and household essentials from trusted Indian brands.' }}</p>
                <div class="hero-actions">
                    <a class="button" href="{{ route('shop.index') }}">Shop daily essentials <span>→</span></a>
                    <a class="hero-secondary-link" href="#shop-categories">Explore categories <span aria-hidden="true">↓</span></a>
                </div>
                <div class="hero-local-note"><span aria-hidden="true">⌖</span> Proudly serving Greater Noida, Uttar Pradesh</div>
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

    <section id="shop-categories" class="wrap section">
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
        <section class="wide-promo">
            <div class="wide-promo-copy"><span class="eyebrow">{{ $promotion->code }} · STORE OFFER</span><h2>{{ $promotion->type === 'percent' ? number_format((float) $promotion->value, 0).'%' : '₹'.number_format((float) $promotion->value, 0) }} off your basket.</h2><p>{{ $promotion->description ?: 'Use code '.$promotion->code.' at checkout on eligible orders.' }}</p><a href="{{ route('shop.index') }}" class="button">Shop the offer →</a></div>
            <div class="promo-art" aria-hidden="true"><img src="{{ $promotion->image_url ?: asset('images/hero-daily-needs.svg') }}" alt="" loading="lazy"></div>
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
        <div class="testimonial-carousel" data-testimonial-carousel role="region" aria-roledescription="carousel" aria-label="Kind words from close to home">
            <div class="testimonial-stage">
                @foreach ($testimonials as $review)
                    <article class="quote-card testimonial-slide {{ $loop->first ? 'is-active' : '' }}" data-testimonial-slide role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $testimonials->count() }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                        <div class="stars" role="img" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</div>
                        <blockquote>“{{ $review->quote }}”</blockquote>
                        <div class="quote-person"><span class="avatar" aria-hidden="true">{{ strtoupper(substr($review->name, 0, 1)) }}</span><div><strong>{{ $review->name }}</strong><small>{{ $review->location }}</small></div></div>
                    </article>
                @endforeach
            </div>
            @if ($testimonials->count() > 1)
                <div class="testimonial-controls" aria-label="Testimonial controls">
                    <button type="button" class="testimonial-control" data-testimonial-prev aria-label="Previous testimonial">‹</button>
                    <div class="testimonial-dots">
                        @foreach ($testimonials as $index => $review)
                            <button type="button" class="testimonial-dot {{ $loop->first ? 'is-active' : '' }}" data-testimonial-dot="{{ $index }}" aria-label="Show testimonial {{ $loop->iteration }}" aria-current="{{ $loop->first ? 'true' : 'false' }}"></button>
                        @endforeach
                    </div>
                    <button type="button" class="testimonial-control" data-testimonial-next aria-label="Next testimonial">›</button>
                </div>
            @endif
        </div>
    </section>
    @endif
    </div>
    @if($welcomePopup)
        @include('store.partials.welcome-popup', ['welcomePopup' => $welcomePopup])
    @endif
@endsection
