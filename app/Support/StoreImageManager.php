<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreImageManager
{
    public function save(?UploadedFile $upload, ?string $url, ?string $current, string $directory, bool $remove = false): ?string
    {
        if ($upload) {
            $path = $upload->store($directory, 'public');

            if ($path === false) {
                throw ValidationException::withMessages([
                    'image_file' => 'The image could not be saved. Check public storage permissions and try again.',
                ]);
            }

            return $path;
        }

        if ($remove) {
            return null;
        }

        if (filled($url)) {
            return $url;
        }

        return $current;
    }

    public function deleteReplaced(?string $previous, ?string $current): void
    {
        if ($previous !== $current && $this->isManagedPath($previous)) {
            Storage::disk('public')->delete($previous);
        }
    }

    public function isManagedPath(?string $image): bool
    {
        return filled($image)
            && ! Str::startsWith($image, ['http://', 'https://', '/'])
            && Storage::disk('public')->exists($image);
    }
}
