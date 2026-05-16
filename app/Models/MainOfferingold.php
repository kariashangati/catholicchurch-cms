<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MainOffering extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'offering_date',
        'mass_name',
        'status',
        'recorded_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'offering_date' => 'date',
        ];
    }
}