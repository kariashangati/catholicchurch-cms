<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
	   use HasFactory;
    protected $table = 'site_settings';

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'group_name',
        'is_public',
        'is_editable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_editable' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopeForGroup(Builder $query, ?string $group): Builder
    {
        if (blank($group)) {
            return $query;
        }

        return $query->where('group_name', $group);
    }

    public function scopeEditable(Builder $query): Builder
    {
        return $query->where('is_editable', true);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderBy('sort_order')
            ->orderBy('setting_key');
    }

    public function getTypedValueAttribute(): mixed
    {
        return match ($this->setting_type) {
            'boolean' => filter_var($this->setting_value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->setting_value,
            'float', 'decimal' => (float) $this->setting_value,
            'json' => json_decode((string) $this->setting_value, true),
            default => $this->setting_value,
        };
    }
}