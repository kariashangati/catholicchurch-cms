<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $table = 'bank_accounts';

    public const STATUS_ACTIVE = 'hai';
    public const STATUS_INACTIVE = 'haifanyi_kazi';
    public const STATUS_CLOSED = 'imefungwa';

    protected $fillable = [
        'bank_name',
        'account_name',
        'account_number',
        'branch_name',
        'status',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_CLOSED,
        ];
    }

    public static function normalizeStatus(?string $status): string
    {
        return match ((string) $status) {
            'active', self::STATUS_ACTIVE => self::STATUS_ACTIVE,
            'inactive', self::STATUS_INACTIVE => self::STATUS_INACTIVE,
            'closed', self::STATUS_CLOSED => self::STATUS_CLOSED,
            default => self::STATUS_ACTIVE,
        };
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = self::normalizeStatus($value);
    }

    public function getStatusLabelAttribute(): string
    {
        return db_trans($this->status ?: self::STATUS_ACTIVE);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match (self::normalizeStatus($this->status)) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_CLOSED => 'dark',
            default => 'secondary',
        };
    }

    public function bankContributions(): HasMany
    {
        return $this->hasMany(BankContribution::class, 'bank_account_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return trim("{$this->bank_name} - {$this->account_number}");
    }
}
