<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SSG MART') }} · Daily essentials, close to home</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="announcement">Everyday essentials, close to home · <strong>Welcome to SSG MART</strong></div>
    <div class="auth-shell"><div style="width:min(100%,460px)">
        <a class="brand" href="{{ route('home') }}" style="justify-content:center;margin-bottom:20px"><span class="brand-mark">S</span><span>SSG MART</span></a>
        <div class="auth-card">{{ $slot }}</div>
        <p style="text-align:center;color:#758078;font-size:11px;margin-top:16px">Your trusted daily-needs store in Greater Noida.</p>
    </div></div>
</body>
</html>
