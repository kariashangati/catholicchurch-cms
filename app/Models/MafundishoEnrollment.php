<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MafundishoEnrollment extends Model
{
    use HasFactory;

    public const TYPE_KOMUNIO = 'komunio';
    public const TYPE_KIPAIMARA = 'kipaimara';
    public const TYPE_NDOA = 'ndoa';

    /**
     * Old status kept for backward compatibility.
     */
    public const STATUS_ACTIVE = 'active';

    /**
     * New status values.
     */
    public const STATUS_CONTINUING = 'continuing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REPEATED = 'repeated';
    public const STATUS_WITHDRAWN = 'withdrawn';

    protected $fillable = [
        'member_id',
        'teaching_type_id',
        'type',
        'year',
        'status',
        'started_at',
        'ended_at',
        'partner_name',
        'partner_jumuiya',
        'partner_phone',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'ended_at' => 'date',
            'year' => 'integer',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function teachingType()
    {
        return $this->belongsTo(TeachingType::class);
    }

    public static function availableTypes(): array
    {
        return TeachingType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('slug')
            ->values()
            ->all();
    }

    public static function legacyTypes(): array
    {
        return [
            self::TYPE_KOMUNIO,
            self::TYPE_KIPAIMARA,
            self::TYPE_NDOA,
        ];
    }

    public static function availableStatuses(): array
    {
        return [
            self::STATUS_CONTINUING,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_REPEATED,
            self::STATUS_WITHDRAWN,
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_CONTINUING => db_trans('mafundisho_status_continuing'),
            self::STATUS_COMPLETED => db_trans('mafundisho_status_completed'),
            self::STATUS_FAILED => db_trans('mafundisho_status_failed'),
            self::STATUS_REPEATED => db_trans('mafundisho_status_repeated'),
            self::STATUS_WITHDRAWN => db_trans('mafundisho_status_withdrawn'),
        ];
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isContinuing(): bool
    {
        return in_array($this->status, [
            self::STATUS_CONTINUING,
            self::STATUS_ACTIVE,
        ], true);
    }

    public function isNdoa(): bool
    {
        return optional($this->teachingType)->sacrament_key === TeachingType::SACRAMENT_MARRIAGE
            || $this->type === self::TYPE_NDOA;
    }

    public function getTeachingTypeLabelAttribute(): string
    {
        return optional($this->teachingType)->name ?: ucfirst((string) $this->type);
    }
}