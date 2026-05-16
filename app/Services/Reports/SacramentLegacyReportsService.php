<?php

namespace App\Services\Reports;

use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\User;
use App\Services\Access\UserScopeResolver;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SacramentLegacyReportsService
{


public function exportData(User $user, array $filters): array
{
    $report = $this->generate($user, $filters);
    $data = $report['data'];
    $isFamilyReport = $report['view'] === 'admin.reports.sacraments.family-census';

    $summary = $data['summary'] ?? [];
    $normalizedFilters = $data['filters'] ?? [];

    return [
        'pageTitle' => $data['pageTitle'] ?? db_trans('sacrament_reports'),
        'reportTitle' => ($data['pageTitle'] ?? db_trans('sacrament_reports')) . ' - ' . $this->exportFilterLabel($normalizedFilters),
        'isFamilyReport' => $isFamilyReport,
        'filters' => $normalizedFilters,
        'filterLabel' => $this->exportFilterLabel($normalizedFilters),
        'summary' => $summary,
        'rows' => $data['rows'] ?? collect(),
        'parents' => $data['parents'] ?? collect(),
        'members' => $data['members'] ?? collect(),
        'familia' => $data['familia'] ?? null,
        'issuedAtText' => now()->translatedFormat('d F Y'),
        'locale' => app()->getLocale(),
    ];
}

public function exportRows(User $user, array $filters): Collection
{
    $data = $this->exportData($user, $filters);

    if ($data['isFamilyReport']) {
        $rows = collect();

        $rows->push([
            db_trans('section'),
            db_trans('sn'),
            db_trans('name'),
            db_trans('phone'),
            db_trans('family_role'),
            db_trans('gender'),
            db_trans('baptized'),
            db_trans('communion'),
            db_trans('confirmation'),
            db_trans('receiving_eucharist'),
            db_trans('married'),
            db_trans('marriage_type'),
            db_trans('familia'),
            db_trans('jumuiya'),
            db_trans('kanda'),
        ]);

        foreach (collect($data['parents']) as $index => $parent) {
            $rows->push([
                db_trans('parents_and_heads'),
                $index + 1,
                $parent->full_name,
                $parent->phone ?: '—',
                $parent->family_role ?: '—',
                ucfirst((string) $parent->gender),
                $parent->is_baptized ? db_trans('yes') : db_trans('no'),
                $parent->has_communion ? db_trans('yes') : db_trans('no'),
                $parent->has_confirmation ? db_trans('yes') : db_trans('no'),
                $parent->receives_eucharist ? db_trans('yes') : db_trans('no'),
                $parent->is_married ? db_trans('yes') : db_trans('no'),
                $parent->marriage_type ?: '—',
                $parent->familia?->name ?? '—',
                $parent->familia?->jumuiya?->name ?? '—',
                $parent->familia?->jumuiya?->kanda?->name ?? '—',
            ]);
        }

        foreach (collect($data['members']) as $index => $member) {
            $rows->push([
                db_trans('family_members'),
                $index + 1,
                $member->full_name,
                $member->phone ?: '—',
                $member->family_role ?: '—',
                ucfirst((string) $member->gender),
                $member->is_baptized ? db_trans('yes') : db_trans('no'),
                $member->has_communion ? db_trans('yes') : db_trans('no'),
                $member->has_confirmation ? db_trans('yes') : db_trans('no'),
                $member->receives_eucharist ? db_trans('yes') : db_trans('no'),
                $member->is_married ? db_trans('yes') : db_trans('no'),
                $member->marriage_type ?: '—',
                $member->familia?->name ?? '—',
                $member->familia?->jumuiya?->name ?? '—',
                $member->familia?->jumuiya?->kanda?->name ?? '—',
            ]);
        }

        return $rows;
    }

    $rows = collect();

    $rows->push([
        db_trans('section'),
        db_trans('sn'),
        db_trans('name'),
        db_trans('familia'),
        db_trans('jumuiya'),
        db_trans('kanda'),
        db_trans('gender'),
        db_trans('members'),
        db_trans('baptized'),
        db_trans('communion'),
        db_trans('confirmation'),
        db_trans('married'),
        db_trans('receiving_eucharist'),
    ]);

    foreach (collect($data['rows']) as $index => $row) {
        $rows->push([
            db_trans('summary'),
            $index + 1,
            $row['name'] ?? '—',
            '—',
            '—',
            '—',
            '—',
            $row['members_total'] ?? 0,
            $row['baptized'] ?? 0,
            $row['communion'] ?? 0,
            $row['confirmation'] ?? 0,
            $row['married'] ?? 0,
            $row['receives_eucharist'] ?? 0,
        ]);
    }

    foreach (collect($data['members']) as $index => $member) {
        $rows->push([
            db_trans('members'),
            $index + 1,
            $member->full_name,
            $member->familia?->name ?? '—',
            $member->familia?->jumuiya?->name ?? '—',
            $member->familia?->jumuiya?->kanda?->name ?? '—',
            ucfirst((string) $member->gender),
            '',
            $member->is_baptized ? db_trans('yes') : db_trans('no'),
            $member->has_communion ? db_trans('yes') : db_trans('no'),
            $member->has_confirmation ? db_trans('yes') : db_trans('no'),
            $member->is_married ? db_trans('yes') : db_trans('no'),
            $member->receives_eucharist ? db_trans('yes') : db_trans('no'),
        ]);
    }

    return $rows;
}

protected function exportFilterLabel(array $filters): string
{
    $parts = [];

    if (! empty($filters['kanda_id'])) {
        $kanda = Kanda::query()->find((int) $filters['kanda_id']);
        $parts[] = db_trans('kanda') . ': ' . ($kanda?->name ?? '—');
    } else {
        $parts[] = db_trans('kanda') . ': ' . db_trans('all');
    }

    if (! empty($filters['jumuiya_id'])) {
        $jumuiya = Jumuiya::query()->find((int) $filters['jumuiya_id']);
        $parts[] = db_trans('jumuiya') . ': ' . ($jumuiya?->name ?? '—');
    } else {
        $parts[] = db_trans('jumuiya') . ': ' . db_trans('all');
    }

    if (! empty($filters['familia_id'])) {
        $familia = Familia::query()->find((int) $filters['familia_id']);
        $parts[] = db_trans('familia') . ': ' . ($familia?->name ?? '—');
    }

    $parts[] = db_trans('filter') . ': ' . db_trans($filters['member_filter'] ?? 'all');

    return implode(' | ', $parts);
}
    public function __construct(
        protected UserScopeResolver $scopeResolver,
    ) {
    }

    public function filtersData(User $user): array
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            return [
                'kandas' => collect(),
                'jumuiyas' => collect(),
                'familias' => collect(),
                'memberFilters' => $this->memberFilters(),
            ];
        }

        $kandas = Kanda::query()
            ->orderBy('name')
            ->when($scope->isKanda(), fn (Builder $query) => $query->whereKey((int) $scope->kandaId))
            ->when($scope->isJumuiya(), fn (Builder $query) => $query->whereKey((int) $scope->kandaId))
            ->get(['id', 'name']);

        $jumuiyas = Jumuiya::query()
            ->orderBy('name')
            ->when($scope->isKanda(), fn (Builder $query) => $query->where('kanda_id', (int) $scope->kandaId))
            ->when($scope->isJumuiya(), fn (Builder $query) => $query->whereKey((int) $scope->jumuiyaId))
            ->get(['id', 'name', 'kanda_id']);

        $familias = Familia::query()
            ->orderBy('name')
            ->when($scope->isKanda(), function (Builder $query) use ($scope): void {
                $query->whereHas('jumuiya', fn (Builder $jumuiyaQuery) => $jumuiyaQuery->where('kanda_id', (int) $scope->kandaId));
            })
            ->when($scope->isJumuiya(), fn (Builder $query) => $query->where('jumuiya_id', (int) $scope->jumuiyaId))
            ->get(['id', 'name', 'jumuiya_id', 'phone']);

        return [
            'kandas' => $kandas,
            'jumuiyas' => $jumuiyas,
            'familias' => $familias,
            'memberFilters' => $this->memberFilters(),
        ];
    }

    public function indexData(User $user): array
    {
        return [
            'pageTitle' => db_trans('sacrament_reports'),
            'filterData' => $this->filtersData($user),
        ];
    }

    public function generate(User $user, array $filters): array
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            throw new AuthorizationException($scope->reason ?? 'This account has an invalid scope.');
        }

        $normalized = $this->normalizeRequestedScope($user, $filters);

        $query = Member::query()->with(['familia.jumuiya.kanda']);

        $this->applyResolvedScope($query, $scope);
        $this->applyRequestedScope($query, $normalized);
        $this->applyMemberFilter($query, $normalized['member_filter']);

        $members = $query
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->orderBy('last_name')
            ->get();

        if (! empty($normalized['familia_id'])) {
            $familia = Familia::with('jumuiya.kanda')->findOrFail((int) $normalized['familia_id']);
            $this->ensureUserCanAccessFamilia($user, $familia);

            return [
                'view' => 'admin.reports.sacraments.family-census',
                'data' => [
                    'pageTitle' => db_trans('family_sacrament_census'),
                    'filters' => $normalized,
                    'familia' => $familia,
                    'parents' => $members->whereIn('family_role', ['Baba', 'Mama', 'Male Head', 'Female Head', 'Parent'])->values(),
                    'members' => $members,
                    'summary' => $this->summaryFromMembers($members),
                ],
            ];
        }

        return [
            'view' => 'admin.reports.sacraments.scope-summary',
            'data' => [
                'pageTitle' => db_trans('sacrament_scope_summary'),
                'filters' => $normalized,
                'rows' => $this->groupedSummary($members, $normalized['group_by']),
                'summary' => $this->summaryFromMembers($members),
                'members' => $members,
            ],
        ];
    }

    protected function applyResolvedScope(Builder $query, object $scope): void
    {
        if ($scope->isGlobal()) {
            return;
        }

        if ($scope->isKanda()) {
            $query->whereHas('familia.jumuiya', fn (Builder $jumuiyaQuery) => $jumuiyaQuery->where('kanda_id', (int) $scope->kandaId));
            return;
        }

        if ($scope->isJumuiya()) {
            $query->whereHas('familia', fn (Builder $familiaQuery) => $familiaQuery->where('jumuiya_id', (int) $scope->jumuiyaId));
        }
    }

    protected function applyRequestedScope(Builder $query, array $filters): void
    {
        if (! empty($filters['familia_id'])) {
            $query->where('familia_id', (int) $filters['familia_id']);
            return;
        }

        if (! empty($filters['jumuiya_id'])) {
            $query->whereHas('familia', fn (Builder $familiaQuery) => $familiaQuery->where('jumuiya_id', (int) $filters['jumuiya_id']));
            return;
        }

        if (! empty($filters['kanda_id'])) {
            $query->whereHas('familia.jumuiya', fn (Builder $jumuiyaQuery) => $jumuiyaQuery->where('kanda_id', (int) $filters['kanda_id']));
        }
    }

    protected function normalizeRequestedScope(User $user, array $filters): array
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            throw new AuthorizationException($scope->reason ?? 'This account has an invalid scope.');
        }

        $kandaId = ! empty($filters['kanda_id']) ? (int) $filters['kanda_id'] : null;
        $jumuiyaId = ! empty($filters['jumuiya_id']) ? (int) $filters['jumuiya_id'] : null;
        $familiaId = ! empty($filters['familia_id']) ? (int) $filters['familia_id'] : null;
        $memberFilter = (string) ($filters['member_filter'] ?? 'all');
        $viewMode = (string) ($filters['view_mode'] ?? 'summary');

        if (! in_array($memberFilter, $this->memberFilters(), true)) {
            $memberFilter = 'all';
        }

        if (! in_array($viewMode, ['summary', 'detailed'], true)) {
            $viewMode = 'summary';
        }

        if ($scope->isKanda()) {
            if ($kandaId && $kandaId !== (int) $scope->kandaId) {
                throw new AuthorizationException('You cannot access another kanda report scope.');
            }

            $kandaId = (int) $scope->kandaId;
        }

        if ($scope->isJumuiya()) {
            if ($kandaId && $kandaId !== (int) $scope->kandaId) {
                throw new AuthorizationException('You cannot access another kanda report scope.');
            }

            if ($jumuiyaId && $jumuiyaId !== (int) $scope->jumuiyaId) {
                throw new AuthorizationException('You cannot access another jumuiya report scope.');
            }

            $kandaId = (int) $scope->kandaId;
            $jumuiyaId = (int) $scope->jumuiyaId;
        }

        if ($kandaId) {
            Kanda::query()->findOrFail($kandaId);
        }

        if ($jumuiyaId) {
            $jumuiya = Jumuiya::query()->findOrFail($jumuiyaId);

            if ($kandaId && (int) $jumuiya->kanda_id !== $kandaId) {
                throw ValidationException::withMessages([
                    'jumuiya_id' => db_trans('selected_jumuiya_does_not_belong_to_kanda'),
                ]);
            }

            if (! $kandaId) {
                $kandaId = (int) $jumuiya->kanda_id;
            }
        }

        if ($familiaId) {
            $familia = Familia::with('jumuiya')->findOrFail($familiaId);
            $this->ensureUserCanAccessFamilia($user, $familia);

            if ($jumuiyaId && (int) $familia->jumuiya_id !== $jumuiyaId) {
                throw ValidationException::withMessages([
                    'familia_id' => db_trans('selected_familia_does_not_belong_to_jumuiya'),
                ]);
            }

            if (! $jumuiyaId) {
                $jumuiyaId = (int) $familia->jumuiya_id;
            }

            if (! $kandaId) {
                $kandaId = (int) $familia->jumuiya?->kanda_id;
            }
        }

        return [
            'kanda_id' => $kandaId,
            'jumuiya_id' => $jumuiyaId,
            'familia_id' => $familiaId,
            'member_filter' => $memberFilter,
            'view_mode' => $viewMode,
            'group_by' => $this->resolveGroupBy($kandaId, $jumuiyaId, $familiaId),
        ];
    }

    protected function resolveGroupBy(?int $kandaId, ?int $jumuiyaId, ?int $familiaId): string
    {
        if ($familiaId) {
            return 'familia';
        }

        if ($jumuiyaId) {
            return 'familia';
        }

        if ($kandaId) {
            return 'jumuiya';
        }

        return 'kanda';
    }

    protected function applyMemberFilter(Builder $query, string $filter): void
    {
        match ($filter) {
            'baptized' => $query->where('is_baptized', true),
            'not_baptized' => $query->where('is_baptized', false),
            'married' => $query->where('is_married', true),
            'not_married' => $query->where('is_married', false),
            'confirmed' => $query->where('has_confirmation', true),
            'not_confirmed' => $query->where('has_confirmation', false),
            'communion' => $query->where('has_communion', true),
            'not_communion' => $query->where('has_communion', false),
            'eucharist' => $query->where('receives_eucharist', true),
            'not_eucharist' => $query->where('receives_eucharist', false),
            'male' => $query->whereRaw('LOWER(gender) = ?', ['male']),
            'female' => $query->whereRaw('LOWER(gender) = ?', ['female']),
            default => null,
        };
    }

    protected function summaryFromMembers(Collection $members): array
    {
        return [
            'members_total' => $members->count(),
            'baptized' => $members->where('is_baptized', true)->count(),
            'communion' => $members->where('has_communion', true)->count(),
            'confirmation' => $members->where('has_confirmation', true)->count(),
            'married' => $members->where('is_married', true)->count(),
            'receives_eucharist' => $members->where('receives_eucharist', true)->count(),
            'male' => $members->filter(fn ($member) => strtolower((string) $member->gender) === 'male')->count(),
            'female' => $members->filter(fn ($member) => strtolower((string) $member->gender) === 'female')->count(),
        ];
    }

    protected function groupedSummary(Collection $members, string $groupBy): Collection
    {
        return $members->groupBy(function (Member $member) use ($groupBy) {
            return match ($groupBy) {
                'kanda' => $member->familia?->jumuiya?->kanda?->name ?? db_trans('unknown'),
                'jumuiya' => $member->familia?->jumuiya?->name ?? db_trans('unknown'),
                'familia' => $member->familia?->name ?? db_trans('unknown'),
                default => db_trans('parish'),
            };
        })->map(function (Collection $group, string $name) {
            return [
                'name' => $name,
                'members_total' => $group->count(),
                'baptized' => $group->where('is_baptized', true)->count(),
                'communion' => $group->where('has_communion', true)->count(),
                'confirmation' => $group->where('has_confirmation', true)->count(),
                'married' => $group->where('is_married', true)->count(),
                'receives_eucharist' => $group->where('receives_eucharist', true)->count(),
                'male' => $group->filter(fn ($member) => strtolower((string) $member->gender) === 'male')->count(),
                'female' => $group->filter(fn ($member) => strtolower((string) $member->gender) === 'female')->count(),
            ];
        })->sortBy('name')->values();
    }

    protected function ensureUserCanAccessFamilia(User $user, Familia $familia): void
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            throw new AuthorizationException($scope->reason ?? 'This account has an invalid scope.');
        }

        if ($scope->isGlobal()) {
            return;
        }

        $familia->loadMissing('jumuiya');

        if ($scope->isKanda() && (int) $familia->jumuiya?->kanda_id === (int) $scope->kandaId) {
            return;
        }

        if ($scope->isJumuiya() && (int) $familia->jumuiya_id === (int) $scope->jumuiyaId) {
            return;
        }

        throw new AuthorizationException('You are not allowed to access this familia report.');
    }

    protected function memberFilters(): array
    {
        return [
            'all',
            'baptized',
            'not_baptized',
            'married',
            'not_married',
            'confirmed',
            'not_confirmed',
            'communion',
            'not_communion',
            'eucharist',
            'not_eucharist',
            'male',
            'female',
        ];
    }
}
