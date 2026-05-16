<?php

namespace App\Services\Cms;

use App\Models\SiteSetting;

class NavigationSettingsService
{
    protected array $keys = [
        'nav.show_home',
        'nav.show_history',
        'nav.show_giving',
        'nav.show_kanda',
        'nav.show_jumuiya',
        'nav.show_masses',
        'nav.show_announcements',
        'nav.show_projects',
        'nav.show_leadership',
        'nav.show_gallery',
        'nav.show_contact',
        'nav.show_login',
        'nav.show_ministries',
    ];

    public function all(): array
    {
        $data = [];

        foreach ($this->keys as $key) {
            $data[$key] = $this->setting($key, false);
        }

        return $data;
    }

    public function update(array $items): void
    {
        foreach ($this->keys as $key) {
            $this->setSetting(
                $key,
                (bool) ($items[$key] ?? false),
                'boolean',
                'navigation',
                true,
                true,
                0
            );
        }
    }

    protected function setting(string $key, mixed $default = null): mixed
    {
        $setting = SiteSetting::query()->where('setting_key', $key)->first();

        return $setting?->typed_value ?? $default;
    }

    protected function setSetting(
        string $key,
        mixed $value,
        string $type = 'string',
        string $group = 'general',
        bool $isPublic = true,
        bool $isEditable = true,
        int $sortOrder = 0
    ): void {
        SiteSetting::query()->updateOrCreate(
            ['setting_key' => $key],
            [
                'setting_value' => $type === 'boolean' ? ($value ? '1' : '0') : (string) $value,
                'setting_type' => $type,
                'group_name' => $group,
                'is_public' => $isPublic,
                'is_editable' => $isEditable,
                'sort_order' => $sortOrder,
            ]
        );
    }
}