<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class NavigationItem extends Model
{
    protected $fillable = ['label', 'url', 'location', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('ssg.navigation-items'));
        static::deleted(fn () => Cache::forget('ssg.navigation-items'));
    }
}
