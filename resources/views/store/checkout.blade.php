@extends('layouts.store')
@section('title', 'A few delivery details · '.$site['business_name'])
@section('content')
<div class="wrap">
    <div class="breadcrumb"><a href="{{ route('cart.index') }}">Basket</a> / Checkout</div>
    <h1 class="page-title">Almost at your door.</h1><p class="page-intro">Choose where to deliver your good things.</p>
    <form class="checkout-layout" method="POST" action="{{ route('checkout.place') }}">@csrf
        <section>
            <div class="basket-card">
                <h2 style="font-size:19px;margin:0 0 6px">Where should we bring it?</h2>
                <p class="muted" style="font-size:12px;margin-bottom:17px">Your saved delivery addresses</p>
                @forelse($addresses as $address)
                    <label class="address-option"><input type="radio" name="address_id" value="{{ $address->id }}" @checked(old('address_id', $address->is_default ? $address->id : null) == $address->id) required><span><strong>{{ $address->recipient_name }} · {{ $address->label }}</strong><br><span class="muted" style="font-size:12px">{{ $address->address_line }}{{ $address->landmark ? ', '.$address->landmark : '' }}, {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}<br>{{ $address->phone }}</span></span></label>
                @empty
                    <div class="empty-state"><p>You don't have a delivery address yet.</p><a class="button button-small" href="{{ route('addresses.index') }}">Add your first address →</a></div>
                @endforelse
                <a class="text-link" href="{{ route('addresses.index') }}">+ Add or manage addresses</a>
            </div>
            <div class="basket-card" style="margin-top:15px">
                <h2 style="font-size:19px;margin:0 0 15px">How would you like to pay?</h2>
                <label class="address-option"><input type="radio" name="payment_method" value="cod" @checked(old('payment_method', 'cod') === 'cod')><span><strong>Cash on delivery</strong><br><span class="muted" style="font-size:12px">Pay when your order arrives.</span></span></label>
                <label class="address-option"><input type="radio" name="payment_method" value="online" @checked(old('payment_method') === 'online')><span><strong>Online payment</strong><br><span class="muted" style="font-size:12px">Payment gateway ready · secure provider can be connected in settings.</span></span></label>
                <div class="admin-field" style="margin-top:14px"><label for="customer_note">A note for our team <span class="muted">(optional)</span></label><textarea class="field" id="customer_note" name="customer_note" rows="3" maxlength="500" placeholder="Gate code, delivery preference…">{{ old('customer_note') }}</textarea></div>
            </div>
        </section>
        <aside class="summary-card" style="height:max-content">
            <h2>Your basket</h2>
            @foreach($summary['lines'] as $line)<div class="summary-line"><span>{{ $line['product']->name }} × {{ $line['quantity'] }}</span><strong>₹{{ number_format($line['line_total'], 2) }}</strong></div>@endforeach
            <div class="summary-line"><span>Subtotal</span><strong>₹{{ number_format($summary['subtotal'], 2) }}</strong></div>
            @if($summary['discount'])<div class="summary-line"><span>Offer · {{ $summary['coupon']->code }}</span><strong>−₹{{ number_format($summary['discount'], 2) }}</strong></div>@endif
            <div class="summary-line"><span>Delivery</span><strong>{{ $summary['delivery_fee'] ? '₹'.number_format($summary['delivery_fee'], 2) : 'On us' }}</strong></div>
            <div class="summary-line summary-total"><span>Total today</span><strong>₹{{ number_format($summary['total'], 2) }}</strong></div>
            @if($addresses->isNotEmpty())<button class="button" style="width:100%;margin-top:14px">Place my order →</button>@endif
            <p class="muted" style="font-size:10px;margin:12px 0 0;text-align:center">Your order is protected and handled with care.</p>
        </aside>
    </form>
</div>
@endsection
