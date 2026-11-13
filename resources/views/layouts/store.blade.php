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
    <div class="announcement">{{ $site['announcement_text'] }} <strong>· Free delivery over ₹{{ number_format((float) $site['free_delivery_minimum']) }}</strong></div>
    <header class="header">
        <div class="wrap header-main">
            <button type="button" class="mobile-toggle" aria-label="Toggle navigation" aria-expanded="false" data-menu-toggle>☰</button>
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark">{{ strtoupper(substr($site['business_name'], 0, 1)) }}</span><span>{{ $site['business_name'] }}</span></a>
            <form class="search" action="{{ route('shop.index') }}" method="GET" role="search">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Search atta, chawal, dal, chai…" aria-label="Search daily essentials">
                <button aria-label="Search">⌕</button>
            </form>
            <div class="header-actions">
                <a class="header-link" href="{{ auth()->check() ? route('account') : route('login') }}" aria-label="{{ auth()->check() ? 'My account' : 'Sign in' }}"><span class="header-icon" aria-hidden="true">♙</span><span class="label">{{ auth()->check() ? 'My account' : 'Sign in' }}</span></a>
                @guest<a class="header-link" href="{{ route('register') }}" aria-label="Register"><span class="header-icon" aria-hidden="true">＋</span><span class="label">Register</span></a>@endguest
                <a class="header-link" href="{{ route('cart.index') }}" aria-label="Basket"><span class="header-icon" aria-hidden="true">♧</span><span class="label">Basket</span><span class="cart-count">{{ array_sum(session('cart', [])) }}</span></a>
            </div>
        </div>
        <nav class="wrap nav" data-menu>
            @foreach(($navigationItems['header'] ?? collect()) as $item)<a href="{{ $item->url }}">{{ $item->label }}</a>@endforeach
            @foreach($storeCategories->take(5) as $category)<a href="{{ route('shop.index', ['category' => $category->slug]) }}">{{ $category->name }}</a>@endforeach
        </nav>
    </header>
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
            <div><h3>Come on in</h3><address>{{ $site['address'] }}</address><a href="https://maps.google.com/?q={{ urlencode($site['address']) }}" target="_blank" rel="noopener">Find us on Google Maps ↗</a></div>
            <div><h3>A few helpful links</h3><div class="footer-links"><a href="{{ auth()->check() ? route('account') : route('login') }}">Your account</a><a href="{{ route('cart.index') }}">Your basket</a>@foreach(($navigationItems['footer'] ?? collect()) as $item)<a href="{{ $item->url }}">{{ $item->label }}</a>@endforeach @foreach($publishedPages as $page)<a href="{{ route('pages.show',$page->slug) }}">{{ $page->title }}</a>@endforeach</div></div>
            <div><h3>We're easy to reach</h3><div class="footer-links"><a href="tel:{{ preg_replace('/\s+/', '', $site['phone']) }}">Call {{ $site['phone'] }}</a><a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a><span>Greater Noida, Uttar Pradesh</span></div></div>
        </div>
        <div class="wrap footer-bottom"><span>© {{ now()->year }} {{ $site['business_name'] }}. Daily essentials, close to home.</span><span>Made for your neighbourhood · India</span></div>
    </footer>
    @stack('scripts')
</body>
</html>
