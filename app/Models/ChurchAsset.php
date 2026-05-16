<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChurchAsset extends Model
{
    use HasFactory;

    public const CONDITION_EXCELLENT = 'bora_sana';
    public const CONDITION_GOOD = 'nzuri';
    public const CONDITION_FAIR = 'wastani';
    public const CONDITION_DAMAGED = 'imeharibika';
    public const CONDITION_DISPOSED = 'imetolewa';

    protected $fillable = [
        'asset_category_id',
        'name',
        'asset_code',
        'registration_number',
        'acquisition_cost',
        'current_value',
        'acquisition_date',
        'condition_status',
        'location',
        'document_path',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'acquisition_cost' => 'decimal:2',
        'current_value' => 'decimal:2',
        'acquisition_date' => 'date',
        'is_active' => 'boolean',
    ];

    public static function conditionStatuses(): array
    {
        return [
            self::CONDITION_EXCELLENT,
            self::CONDITION_GOOD,
            self::CONDITION_FAIR,
            self::CONDITION_DAMAGED,
            self::CONDITION_DISPOSED,
        ];
    }

    public static function normalizeConditionStatus(?string $status): string
    {
        return match ($status) {
            'excellent' => self::CONDITION_EXCELLENT,
            'good' => self::CONDITION_GOOD,
            'fair' => self::CONDITION_FAIR,
            'damaged' => self::CONDITION_DAMAGED,
            'disposed' => self::CONDITION_DISPOSED,
            self::CONDITION_EXCELLENT,
            self::CONDITION_GOOD,
            self::CONDITION_FAIR,
            self::CONDITION_DAMAGED,
            self::CONDITION_DISPOSED => $status,
            default => self::CONDITION_GOOD,
        };
    }

    public function setConditionStatusAttribute($value): void
    {
        $this->attributes['condition_status'] = self::normalizeConditionStatus($value);
    }

    public function getConditionStatusLabelAttribute(): string
    {
        return db_trans($this->condition_status ?: self::CONDITION_GOOD);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_category_id');
    }
}
