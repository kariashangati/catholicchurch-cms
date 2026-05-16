<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TeachingType extends Model
{
    use HasFactory;

    public const SACRAMENT_COMMUNION = 'communion';
    public const SACRAMENT_CONFIRMATION = 'confirmation';
    public const SACRAMENT_MARRIAGE = 'marriage';

    public const ELIGIBILITY_ALL = 'all';
    public const ELIGIBILITY_COMMUNION = 'communion';
    public const ELIGIBILITY_CONFIRMATION = 'confirmation';
    public const ELIGIBILITY_MARRIAGE = 'marriage';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'sacrament_key',
        'eligibility_rule',
        'requires_partner_info',
        'is_system',
        'is_active',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'requires_partner_info' => 'boolean',
        'is_system' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (TeachingType $type): void {
            if (blank($type->slug) && filled($type->name)) {
                $type->slug = Str::slug($type->name);
            }
        });
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(MafundishoEnrollment::class, 'teaching_type_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public static function sacramentOptions(): array
    {
        return [
            self::SACRAMENT_COMMUNION => db_trans('mafundisho_sacrament_communion'),
            self::SACRAMENT_CONFIRMATION => db_trans('mafundisho_sacrament_confirmation'),
            self::SACRAMENT_MARRIAGE => db_trans('mafundisho_sacrament_marriage'),
        ];
    }

    public static function eligibilityOptions(): array
    {
        return [
            self::ELIGIBILITY_ALL => db_trans('mafundisho_eligibility_all'),
            self::ELIGIBILITY_COMMUNION => db_trans('mafundisho_eligibility_communion'),
            self::ELIGIBILITY_CONFIRMATION => db_trans('mafundisho_eligibility_confirmation'),
            self::ELIGIBILITY_MARRIAGE => db_trans('mafundisho_eligibility_marriage'),
        ];
    }

    public function requiresPartnerInfo(): bool
    {
        return $this->requires_partner_info || $this->sacrament_key === self::SACRAMENT_MARRIAGE;
    }
}
