<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContributionBatch extends Model
{
    use HasFactory;

    public const SOURCE_CASH = 'cash';
    public const SOURCE_BANK = 'bank';

    protected $fillable = [
        'kanda_id',
        'jumuiya_id',
        'contribution_type_id',
        'source_type',
        'bank_account_id',
        'contribution_date',
        'period_year',
        'period_month',
        'total_amount',
        'rows_count',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'contribution_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public static function availableSources(): array
    {
        return [self::SOURCE_CASH, self::SOURCE_BANK];
    }

    public function kanda(): BelongsTo
    {
        return $this->belongsTo(Kanda::class);
    }

    public function jumuiya(): BelongsTo
    {
        return $this->belongsTo(Jumuiya::class);
    }

    public function contributionType(): BelongsTo
    {
        return $this->belongsTo(ContributionType::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function denominations(): HasMany
    {
        return $this->hasMany(ContributionBatchDenomination::class);
    }

    public function cashContributions(): HasMany
    {
        return $this->hasMany(CashContribution::class, 'contribution_batch_id');
    }

    public function bankContributions(): HasMany
    {
        return $this->hasMany(BankContribution::class, 'contribution_batch_id');
    }
}
