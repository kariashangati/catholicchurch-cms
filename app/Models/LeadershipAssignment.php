<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadershipAssignment extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'hai';
    public const STATUS_ENDED = 'imemalizika';
    public const STATUS_REVOKED = 'imefutwa';
    public const STATUS_SUSPENDED = 'imesitishwa';

    protected $fillable = [
        'member_id',
        'user_id',
        'leadership_position_id',
        'kanda_id',
        'jumuiya_id',
        'apostolic_group_id',
        'scope_type',
        'scope_label',
        'started_at',
        'ended_at',
        'status',
        'notes',
        'appointed_by',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
        ];
    }

    public static function availableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_ENDED,
            self::STATUS_REVOKED,
            self::STATUS_SUSPENDED,
        ];
    }

    public static function legacyStatuses(): array
    {
        return [
            'active',
            'ended',
            'revoked',
            'suspended',
        ];
    }

    public static function normalizeStatus(?string $status): string
    {
        return match ($status) {
            'active', self::STATUS_ACTIVE => self::STATUS_ACTIVE,
            'ended', self::STATUS_ENDED => self::STATUS_ENDED,
            'revoked', self::STATUS_REVOKED => self::STATUS_REVOKED,
            'suspended', self::STATUS_SUSPENDED => self::STATUS_SUSPENDED,
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
        return match ($this->status) {
            self::STATUS_ACTIVE, 'active' => 'success',
            self::STATUS_SUSPENDED, 'suspended' => 'warning',
            self::STATUS_REVOKED, 'revoked' => 'danger',
            default => 'secondary',
        };
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(LeadershipPosition::class, 'leadership_position_id');
    }

    public function kanda(): BelongsTo
    {
        return $this->belongsTo(Kanda::class);
    }

    public function jumuiya(): BelongsTo
    {
        return $this->belongsTo(Jumuiya::class);
    }

    public function apostolicGroup(): BelongsTo
    {
        return $this->belongsTo(ApostolicGroup::class);
    }

    public function appointer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appointed_by');
    }
}
