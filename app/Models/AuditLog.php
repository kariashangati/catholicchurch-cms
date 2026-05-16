<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'log_name',
        'event',
        'module',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'description',
        'old_values',
        'new_values',
        'properties',
        'ip_address',
        'user_agent',
        'browser',
        'platform',
        'device_type',
        'method',
        'route_name',
        'url',
        'risk_level',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'properties' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->user();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForModule(Builder $query, ?string $module): Builder
    {
        if (blank($module)) {
            return $query;
        }

        return $query->where('module', $module);
    }

    public function scopeForEvent(Builder $query, ?string $event): Builder
    {
        if (blank($event)) {
            return $query;
        }

        return $query->where('event', $event);
    }

    public function scopeForActor(Builder $query, ?int $userId): Builder
    {
        if (blank($userId)) {
            return $query;
        }

        return $query->where('user_id', $userId);
    }

    public function scopeForRisk(Builder $query, ?string $riskLevel): Builder
    {
        if (blank($riskLevel)) {
            return $query;
        }

        return $query->where('risk_level', $riskLevel);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $innerQuery) use ($term) {
            $innerQuery
                ->where('description', 'like', "%{$term}%")
                ->orWhere('event', 'like', "%{$term}%")
                ->orWhere('action', 'like', "%{$term}%")
                ->orWhere('module', 'like', "%{$term}%")
                ->orWhere('subject_label', 'like', "%{$term}%")
                ->orWhere('ip_address', 'like', "%{$term}%")
                ->orWhere('route_name', 'like', "%{$term}%");
        });
    }

    public function getActorNameAttribute(): string
    {
        return $this->user?->name ?: 'System';
    }

    public function getDisplayActionAttribute(): string
    {
        return $this->action ?: ucfirst((string) $this->event);
    }

    public function getDisplaySubjectAttribute(): string
    {
        return $this->subject_label ?: 'N/A';
    }
}