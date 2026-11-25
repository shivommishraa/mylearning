<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SSG MART') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="announcement">{{ $site['announcement_text'] }} <strong>· Free delivery over ₹{{ number_format((float) $site['free_delivery_minimum']) }}</strong></div>
    <header class="header"><div class="wrap header-main">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark">{{ strtoupper(substr($site['business_name'], 0, 1)) }}</span><span>{{ $site['business_name'] }}</span></a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="button button-light button-small">Sign out</button></form>
    </div>
    @if(($navigationItems['header'] ?? collect())->isNotEmpty())
        <nav class="wrap nav" data-menu>
            @foreach(($navigationItems['header'] ?? collect()) as $item)
                @if($item->children->isEmpty())
                    <a href="{{ $item->url }}">{{ $item->label }}</a>
                @else
                    <div class="nav-group" data-nav-group>
                        <a href="{{ $item->url }}">{{ $item->label }}</a>
                        <button class="nav-submenu-toggle" type="button" data-nav-submenu-toggle aria-expanded="false" aria-haspopup="true" aria-controls="app-nav-submenu-{{ $item->id }}" aria-label="Toggle {{ $item->label }} submenu"><svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M4 6l4 4 4-4"/></svg></button>
                        <div class="nav-submenu" id="app-nav-submenu-{{ $item->id }}" data-nav-submenu>
                            @foreach($item->children as $child)<a href="{{ $child->url }}">{{ $child->label }}</a>@endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </nav>
    @endif
    </header>
    @if (session('success'))<div class="wrap alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="wrap alert alert-error"><ul style="margin:0;padding-left:18px">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <main class="wrap" style="padding-top:28px;padding-bottom:40px">{{ $slot }}</main>
    <footer class="footer">
        <div class="wrap footer-bottom" style="border-top:0;margin-top:0;padding-top:0">
            <span>© {{ now()->year }} {{ $site['business_name'] }}. {{ $site['footer_tagline'] }}</span>
            @if(($navigationItems['footer'] ?? collect())->isNotEmpty())
                <nav class="footer-links" aria-label="Footer navigation" data-footer-menu>
                    @foreach(($navigationItems['footer'] ?? collect()) as $item)
                        <a href="{{ $item->url }}">{{ $item->label }}</a>
                        @foreach($item->children as $child)<a href="{{ $child->url }}">{{ $child->label }}</a>@endforeach
                    @endforeach
                </nav>
            @endif
        </div>
    </footer>
</body>
</html>
