<?php

namespace App\Models;

use App\Models\Concerns\HasStoreImage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasStoreImage;

    protected $fillable = ['name', 'slug', 'description', 'image', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('ssg.store-categories'));
        static::deleted(fn () => Cache::forget('ssg.store-categories'));
    }
}
