@extends('layouts.store')
@section('title', 'Saved favourites · '.$site['business_name'])
@section('content')
<div class="wrap">
    <div class="breadcrumb"><a href="{{ route('account') }}">Your account</a> / Saved favourites</div>
    <h1 class="page-title">A few things you love.</h1><p class="page-intro">Your saved favourites, ready whenever you are.</p>
    <div class="account-layout">@include('account.partials.navigation', ['active' => 'wishlist'])
        <section class="account-content">@if($products->isNotEmpty())<div class="product-grid">@foreach($products as $product)@include('components.product-card', ['product' => $product])@endforeach</div>{{ $products->links('components.pagination') }}@else<div class="empty-state"><h2>Save a little something</h2><p>Tap the heart on a product to keep it close.</p><a class="button button-small" href="{{ route('shop.index') }}">Browse the shop</a></div>@endif</section>
    </div>
</div>
@endsection
