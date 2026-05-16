<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgeGroup extends Model
{
    use HasFactory;

    public const GENDER_SCOPE_ALL = 'zote';
    public const GENDER_SCOPE_MALE = 'mwanaume';
    public const GENDER_SCOPE_FEMALE = 'mwanamke';

    protected $fillable = [
        'name',
        'min_age',
        'max_age',
        'gender_scope',
        'description',
        'is_active',
    ];

    protected $casts = [
        'min_age' => 'integer',
        'max_age' => 'integer',
        'is_active' => 'boolean',
    ];

    public static function genderScopes(): array
    {
        return [
            self::GENDER_SCOPE_ALL => function_exists('db_trans') ? db_trans('all') : 'Zote',
            self::GENDER_SCOPE_MALE => function_exists('db_trans') ? db_trans('male') : 'Mwanaume',
            self::GENDER_SCOPE_FEMALE => function_exists('db_trans') ? db_trans('female') : 'Mwanamke',
        ];
    }

    public static function normalizeGenderScope(?string $scope): string
    {
        return match ((string) $scope) {
            'all', self::GENDER_SCOPE_ALL => self::GENDER_SCOPE_ALL,
            'male', self::GENDER_SCOPE_MALE => self::GENDER_SCOPE_MALE,
            'female', self::GENDER_SCOPE_FEMALE => self::GENDER_SCOPE_FEMALE,
            default => self::GENDER_SCOPE_ALL,
        };
    }

    public function setGenderScopeAttribute($value): void
    {
        $this->attributes['gender_scope'] = self::normalizeGenderScope($value);
    }

    public function getGenderScopeLabelAttribute(): string
    {
        return self::genderScopes()[$this->gender_scope] ?? ucfirst((string) $this->gender_scope);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
