<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class LoginHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'email',
        'status',
        'is_suspicious',
        'risk_level',
        'suspicion_reasons',
        'ip_address',
        'country',
        'city',
        'browser',
        'platform',
        'device_type',
        'device_name',
        'user_agent',
        'session_id',
        'logged_in_at',
        'logged_out_at',
        'last_seen_at',
        'failure_reason',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'is_suspicious' => 'boolean',
            'suspicion_reasons' => 'array',
            'meta' => 'array',
            'logged_in_at' => 'datetime',
            'logged_out_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $innerQuery) use ($term) {
            $innerQuery
                ->where('user_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('ip_address', 'like', "%{$term}%")
                ->orWhere('country', 'like', "%{$term}%")
                ->orWhere('city', 'like', "%{$term}%")
                ->orWhere('browser', 'like', "%{$term}%")
                ->orWhere('platform', 'like', "%{$term}%")
                ->orWhere('device_name', 'like', "%{$term}%")
                ->orWhere('status', 'like', "%{$term}%");
        });
    }

    public function scopeForUser(Builder $query, ?int $userId): Builder
    {
        if (blank($userId)) {
            return $query;
        }

        return $query->where('user_id', $userId);
    }

    public function scopeForStatus(Builder $query, ?string $status): Builder
    {
        if (blank($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeForRisk(Builder $query, ?string $riskLevel): Builder
    {
        if (blank($riskLevel)) {
            return $query;
        }

        return $query->where('risk_level', $riskLevel);
    }

    public function scopeSuspiciousOnly(Builder $query, bool $only = false): Builder
    {
        if (! $only) {
            return $query;
        }

        return $query->where('is_suspicious', true);
    }

    public function getDisplayUserAttribute(): string
    {
        if (! empty($this->user_name)) {
            return $this->user_name;
        }

        return $this->user?->name ?: 'Unknown User';
    }

    public function getDisplayEmailAttribute(): string
    {
        if (! empty($this->email)) {
            return $this->email;
        }

        return $this->user?->email ?: '-';
    }

    public function getDisplayDeviceAttribute(): string
    {
        return trim(collect([
            $this->device_name,
            $this->browser,
            $this->platform,
        ])->filter()->implode(' • ')) ?: 'Unknown device';
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'success' => 'success',
            'failed' => 'danger',
            'logged_out' => 'secondary',
            'locked_out' => 'warning',
            default => 'dark',
        };
    }
}