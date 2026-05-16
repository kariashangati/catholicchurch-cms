<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetExpenseEstimate extends Model
{
    use HasFactory;

    protected $table = 'budget_expense_estimates';

    public const GROUP_ORDINARY = 'kawaida';
    public const GROUP_DEVELOPMENT = 'maendeleo';

    protected $fillable = [
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

    public static function groupOptions(): array
    {
        return [
            self::GROUP_ORDINARY,
            self::GROUP_DEVELOPMENT,
        ];
    }

    public static function normalizeGroup(?string $group): string
    {
        return match (strtolower(trim((string) $group))) {
            'ordinary', 'normal', 'kawaida' => self::GROUP_ORDINARY,
            'development', 'maendeleo' => self::GROUP_DEVELOPMENT,
            default => self::GROUP_ORDINARY,
        };
    }

    public function setCategoryGroupAttribute($value): void
    {
        $this->attributes['category_group'] = self::normalizeGroup($value);
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
