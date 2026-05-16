<?php

namespace App\Services\Membership;

use App\Models\AgeGroup;
use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AgeGroupService
{
    public function create(array $data): AgeGroup
    {
        return DB::transaction(function () use ($data) {
            $payload = $this->payload($data);
            $this->ensureNoOverlap($payload);

            $ageGroup = AgeGroup::create($payload);
            $this->syncMembers();

            return $ageGroup->fresh();
        });
    }

    public function update(AgeGroup $ageGroup, array $data): AgeGroup
    {
        return DB::transaction(function () use ($ageGroup, $data) {
            $payload = $this->payload($data);
            $this->ensureNoOverlap($payload, $ageGroup);

            $ageGroup->update($payload);
            $this->syncMembers();

            return $ageGroup->fresh();
        });
    }

    public function delete(AgeGroup $ageGroup): void
    {
        DB::transaction(function () use ($ageGroup) {
            Member::where('age_group_id', $ageGroup->id)->update(['age_group_id' => null]);
            $ageGroup->delete();
            $this->syncMembers();
        });
    }

    public function syncMembers(): void
    {
        $today = now()->toDateString();
        $groups = AgeGroup::query()
            ->where('is_active', true)
            ->orderBy('min_age')
            ->orderBy('max_age')
            ->get();

        Member::query()->whereNull('date_of_birth')->update(['age_group_id' => null]);

        foreach ($groups as $group) {
            $memberIds = Member::query()
                ->whereNotNull('date_of_birth')
                ->when($group->gender_scope && $group->gender_scope !== AgeGroup::GENDER_SCOPE_ALL, function (Builder $query) use ($group): void {
                    $gender = $group->gender_scope === AgeGroup::GENDER_SCOPE_MALE ? 'Male' : 'Female';
                    $query->where('gender', $gender);
                })
                ->whereRaw('TIMESTAMPDIFF(YEAR, date_of_birth, ?) BETWEEN ? AND ?', [$today, $group->min_age, $group->max_age])
                ->pluck('id');

            if ($memberIds->isNotEmpty()) {
                Member::whereIn('id', $memberIds)->update(['age_group_id' => $group->id]);
            }
        }

        $validGroupIds = $groups->pluck('id')->all();

        Member::query()
            ->whereNotNull('age_group_id')
            ->whereNotIn('age_group_id', $validGroupIds)
            ->update(['age_group_id' => null]);
    }


    public function exportData(): array
    {
        $ageGroups = AgeGroup::query()
            ->withCount('members')
            ->orderBy('min_age')
            ->orderBy('name')
            ->get();

        return [
            'pageTitle' => db_trans('age_groups'),
            'reportTitle' => db_trans('age_groups'),
            'sectionTitle' => db_trans('age_groups'),
            'columns' => $this->exportColumns(),
            'rows' => $this->exportBodyRows($ageGroups),
            'metaItems' => [
                ['label' => db_trans('records'), 'value' => number_format($ageGroups->count())],
                ['label' => db_trans('active'), 'value' => number_format($ageGroups->where('is_active', true)->count())],
                ['label' => db_trans('inactive'), 'value' => number_format($ageGroups->where('is_active', false)->count())],
                ['label' => db_trans('members_without_dob'), 'value' => number_format(Member::query()->whereNull('date_of_birth')->count())],
            ],
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function exportRows(): array
    {
        $data = $this->exportData();

        return array_merge([$data['columns']], $data['rows']);
    }

    protected function exportColumns(): array
    {
        return [
            db_trans('sn'),
            db_trans('name'),
            db_trans('age_range'),
            db_trans('gender_scope'),
            db_trans('members'),
            db_trans('status'),
            db_trans('description'),
        ];
    }

    protected function exportBodyRows($ageGroups): array
    {
        $genderScopes = AgeGroup::genderScopes();

        return $ageGroups->values()->map(function (AgeGroup $ageGroup, int $index) use ($genderScopes): array {
            $normalizedScope = AgeGroup::normalizeGenderScope($ageGroup->gender_scope ?? null);

            return [
                $index + 1,
                $ageGroup->name,
                $ageGroup->min_age . ' - ' . $ageGroup->max_age,
                $genderScopes[$normalizedScope] ?? ($ageGroup->gender_scope_label ?? '—'),
                (int) ($ageGroup->members_count ?? 0),
                $ageGroup->is_active ? db_trans('active') : db_trans('inactive'),
                $ageGroup->description ?: '—',
            ];
        })->all();
    }

    protected function payload(array $data): array
    {
        return Arr::only([
            'name' => trim((string) ($data['name'] ?? '')),
            'min_age' => (int) ($data['min_age'] ?? 0),
            'max_age' => (int) ($data['max_age'] ?? 0),
            'gender_scope' => AgeGroup::normalizeGenderScope($data['gender_scope'] ?? null),
            'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ], ['name', 'min_age', 'max_age', 'gender_scope', 'description', 'is_active']);
    }

    protected function ensureNoOverlap(array $payload, ?AgeGroup $ignore = null): void
    {
        $query = AgeGroup::query()
            ->when($ignore, fn (Builder $builder) => $builder->whereKeyNot($ignore->id))
            ->where(function (Builder $builder) use ($payload): void {
                $incomingScope = AgeGroup::normalizeGenderScope($payload['gender_scope'] ?? null);

                $builder
                    ->where('gender_scope', AgeGroup::GENDER_SCOPE_ALL)
                    ->orWhere('gender_scope', $incomingScope)
                    ->orWhere(function (Builder $nested) use ($incomingScope): void {
                        if ($incomingScope === AgeGroup::GENDER_SCOPE_ALL) {
                            $nested->whereIn('gender_scope', [AgeGroup::GENDER_SCOPE_MALE, AgeGroup::GENDER_SCOPE_FEMALE]);
                        }
                    })
                    ->orWhere(function (Builder $legacy) use ($incomingScope): void {
                        $legacy->whereIn('gender_scope', ['all', 'male', 'female']);

                        if ($incomingScope !== AgeGroup::GENDER_SCOPE_ALL) {
                            $legacy->whereIn('gender_scope', ['all', $incomingScope === AgeGroup::GENDER_SCOPE_MALE ? 'male' : 'female']);
                        }
                    });
            })
            ->where(function (Builder $builder) use ($payload): void {
                $builder
                    ->whereBetween('min_age', [$payload['min_age'], $payload['max_age']])
                    ->orWhereBetween('max_age', [$payload['min_age'], $payload['max_age']])
                    ->orWhere(function (Builder $nested) use ($payload): void {
                        $nested->where('min_age', '<=', $payload['min_age'])
                            ->where('max_age', '>=', $payload['max_age']);
                    });
            });

        if ($query->exists()) {
            throw new RuntimeException(function_exists('db_trans') ? db_trans('age_group_range_overlaps') : 'The age range overlaps with another existing age group.');
        }
    }
}
