<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributionBatchDenomination extends Model
{
    use HasFactory;

    protected $fillable = [
        'contribution_batch_id',
        'denomination_value',
        'quantity',
        'total_amount',
    ];

    protected $casts = [
        'denomination_value' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ContributionBatch::class, 'contribution_batch_id');
    }
}
