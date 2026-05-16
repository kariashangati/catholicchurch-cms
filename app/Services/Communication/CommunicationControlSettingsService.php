<?php

namespace App\Services\Communication;

use Illuminate\Support\Arr;

class CommunicationControlSettingsService
{
    private string $configKey = 'communication_center.controls';

    public function defaults(): array
    {
        return [
            'quiet_hours_enabled' => true,
            'quiet_hours_start' => '21:00',
            'quiet_hours_end' => '06:00',
            'default_duplicate_window_hours' => 24,
            'default_requires_approval' => false,
            'approval_threshold_recipients' => 250,
            'approval_threshold_segments' => 350,
            'allow_retry_failed' => true,
        ];
    }

    public function all(): array
    {
        $defaults = $this->defaults();

        if (! function_exists('db_trans')) {
            return $defaults;
        }

        $stored = db_trans($this->configKey, app()->getLocale());

        if (! is_string($stored) || $stored === $this->configKey) {
            return $defaults;
        }

        $decoded = json_decode($stored, true);

        return is_array($decoded) ? array_merge($defaults, $decoded) : $defaults;
    }

    public function update(array $data): array
    {
        $payload = array_merge($this->defaults(), Arr::only($data, array_keys($this->defaults())));

        $translationModel = app(config('communication_center.translation_model', \App\Models\Translation::class));

        foreach (config('communication_center.supported_locales', ['en', 'sw']) as $locale) {
            $translationModel::updateOrCreate(
                ['locale' => $locale, 'translation_key' => $this->configKey],
                ['translation_value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]
            );
        }

        cache()->forget("translation_" . app()->getLocale() . "_{$this->configKey}");
        cache()->forget("laravel-cache-translation_" . app()->getLocale() . "_{$this->configKey}");

        return $payload;
    }
}
