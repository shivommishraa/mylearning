<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\CustomerAddressManager;
use App\Support\ProfilePhotoManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'address' => app(CustomerAddressManager::class)->primaryAddress($user),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(
        ProfileUpdateRequest $request,
        ProfilePhotoManager $photos,
        CustomerAddressManager $addresses,
    ): RedirectResponse {
        $data = $request->validated();
        $user = $request->user();
        $previousPhoto = $user->profile_photo_path;
        $newPhoto = $photos->save(
            $data['profile_photo'] ?? null,
            $previousPhoto,
            $request->boolean('remove_profile_photo'),
        );

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'profile_photo_path' => $newPhoto,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        $photos->deleteReplaced($previousPhoto, $newPhoto);
        $addresses->syncProfileAddress($user, $data);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request, ProfilePhotoManager $photos): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        $profilePhoto = $user->profile_photo_path;

        Auth::logout();

        $user->delete();
        $photos->deleteReplaced($profilePhoto, null);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
