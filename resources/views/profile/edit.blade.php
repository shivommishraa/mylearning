@extends('layouts.store')
@section('title', 'Personal details · '.$site['business_name'])
@section('content')
<div class="wrap customer-profile-page">
    <div class="breadcrumb"><a href="{{ route('account') }}">Your account</a> / Personal details</div>

    <section class="customer-profile-hero">
        <div class="customer-profile-hero-avatar">
            @if($user->profile_photo_path)
                <img src="{{ route('profile.photo', $user) }}" alt="{{ $user->name }} profile photo">
            @else
                <span>{{ str($user->name)->substr(0, 1)->upper() }}</span>
            @endif
        </div>
        <div>
            <span class="eyebrow">Your SSG MART account</span>
            <h1>Good to see you, {{ str($user->name)->before(' ') }}.</h1>
            <p>Keep your details up to date for a smoother, more personal shopping experience.</p>
        </div>
        <span class="customer-profile-member">Member since {{ $user->created_at->format('M Y') }}</span>
    </section>

    <div class="account-layout customer-profile-layout">
        @include('account.partials.navigation', ['active' => 'profile'])

        <main class="account-content customer-profile-content">
            @if(session('status') === 'profile-updated')
                <div class="alert-success customer-profile-alert" role="status">Your profile details have been saved.</div>
            @endif
            @if(session('success'))
                <div class="alert-success customer-profile-alert" role="status">{{ session('success') }}</div>
            @endif

            <section class="customer-profile-card">
                <div class="customer-profile-card-heading">
                    <span class="customer-profile-step">01</span>
                    <div><h2>Personal information</h2><p>Your photo and contact details are kept private to your account.</p></div>
                </div>
                @if(($site['require_email_verification'] ?? '0') === '1' && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <form id="send-profile-verification" method="POST" action="{{ route('verification.send') }}">@csrf</form>
                    <div class="customer-profile-verification">
                        <span>Your email address is not verified.</span>
                        <button class="text-link" type="submit" form="send-profile-verification">Send a new verification link</button>
                        @if(session('status') === 'verification-link-sent')<p>A new verification link has been sent.</p>@endif
                    </div>
                @endif
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PATCH')
                    <div class="customer-profile-fields">
                        <div class="customer-profile-field">
                            <label for="profile-name">Full name</label>
                            <input id="profile-name" type="text" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                            @error('name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="customer-profile-field">
                            <label for="profile-email">Email address</label>
                            <input id="profile-email" type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                            @error('email')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="customer-profile-field">
                            <label for="profile-phone">Phone <span class="optional-note">Optional</span></label>
                            <input id="profile-phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                            @error('phone')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="customer-profile-photo-cell">
                            @include('components.profile-photo-field', ['profileUser' => $user, 'photoInputId' => 'profile-photo'])
                        </div>
                    </div>

                    <div class="customer-profile-card-heading customer-profile-address-heading">
                        <span class="customer-profile-step">02</span>
                        <div><h2>Primary delivery address <span class="optional-note">Optional</span></h2><p>Save a home address for quicker checkout. You can manage additional addresses separately.</p></div>
                    </div>
                    <div class="customer-profile-fields customer-profile-address-fields">
                        <div class="customer-profile-field customer-profile-field-wide">
                            <label for="profile-address">Street address</label>
                            <input id="profile-address" type="text" name="address_line" value="{{ old('address_line', $address?->address_line) }}" autocomplete="street-address">
                            @error('address_line')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="customer-profile-field">
                            <label for="profile-address-phone">Address phone</label>
                            <input id="profile-address-phone" type="tel" name="address_phone" value="{{ old('address_phone', $address?->phone ?? $user->phone) }}" autocomplete="tel">
                            @error('address_phone')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="customer-profile-field">
                            <label for="profile-city">City</label>
                            <input id="profile-city" type="text" name="city" value="{{ old('city', $address?->city) }}" autocomplete="address-level2">
                            @error('city')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="customer-profile-field">
                            <label for="profile-state">State</label>
                            <input id="profile-state" type="text" name="state" value="{{ old('state', $address?->state) }}" autocomplete="address-level1">
                            @error('state')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="customer-profile-field">
                            <label for="profile-postal-code">Postal code</label>
                            <input id="profile-postal-code" type="text" name="postal_code" value="{{ old('postal_code', $address?->postal_code) }}" autocomplete="postal-code">
                            @error('postal_code')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="customer-profile-field">
                            <label for="profile-landmark">Landmark <span class="optional-note">Optional</span></label>
                            <input id="profile-landmark" type="text" name="landmark" value="{{ old('landmark', $address?->landmark) }}">
                            @error('landmark')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    @if($address)
                        <label class="profile-remove-option customer-profile-remove-address">
                            <input type="checkbox" name="remove_address" value="1" @checked(old('remove_address'))>
                            Remove this primary delivery address
                        </label>
                    @endif
                    <div class="customer-profile-form-footer">
                        <span>Your photo and address are optional.</span>
                        <button class="button" type="submit">Save personal details</button>
                    </div>
                </form>
            </section>

            <section class="customer-profile-card customer-profile-secondary">
                <div class="customer-profile-card-heading">
                    <span class="customer-profile-step">03</span>
                    <div><h2>Account security</h2><p>Update your password to keep your account protected.</p></div>
                </div>
                @include('profile.partials.update-password-form')
            </section>

            <section class="customer-profile-card customer-profile-danger">
                <div class="customer-profile-card-heading">
                    <span class="customer-profile-step">04</span>
                    <div><h2>Close your account</h2><p>Delete your account if you no longer want to use SSG MART.</p></div>
                </div>
                @include('profile.partials.delete-user-form')
            </section>
        </main>
    </div>
</div>
@endsection
