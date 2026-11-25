<?php

namespace App\Models;

use App\Models\Concerns\HasStoreImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WelcomePopupImage extends Model
{
    use HasStoreImage;

    protected $fillable = ['path', 'sort_order'];

    public function getImageAttribute(): ?string
    {
        return $this->path;
    }

    public function popup(): BelongsTo
    {
        return $this->belongsTo(WelcomePopup::class, 'welcome_popup_id');
    }
}
