<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
        @csrf

        <div class="mt-4">
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone" :value="__('Phone (optional)')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="mt-5">
            @include('components.profile-photo-field', ['profileUser' => new \App\Models\User, 'photoInputId' => 'register-profile-photo'])
        </div>

        <section class="registration-address">
            <div class="registration-section-heading">
                <div>
                    <h2>Delivery address <span class="optional-note">Optional</span></h2>
                    <p>You can add this now or save an address later in your account.</p>
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
        </section>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>
            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
