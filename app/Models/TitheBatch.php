<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TitheBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'kanda_id',
        'jumuiya_id',
        'contribution_date',
        'tithe_year',
        'tithe_month',
        'total_amount',
        'rows_count',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'contribution_date' => 'date',
        'total_amount' => 'decimal:2',
        'tithe_year' => 'integer',
        'tithe_month' => 'integer',
        'rows_count' => 'integer',
    ];

    public function jumuiya(): BelongsTo
    {
        return $this->belongsTo(Jumuiya::class);
    }

    public function kanda(): BelongsTo
    {
        return $this->belongsTo(Kanda::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function tithes(): HasMany
    {
        return $this->hasMany(Tithe::class, 'tithe_batch_id');
    }

    public function denominations(): HasMany
    {
        return $this->hasMany(TitheBatchDenomination::class, 'tithe_batch_id');
    }
}