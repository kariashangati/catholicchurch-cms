<?php

namespace App\Services\SystemConfig;

use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SiteSettingsService
{
    public function allGrouped(): Collection
    {
        return SiteSetting::query()
            ->ordered()
            ->get()
            ->groupBy('group_name');
    }

    public function group(string $groupName): Collection
    {
        return SiteSetting::query()
            ->forGroup($groupName)
            ->ordered()
            ->get();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $setting = SiteSetting::query()
            ->where('setting_key', $key)
            ->first();

        return $setting?->typed_value ?? $default;
    }

    public function set(
        string $key,
        mixed $value,
        string $type = 'string',
        string $group = 'general',
        bool $isPublic = false,
        bool $isEditable = true,
        int $sortOrder = 0
    ): SiteSetting {
        return SiteSetting::query()->updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $this->normalizeValue($value, $type),
                'setting_type' => $type,
                'group_name' => $group,
                'is_public' => $isPublic,
                'is_editable' => $isEditable,
                'sort_order' => $sortOrder,
            ]
        );
    }

    public function setMany(array $items, string $group = 'general'): void
    {
        foreach ($items as $key => $item) {
            $value = is_array($item) ? ($item['value'] ?? null) : $item;
            $type = is_array($item) ? ($item['type'] ?? 'string') : 'string';
            $isPublic = is_array($item) ? (bool) ($item['is_public'] ?? false) : false;
            $isEditable = is_array($item) ? (bool) ($item['is_editable'] ?? true) : true;
            $sortOrder = is_array($item) ? (int) ($item['sort_order'] ?? 0) : 0;

            $this->set($key, $value, $type, $group, $isPublic, $isEditable, $sortOrder);
        }
    }

    public function uploadAndSet(
        string $key,
        UploadedFile $file,
        string $directory = 'uploads/settings',
        string $group = 'branding',
        bool $isPublic = true,
        int $sortOrder = 0
    ): SiteSetting {
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $filename, 'public');

        return $this->set(
            $key,
            'storage/' . $path,
            'string',
            $group,
            $isPublic,
            true,
            $sortOrder
        );
    }

    public function removeAsset(string $key): void
    {
        $setting = SiteSetting::query()->where('setting_key', $key)->first();

        if (! $setting || blank($setting->setting_value)) {
            return;
        }

        $storedPath = str_replace('storage/', '', (string) $setting->setting_value);

        if (Storage::disk('public')->exists($storedPath)) {
            Storage::disk('public')->delete($storedPath);
        }

        $setting->update([
            'setting_value' => null,
        ]);
    }

    public function maintenanceRoutes(): array
    {
        $rows = SiteSetting::query()
            ->forGroup('maintenance')
            ->where('setting_key', 'like', 'maintenance.routes.%')
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $key = (string) $row->setting_key;

            if (! str_starts_with($key, 'maintenance.routes.')) {
                continue;
            }

            $suffix = substr($key, strlen('maintenance.routes.'));
            $lastDot = strrpos($suffix, '.');

            if ($lastDot === false) {
                continue;
            }

            $routeName = substr($suffix, 0, $lastDot);
            $field = substr($suffix, $lastDot + 1);

            if (! in_array($field, ['enabled', 'title', 'message'], true)) {
                continue;
            }

            $grouped[$routeName][$field] = $row->typed_value;
        }

        ksort($grouped);

        return $grouped;
    }

    protected function normalizeValue(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) ((int) $value),
            'float', 'decimal' => (string) ((float) $value),
            'json' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            default => (string) $value,
        };
    }
}
