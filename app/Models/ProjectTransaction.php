<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectTransaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const TYPE_INCOME = 'mapato';
    public const TYPE_EXPENSE = 'matumizi';

    public const STATUS_PENDING = 'inasubiri';
    public const STATUS_APPROVED = 'imeidhinishwa';
    public const STATUS_REJECTED = 'imekataliwa';

    public const PAYMENT_CASH = 'taslimu';
    public const PAYMENT_BANK = 'benki';
    public const PAYMENT_MOBILE = 'simu';
    public const PAYMENT_CHEQUE = 'hundi';

    protected $fillable = [
        'project_id',
        'transaction_type',
        'amount',
        'transaction_date',
        'payment_method',
        'reference_no',
        'receipt_no',
        'description',
        'status',
        'recorded_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public static function availableTypes(): array
    {
        return [
            self::TYPE_INCOME,
            self::TYPE_EXPENSE,
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
            self::PAYMENT_CHEQUE,
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return db_trans($this->transaction_type ?: self::TYPE_INCOME);
    }

    public function getStatusLabelAttribute(): string
    {
        return db_trans($this->status ?: self::STATUS_PENDING);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return $this->payment_method ? db_trans($this->payment_method) : '—';
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
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