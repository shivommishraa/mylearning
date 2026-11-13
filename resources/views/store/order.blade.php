@extends('layouts.store')
@section('title', 'Order '.$order->order_number.' · '.$site['business_name'])
@section('content')
<div class="wrap" style="max-width:900px">
    <div class="breadcrumb"><a href="{{ route('account') }}">My account</a> / Order {{ $order->order_number }}</div>
    <div class="empty-state" style="margin-bottom:22px"><div style="font-size:35px;color:var(--green)">✓</div><h1 class="page-title" style="font-size:30px">A little goodness is on its way.</h1><p>Thank you for choosing your neighbourhood store. Your order number is <strong>{{ $order->order_number }}</strong>.</p><span class="status-pill">{{ $order->status }}</span></div>
    <div class="checkout-layout">
        <section class="basket-card"><h2 style="font-size:19px">Your good things</h2>
            @foreach($order->items as $item)<div class="summary-line"><span>{{ $item->product_name }} × {{ $item->quantity }} <span class="muted">({{ $item->unit }})</span></span><strong>₹{{ number_format((float) $item->line_total, 2) }}</strong></div>@endforeach
            <div class="summary-line"><span>Subtotal</span><strong>₹{{ number_format((float) $order->subtotal, 2) }}</strong></div>
            @if($order->discount)<div class="summary-line"><span>Offer · {{ $order->coupon_code }}</span><strong>−₹{{ number_format((float) $order->discount, 2) }}</strong></div>@endif
            <div class="summary-line"><span>Delivery</span><strong>{{ $order->delivery_fee ? '₹'.number_format((float) $order->delivery_fee, 2) : 'On us' }}</strong></div>
            <div class="summary-line summary-total"><span>Total</span><strong>₹{{ number_format((float) $order->total, 2) }}</strong></div>
            <a class="button button-light button-small" href="{{ route('orders.invoice', $order) }}" target="_blank" rel="noopener">View / print invoice</a>
        </section>
        <aside class="summary-card"><h2>Delivery details</h2><p><strong>{{ $order->delivery_address['recipient_name'] }}</strong><br>{{ $order->delivery_address['address_line'] }}<br>{{ $order->delivery_address['city'] }}, {{ $order->delivery_address['state'] }} {{ $order->delivery_address['postal_code'] }}<br>{{ $order->delivery_address['phone'] }}</p><p class="muted" style="font-size:12px">Payment: {{ $order->payment_method === 'cod' ? 'Cash on delivery' : 'Online · payment pending' }}</p><p class="muted" style="font-size:12px">Order placed {{ $order->created_at->format('d M Y, h:i A') }}</p><a class="text-link" href="{{ route('account') }}">Back to your orders →</a></aside>
    </div>
</div>
@endsection
