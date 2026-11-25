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
<form class="admin-panel" method="POST" enctype="multipart/form-data" action="{{ $customer->exists ? route('admin.customers.update', $customer) : route('admin.customers.store') }}">
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
            <input id="customer-phone" type="tel" name="phone" value="{{ old('phone', $customer->phone) }}" maxlength="20">
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
    <div class="admin-profile-section">
        <h2>Profile photo</h2>
        @include('components.profile-photo-field', ['profileUser' => $customer, 'photoInputId' => 'customer-profile-photo'])
    </div>
    <div class="admin-profile-section">
        <h2>Primary delivery address <span class="optional-note">Optional</span></h2>
        <p class="muted">Leave the address empty to keep it unset. An existing address can be removed with the checkbox.</p>
        <div class="registration-address-grid">
            <div class="admin-field">
                <label for="customer-address-line">Street address</label>
                <input id="customer-address-line" name="address_line" value="{{ old('address_line', $address?->address_line) }}" maxlength="255">
                @error('address_line')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="admin-field">
                <label for="customer-address-phone">Address phone</label>
                <input id="customer-address-phone" type="tel" name="address_phone" value="{{ old('address_phone', $address?->phone ?? $customer->phone) }}" maxlength="20">
                @error('address_phone')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="admin-field">
                <label for="customer-city">City</label>
                <input id="customer-city" name="city" value="{{ old('city', $address?->city) }}" maxlength="100">
                @error('city')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="admin-field">
                <label for="customer-state">State</label>
                <input id="customer-state" name="state" value="{{ old('state', $address?->state) }}" maxlength="100">
                @error('state')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="admin-field">
                <label for="customer-postal-code">Postal code</label>
                <input id="customer-postal-code" name="postal_code" value="{{ old('postal_code', $address?->postal_code) }}" maxlength="12">
                @error('postal_code')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="admin-field">
                <label for="customer-landmark">Landmark (optional)</label>
                <input id="customer-landmark" name="landmark" value="{{ old('landmark', $address?->landmark) }}" maxlength="160">
                @error('landmark')<p class="field-error">{{ $message }}</p>@enderror
            </div>
        </div>
        @if($address)
            <label class="profile-remove-option">
                <input type="checkbox" name="remove_address" value="1" @checked(old('remove_address'))>
                Remove this primary delivery address
            </label>
        @endif
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
