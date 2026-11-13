@extends('layouts.store')
@section('title', 'Order history · '.$site['business_name'])
@section('content')
<div class="wrap">
    <div class="breadcrumb"><a href="{{ route('account') }}">Your account</a> / Order history</div>
    <h1 class="page-title">Your good things, delivered.</h1><p class="page-intro">Every order, right where you left it.</p>
    <div class="account-layout">@include('account.partials.navigation',['active'=>'orders'])
        <section class="account-content"><div class="table-wrap"><table class="data-table"><thead><tr><th>Order</th><th>Placed</th><th>Status</th><th>Payment</th><th>Total</th><th></th></tr></thead><tbody>
        @forelse($orders as $order)<tr><td><strong>{{ $order->order_number }}</strong></td><td>{{ $order->created_at->format('d M Y') }}</td><td><span class="status-pill">{{ $order->status }}</span></td><td>{{ $order->payment_method === 'cod' ? 'Cash on delivery' : 'Online' }}</td><td>₹{{ number_format((float)$order->total,2) }}</td><td><a class="text-link" href="{{ route('orders.show',$order) }}">Track →</a></td></tr>
        @empty<tr><td colspan="6">Nothing has been ordered just yet. Let's change that.</td></tr>@endforelse
        </tbody></table></div>{{ $orders->links('components.pagination') }}</section>
    </div>
</div>
@endsection
