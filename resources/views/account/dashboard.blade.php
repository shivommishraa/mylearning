@extends('layouts.store')
@section('title', 'My account · '.$site['business_name'])
@section('content')
<div class="wrap">
    <div class="breadcrumb"><a href="{{ route('home') }}">Home</a> / Your account</div>
    <h1 class="page-title">Hello, {{ auth()->user()->name }}.</h1><p class="page-intro">Your SSG MART, made personal.</p>
    <div class="account-layout">
        @include('account.partials.navigation', ['active' => 'overview'])
        <section class="account-content">
            <div class="account-stats"><div class="stat-card"><strong>{{ $orderCount }}</strong><span>Orders placed</span></div><div class="stat-card"><strong>{{ $addresses }}</strong><span>Saved addresses</span></div><div class="stat-card"><strong>{{ auth()->user()->wishlist()->count() }}</strong><span>Saved favourites</span></div></div>
            <div class="section-heading"><div><h2 style="font-size:21px">A little look at your orders</h2><p>Your recent good things, all in one place.</p></div><a class="text-link" href="{{ route('account.orders') }}">All orders →</a></div>
            @if($orders->isNotEmpty())
                <div class="table-wrap"><table class="data-table"><thead><tr><th>Order</th><th>Placed</th><th>Status</th><th>Total</th><th></th></tr></thead><tbody>@foreach($orders as $order)<tr><td><strong>{{ $order->order_number }}</strong></td><td>{{ $order->created_at->format('d M Y') }}</td><td><span class="status-pill">{{ $order->status }}</span></td><td>₹{{ number_format((float) $order->total, 2) }}</td><td><a class="text-link" href="{{ route('orders.show', $order) }}">View →</a></td></tr>@endforeach</tbody></table></div>
            @else
                <div class="empty-state"><h2>Let's make your first basket</h2><p>Your orders will feel right at home here.</p><a class="button button-small" href="{{ route('shop.index') }}">Find something good →</a></div>
            @endif
        </section>
    </div>
</div>
@endsection
