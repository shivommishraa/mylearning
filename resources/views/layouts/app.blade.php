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
    <div class="announcement">A little more goodness in every basket · <strong>SSG MART</strong></div>
    <header class="header"><div class="wrap header-main">
        <a class="brand" href="{{ route('home') }}"><span class="brand-mark">S</span><span>SSG MART</span></a>
        <nav class="nav" style="display:flex;flex:1;border:0;height:auto"><a href="{{ route('shop.index') }}">Shop</a><a href="{{ route('account') }}">My account</a><a href="{{ route('addresses.index') }}">Addresses</a><a href="{{ route('profile.edit') }}">Profile</a></nav>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="button button-light button-small">Sign out</button></form>
    </div></header>
    @if (session('success'))<div class="wrap alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="wrap alert alert-error"><ul style="margin:0;padding-left:18px">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <main class="wrap" style="padding-top:28px;padding-bottom:40px">{{ $slot }}</main>
    <footer class="footer"><div class="wrap footer-bottom" style="border-top:0;margin-top:0;padding-top:0"><span>© {{ now()->year }} SSG MART</span><a href="{{ route('home') }}">Back to the store →</a></div></footer>
</body>
</html>
