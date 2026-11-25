<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfilePhotoManager
{
    public function save(?UploadedFile $upload, ?string $current, bool $remove = false): ?string
    {
        if ($upload) {
            $path = $upload->store('profiles', 'local');

            if ($path === false) {
                throw ValidationException::withMessages([
                    'profile_photo' => 'The profile photo could not be saved. Check storage permissions and try again.',
                ]);
            }

            return $path;
        }

        return $remove ? null : $current;
    }

    public function deleteReplaced(?string $previous, ?string $current): void
    {
        if ($previous !== $current && $this->isManagedPath($previous)) {
            Storage::disk('local')->delete($previous);
        }
    }

    public function isManagedPath(?string $path): bool
    {
        return filled($path)
            && preg_match('#\Aprofiles/[A-Za-z0-9_-][A-Za-z0-9._-]*\.(?:jpe?g|png|webp)\z#i', $path) === 1;
    }
}
