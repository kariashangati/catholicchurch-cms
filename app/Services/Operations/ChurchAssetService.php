<?php

namespace App\Services\Operations;

use App\Models\ChurchAsset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ChurchAssetService
{
    public function create(array $data): ChurchAsset
    {
        return ChurchAsset::create($this->payload($data));
    }

    public function update(ChurchAsset $asset, array $data): ChurchAsset
    {
        $asset->update($this->payload($data, $asset));

        return $asset->refresh();
    }

    public function exportData(): array
    {
        $assets = ChurchAsset::query()
            ->with('category')
            ->latest()
            ->get();

        return [
            'reportTitle' => db_trans('church_assets'),
            'sectionTitle' => db_trans('church_assets'),
            'columns' => $this->exportColumns(),
            'rows' => $this->exportRows($assets),
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
            db_trans('category'),
            db_trans('asset_code'),
            db_trans('condition_status'),
            db_trans('location'),
            db_trans('current_value'),
            db_trans('status'),
        ];
    }

    protected function exportRows(Collection $assets): array
    {
        return $assets->values()->map(function (ChurchAsset $asset, int $index): array {
            return [
                $index + 1,
                $asset->name,
                $asset->category?->name ?? '—',
                $asset->asset_code ?: '—',
                $asset->condition_status_label ?? $asset->condition_status ?? '—',
                $asset->location ?: '—',
                $asset->current_value !== null ? number_format((float) $asset->current_value, 2) : '—',
                $asset->is_active ? db_trans('active') : db_trans('inactive'),
            ];
        })->all();
    }

    protected function payload(array $data, ?ChurchAsset $asset = null): array
    {
        $documentPath = $asset?->document_path;

        if (($data['document'] ?? null) instanceof UploadedFile) {
            if ($documentPath) {
                Storage::disk('public')->delete($documentPath);
            }

            $documentPath = $data['document']->store('church-assets', 'public');
        }

        return [
            'asset_category_id' => $data['asset_category_id'],
            'name' => $data['name'],
            'asset_code' => $data['asset_code'] ?? null,
            'registration_number' => $data['registration_number'] ?? null,
            'acquisition_cost' => $data['acquisition_cost'] ?? null,
            'current_value' => $data['current_value'] ?? null,
            'acquisition_date' => $data['acquisition_date'] ?? null,
            'condition_status' => $data['condition_status'],
            'location' => $data['location'] ?? null,
            'document_path' => $documentPath,
            'notes' => $data['notes'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }
}
