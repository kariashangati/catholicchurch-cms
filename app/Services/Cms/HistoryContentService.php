<?php

namespace App\Services\Cms;

use App\Models\History;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class HistoryContentService
{
    public function paginated(int $perPage = 15): LengthAwarePaginator
    {
        return History::query()
            ->orderBy('display_order')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(array $data): History
    {
        return History::query()->create([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['title']),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'featured_image' => $data['featured_image'] ?? null,
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'is_published' => (bool) ($data['is_published'] ?? true),
            'published_at' => $data['published_at'] ?? now(),
            'display_order' => (int) ($data['display_order'] ?? 0),
        ]);
    }

    public function update(History $history, array $data): History
    {
        $history->update([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['title']),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'],
            'featured_image' => $data['featured_image'] ?? null,
            'is_featured' => (bool) ($data['is_featured'] ?? false),
            'is_published' => (bool) ($data['is_published'] ?? true),
            'published_at' => $data['published_at'] ?? null,
            'display_order' => (int) ($data['display_order'] ?? 0),
        ]);

        return $history->refresh();
    }

    public function delete(History $history): void
    {
        $history->delete();
    }
}