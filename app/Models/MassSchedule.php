<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MassSchedule extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'rasimu';
    public const STATUS_PUBLISHED = 'imechapishwa';
    public const STATUS_CANCELLED = 'imefutwa';

    protected $fillable = [
        'title',
        'mass_type_id',
        'scheduled_at',
        'location',
        'description',
        'status',
        'special_occasion',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'special_occasion' => 'boolean',
        ];
    }

    public static function availableStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PUBLISHED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function normalizeStatus(?string $status): string
    {
        return match ($status) {
            'draft' => self::STATUS_DRAFT,
            'scheduled', 'published' => self::STATUS_PUBLISHED,
            'cancelled', 'canceled' => self::STATUS_CANCELLED,
            self::STATUS_DRAFT, self::STATUS_PUBLISHED, self::STATUS_CANCELLED => $status,
            default => self::STATUS_DRAFT,
        };
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = self::normalizeStatus($value);
    }

    public function getStatusLabelAttribute(): string
    {
        return db_trans($this->status ?: self::STATUS_DRAFT);
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED, 'published', 'scheduled' => 'success',
            self::STATUS_CANCELLED, 'cancelled', 'canceled' => 'danger',
            default => 'secondary',
        };
    }

    public function massType()
    {
        return $this->belongsTo(MassType::class);
    }

    public function assignments()
    {
        return $this->hasMany(MassScheduleAssignment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
