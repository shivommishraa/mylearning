<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasStoreImage
{
    public function getImageUrlAttribute(): ?string
    {
        if (blank($this->image)) {
            return null;
        }

        if (Str::startsWith($this->image, ['http://', 'https://', '/'])) {
            return $this->image;
        }

        return asset('media/'.$this->image);
    }
}
