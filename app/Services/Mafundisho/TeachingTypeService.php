<?php

namespace App\Services\Mafundisho;

use App\Models\TeachingType;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TeachingTypeService
{
    public function paginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return TeachingType::query()
            ->withCount('enrollments')
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $search = trim((string) $filters['search']);
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when(($filters['status'] ?? null) === 'active', fn ($query) => $query->where('is_active', true))
            ->when(($filters['status'] ?? null) === 'inactive', fn ($query) => $query->where('is_active', false))
            ->ordered()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function activeTypes(): Collection
    {
        return TeachingType::query()->active()->ordered()->get();
    }

    public function allOrdered(): Collection
    {
        return TeachingType::query()->ordered()->get();
    }

    public function create(array $validated, User $user): TeachingType
    {
        return DB::transaction(function () use ($validated, $user) {
            $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? $validated['name']);
            $validated['created_by'] = $user->id;
            $validated['updated_by'] = $user->id;
            $validated['is_system'] = false;
            $validated['is_active'] = (bool) ($validated['is_active'] ?? true);
            $validated['requires_partner_info'] = (bool) ($validated['requires_partner_info'] ?? false);
            $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

            return TeachingType::query()->create($validated);
        });
    }

    public function update(TeachingType $teachingType, array $validated, User $user): TeachingType
    {
        return DB::transaction(function () use ($teachingType, $validated, $user) {
            if ($teachingType->is_system) {
                unset($validated['slug'], $validated['sacrament_key'], $validated['eligibility_rule']);
            } elseif (array_key_exists('slug', $validated)) {
                $validated['slug'] = $this->uniqueSlug($validated['slug'] ?: $validated['name'], $teachingType->id);
            }

            $validated['updated_by'] = $user->id;
            $validated['is_active'] = (bool) ($validated['is_active'] ?? false);
            $validated['requires_partner_info'] = (bool) ($validated['requires_partner_info'] ?? false);
            $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

            $teachingType->update($validated);

            return $teachingType->refresh();
        });
    }

    public function deleteOrDeactivate(TeachingType $teachingType): void
    {
        if ($teachingType->is_system) {
            throw ValidationException::withMessages([
                'teaching_type' => db_trans('mafundisho_system_type_cannot_be_deleted'),
            ]);
        }

        if ($teachingType->enrollments()->exists()) {
            $teachingType->update(['is_active' => false]);
            return;
        }

        $teachingType->delete();
    }

    protected function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $counter = 2;

        while (TeachingType::query()
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
