@extends('layouts.store')
@section('title', 'Personal details · '.$site['business_name'])
@section('content')
@php
    $hasAdditionalDetails = filled($user->father_husband_name)
        || filled($user->date_of_birth)
        || filled($user->gender)
        || filled($user->profile_photo_path)
        || $address !== null;
    $showAdditionalDetails = (bool) old('additional_details', $hasAdditionalDetails);
@endphp
<div class="wrap customer-profile-page">
    <div class="breadcrumb"><a href="{{ route('account') }}">Your account</a> / Personal details</div>

    <section class="customer-profile-hero">
        @if($user->profile_photo_path)
            <button class="customer-profile-hero-avatar" type="button" data-photo-open aria-label="View and edit profile photo">
                <img src="{{ route('profile.photo', $user) }}" alt="{{ $user->name }} profile photo">
            </button>
        @else
            <div class="customer-profile-hero-avatar">
                <span>{{ str($user->name)->substr(0, 1)->upper() }}</span>
            </div>
        @endif
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
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" data-contact-pair data-validated-form>
                    @csrf
                    @method('PATCH')
                    <div class="customer-profile-fields">
                        <div class="customer-profile-field">
                            <label for="profile-name">Full name</label>
                            <input id="profile-name" type="text" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                            @error('name')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="customer-profile-field">
                            <label for="profile-email">Email address <span class="optional-note">Use email or mobile</span></label>
                            <input id="profile-email" type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" data-contact-email>
                            @error('email')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                        <div class="customer-profile-field">
                            <label for="profile-phone">Mobile number <span class="optional-note">Use email or mobile</span></label>
                            <input id="profile-phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" autocomplete="tel" data-contact-phone>
                            @error('phone')<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div data-additional-details>
                        <div class="additional-details-toggle customer-profile-additional-toggle">
                            <input type="hidden" name="additional_details" value="0">
                            <label>
                                <input type="checkbox" name="additional_details" value="1" data-additional-details-toggle @checked($showAdditionalDetails)>
                                <span>अन्य विवरण भरना चाहते हैं?</span>
                            </label>
                            <p>Add a photo, address and other personal details if you wish.</p>
                        </div>
                        <div class="additional-details-panel" data-additional-details-panel @if(! $showAdditionalDetails) hidden @endif>
                            <div class="customer-profile-card-heading customer-profile-address-heading">
                                <span class="customer-profile-step">02</span>
                                <div><h2>Additional personal details</h2><p>These details are optional and only visible in your account.</p></div>
                            </div>
                            <div class="customer-profile-fields">
                                <div class="customer-profile-field">
                                    <label for="profile-father-name">Father / Husband name</label>
                                    <input id="profile-father-name" type="text" name="father_husband_name" value="{{ old('father_husband_name', $user->father_husband_name) }}" autocomplete="additional-name">
                                    @error('father_husband_name')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="customer-profile-field">
                                    <label for="profile-date-of-birth">Date of birth</label>
                                    <input id="profile-date-of-birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}">
                                    @error('date_of_birth')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="customer-profile-field">
                                    <label for="profile-gender">Gender</label>
                                    <select id="profile-gender" name="gender">
                                        <option value="">Select if you wish</option>
                                        <option value="female" @selected(old('gender', $user->gender) === 'female')>Female</option>
                                        <option value="male" @selected(old('gender', $user->gender) === 'male')>Male</option>
                                        <option value="other" @selected(old('gender', $user->gender) === 'other')>Other</option>
                                        <option value="prefer_not_to_say" @selected(old('gender', $user->gender) === 'prefer_not_to_say')>Prefer not to say</option>
                                    </select>
                                    @error('gender')<p class="field-error">{{ $message }}</p>@enderror
                                </div>
                                <div class="customer-profile-photo-cell">
                                    @include('components.profile-photo-field', ['profileUser' => $user, 'photoInputId' => 'profile-photo'])
                                </div>
                            </div>

                            <div class="customer-profile-card-heading customer-profile-address-heading">
                                <span class="customer-profile-step">03</span>
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
                        </div>
                    </div>
                    <div class="customer-profile-form-footer">
                        <span>All additional details are optional.</span>
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
