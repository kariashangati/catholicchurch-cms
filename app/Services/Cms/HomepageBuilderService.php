<?php

namespace App\Services\Cms;

use App\Models\PageSection;
use App\Models\SiteSetting;
use Illuminate\Support\Collection;

class HomepageBuilderService
{
    public function sections(): Collection
    {
        return PageSection::query()
            ->where('page_key', 'home')
            ->orderBy('display_order')
            ->get();
    }

    public function heroSettings(): array
    {
        return [
            'hero_badge' => $this->setting('hero_badge'),
            'enable_video' => $this->setting('hero.enable_video', false),
            'video_url' => $this->setting('hero.video_url'),
            'poster_url' => $this->setting('hero.poster_url'),
            'image_url' => $this->setting('hero.image_url'),
            'mobile_image_url' => $this->setting('hero.mobile_image_url'),
            'overlay_opacity' => $this->setting('hero.overlay_opacity', '0.50'),
            'cta_primary_text' => $this->setting('hero.cta_primary_text'),
            'cta_primary_link' => $this->setting('hero.cta_primary_link'),
            'cta_secondary_text' => $this->setting('hero.cta_secondary_text'),
            'cta_secondary_link' => $this->setting('hero.cta_secondary_link'),
        ];
    }

    public function homepageToggles(): array
    {
        return [
            'homepage.show_announcements' => $this->setting('homepage.show_announcements', true),
            'homepage.show_masses' => $this->setting('homepage.show_masses', true),
            'homepage.show_projects' => $this->setting('homepage.show_projects', true),
            'homepage.show_ministries' => $this->setting('homepage.show_ministries', false),
            'homepage.show_gallery' => $this->setting('homepage.show_gallery', true),
            'homepage.show_leadership' => $this->setting('homepage.show_leadership', true),
            'homepage.show_contact' => $this->setting('homepage.show_contact', true),
        ];
    }

    public function updateSections(array $sections): void
    {
        foreach ($sections as $section) {
            if (empty($section['section_key'])) {
                continue;
            }

            PageSection::query()->updateOrCreate(
                [
                    'page_key' => 'home',
                    'section_key' => $section['section_key'],
                ],
                [
                    'title' => $section['title'] ?? null,
                    'subtitle' => $section['subtitle'] ?? null,
                    'content' => $section['content'] ?? null,
                    'data_source' => $section['data_source'] ?? null,
                    'layout' => $section['layout'] ?? null,
                    'is_enabled' => (bool) ($section['is_enabled'] ?? false),
                    'display_order' => (int) ($section['display_order'] ?? 0),
                    'settings_json' => $section['settings_json'] ?? null,
                ]
            );
        }
    }

    public function updateHeroSettings(array $settings): void
    {
        $this->setSetting('hero_badge', $settings['hero_badge'] ?? null, 'string', 'hero');
        $this->setSetting('hero.enable_video', (bool) ($settings['enable_video'] ?? false), 'boolean', 'hero');
        $this->setSetting('hero.video_url', $settings['video_url'] ?? null, 'string', 'hero');
        $this->setSetting('hero.poster_url', $settings['poster_url'] ?? null, 'string', 'hero');
        $this->setSetting('hero.image_url', $settings['image_url'] ?? null, 'string', 'hero');
        $this->setSetting('hero.mobile_image_url', $settings['mobile_image_url'] ?? null, 'string', 'hero');
        $this->setSetting('hero.overlay_opacity', $settings['overlay_opacity'] ?? '0.50', 'string', 'hero');
        $this->setSetting('hero.cta_primary_text', $settings['cta_primary_text'] ?? null, 'string', 'hero');
        $this->setSetting('hero.cta_primary_link', $settings['cta_primary_link'] ?? null, 'string', 'hero');
        $this->setSetting('hero.cta_secondary_text', $settings['cta_secondary_text'] ?? null, 'string', 'hero');
        $this->setSetting('hero.cta_secondary_link', $settings['cta_secondary_link'] ?? null, 'string', 'hero');
    }

    public function updateHomepageToggles(array $toggles): void
    {
        foreach ($toggles as $key => $value) {
            $this->setSetting($key, (bool) $value, 'boolean', 'homepage');
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
                'setting_value' => $this->normalizeValue($value, $type),
                'setting_type' => $type,
                'group_name' => $group,
                'is_public' => $isPublic,
                'is_editable' => $isEditable,
                'sort_order' => $sortOrder,
            ]
        );
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