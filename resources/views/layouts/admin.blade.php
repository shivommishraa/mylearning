<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') · {{ $site['business_name'] }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar" id="admin-sidebar" data-admin-sidebar aria-label="Admin navigation">
        <div class="admin-sidebar-header">
            <a class="brand" href="{{ route('admin.dashboard') }}"><span class="brand-mark">S</span><span>SSG <span style="font-weight:500">STUDIO</span></span></a>
            <button type="button" class="admin-sidebar-close" data-admin-menu-close aria-label="Close admin menu">×</button>
        </div>
        <nav class="admin-sidebar-nav">
            <small>Workspace</small>
            @can('dashboard.view')<a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">▦ &nbsp; Overview</a>@endcan
            @can('orders.view')<a class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">▤ &nbsp; Orders</a>@endcan
            @can('products.view')<a class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">◫ &nbsp; Products</a>@endcan
            @can('products.settings')<a class="{{ request()->routeIs('admin.products.settings*') ? 'active' : '' }}" href="{{ route('admin.products.settings') }}">⚙ &nbsp; Product display settings</a>@endcan
            @can('categories.view')<a class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}">◈ &nbsp; Categories</a>@endcan
            @can('coupons.view')<a class="{{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}" href="{{ route('admin.coupons.index') }}">◇ &nbsp; Offers & coupons</a>@endcan
            <small>People & story</small>
            @can('customers.view')<a class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" href="{{ route('admin.customers.index') }}">♙ &nbsp; Customers</a>@endcan
            @can('enquiries.view')<a class="{{ request()->routeIs('admin.enquiries.*') ? 'active' : '' }}" href="{{ route('admin.enquiries.index') }}">✉ &nbsp; Enquiries</a>@endcan
            @can('content.view')<a class="{{ request()->routeIs('admin.sections.*') ? 'active' : '' }}" href="{{ route('admin.sections.index') }}">▤ &nbsp; Homepage collections</a><a class="{{ request()->routeIs('admin.banners.*') ? 'active' : '' }}" href="{{ route('admin.banners.index') }}">▧ &nbsp; Homepage banners</a><a class="{{ request()->routeIs('admin.testimonials.*') ? 'active' : '' }}" href="{{ route('admin.testimonials.index') }}">♡ &nbsp; Testimonials</a>@endcan
            @can('welcome_popup.view')<a class="{{ request()->routeIs('admin.welcome-popup.*') ? 'active' : '' }}" href="{{ route('admin.welcome-popup.index') }}">✧ &nbsp; Welcome popup</a>@endcan
            @can('pages.view')<a class="{{ request()->routeIs('admin.pages.*') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}">▤ &nbsp; Content pages</a>@endcan
            @can('brands.view')<a class="{{ request()->routeIs('admin.brands.*') ? 'active' : '' }}" href="{{ route('admin.brands.index') }}">◉ &nbsp; Brands</a>@endcan
            @can('menus.view')<a class="{{ request()->routeIs('admin.menus.*') ? 'active' : '' }}" href="{{ route('admin.menus.index') }}">☰ &nbsp; Navigation menus</a>@endcan
            <small>Store settings</small>
            @can('settings.view')<a class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">⚙ &nbsp; Website & delivery</a>@endcan
            @role('Super Admin')<a class="{{ request()->routeIs('admin.admins.*') ? 'active' : '' }}" href="{{ route('admin.admins.index') }}">♧ &nbsp; Admin access</a>@endrole
            @can('activity.view')<a class="{{ request()->routeIs('admin.activity.*') ? 'active' : '' }}" href="{{ route('admin.activity.index') }}">◷ &nbsp; Activity log</a>@endcan
            <small>Visit the shop</small><a href="{{ route('home') }}">↗ &nbsp; Customer storefront</a>
        </nav>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <button type="button" class="admin-menu-toggle" data-admin-menu-toggle aria-controls="admin-sidebar" aria-expanded="false">
                <span aria-hidden="true">☰</span><span>Menu</span>
            </button>
            <span class="admin-topbar-note muted">A little goodness, well managed.</span>
            <div class="admin-user-controls"><strong class="admin-user-name">{{ auth()->user()->name }}</strong><form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="remove-link">Sign out</button></form></div>
        </header>
        <main class="admin-content">
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </main>
    </div>
</div>
<button type="button" class="admin-menu-backdrop" data-admin-menu-backdrop aria-label="Close admin menu" hidden></button>
@stack('scripts')
</body>
</html>
