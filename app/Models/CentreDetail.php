<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CentreDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'centre_name',
        'address',
        'email',
        'region',
        'country',
        'diocese',
        'telephone_1',
        'telephone_2',
        'telephone_3',
        'photo',
        'unique_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}