@extends('layouts.admin')
@section('title', 'Customer details')
@section('content')
<div class="admin-title">
    <div><h1>{{ $customer->name }}</h1><p>Customer details, account verification and recent order history.</p></div>
    <div class="admin-actions">
        @can('customers.edit')
            <a class="button button-small" href="{{ route('admin.customers.edit', $customer) }}">Edit customer</a>
        @endcan
        <a class="text-link" href="{{ route('admin.customers.index') }}">← Customers</a>
    </div>
</div>
<section class="admin-panel">
    <div class="customer-admin-identity">
        <div class="profile-photo-current">
            @if($customer->profile_photo_path)
                <img src="{{ route('profile.photo', $customer) }}" alt="{{ $customer->name }} profile photo">
            @else
                <span>{{ str($customer->name)->substr(0, 1)->upper() }}</span>
            @endif
        </div>
        <div><strong>{{ $customer->name }}</strong><span>Customer profile</span></div>
    </div>
    <div class="form-grid">
        <div><span class="muted">Email</span><p><a class="text-link" href="mailto:{{ $customer->email }}">{{ $customer->email }}</a></p></div>
        <div><span class="muted">Phone</span><p>{{ $customer->phone ?: '—' }}</p></div>
        <div><span class="muted">Email verification</span><p><span class="status-pill">{{ $customer->email_verified_at ? 'Verified' : 'Pending' }}</span></p></div>
        <div><span class="muted">Father / Husband name</span><p>{{ $customer->father_husband_name ?: '—' }}</p></div>
        <div><span class="muted">Date of birth</span><p>{{ $customer->date_of_birth?->format('d M Y') ?: '—' }}</p></div>
        <div><span class="muted">Gender</span><p>{{ $customer->gender ? str($customer->gender)->replace('_', ' ')->title() : '—' }}</p></div>
        <div><span class="muted">Customer since</span><p>{{ $customer->created_at->format('d M Y') }}</p></div>
        <div><span class="muted">Total orders</span><p>{{ $customer->orders_count }}</p></div>
    </div>
    <div class="customer-admin-address">
        <h2>Primary delivery address</h2>
        @if($address)
            <p>{{ $address->address_line }}@if($address->landmark), {{ $address->landmark }}@endif</p>
            <p>{{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}</p>
            <p>{{ $address->recipient_name }} · {{ $address->phone }}</p>
        @else
            <p class="muted">No delivery address saved.</p>
        @endif
    </div>
</section>
<section class="admin-panel">
    <h2>Recent orders</h2>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Total</th>@can('orders.view')<th></th>@endcan</tr></thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td><strong>{{ $order->order_number }}</strong></td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td><span class="status-pill">{{ str($order->status)->replace('_', ' ')->title() }}</span></td>
                        <td>₹{{ number_format((float) $order->total, 2) }}</td>
                        @can('orders.view')
                            <td><a class="text-link" href="{{ route('admin.orders.show', $order) }}">View order</a></td>
                        @endcan
                    </tr>
                @empty
                    <tr><td colspan="{{ auth()->user()->can('orders.view') ? 5 : 4 }}">No orders placed yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@if(auth()->user()->can('customers.delete') && $customer->orders_count === 0)
    <section class="admin-panel">
        <h2>Delete customer</h2>
        <p class="muted">Accounts with order history are retained to preserve store records.</p>
        <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('Delete this customer account?')">
            @csrf @method('DELETE')
            <button class="remove-link" style="border:0;background:none">Delete customer account</button>
        </form>
    </section>
@endif
@endsection
