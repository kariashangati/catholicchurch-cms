<?php

namespace App\Services\Operations;

use App\Models\AssetCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AssetCategoryService
{
    public function create(array $data): AssetCategory
    {
        return AssetCategory::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }

    public function update(AssetCategory $category, array $data): AssetCategory
    {
        $category->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return $category->refresh();
    }

    public function exportData(): array
    {
        $categories = AssetCategory::query()
            ->latest()
            ->get();

        return [
            'reportTitle' => db_trans('asset_categories'),
            'sectionTitle' => db_trans('asset_categories'),
            'columns' => $this->exportColumns(),
            'rows' => $this->exportRows($categories),
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function exportRowsForExcel(): array
    {
        $data = $this->exportData();

        return array_merge([$data['columns']], $data['rows']);
    }

    protected function exportColumns(): array
    {
        return [
            '#',
            db_trans('name'),
            db_trans('description'),
            db_trans('status'),
        ];
    }

    protected function exportRows(Collection $categories): array
    {
        return $categories->values()->map(function (AssetCategory $category, int $index): array {
            return [
                $index + 1,
                $category->name,
                $category->description ?: '—',
                $category->is_active ? db_trans('active') : db_trans('inactive'),
            ];
        })->all();
    }
}
