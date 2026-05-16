<?php

namespace App\Services\Cms;

use App\Models\HeroBanner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HeroBannerService
{
    public function paginated(int $perPage = 15): LengthAwarePaginator
    {
        return HeroBanner::query()
            ->orderBy('display_order')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(array $data): HeroBanner
    {
        return HeroBanner::query()->create([
            'title' => $data['title'] ?? db_trans('hero_banner'),
            'subtitle' => $data['subtitle'] ?? null,
            'description' => $data['description'] ?? null,
            'background_type' => $data['background_type'] ?? 'image',
            'background_value' => $data['background_value'] ?? null,
            'poster_image' => $data['poster_image'] ?? null,
            'primary_button_text' => $data['primary_button_text'] ?? null,
            'primary_button_link' => $data['primary_button_link'] ?? null,
            'secondary_button_text' => $data['secondary_button_text'] ?? null,
            'secondary_button_link' => $data['secondary_button_link'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'display_order' => (int) ($data['display_order'] ?? 0),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'overlay_opacity' => $data['overlay_opacity'] ?? 0.55,
        ]);
    }

    public function update(HeroBanner $heroBanner, array $data): HeroBanner
    {
        $heroBanner->update([
            'title' => $data['title'] ?? $heroBanner->title ?? db_trans('hero_banner'),
            'subtitle' => $data['subtitle'] ?? $heroBanner->subtitle,
            'description' => $data['description'] ?? $heroBanner->description,
            'background_type' => $data['background_type'] ?? $heroBanner->background_type ?? 'image',
            'background_value' => $data['background_value'] ?? $heroBanner->background_value,
            'poster_image' => $data['poster_image'] ?? $heroBanner->poster_image,
            'primary_button_text' => $data['primary_button_text'] ?? null,
            'primary_button_link' => $data['primary_button_link'] ?? null,
            'secondary_button_text' => $data['secondary_button_text'] ?? null,
            'secondary_button_link' => $data['secondary_button_link'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'display_order' => (int) ($data['display_order'] ?? 0),
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'overlay_opacity' => $data['overlay_opacity'] ?? 0.55,
        ]);

        return $heroBanner->refresh();
    }

    public function delete(HeroBanner $heroBanner): void
    {
        $heroBanner->delete();
    }
}
