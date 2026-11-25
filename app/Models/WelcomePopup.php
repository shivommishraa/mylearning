<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WelcomePopup extends Model
{
    public const DISPLAY_MODES = [
        'image_only',
        'image_content',
        'content_buttons',
        'image_content_buttons',
    ];

    protected $fillable = [
        'is_enabled',
        'display_mode',
        'title',
        'description',
        'display_frequency',
        'display_delay',
        'image_position',
        'content_alignment',
        'button_style',
        'accent_color',
        'show_close_button',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'show_close_button' => 'boolean',
            'display_delay' => 'integer',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(WelcomePopupImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function buttons(): HasMany
    {
        return $this->hasMany(WelcomePopupButton::class)->orderBy('sort_order')->orderBy('id');
    }

    public function displaysImages(): bool
    {
        return in_array($this->display_mode, ['image_only', 'image_content', 'image_content_buttons'], true);
    }

    public function displaysContent(): bool
    {
        return in_array($this->display_mode, ['image_content', 'content_buttons', 'image_content_buttons'], true);
    }

    public function displaysButtons(): bool
    {
        return in_array($this->display_mode, ['content_buttons', 'image_content_buttons'], true);
    }
}
