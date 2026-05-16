<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationBalanceSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider',
        'balance_units',
        'currency',
        'raw_response',
        'fetched_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'balance_units' => 'decimal:2',
            'raw_response' => 'array',
            'fetched_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
