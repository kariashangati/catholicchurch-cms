<?php

namespace App\Services\Operations;

use App\Models\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ServiceProviderService
{
    public function create(array $data): ServiceProvider
    {
        $this->ensureUniqueCombination($data);

        return ServiceProvider::create($this->payload($data));
    }

    public function update(ServiceProvider $provider, array $data): ServiceProvider
    {
        $this->ensureUniqueCombination($data, $provider->id);
        $provider->update($this->payload($data));

        return $provider->refresh();
    }

    public function exportData(): array
    {
        $providers = ServiceProvider::query()
            ->with(['category', 'member'])
            ->latest()
            ->get();

        return [
            'reportTitle' => db_trans('service_providers'),
            'sectionTitle' => db_trans('service_providers'),
            'columns' => $this->exportColumns(),
            'rows' => $this->exportRows($providers),
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
            db_trans('phone'),
            db_trans('email'),
            db_trans('status'),
            db_trans('internal'),
            db_trans('active'),
        ];
    }

    protected function exportRows(Collection $providers): array
    {
        return $providers->values()->map(function (ServiceProvider $provider, int $index): array {
            return [
                $index + 1,
                $provider->name,
                $provider->category?->name ?? '—',
                $provider->phone ?: '—',
                $provider->email ?: '—',
                $provider->status_label ?? $provider->status ?? '—',
                $provider->is_internal ? db_trans('yes') : db_trans('no'),
                $provider->is_active ? db_trans('active') : db_trans('inactive'),
            ];
        })->all();
    }

    protected function payload(array $data): array
    {
        return [
            'service_category_id' => $data['service_category_id'],
            'member_id' => $data['member_id'] ?? null,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'address' => $data['address'],
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'],
            'is_internal' => (bool) ($data['is_internal'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ];
    }

    protected function ensureUniqueCombination(array $data, ?int $ignoreId = null): void
    {
        $query = ServiceProvider::query()
            ->where('service_category_id', $data['service_category_id'])
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($data['name'])]);

        if ($ignoreId) {
            $query->whereKeyNot($ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'name' => db_trans('service_provider_category_name_taken'),
            ]);
        }
    }
}
