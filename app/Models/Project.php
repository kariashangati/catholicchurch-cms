<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_PLANNED = 'imepangwa';
    public const STATUS_ACTIVE = 'inaendelea';
    public const STATUS_ON_HOLD = 'imesitishwa';
    public const STATUS_COMPLETED = 'imekamilika';
    public const STATUS_CANCELLED = 'imefutwa';

    protected $fillable = [
        'project_category_id',
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'budget_amount',
        'target_amount',
        'cover_image',
        'attachment',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget_amount' => 'decimal:2',
        'target_amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public static function availableStatuses(): array
    {
        return [
            self::STATUS_PLANNED,
            self::STATUS_ACTIVE,
            self::STATUS_ON_HOLD,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return db_trans($this->status ?: self::STATUS_PLANNED);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ProjectTransaction::class);
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