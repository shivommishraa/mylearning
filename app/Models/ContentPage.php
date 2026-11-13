<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ContentPage extends Model
{
    protected $fillable = ['title', 'slug', 'body', 'meta_title', 'meta_description', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('ssg.published-pages'));
        static::deleted(fn () => Cache::forget('ssg.published-pages'));
    }
}
