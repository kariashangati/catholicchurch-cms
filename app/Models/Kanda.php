<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kanda extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
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

    public function jumuiyas()
    {
        return $this->hasMany(Jumuiya::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}