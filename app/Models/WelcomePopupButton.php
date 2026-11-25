<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WelcomePopupButton extends Model
{
    protected $fillable = ['text', 'url', 'target', 'sort_order'];

    public function popup(): BelongsTo
    {
        return $this->belongsTo(WelcomePopup::class, 'welcome_popup_id');
    }
}
