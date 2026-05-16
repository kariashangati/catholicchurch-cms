<?php

namespace App\Services\Cms;

use App\Models\Gallery;
use App\Models\GalleryImage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class GalleryContentService
{
    public function paginated(int $perPage = 15): LengthAwarePaginator
    {
        return Gallery::query()
            ->withCount('images')
            ->orderBy('display_order')
            ->orderByDesc('event_date')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function createGallery(array $data): Gallery
    {
        return Gallery::query()->create([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['title']),
            'description' => $data['description'] ?? null,
            'cover_image' => $data['cover_image'] ?? null,
            'is_published' => (bool) ($data['is_published'] ?? true),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'event_date' => $data['event_date'] ?? null,
            'display_order' => (int) ($data['display_order'] ?? 0),
        ]);
    }

    public function updateGallery(Gallery $gallery, array $data): Gallery
    {
        $gallery->update([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['title']),
            'description' => $data['description'] ?? null,
            'cover_image' => $data['cover_image'] ?? null,
            'is_published' => (bool) ($data['is_published'] ?? true),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'event_date' => $data['event_date'] ?? null,
            'display_order' => (int) ($data['display_order'] ?? 0),
        ]);

        return $gallery->refresh();
    }

    public function deleteGallery(Gallery $gallery): void
    {
        $gallery->delete();
    }

    public function addImage(Gallery $gallery, array $data): GalleryImage
    {
        return $gallery->images()->create([
            'image_path' => $data['image_path'],
            'caption' => $data['caption'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
            'display_order' => (int) ($data['display_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }

    public function updateImage(GalleryImage $image, array $data): GalleryImage
    {
        $image->update([
            'image_path' => $data['image_path'] ?? $image->image_path,
            'caption' => $data['caption'] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
            'display_order' => (int) ($data['display_order'] ?? 0),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return $image->refresh();
    }

    public function deleteImage(GalleryImage $image): void
    {
        $image->delete();
    }
}