<?php

namespace App\Services\Operations;

use App\Models\ServiceCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ServiceCategoryService
{
    public function create(array $data): ServiceCategory
    {
        return ServiceCategory::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }

    public function update(ServiceCategory $category, array $data): ServiceCategory
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
        $categories = ServiceCategory::query()
            ->latest()
            ->get();

        return [
            'reportTitle' => db_trans('service_categories'),
            'sectionTitle' => db_trans('service_categories'),
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
        return $categories->values()->map(function (ServiceCategory $category, int $index): array {
            return [
                $index + 1,
                $category->name,
                $category->description ?: '—',
                $category->is_active ? db_trans('active') : db_trans('inactive'),
            ];
        })->all();
    }
}
