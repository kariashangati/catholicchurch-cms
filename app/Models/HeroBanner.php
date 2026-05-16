<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'subtitle', 'description', 'background_type', 'background_value',
        'poster_image', 'primary_button_text', 'primary_button_link', 'secondary_button_text',
        'secondary_button_link', 'is_active', 'display_order', 'starts_at', 'ends_at', 'overlay_opacity',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'overlay_opacity' => 'float',
        ];
    }
}
