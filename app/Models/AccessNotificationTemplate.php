<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class AccessNotificationTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'channel',
        'locale',
        'subject',
        'message',
        'is_active',
        'is_default',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForLocale(Builder $query, ?string $locale): Builder
    {
        if (blank($locale)) {
            return $query;
        }

        return $query->where('locale', $locale);
    }

    public function scopeForChannel(Builder $query, ?string $channel): Builder
    {
        if (blank($channel)) {
            return $query;
        }

        return $query->where('channel', $channel);
    }

    public function scopeForCode(Builder $query, ?string $code): Builder
    {
        if (blank($code)) {
            return $query;
        }

        return $query->where('code', $code);
    }

    public function render(array $payload = []): string
    {
        $message = $this->message;

        foreach ($payload as $key => $value) {
            $message = str_replace('{{ ' . $key . ' }}', (string) $value, $message);
            $message = str_replace('{{' . $key . '}}', (string) $value, $message);
        }

        return $message;
    }
}