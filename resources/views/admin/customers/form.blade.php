@extends('layouts.admin')
@section('title', $customer->exists ? 'Edit customer' : 'Add customer')
@section('content')
<div class="admin-title">
    <div>
        <h1>{{ $customer->exists ? 'Update customer details' : 'Add a customer' }}</h1>
        <p>{{ $customer->exists ? 'Update the customer profile and account access.' : 'Create a customer account for your neighbourhood store.' }}</p>
    </div>
    @can('customers.view')
        <a class="text-link" href="{{ route('admin.customers.index') }}">← Customers</a>
    @endcan
</div>
<form class="admin-panel" method="POST" action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}">
    @csrf
    @if($customer->exists) @method('PUT') @endif
    <div class="form-grid">
        <div class="admin-field">
            <label for="customer-name">Name</label>
            <input id="customer-name" name="name" value="{{ old('name', $customer->name) }}" maxlength="120" required>
        </div>
        <div class="admin-field">
            <label for="customer-email">Email</label>
            <input id="customer-email" type="email" name="email" value="{{ old('email', $customer->email) }}" maxlength="255" required>
        </div>
        <div class="admin-field">
            <label for="customer-phone">Phone (optional)</label>
            <input id="customer-phone" type="tel" name="phone" value="{{ old('phone', $customer->phone) }}" maxlength="30">
        </div>
        <div class="admin-field">
            <label for="customer-password">{{ $customer->exists ? 'New password (optional)' : 'Password' }}</label>
            <input id="customer-password" type="password" name="password" autocomplete="new-password" {{ $customer->exists ? '' : 'required' }}>
        </div>
        <div class="admin-field">
            <label for="customer-password-confirmation">Confirm password</label>
            <input id="customer-password-confirmation" type="password" name="password_confirmation" autocomplete="new-password" {{ $customer->exists ? '' : 'required' }}>
            @if($customer->exists)
                <small>Leave both password fields blank to keep the current password.</small>
            @endif
        </div>
    </div>
    @if($customer->exists)
        <p class="muted" style="font-size:12px;margin:18px 0 0">Email verification: <strong>{{ $customer->email_verified_at ? 'Verified' : 'Pending' }}</strong>. Changing the email address requires verification again when that setting is enabled.</p>
    @endif
    <div style="display:flex;gap:12px;align-items:center;margin-top:22px">
        <button class="button">{{ $customer->exists ? 'Save changes' : 'Create customer' }}</button>
        @can('customers.view')
            <a class="text-link" href="{{ $customer->exists ? route('admin.customers.show', $customer) : route('admin.customers.index') }}">Cancel</a>
        @endcan
    </div>
</form>
@endsection
