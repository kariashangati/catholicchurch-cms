<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferingType extends Model
{
    use HasFactory;

    public const CATEGORY_GENERAL = 'jumla';
    public const CATEGORY_MASS = 'misa';
    public const CATEGORY_SPECIAL = 'maalum';
    public const CATEGORY_PROJECT = 'mradi';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function availableCategories(): array
    {
        return [
            self::CATEGORY_GENERAL,
            self::CATEGORY_MASS,
            self::CATEGORY_SPECIAL,
            self::CATEGORY_PROJECT,
        ];
    }

    public static function normalizeCategory(?string $category): string
    {
        return match ($category) {
            'general' => self::CATEGORY_GENERAL,
            'mass' => self::CATEGORY_MASS,
            'special' => self::CATEGORY_SPECIAL,
            'project' => self::CATEGORY_PROJECT,
            self::CATEGORY_GENERAL, self::CATEGORY_MASS, self::CATEGORY_SPECIAL, self::CATEGORY_PROJECT => $category,
            default => self::CATEGORY_GENERAL,
        };
    }

    public function setCategoryAttribute($value): void
    {
        $this->attributes['category'] = self::normalizeCategory($value);
    }

    public function getCategoryLabelAttribute(): string
    {
        return db_trans($this->category ?: self::CATEGORY_GENERAL);
    }

    public function offerings()
    {
        return $this->hasMany(Offering::class);
    }
}
