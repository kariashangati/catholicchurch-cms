<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'description', 'cover_image', 'is_published', 'is_featured', 'event_date', 'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'event_date' => 'date',
        ];
    }

    public function images()
    {
        return $this->hasMany(GalleryImage::class)->orderBy('display_order');
    }
}
