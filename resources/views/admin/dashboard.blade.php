@extends('layouts.admin')
@section('title', 'Overview')
@section('content')
@php($chartMax = max(1, (float) $chart->max('total')))
<div class="admin-title"><div><h1>Good morning, {{ auth()->user()->name }}.</h1><p>Here’s the lovely little story your store is telling.</p></div><span class="muted" style="font-size:12px">{{ now()->format('l, d M Y') }}</span></div>
<div class="admin-grid">
    <div class="admin-metric"><span>All orders</span><strong>{{ number_format($orders) }}</strong></div>
    <div class="admin-metric"><span>Needs a little attention</span><strong>{{ number_format($pending) }}</strong></div>
    <div class="admin-metric"><span>Revenue from fulfilled orders</span><strong>₹{{ number_format($revenue, 0) }}</strong></div>
    <div class="admin-metric"><span>Neighbourhood customers</span><strong>{{ number_format($customers) }}</strong></div>
</div>
<div class="admin-columns">
    <section class="admin-panel"><h2>Sales, over the last little while</h2>
        @php($points = $chart->values()->map(fn($item, $index) => ($index * 95).','. (145 - (($item['total'] / $chartMax) * 120)))->implode(' '))
        <svg class="sparkline" viewBox="0 0 600 180" role="img" aria-label="Sales over the last seven days">
            <line class="sparkline-grid" x1="0" y1="30" x2="600" y2="30"/><line class="sparkline-grid" x1="0" y1="90" x2="600" y2="90"/><line class="sparkline-grid" x1="0" y1="150" x2="600" y2="150"/>
            <polyline class="sparkline-line" points="{{ $points }}"/>
            @foreach($chart->values() as $index => $day)<circle class="sparkline-point" cx="{{ $index * 95 }}" cy="{{ 145 - (($day['total'] / $chartMax) * 120) }}" r="4"><title>{{ $day['label'] }}: ₹{{ number_format($day['total'], 2) }}</title></circle>@endforeach
        </svg>
        <div style="display:flex;justify-content:space-between;color:var(--muted);font-size:10px">@foreach($chart as $day)<span>{{ $day['label'] }}</span>@endforeach</div>
    </section>
    <section class="admin-panel"><h2>Low on the shelf</h2>
        @forelse($lowStock as $product)<div class="stock-row"><span>{{ $product->name }}</span><strong style="color:{{ $product->stock < 5 ? '#ad5141' : '#9a7630' }}">{{ $product->stock }} left</strong></div>@empty<p class="muted" style="font-size:12px">Your shelves are looking good.</p>@endforelse
        @can('products.view')<a class="text-link" style="display:inline-block;margin-top:12px" href="{{ route('admin.products.index') }}">Check all products →</a>@endcan
    </section>
</div>
<section class="admin-panel" style="margin-top:17px"><div class="section-heading" style="margin-bottom:13px"><h2>Just came through the door</h2>@can('orders.view')<a class="text-link" href="{{ route('admin.orders.index') }}">All orders →</a>@endcan</div>
    <div class="table-wrap"><table class="data-table"><thead><tr><th>Order</th><th>Neighbour</th><th>Date</th><th>Status</th><th>Total</th>@can('orders.view')<th></th>@endcan</tr></thead><tbody>
        @forelse($recentOrders as $order)<tr><td><strong>{{ $order->order_number }}</strong></td><td>{{ $order->user->name }}</td><td>{{ $order->created_at->format('d M, h:i A') }}</td><td><span class="status-pill">{{ $order->status }}</span></td><td>₹{{ number_format((float) $order->total, 2) }}</td>@can('orders.view')<td><a class="text-link" href="{{ route('admin.orders.show', $order) }}">Open →</a></td>@endcan</tr>@empty<tr><td colspan="6">Nothing on the order board just yet.</td></tr>@endforelse
    </tbody></table></div>
</section>
@endsection
