<?php

use App\Models\Translation;
use Illuminate\Support\Facades\Cache;

if (! function_exists('db_trans')) {
    function db_trans(string $key, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        return Cache::remember("translation_{$locale}_{$key}", 3600, function () use ($locale, $key) {
            $translation = Translation::query()
                ->where('locale', $locale)
                ->where('translation_key', $key)
                ->value('translation_value');

            return $translation ?? ucwords(str_replace('_', ' ', $key));
        });
    }
}