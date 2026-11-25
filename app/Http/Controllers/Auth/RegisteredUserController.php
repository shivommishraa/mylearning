<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\CustomerAddressManager;
use App\Support\ProfilePhotoManager;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request, ProfilePhotoManager $photos, CustomerAddressManager $addresses): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:20'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ] + $addresses->validationRules());

        $photoPath = $photos->save($data['profile_photo'] ?? null, null);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'profile_photo_path' => $photoPath,
            'password' => Hash::make($data['password']),
        ]);
        $addresses->syncProfileAddress($user, $data);

        $requiresEmailVerification = SiteSetting::getValue('require_email_verification', '0') === '1';

        if ($requiresEmailVerification) {
            event(new Registered($user));
        }

        Auth::login($user);

        if ($requiresEmailVerification) {
            return redirect()->route('verification.notice');
        }

        return redirect(route('dashboard', absolute: false));
    }
}
