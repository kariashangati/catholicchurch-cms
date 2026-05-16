<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContributionTypePlan extends Model
{
    use HasFactory;

    protected $table = 'contribution_type_plans';

    protected $fillable = [
        'contribution_type_id',
        'target_amount',
        'installments_count',
        'due_date',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function contributionType(): BelongsTo
    {
        return $this->belongsTo(ContributionType::class, 'contribution_type_id');
    }
}
