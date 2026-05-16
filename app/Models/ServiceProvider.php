<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceProvider extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'hai';
    public const STATUS_INACTIVE = 'haifanyi_kazi';
    public const STATUS_BLACKLISTED = 'imezuiwa';

    protected $fillable = [
        'service_category_id',
        'member_id',
        'name',
        'phone',
        'email',
        'address',
        'notes',
        'status',
        'is_internal',
        'is_active',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'is_active' => 'boolean',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_BLACKLISTED,
        ];
    }

    public static function normalizeStatus(?string $status): string
    {
        return match ($status) {
            'active' => self::STATUS_ACTIVE,
            'inactive' => self::STATUS_INACTIVE,
            'blacklisted' => self::STATUS_BLACKLISTED,
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
            self::STATUS_BLACKLISTED => $status,
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
