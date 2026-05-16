<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Familia extends Model
{
    use HasFactory;

    protected $fillable = [
        'jumuiya_id',
        'name',
        'phone',
        'envelope_no',
        'address',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function getMembersTotalAttribute(): int
    {
        return $this->members()->count();
    }
    public function jumuiya()
    {
        return $this->belongsTo(Jumuiya::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }
}