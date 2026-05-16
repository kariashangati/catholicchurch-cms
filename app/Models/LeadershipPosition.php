<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadershipPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'level_type', 'committee_type', 'auto_role_name', 'display_order', 'is_system', 'is_active', 'description',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LeadershipAssignment::class);
    }
}
