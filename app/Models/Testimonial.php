<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['name', 'location', 'quote', 'rating', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
