@extends('layouts.store')
@section('title', 'Delivery addresses · '.$site['business_name'])
@section('content')
<div class="wrap">
    <div class="breadcrumb"><a href="{{ route('account') }}">Your account</a> / Addresses</div>
    <h1 class="page-title">Places we can find you.</h1><p class="page-intro">Save your favourite delivery spots for a quicker checkout.</p>
    <div class="account-layout">
        @include('account.partials.navigation', ['active' => 'addresses'])
        <section class="account-content">
            <div class="basket-card" style="margin-bottom:20px"><h2 style="font-size:19px">Add a delivery address</h2>
                <form method="POST" action="{{ route('addresses.store') }}">@csrf @include('account.partials.address-fields', ['address' => null])<button class="button button-small">Save this address</button></form>
            </div>
            <h2 style="font-size:20px">Your saved places</h2>
            <div class="form-grid">
                @forelse($addresses as $address)
                    <div class="basket-card">
                        <form method="POST" action="{{ route('addresses.update', $address) }}">@csrf @method('PUT')
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px"><strong>{{ $address->label }}</strong>@if($address->is_default)<span class="status-pill">Default</span>@endif</div>
                            @include('account.partials.address-fields', ['address' => $address])
                            <button class="button button-light button-small">Save changes</button>
                        </form>
                        <form method="POST" action="{{ route('addresses.destroy', $address) }}" style="margin-top:8px">@csrf @method('DELETE')<button class="remove-link" style="border:0;background:none;padding:5px">Remove this address</button></form>
                    </div>
                @empty
                    <div class="empty-state"><p>Your saved places will appear here.</p></div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
