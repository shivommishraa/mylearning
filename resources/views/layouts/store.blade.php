<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', $site['seo_description'])">
    <meta name="theme-color" content="#246b45">
    <title>@yield('title', $site['seo_title'])</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="canonical" href="{{ url()->current() }}">
    @php
        $storeStructuredData = [
            '@context' => 'https://schema.org',
            '@type' => 'GroceryStore',
            'name' => $site['business_name'],
            'telephone' => $site['phone'],
            'email' => $site['email'],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $site['address'],
                'addressLocality' => 'Greater Noida',
                'addressRegion' => 'Uttar Pradesh',
                'postalCode' => '201318',
                'addressCountry' => 'IN',
            ],
        ];
    @endphp
    <script type="application/ld+json">@json($storeStructuredData)</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body>
    <div class="store-loader" data-page-loader role="status" aria-live="polite">
        <div class="store-loader-content">
            <span class="store-loader-icon" aria-hidden="true">S</span>
            <strong>SSG MART</strong>
            <p>Getting your essentials ready</p>
            <span class="store-loader-progress" aria-hidden="true"><span></span></span>
        </div>
    </div>
    <div class="announcement">{{ $site['announcement_text'] }} <strong>· Free delivery over ₹{{ number_format((float) $site['free_delivery_minimum']) }}</strong></div>
    <header class="header">
        <div class="wrap header-main">
            @if(($navigationItems['header'] ?? collect())->isNotEmpty())
                <button type="button" class="mobile-toggle" aria-label="Open navigation" aria-expanded="false" data-menu-toggle>
                    <span data-menu-open-icon aria-hidden="true">☰</span>
                    <span data-menu-close-icon aria-hidden="true">×</span>
                </button>
            @endif
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark">{{ strtoupper(substr($site['business_name'], 0, 1)) }}</span><span>{{ $site['business_name'] }}</span></a>
            <form class="search" action="{{ route('shop.index') }}" method="GET" role="search" autocomplete="off" data-live-search data-suggestions-url="{{ route('search.suggest') }}">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search atta, chawal, dal, chai…" aria-label="Search daily essentials" aria-autocomplete="list" aria-expanded="false" aria-controls="search-suggestions" maxlength="120" data-search-input>
                <button type="submit" aria-label="Search">⌕</button>
                <div class="search-suggestions" id="search-suggestions" data-search-panel hidden>
                    <div class="search-suggestions-message" data-search-message role="status" hidden></div>
                    <div class="search-suggestions-list" data-search-results role="listbox" aria-label="Product suggestions"></div>
                    <a class="search-suggestions-all" data-search-all hidden></a>
                </div>
            </form>
            <div class="header-actions">
                <a class="header-link {{ auth()->check() ? 'header-profile-link' : 'header-signin-link' }}" href="{{ auth()->check() ? route('profile.edit') : route('login') }}" aria-label="{{ auth()->check() ? 'My profile' : 'Sign in' }}"><span class="header-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><circle cx="12" cy="8" r="3.5"/><path d="M5 21a7 7 0 0 1 14 0"/></svg></span><span class="label">{{ auth()->check() ? 'My profile' : 'Sign in' }}</span></a>
                @guest<a class="header-link" href="{{ route('register') }}" aria-label="Register"><span class="header-icon" aria-hidden="true">＋</span><span class="label">Register</span></a>@endguest
                <a class="header-link" href="{{ route('cart.index') }}" aria-label="Basket"><span class="header-icon" aria-hidden="true">♧</span><span class="label">Basket</span><span class="cart-count">{{ array_sum(session('cart', [])) }}</span></a>
            </div>
        </div>
        @if(($navigationItems['header'] ?? collect())->isNotEmpty())
            <nav class="wrap nav" data-menu>
                @foreach(($navigationItems['header'] ?? collect()) as $item)
                    @if($item->children->isEmpty())
                        <a href="{{ $item->url }}">{{ $item->label }}</a>
                    @else
                        <div class="nav-group" data-nav-group>
                            <a href="{{ $item->url }}">{{ $item->label }}</a>
                            <button class="nav-submenu-toggle" type="button" data-nav-submenu-toggle aria-expanded="false" aria-haspopup="true" aria-controls="nav-submenu-{{ $item->id }}" aria-label="Toggle {{ $item->label }} submenu"><svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M4 6l4 4 4-4"/></svg></button>
                            <div class="nav-submenu" id="nav-submenu-{{ $item->id }}" data-nav-submenu>
                                @foreach($item->children as $child)<a href="{{ $child->url }}">{{ $child->label }}</a>@endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>
        @endif
    </header>
    @if(($navigationItems['header'] ?? collect())->isNotEmpty())
        <button type="button" class="store-menu-backdrop" aria-label="Close navigation" tabindex="-1" data-menu-backdrop hidden></button>
    @endif
    @if (session('success'))<div class="wrap alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="wrap alert alert-error" role="alert">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="wrap alert alert-error" role="alert"><ul style="margin:0;padding-left:18px">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <main>@yield('content')</main>
    <section class="benefits"><div class="wrap benefit-grid">
        <div class="benefit"><span class="benefit-icon">✳</span><div><strong>Everyday essentials</strong><span>For your pantry and home</span></div></div>
        <div class="benefit"><span class="benefit-icon">⌂</span><div><strong>Your neighbourhood store</strong><span>Greater Noida, Uttar Pradesh</span></div></div>
        <div class="benefit"><span class="benefit-icon">↗</span><div><strong>At your door, on time</strong><span>Delivery that fits your day</span></div></div>
        <div class="benefit"><span class="benefit-icon">♡</span><div><strong>Here for you</strong><span>Real people, ready to help</span></div></div>
    </div></section>
    <footer class="footer">
        <div class="wrap footer-grid">
            <div><a class="brand" href="{{ route('home') }}"><span class="brand-mark">{{ strtoupper(substr($site['business_name'], 0, 1)) }}</span><span>{{ $site['business_name'] }}</span></a><p>Your neighbourhood store for pantry staples, household care and everyday essentials.</p><a href="https://wa.me/{{ preg_replace('/\D+/', '', $site['whatsapp']) }}" target="_blank" rel="noopener">Chat with us on WhatsApp ↗</a></div>
            <div><h3>Come on in</h3><address>{{ $site['address'] }}</address><a href="https://www.google.com/maps/dir/?api=1&amp;destination=28.5222018%2C77.40182545" target="_blank" rel="noopener">Find us on Google Maps ↗</a></div>
            @if(($navigationItems['footer'] ?? collect())->isNotEmpty())
                <div><h3>A few helpful links</h3><nav class="footer-links" aria-label="Footer navigation" data-footer-menu>@foreach(($navigationItems['footer'] ?? collect()) as $item)<a href="{{ $item->url }}">{{ $item->label }}</a>@if($item->children->isNotEmpty())<div class="footer-submenu">@foreach($item->children as $child)<a href="{{ $child->url }}">{{ $child->label }}</a>@endforeach</div>@endif @endforeach</nav></div>
            @endif
            <div><h3>We're easy to reach</h3><div class="footer-links"><a href="tel:{{ preg_replace('/\s+/', '', $site['phone']) }}">Call {{ $site['phone'] }}</a><a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a><span>Greater Noida, Uttar Pradesh</span></div></div>
        </div>
        <div class="wrap footer-bottom"><span>© {{ now()->year }} {{ $site['business_name'] }}. {{ $site['footer_tagline'] }}</span><span>@if(filled($site['footer_note_url'] ?? null))<a href="{{ $site['footer_note_url'] }}">{{ $site['footer_note'] }}</a>@else{{ $site['footer_note'] }}@endif</span></div>
    </footer>
    <button class="back-to-top" type="button" data-back-to-top aria-label="Scroll to top" aria-hidden="true" tabindex="-1">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5m-6 6 6-6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    @stack('scripts')
</body>
</html>
