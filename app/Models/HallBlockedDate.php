<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallBlockedDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'hall_id',
        'blocked_date',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'blocked_date' => 'date',
    ];

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
