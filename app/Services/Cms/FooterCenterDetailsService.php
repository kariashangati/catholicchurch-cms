<?php

namespace App\Services\Cms;

use App\Models\SiteSetting;

class FooterCenterDetailsService
{
    public function data(): array
    {
        return [
            'footer.tagline' => $this->setting('footer.tagline'),
            'footer.description' => $this->setting('footer.description'),
            'footer.newsletter_text' => $this->setting('footer.newsletter_text'),
            'footer.newsletter_note' => $this->setting('footer.newsletter_note'),
            'footer.bottom_note' => $this->setting('footer.bottom_note'),
            'footer.youtube_url' => $this->setting('footer.youtube_url'),
            'footer.facebook_url' => $this->setting('footer.facebook_url'),
            'footer.instagram_url' => $this->setting('footer.instagram_url'),
            'footer.tiktok_url' => $this->setting('footer.tiktok_url'),

            'church.email' => $this->setting('church.email'),
            'church.phone' => $this->setting('church.phone'),
            'church.address' => $this->setting('church.address'),
            'contact.working_hours' => $this->setting('contact.working_hours'),
            'contact.map_embed' => $this->setting('contact.map_embed'),
        ];
    }

    public function update(array $items): void
    {
        $definitions = [
            'footer.tagline' => ['group' => 'footer'],
            'footer.description' => ['group' => 'footer'],
            'footer.newsletter_text' => ['group' => 'footer'],
            'footer.newsletter_note' => ['group' => 'footer'],
            'footer.bottom_note' => ['group' => 'footer'],
            'footer.youtube_url' => ['group' => 'footer'],
            'footer.facebook_url' => ['group' => 'footer'],
            'footer.instagram_url' => ['group' => 'footer'],
            'footer.tiktok_url' => ['group' => 'footer'],

            'church.email' => ['group' => 'contact'],
            'church.phone' => ['group' => 'contact'],
            'church.address' => ['group' => 'contact'],
            'contact.working_hours' => ['group' => 'contact'],
            'contact.map_embed' => ['group' => 'contact'],
        ];

        foreach ($definitions as $key => $meta) {
            $this->setSetting(
                $key,
                $items[$key] ?? null,
                'string',
                $meta['group'],
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
                'setting_value' => $value === null ? null : (string) $value,
                'setting_type' => $type,
                'group_name' => $group,
                'is_public' => $isPublic,
                'is_editable' => $isEditable,
                'sort_order' => $sortOrder,
            ]
        );
    }
}