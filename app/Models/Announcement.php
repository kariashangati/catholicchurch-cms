<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'summary', 'content', 'image', 'publish_from', 'publish_until',
        'is_published', 'is_featured', 'show_on_homepage', 'display_order', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'publish_from' => 'datetime',
            'publish_until' => 'datetime',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'show_on_homepage' => 'boolean',
        ];
    }
}
