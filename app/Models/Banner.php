<?php

namespace App\Models;

use App\Models\Concerns\HasStoreImage;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasStoreImage;

    protected $fillable = [
        'eyebrow', 'title', 'subtitle', 'image', 'button_text', 'button_url', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
