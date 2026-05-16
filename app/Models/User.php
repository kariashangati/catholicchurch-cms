<?php

namespace App\Models;

use App\Support\AdminScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'member_id',
        'kanda_id',
        'jumuiya_id',
        'phone',
        'is_active',
        'last_login_at',
        'admin_theme',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function kanda()
    {
        return $this->belongsTo(Kanda::class);
    }

    public function jumuiya()
    {
        return $this->belongsTo(Jumuiya::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function createdAccessNotificationTemplates(): HasMany
    {
        return $this->hasMany(AccessNotificationTemplate::class, 'created_by');
    }

    public function updatedAccessNotificationTemplates(): HasMany
    {
        return $this->hasMany(AccessNotificationTemplate::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function isGlobalScopeUser(): bool
    {
        return blank($this->kanda_id) && blank($this->jumuiya_id);
    }

    public function isKandaScopeUser(): bool
    {
        return filled($this->kanda_id) && blank($this->jumuiya_id);
    }

    public function isJumuiyaScopeUser(): bool
    {
        return filled($this->jumuiya_id);
    }

    public function getScopeTypeAttribute(): string
    {
        if ($this->isJumuiyaScopeUser()) {
            return AdminScope::TYPE_JUMUIYA;
        }

        if ($this->isKandaScopeUser()) {
            return AdminScope::TYPE_KANDA;
        }

        return AdminScope::TYPE_GLOBAL;
    }

    public function getDisplayScopeAttribute(): string
    {
        if ($this->jumuiya?->name) {
            return collect([
                $this->kanda?->name,
                $this->jumuiya->name,
            ])->filter()->implode(' • ');
        }

        if ($this->kanda?->name) {
            return $this->kanda->name;
        }

        return 'Global';
    }
}