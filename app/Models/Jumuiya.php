<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jumuiya extends Model
{
    use HasFactory;

    protected $fillable = [
        'kanda_id',
        'name',
        'slug',
        'comment',
        'image',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function kanda()
    {
        return $this->belongsTo(Kanda::class);
    }

    public function familias()
    {
        return $this->hasMany(Familia::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}