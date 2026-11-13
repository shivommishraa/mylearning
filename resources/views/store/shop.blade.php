@extends('layouts.store')
@section('title', 'Shop everyday essentials · '.$site['business_name'])
@section('content')
<div class="wrap">
    <div class="breadcrumb"><a href="{{ route('home') }}">Home</a> / Everyday essentials</div>
    <span class="eyebrow">THE SSG MART COLLECTION</span>
    <h1 class="page-title">Everything your home needs.</h1>
    <p class="page-intro">Pantry staples, trusted personal care and household favourites in one convenient shop.</p>
    <div class="shop-layout">
        <aside class="filters">
            <h3>Find your essentials</h3>
            <form method="GET" action="{{ route('shop.index') }}">
                <div class="filter-group"><label for="q">Search products</label><input class="field" id="q" name="q" value="{{ request('q') }}" placeholder="Rice, snacks, home care…"></div>
                <div class="filter-group"><strong style="font-size:12px">Shop by category</strong>
                    @foreach ($categories as $category)<label><input type="radio" name="category" value="{{ $category->slug }}" @checked(request('category') === $category->slug)> {{ $category->name }}</label>@endforeach
                    <label><input type="radio" name="category" value="" @checked(!request('category'))> All essentials</label>
                </div>
                <div class="filter-group"><label for="brand">Popular brands</label><select class="field" id="brand" name="brand"><option value="">All brands</option>@foreach($brands as $brand)<option value="{{ $brand->slug }}" @selected(request('brand') === $brand->slug)>{{ $brand->name }}</option>@endforeach</select></div>
                @if($storefrontFeatures['show_product_prices'])<div class="filter-group"><label>Price range (₹)</label><div style="display:flex;gap:7px"><input type="number" min="0" name="min" value="{{ request('min') }}" placeholder="Min"><input type="number" min="0" name="max" value="{{ request('max') }}" placeholder="Max"></div></div>@endif
                <button class="button button-small" style="width:100%">Apply filters</button>
            </form>
        </aside>
        <div class="shop-results">
            <div class="shop-toolbar"><span>{{ $products->total() }} everyday essentials</span><form method="GET" action="{{ route('shop.index') }}">@foreach(request()->except('sort','page') as $key => $value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endforeach<select name="sort" onchange="this.form.submit()" aria-label="Sort products"><option value="">Recommended</option>@if($storefrontFeatures['show_product_prices'])<option value="price-low" @selected(request('sort') === 'price-low')>Price: low to high</option><option value="price-high" @selected(request('sort') === 'price-high')>Price: high to low</option>@endif<option value="name" @selected(request('sort') === 'name')>Name: A to Z</option></select></form></div>
            @if ($products->isNotEmpty())<div class="product-grid">@foreach ($products as $product)@include('components.product-card', ['product' => $product])@endforeach</div>{{ $products->links('components.pagination') }}
            @else<div class="empty-state"><h2>No matches just yet</h2><p>Try a different search or browse one of our other everyday categories.</p><a class="button button-small" href="{{ route('shop.index') }}">See all essentials</a></div>@endif
        </div>
    </div>
</div>
@endsection
