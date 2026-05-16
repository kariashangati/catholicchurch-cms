<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashContribution extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'cash_contributions';

    public const STATUS_PENDING = 'inasubiri';
    public const STATUS_APPROVED = 'imeidhinishwa';
    public const STATUS_REJECTED = 'imekataliwa';

    protected $fillable = [
        'member_id',
        'familia_id',
        'jumuiya_id',
        'kanda_id',
        'contribution_type_id',
        'contribution_batch_id',
        'amount',
        'contribution_date',
        'notes',
        'status',
        'recorded_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'contribution_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public static function availableStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
        ];
    }

    public static function normalizeStatus(?string $status): string
    {
        return match ($status) {
            'pending' => self::STATUS_PENDING,
            'approved' => self::STATUS_APPROVED,
            'rejected' => self::STATUS_REJECTED,
            self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED => $status,
            default => self::STATUS_APPROVED,
        };
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = self::normalizeStatus($value);
    }

    public function getStatusLabelAttribute(): string
    {
        return db_trans($this->status ?: self::STATUS_PENDING);
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED, 'approved' => 'ui-status-approved',
            self::STATUS_REJECTED, 'rejected' => 'ui-status-rejected',
            default => 'ui-status-pending',
        };
    }

    public function contributionType(): BelongsTo
    {
        return $this->belongsTo(ContributionType::class, 'contribution_type_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ContributionBatch::class, 'contribution_batch_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}