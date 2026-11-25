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
use Illuminate\Validation\Rule;
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
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'required_without:phone', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:20', Rule::unique('users', 'phone')],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'additional_details' => ['nullable', 'boolean'],
        ];
        $hasAdditionalDetails = $request->boolean('additional_details');

        if ($hasAdditionalDetails) {
            $rules += [
                'father_husband_name' => ['nullable', 'string', 'max:120'],
                'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'],
                'gender' => ['nullable', Rule::in(['female', 'male', 'other', 'prefer_not_to_say'])],
                'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            ] + $addresses->validationRules();
        }

        $data = $request->validate($rules);

        $photoPath = $hasAdditionalDetails
            ? $photos->save($data['profile_photo'] ?? null, null)
            : null;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'father_husband_name' => $hasAdditionalDetails ? ($data['father_husband_name'] ?? null) : null,
            'date_of_birth' => $hasAdditionalDetails ? ($data['date_of_birth'] ?? null) : null,
            'gender' => $hasAdditionalDetails ? ($data['gender'] ?? null) : null,
            'profile_photo_path' => $photoPath,
            'password' => Hash::make($data['password']),
        ]);
        if ($hasAdditionalDetails) {
            $addresses->syncProfileAddress($user, $data);
        }

        $requiresEmailVerification = SiteSetting::getValue('require_email_verification', '0') === '1'
            && filled($user->email);

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
