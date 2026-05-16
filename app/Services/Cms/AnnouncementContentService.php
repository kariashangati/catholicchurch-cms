<?php

namespace App\Services\Cms;

use App\Models\Announcement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class AnnouncementContentService
{
    public function paginated(int $perPage = 15): LengthAwarePaginator
    {
        return Announcement::query()
            ->orderBy('display_order')
            ->orderByDesc('publish_from')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(array $data, ?int $actorId = null): Announcement
    {
        return Announcement::query()->create([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['title']),
            'summary' => $data['summary'] ?? null,
            'content' => $data['content'] ?? null,
            'image' => $data['image'] ?? null,
            'publish_from' => $data['publish_from'] ?? now(),
            'publish_until' => $data['publish_until'] ?? null,
            'is_published' => (bool) ($data['is_published'] ?? true),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'show_on_homepage' => (bool) ($data['show_on_homepage'] ?? true),
            'display_order' => (int) ($data['display_order'] ?? 0),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    public function update(Announcement $announcement, array $data, ?int $actorId = null): Announcement
    {
        $announcement->update([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['title']),
            'summary' => $data['summary'] ?? null,
            'content' => $data['content'] ?? null,
            'image' => $data['image'] ?? null,
            'publish_from' => $data['publish_from'] ?? null,
            'publish_until' => $data['publish_until'] ?? null,
            'is_published' => (bool) ($data['is_published'] ?? true),
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'show_on_homepage' => (bool) ($data['show_on_homepage'] ?? true),
            'display_order' => (int) ($data['display_order'] ?? 0),
            'updated_by' => $actorId,
        ]);

        return $announcement->refresh();
    }

    public function delete(Announcement $announcement): void
    {
        $announcement->delete();
    }
}