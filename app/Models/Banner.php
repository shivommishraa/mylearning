<?php

namespace App\Models;

use App\Models\Concerns\HasStoreImage;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasStoreImage;

    protected $fillable = [
        'eyebrow', 'title', 'subtitle', 'image', 'button_text', 'button_url', 'link_target', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function hasOverlayContent(): bool
    {
        return filled($this->eyebrow)
            || filled($this->title)
            || filled($this->subtitle)
            || (filled($this->button_text) && filled($this->button_url));
    }
}
