<x-guest-layout>
    <div class="auth-intro">
        <span class="eyebrow">Create your account</span>
        <h1>Welcome to your neighbourhood store.</h1>
        <p>Use your email or mobile number to get started. Add the rest whenever you like.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" data-contact-pair data-validated-form>
        @csrf

        <div class="auth-form-grid">
            <div class="auth-form-field auth-field-wide">
                <x-input-label for="name" :value="__('Full name')" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <p class="auth-form-hint auth-field-wide">Enter at least one contact method: email or mobile number.</p>
            <div class="auth-form-field">
                <x-input-label for="email" :value="__('Email (optional if you use mobile)')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" autocomplete="email" data-contact-email />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div class="auth-form-field">
                <x-input-label for="phone" :value="__('Mobile number (optional if you use email)')" />
                <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" autocomplete="tel" data-contact-phone />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <div class="auth-form-field auth-field-wide">
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" data-password-field />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div class="auth-form-field auth-field-wide">
                <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" data-password-confirmation />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div data-additional-details>
            <div class="additional-details-toggle">
                <input type="hidden" name="additional_details" value="0">
                <label>
                    <input type="checkbox" name="additional_details" value="1" data-additional-details-toggle @checked(old('additional_details', false))>
                    <span>अन्य विवरण भरना चाहते हैं?</span>
                </label>
                <p>These details are optional and can be added later in your profile.</p>
            </div>

        <section class="additional-details-panel" data-additional-details-panel @if(! old('additional_details', false)) hidden @endif>
            <div class="registration-section-heading">
                <div>
                    <span class="eyebrow">Optional details</span>
                    <h2>Make your account more personal</h2>
                </div>
            </div>
            <div class="auth-form-grid">
                <div class="auth-form-field">
                    <x-input-label for="father_husband_name" :value="__('Father / Husband name')" />
                    <x-text-input id="father_husband_name" class="block mt-1 w-full" type="text" name="father_husband_name" :value="old('father_husband_name')" autocomplete="additional-name" />
                    <x-input-error :messages="$errors->get('father_husband_name')" class="mt-2" />
                </div>
                <div class="auth-form-field">
                    <x-input-label for="date_of_birth" :value="__('Date of birth')" />
                    <x-text-input id="date_of_birth" class="block mt-1 w-full" type="date" name="date_of_birth" :value="old('date_of_birth')" />
                    <x-input-error :messages="$errors->get('date_of_birth')" class="mt-2" />
                </div>
                <div class="auth-form-field">
                    <x-input-label for="gender" :value="__('Gender')" />
                    <select id="gender" class="block mt-1 w-full" name="gender">
                        <option value="">Select if you wish</option>
                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                        <option value="other" @selected(old('gender') === 'other')>Other</option>
                        <option value="prefer_not_to_say" @selected(old('gender') === 'prefer_not_to_say')>Prefer not to say</option>
                    </select>
                    <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                </div>
            </div>

            <div class="registration-additional-photo">
                @include('components.profile-photo-field', ['profileUser' => new \App\Models\User, 'photoInputId' => 'register-profile-photo'])
            </div>

            <div class="registration-address">
                <div class="registration-section-heading">
                    <div>
                        <h2>Delivery address <span class="optional-note">Optional</span></h2>
                        <p>You can also save an address later in your account.</p>
                    </div>
                </div>
                <div class="registration-address-grid">
                    <div>
                        <x-input-label for="address_line" :value="__('Street address')" />
                        <x-text-input id="address_line" class="block mt-1 w-full" name="address_line" :value="old('address_line')" autocomplete="street-address" />
                        <x-input-error :messages="$errors->get('address_line')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="address_phone" :value="__('Address phone (optional)')" />
                        <x-text-input id="address_phone" class="block mt-1 w-full" type="tel" name="address_phone" :value="old('address_phone', old('phone'))" autocomplete="tel" />
                        <x-input-error :messages="$errors->get('address_phone')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="city" :value="__('City')" />
                        <x-text-input id="city" class="block mt-1 w-full" name="city" :value="old('city')" autocomplete="address-level2" />
                        <x-input-error :messages="$errors->get('city')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="state" :value="__('State')" />
                        <x-text-input id="state" class="block mt-1 w-full" name="state" :value="old('state')" autocomplete="address-level1" />
                        <x-input-error :messages="$errors->get('state')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="postal_code" :value="__('Postal code')" />
                        <x-text-input id="postal_code" class="block mt-1 w-full" name="postal_code" :value="old('postal_code')" autocomplete="postal-code" />
                        <x-input-error :messages="$errors->get('postal_code')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="landmark" :value="__('Landmark (optional)')" />
                        <x-text-input id="landmark" class="block mt-1 w-full" name="landmark" :value="old('landmark')" />
                        <x-input-error :messages="$errors->get('landmark')" class="mt-2" />
                    </div>
                </div>
            </div>
        </section>
        </div>

        <div class="auth-form-footer">
            <a href="{{ route('login') }}">Already have an account? Sign in</a>
            <x-primary-button>{{ __('Create account') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
