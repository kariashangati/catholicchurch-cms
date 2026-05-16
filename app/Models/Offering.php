<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offering extends Model
{
    use HasFactory, SoftDeletes;

    public const SCOPE_PARISH = 'parish';
    public const SCOPE_KANDA = 'kanda';
    public const SCOPE_JUMUIYA = 'jumuiya';

    public const STATUS_PENDING = 'inasubiri';
    public const STATUS_APPROVED = 'imeidhinishwa';
    public const STATUS_REJECTED = 'imekataliwa';

    public const PAYMENT_CASH = 'taslimu';
    public const PAYMENT_BANK = 'benki';
    public const PAYMENT_MOBILE_MONEY = 'simu';
    public const PAYMENT_OTHER = 'nyingine';

    protected $fillable = [
        'offering_type_id',
        'mass_type_id',
        'collection_scope',
        'centre_detail_id',
        'kanda_id',
        'jumuiya_id',
        'collection_date',
        'amount',
        'payment_method',
        'reference_no',
        'receipt_no',
        'status',
        'description',
        'notes',
        'recorded_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'collection_date' => 'date',
            'amount' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public static function availableScopes(): array
    {
        return [self::SCOPE_PARISH, self::SCOPE_KANDA, self::SCOPE_JUMUIYA];
    }

    public static function availableStatuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED];
    }

    public static function availablePaymentMethods(): array
    {
        return [self::PAYMENT_CASH, self::PAYMENT_BANK, self::PAYMENT_MOBILE_MONEY, self::PAYMENT_OTHER];
    }

    public static function normalizeStatus(?string $status): string
    {
        return match ($status) {
            'pending' => self::STATUS_PENDING,
            'approved' => self::STATUS_APPROVED,
            'rejected' => self::STATUS_REJECTED,
            self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED => $status,
            default => self::STATUS_PENDING,
        };
    }

    public static function normalizePaymentMethod(?string $method): string
    {
        return match ($method) {
            'cash' => self::PAYMENT_CASH,
            'bank' => self::PAYMENT_BANK,
            'mobile_money' => self::PAYMENT_MOBILE_MONEY,
            'other' => self::PAYMENT_OTHER,
            self::PAYMENT_CASH, self::PAYMENT_BANK, self::PAYMENT_MOBILE_MONEY, self::PAYMENT_OTHER => $method,
            default => self::PAYMENT_CASH,
        };
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = self::normalizeStatus($value);
    }

    public function setPaymentMethodAttribute($value): void
    {
        $this->attributes['payment_method'] = self::normalizePaymentMethod($value);
    }

    public function offeringType()
    {
        return $this->belongsTo(OfferingType::class);
    }

    public function massType()
    {
        return $this->belongsTo(MassType::class);
    }

    public function centreDetail()
    {
        return $this->belongsTo(CentreDetail::class);
    }

    public function kanda()
    {
        return $this->belongsTo(Kanda::class);
    }

    public function jumuiya()
    {
        return $this->belongsTo(Jumuiya::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getScopeLabelAttribute(): string
    {
        return match ($this->collection_scope) {
            self::SCOPE_PARISH => db_trans('parish'),
            self::SCOPE_KANDA => db_trans('kanda'),
            self::SCOPE_JUMUIYA => db_trans('jumuiya'),
            default => $this->collection_scope ? (db_trans($this->collection_scope) ?: ucfirst((string) $this->collection_scope)) : '—',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return db_trans($this->status ?: self::STATUS_PENDING);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return db_trans($this->payment_method ?: self::PAYMENT_CASH);
    }

    public function getLocationNameAttribute(): string
    {
        return match ($this->collection_scope) {
            self::SCOPE_PARISH => db_trans('parish'),
            self::SCOPE_KANDA => $this->kanda?->name ?? '—',
            self::SCOPE_JUMUIYA => $this->jumuiya?->name ?? '—',
            default => '—',
        };
    }
}
