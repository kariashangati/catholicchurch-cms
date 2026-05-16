<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tithe extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = 'inasubiri';
    public const STATUS_APPROVED = 'imeidhinishwa';
    public const STATUS_REJECTED = 'imekataliwa';

    public const PAYMENT_CASH = 'taslimu';
    public const PAYMENT_BANK = 'benki';
    public const PAYMENT_MOBILE = 'simu';
    public const PAYMENT_OTHER = 'nyingine';

    protected $fillable = [
        'member_id',
        'jumuiya_id',
        'amount',
        'contribution_date',
        'payment_method',
        'reference_no',
        'receipt_no',
        'status',
        'recorded_by',
        'approved_by',
        'approved_at',
        'notes',
        'override_reason',
        'override_approved_by',
        'override_approved_at',
        'tithe_batch_id',
        'tithe_year',
        'tithe_month',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'contribution_date' => 'date',
            'approved_at' => 'datetime',
            'override_approved_at' => 'datetime',
            'tithe_year' => 'integer',
            'tithe_month' => 'integer',
        ];
    }

    public static function availableStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
        ];
    }

    public static function availablePaymentMethods(): array
    {
        return [
            self::PAYMENT_CASH,
            self::PAYMENT_BANK,
            self::PAYMENT_MOBILE,
            self::PAYMENT_OTHER,
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function jumuiya(): BelongsTo
    {
        return $this->belongsTo(Jumuiya::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function overrideApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'override_approved_by');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(TitheBatch::class, 'tithe_batch_id');
    }

    public function getFamilyNameAttribute(): ?string
    {
        return $this->member?->familia?->name;
    }

    public function getKandaNameAttribute(): ?string
    {
        return $this->jumuiya?->kanda?->name;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status ? db_trans($this->status) : db_trans(self::STATUS_PENDING);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return $this->payment_method ? db_trans($this->payment_method) : '—';
    }

    public function getIsBulkAttribute(): bool
    {
        return ! empty($this->tithe_batch_id);
    }
}