<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\ProfilePhotoManager;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfilePhotoController extends Controller
{
    public function __invoke(User $user, ProfilePhotoManager $photos): StreamedResponse
    {
        $viewer = auth()->user();
        $canViewCustomerPhoto = (
            $viewer->can('customers.view') || $viewer->can('customers.edit')
        ) && ! $user->roles()->exists();

        abort_unless($viewer->is($user) || $canViewCustomerPhoto, 403);

        $path = $user->profile_photo_path;
        abort_unless($photos->isManagedPath($path), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, headers: [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
