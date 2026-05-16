<?php

namespace App\Services\Cms;

use App\Models\Page;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PageContentService
{
    public function paginated(int $perPage = 15): LengthAwarePaginator
    {
        return Page::query()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(array $data, ?int $actorId = null): Page
    {
        return Page::query()->create([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['title']),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? null,
            'featured_image' => $data['featured_image'] ?? null,
            'template' => $data['template'] ?? 'default',
            'is_published' => (bool) ($data['is_published'] ?? true),
            'show_in_menu' => (bool) ($data['show_in_menu'] ?? false),
            'show_in_footer' => (bool) ($data['show_in_footer'] ?? false),
            'menu_title' => $data['menu_title'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'published_at' => $data['published_at'] ?? now(),
            'created_by' => $actorId,
            'updated_by' => $actorId,
        ]);
    }

    public function update(Page $page, array $data, ?int $actorId = null): Page
    {
        $page->update([
            'title' => $data['title'],
            'slug' => $data['slug'] ?? Str::slug($data['title']),
            'excerpt' => $data['excerpt'] ?? null,
            'content' => $data['content'] ?? null,
            'featured_image' => $data['featured_image'] ?? null,
            'template' => $data['template'] ?? 'default',
            'is_published' => (bool) ($data['is_published'] ?? true),
            'show_in_menu' => (bool) ($data['show_in_menu'] ?? false),
            'show_in_footer' => (bool) ($data['show_in_footer'] ?? false),
            'menu_title' => $data['menu_title'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'published_at' => $data['published_at'] ?? null,
            'updated_by' => $actorId,
        ]);

        return $page->refresh();
    }

    public function delete(Page $page): void
    {
        $page->delete();
    }
}