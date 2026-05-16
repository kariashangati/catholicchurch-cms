<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetIncomeEstimate extends Model
{
    use HasFactory;

    protected $table = 'budget_income_estimates';

    public const SOURCE_TITHE = 'zaka';
    public const SOURCE_OFFERING = 'sadaka';
    public const SOURCE_CONTRIBUTION = 'michango';
    public const SOURCE_OTHER = 'nyingine';

    public const GROUP_ORDINARY = 'kawaida';
    public const GROUP_DEVELOPMENT = 'maendeleo';

    protected $fillable = [
        'source_type',
        'category_name',
        'category_group',
        'amount',
        'budget_year',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'budget_year' => 'integer',
        ];
    }

    public static function sourceOptions(): array
    {
        return [
            self::SOURCE_TITHE,
            self::SOURCE_OFFERING,
            self::SOURCE_CONTRIBUTION,
            self::SOURCE_OTHER,
        ];
    }

    public static function groupOptions(): array
    {
        return [
            self::GROUP_ORDINARY,
            self::GROUP_DEVELOPMENT,
        ];
    }

    public static function normalizeSource(?string $source): string
    {
        return match (strtolower(trim((string) $source))) {
            'tithe', 'zaka' => self::SOURCE_TITHE,
            'offering', 'sadaka' => self::SOURCE_OFFERING,
            'contribution', 'contributions', 'mchango', 'michango' => self::SOURCE_CONTRIBUTION,
            'other', 'others', 'nyingine' => self::SOURCE_OTHER,
            default => self::SOURCE_OTHER,
        };
    }

    public static function normalizeGroup(?string $group): string
    {
        return match (strtolower(trim((string) $group))) {
            'ordinary', 'normal', 'kawaida' => self::GROUP_ORDINARY,
            'development', 'maendeleo' => self::GROUP_DEVELOPMENT,
            default => self::GROUP_ORDINARY,
        };
    }

    public function setSourceTypeAttribute($value): void
    {
        $this->attributes['source_type'] = self::normalizeSource($value);
    }

    public function setCategoryGroupAttribute($value): void
    {
        $this->attributes['category_group'] = self::normalizeGroup($value);
    }

    public function getSourceLabelAttribute(): string
    {
        return db_trans($this->source_type ?: self::SOURCE_OTHER);
    }

    public function getGroupLabelAttribute(): string
    {
        return db_trans($this->category_group ?: self::GROUP_ORDINARY);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
