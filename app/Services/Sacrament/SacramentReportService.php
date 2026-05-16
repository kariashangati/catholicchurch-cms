<?php

namespace App\Services\Sacrament;

use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\User;
use App\Services\Access\ScopeAccessGate;
use App\Services\Access\UserScopeResolver;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SacramentReportService
{
    public function __construct(
        protected UserScopeResolver $scopeResolver,
        protected ScopeAccessGate $scopeAccessGate,
    ) {
    }

    public function getDashboardData(User $user, array $filters = []): array
    {
        $filteredMembers = $this->applyMemberFilters(
            $this->scopedMembersQuery($user),
            $filters
        );

        $stats = $this->buildOverallStats($user, $filteredMembers);
        $kandaRows = $this->buildKandaRows($user, $filters);
        $jumuiyaRows = $this->buildJumuiyaRows($user, $filters);
        $alerts = $this->buildDataQualityAlerts($user);

        return [
            'pageTitle' => db_trans('sacraments'),
            'filters' => $filters,
            'stats' => $stats,
            'rates' => $this->buildRates($stats),
            'kandaRows' => $kandaRows->take(8),
            'jumuiyaRows' => $jumuiyaRows->take(8),
            'alerts' => $alerts,
            'distributionChart' => [
                'labels' => [
                    db_trans('baptized'),
                    db_trans('communion'),
                    db_trans('confirmation'),
                    db_trans('married'),
                    db_trans('receiving_eucharist'),
                ],
                'data' => [
                    $stats['baptized'],
                    $stats['communion'],
                    $stats['confirmation'],
                    $stats['married'],
                    $stats['eucharist'],
                ],
            ],
            'kandaChart' => [
                'labels' => $kandaRows->take(8)->pluck('name')->values(),
                'baptized' => $kandaRows->take(8)->pluck('baptized')->values(),
                'communion' => $kandaRows->take(8)->pluck('communion')->values(),
                'confirmation' => $kandaRows->take(8)->pluck('confirmation')->values(),
            ],
        ];
    }

    public function getKandaIndexData(User $user, array $filters = []): array
    {
        $rows = $this->buildKandaRows($user, $filters);

        return [
            'pageTitle' => db_trans('kanda_sacrament_summary'),
            'filters' => $filters,
            'rows' => $rows,
            'stats' => [
                'total_kandas' => $rows->count(),
                'total_members' => $rows->sum('members'),
                'baptized' => $rows->sum('baptized'),
                'confirmation' => $rows->sum('confirmation'),
                'communion' => $rows->sum('communion'),
                'married' => $rows->sum('married'),
                'eucharist' => $rows->sum('eucharist'),
            ],
            'chartData' => [
                'labels' => $rows->pluck('name')->values(),
                'members' => $rows->pluck('members')->values(),
                'baptized' => $rows->pluck('baptized')->values(),
                'confirmation' => $rows->pluck('confirmation')->values(),
            ],
        ];
    }

    public function getKandaShowData(Kanda $kanda, User $user, array $filters = []): array
    {
        $this->scopeAccessGate->authorizeKanda($user, $kanda);

        $summaryRows = $this->buildJumuiyaRowsForKanda($kanda, $filters);
        $memberQuery = $this->applyMemberFilters(
            $this->membersForKanda($kanda, $user),
            $filters
        );

        $members = $this->paginateMembers($memberQuery);
        $stats = $this->buildMemberStats($memberQuery);

        return [
            'pageTitle' => db_trans('kanda_sacrament_report'),
            'filters' => $filters,
            'kanda' => $kanda->loadMissing('jumuiyas'),
            'stats' => $stats,
            'rates' => $this->buildRates($stats),
            'rows' => $summaryRows,
            'members' => $members,
            'chartData' => [
                'labels' => $summaryRows->pluck('name')->values(),
                'baptized' => $summaryRows->pluck('baptized')->values(),
                'communion' => $summaryRows->pluck('communion')->values(),
                'confirmation' => $summaryRows->pluck('confirmation')->values(),
                'married' => $summaryRows->pluck('married')->values(),
                'eucharist' => $summaryRows->pluck('eucharist')->values(),
            ],
        ];
    }

    public function getJumuiyaIndexData(User $user, array $filters = []): array
    {
        $rows = $this->buildJumuiyaRows($user, $filters);

        return [
            'pageTitle' => db_trans('jumuiya_sacrament_summary'),
            'filters' => $filters,
            'rows' => $rows,
            'stats' => [
                'total_jumuiyas' => $rows->count(),
                'total_members' => $rows->sum('members'),
                'baptized' => $rows->sum('baptized'),
                'confirmation' => $rows->sum('confirmation'),
                'communion' => $rows->sum('communion'),
                'married' => $rows->sum('married'),
                'eucharist' => $rows->sum('eucharist'),
            ],
            'chartData' => [
                'labels' => $rows->take(10)->pluck('name')->values(),
                'members' => $rows->take(10)->pluck('members')->values(),
                'baptized' => $rows->take(10)->pluck('baptized')->values(),
                'confirmation' => $rows->take(10)->pluck('confirmation')->values(),
            ],
        ];
    }

    public function getJumuiyaShowData(Jumuiya $jumuiya, User $user, array $filters = []): array
    {
        $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);

        $memberQuery = $this->applyMemberFilters(
            $this->membersForJumuiya($jumuiya, $user),
            $filters
        );

        $members = $this->paginateMembers($memberQuery);
        $stats = $this->buildMemberStats($memberQuery);

        $familias = Familia::query()
            ->where('jumuiya_id', $jumuiya->id)
            ->withCount('members')
            ->orderBy('name')
            ->get();

        return [
            'pageTitle' => db_trans('jumuiya_sacrament_report'),
            'filters' => $filters,
            'jumuiya' => $jumuiya->loadMissing('kanda'),
            'stats' => $stats,
            'rates' => $this->buildRates($stats),
            'members' => $members,
            'familias' => $familias,
            'chartData' => [
                'labels' => [
                    db_trans('baptized'),
                    db_trans('communion'),
                    db_trans('confirmation'),
                    db_trans('married'),
                    db_trans('receiving_eucharist'),
                ],
                'data' => [
                    $stats['baptized'],
                    $stats['communion'],
                    $stats['confirmation'],
                    $stats['married'],
                    $stats['eucharist'],
                ],
            ],
        ];
    }

    protected function buildOverallStats(User $user, Builder $filteredMembers): array
    {
        $baseForCounts = $this->applyMemberFilters(
            $this->scopedMembersQuery($user),
            []
        );

        return [
            'total_members' => (clone $filteredMembers)->count(),
            'baptized' => (clone $filteredMembers)->where('members.is_baptized', true)->count(),
            'communion' => (clone $filteredMembers)->where('members.has_communion', true)->count(),
            'confirmation' => (clone $filteredMembers)->where('members.has_confirmation', true)->count(),
            'married' => (clone $filteredMembers)->where('members.is_married', true)->count(),
            'eucharist' => (clone $filteredMembers)->where('members.receives_eucharist', true)->count(),
            'kandas' => $this->scopedKandasQuery($user)->count(),
            'jumuiyas' => $this->scopedJumuiyasQuery($user)->count(),
            'familias' => $this->scopedFamiliasQuery($user)->count(),
            'active_members' => (clone $baseForCounts)->where('members.is_active', true)->count(),
        ];
    }

    protected function buildMemberStats(Builder $query): array
    {
        return [
            'total_members' => (clone $query)->count(),
            'baptized' => (clone $query)->where('members.is_baptized', true)->count(),
            'communion' => (clone $query)->where('members.has_communion', true)->count(),
            'confirmation' => (clone $query)->where('members.has_confirmation', true)->count(),
            'married' => (clone $query)->where('members.is_married', true)->count(),
            'eucharist' => (clone $query)->where('members.receives_eucharist', true)->count(),
            'male_members' => (clone $query)
                ->whereIn(DB::raw('LOWER(members.gender)'), ['male', 'mwanaume'])
                ->count(),
            'female_members' => (clone $query)
                ->whereIn(DB::raw('LOWER(members.gender)'), ['female', 'mwanamke'])
                ->count(),
            'active_members' => (clone $query)->where('members.is_active', true)->count(),
        ];
    }

    protected function buildRates(array $stats): array
    {
        $total = max((int) ($stats['total_members'] ?? 0), 1);

        return [
            'baptized_rate' => round((($stats['baptized'] ?? 0) / $total) * 100, 1),
            'communion_rate' => round((($stats['communion'] ?? 0) / $total) * 100, 1),
            'confirmation_rate' => round((($stats['confirmation'] ?? 0) / $total) * 100, 1),
            'marriage_rate' => round((($stats['married'] ?? 0) / $total) * 100, 1),
            'eucharist_rate' => round((($stats['eucharist'] ?? 0) / $total) * 100, 1),
        ];
    }

    protected function buildKandaRows(User $user, array $filters = []): Collection
    {
        return $this->scopedKandasQuery($user)
            ->orderBy('name')
            ->get()
            ->map(function (Kanda $kanda) use ($user, $filters) {
                $memberQuery = $this->applyMemberFilters(
                    $this->membersForKanda($kanda, $user),
                    $filters
                );

                $members = (clone $memberQuery)->count();

                return collect([
                    'id' => $kanda->id,
                    'name' => $kanda->name,
                    'code' => $kanda->code,
                    'jumuiyas' => Jumuiya::where('kanda_id', $kanda->id)->count(),
                    'familias' => Familia::whereHas('jumuiya', fn ($q) => $q->where('kanda_id', $kanda->id))->count(),
                    'members' => $members,
                    'baptized' => (clone $memberQuery)->where('members.is_baptized', true)->count(),
                    'communion' => (clone $memberQuery)->where('members.has_communion', true)->count(),
                    'confirmation' => (clone $memberQuery)->where('members.has_confirmation', true)->count(),
                    'married' => (clone $memberQuery)->where('members.is_married', true)->count(),
                    'eucharist' => (clone $memberQuery)->where('members.receives_eucharist', true)->count(),
                    'is_active' => (bool) $kanda->is_active,
                    'baptized_rate' => $members > 0 ? round(((clone $memberQuery)->where('members.is_baptized', true)->count() / $members) * 100, 1) : 0,
                    'confirmation_rate' => $members > 0 ? round(((clone $memberQuery)->where('members.has_confirmation', true)->count() / $members) * 100, 1) : 0,
                ]);
            });
    }

    protected function buildJumuiyaRows(User $user, array $filters = []): Collection
    {
        return $this->scopedJumuiyasQuery($user)
            ->with('kanda')
            ->orderBy('name')
            ->get()
            ->map(function (Jumuiya $jumuiya) use ($user, $filters) {
                $memberQuery = $this->applyMemberFilters(
                    $this->membersForJumuiya($jumuiya, $user),
                    $filters
                );

                $members = (clone $memberQuery)->count();

                return collect([
                    'id' => $jumuiya->id,
                    'name' => $jumuiya->name,
                    'kanda_name' => $jumuiya->kanda?->name,
                    'familias' => Familia::where('jumuiya_id', $jumuiya->id)->count(),
                    'members' => $members,
                    'baptized' => (clone $memberQuery)->where('members.is_baptized', true)->count(),
                    'communion' => (clone $memberQuery)->where('members.has_communion', true)->count(),
                    'confirmation' => (clone $memberQuery)->where('members.has_confirmation', true)->count(),
                    'married' => (clone $memberQuery)->where('members.is_married', true)->count(),
                    'eucharist' => (clone $memberQuery)->where('members.receives_eucharist', true)->count(),
                    'is_active' => (bool) $jumuiya->is_active,
                    'baptized_rate' => $members > 0 ? round(((clone $memberQuery)->where('members.is_baptized', true)->count() / $members) * 100, 1) : 0,
                    'confirmation_rate' => $members > 0 ? round(((clone $memberQuery)->where('members.has_confirmation', true)->count() / $members) * 100, 1) : 0,
                ]);
            });
    }

    protected function buildJumuiyaRowsForKanda(Kanda $kanda, array $filters = []): Collection
    {
        return Jumuiya::query()
            ->where('kanda_id', $kanda->id)
            ->with('kanda')
            ->orderBy('name')
            ->get()
            ->map(function (Jumuiya $jumuiya) use ($filters) {
                $memberQuery = $this->applyMemberFilters(
                    Member::query()
                        ->select('members.*')
                        ->leftJoin('familias', 'familias.id', '=', 'members.familia_id')
                        ->where('familias.jumuiya_id', $jumuiya->id),
                    $filters
                );

                $members = (clone $memberQuery)->count();

                return collect([
                    'id' => $jumuiya->id,
                    'name' => $jumuiya->name,
                    'familias' => Familia::where('jumuiya_id', $jumuiya->id)->count(),
                    'members' => $members,
                    'baptized' => (clone $memberQuery)->where('members.is_baptized', true)->count(),
                    'communion' => (clone $memberQuery)->where('members.has_communion', true)->count(),
                    'confirmation' => (clone $memberQuery)->where('members.has_confirmation', true)->count(),
                    'married' => (clone $memberQuery)->where('members.is_married', true)->count(),
                    'eucharist' => (clone $memberQuery)->where('members.receives_eucharist', true)->count(),
                ]);
            });
    }

    protected function buildDataQualityAlerts(User $user): array
    {
        $base = $this->scopedMembersQuery($user);

        return [
            'missing_date_of_birth' => (clone $base)->whereNull('members.date_of_birth')->count(),
            'missing_family_role' => (clone $base)->where(function ($query) {
                $query->whereNull('members.family_role')
                    ->orWhere('members.family_role', '');
            })->count(),
            'baptized_without_parish' => (clone $base)
                ->where('members.is_baptized', true)
                ->where(function ($q) {
                    $q->whereNull('members.baptism_parish')
                        ->orWhere('members.baptism_parish', '');
                })->count(),
            'married_without_type' => (clone $base)
                ->where('members.is_married', true)
                ->where(function ($q) {
                    $q->whereNull('members.marriage_type')
                        ->orWhere('members.marriage_type', '');
                })->count(),
        ];
    }

    protected function applyMemberFilters(Builder $query, array $filters): Builder
    {
        if (($filters['active_only'] ?? null) === '1') {
            $query->where('members.is_active', true);
        }

        if (! empty($filters['gender'])) {
            $gender = strtolower(trim((string) $filters['gender']));

            $acceptedValues = match ($gender) {
                'male', 'mwanaume' => ['male', 'mwanaume'],
                'female', 'mwanamke' => ['female', 'mwanamke'],
                default => [$gender],
            };

            $query->whereIn(DB::raw('LOWER(members.gender)'), $acceptedValues);
        }

        if (! empty($filters['sacrament']) && $filters['sacrament'] !== 'all') {
            match ($filters['sacrament']) {
                'baptized' => $query->where('members.is_baptized', true),
                'communion' => $query->where('members.has_communion', true),
                'confirmation' => $query->where('members.has_confirmation', true),
                'married' => $query->where('members.is_married', true),
                'eucharist' => $query->where('members.receives_eucharist', true),
                default => null,
            };
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('members.first_name', 'like', "%{$search}%")
                    ->orWhere('members.middle_name', 'like', "%{$search}%")
                    ->orWhere('members.last_name', 'like', "%{$search}%")
                    ->orWhere('members.member_code', 'like', "%{$search}%")
                    ->orWhere('members.phone', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    protected function paginateMembers(Builder $query): LengthAwarePaginator
    {
        return $query
            ->with(['familia.jumuiya.kanda'])
            ->orderBy('members.first_name')
            ->orderBy('members.last_name')
            ->paginate(20)
            ->withQueryString();
    }

    protected function scopedMembersQuery(User $user): Builder
    {
        $scope = $this->scopeResolver->resolve($user);

        $query = Member::query()
            ->select('members.*')
            ->leftJoin('familias', 'familias.id', '=', 'members.familia_id')
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'familias.jumuiya_id')
            ->leftJoin('kandas', 'kandas.id', '=', 'jumuiyas.kanda_id');

        if ($scope->isInvalid()) {
            return $query->whereRaw('1 = 0');
        }

        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->where('jumuiyas.kanda_id', $scope->kandaId);
        }

        if ($scope->isJumuiya()) {
            return $query->where('familias.jumuiya_id', $scope->jumuiyaId);
        }

        return $query->whereRaw('1 = 0');
    }

    protected function scopedKandasQuery(User $user): Builder
    {
        $scope = $this->scopeResolver->resolve($user);

        $query = Kanda::query();

        if ($scope->isInvalid()) {
            return $query->whereRaw('1 = 0');
        }

        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->whereKey($scope->kandaId);
        }

        if ($scope->isJumuiya()) {
            return $scope->kandaId
                ? $query->whereKey($scope->kandaId)
                : $query->whereRaw('1 = 0');
        }

        return $query->whereRaw('1 = 0');
    }

    protected function scopedJumuiyasQuery(User $user): Builder
    {
        $scope = $this->scopeResolver->resolve($user);

        $query = Jumuiya::query();

        if ($scope->isInvalid()) {
            return $query->whereRaw('1 = 0');
        }

        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->where('kanda_id', $scope->kandaId);
        }

        if ($scope->isJumuiya()) {
            return $query->whereKey($scope->jumuiyaId);
        }

        return $query->whereRaw('1 = 0');
    }

    protected function scopedFamiliasQuery(User $user): Builder
    {
        $scope = $this->scopeResolver->resolve($user);

        $query = Familia::query()
            ->select('familias.*')
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'familias.jumuiya_id');

        if ($scope->isInvalid()) {
            return $query->whereRaw('1 = 0');
        }

        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->where('jumuiyas.kanda_id', $scope->kandaId);
        }

        if ($scope->isJumuiya()) {
            return $query->where('familias.jumuiya_id', $scope->jumuiyaId);
        }

        return $query->whereRaw('1 = 0');
    }
	
	
	public function getKandaExportRows(User $user, array $filters = []): Collection
{
    return $this->buildKandaRows($user, $filters)
        ->values()
        ->map(function (Collection $row, int $index) {
            return (object) [
                'sn' => $index + 1,
                'name' => $row['name'],
                'code' => $row['code'] ?: '—',
                'jumuiyas' => (int) $row['jumuiyas'],
                'familias' => (int) $row['familias'],
                'members' => (int) $row['members'],
                'baptized' => (int) $row['baptized'],
                'communion' => (int) $row['communion'],
                'confirmation' => (int) $row['confirmation'],
                'married' => (int) $row['married'],
                'eucharist' => (int) $row['eucharist'],
            ];
        });
}

public function getKandaExportPdfData(User $user, array $filters = []): array
{
    $rows = $this->getKandaExportRows($user, $filters);

    return [
        'pageTitle' => db_trans('kanda_sacrament_summary'),
        'reportTitle' => db_trans('taarifa_za_sakramenti_za_kanda_zote'),
        'rows' => $rows,
        'metaItems' => [
            ['label' => db_trans('kandas'), 'value' => number_format($rows->count())],
            ['label' => db_trans('members'), 'value' => number_format($rows->sum('members'))],
            ['label' => db_trans('baptized'), 'value' => number_format($rows->sum('baptized'))],
            ['label' => db_trans('generated_on'), 'value' => now()->translatedFormat('d M Y')],
        ],
    ];
}


public function getKandaMemberExportRows(Kanda $kanda, User $user, array $filters = []): Collection
{
    $this->scopeAccessGate->authorizeKanda($user, $kanda);

    return $this->applyMemberFilters(
            $this->membersForKanda($kanda, $user),
            $filters
        )
        ->with(['familia.jumuiya.kanda'])
        ->orderBy('jumuiyas.name')
        ->orderBy('familias.name')
        ->orderBy('members.first_name')
        ->orderBy('members.middle_name')
        ->orderBy('members.last_name')
        ->get()
        ->values()
        ->map(function (Member $member, int $index) {
            return (object) [
                'sn' => $index + 1,
                'member' => $member->full_name,
                'member_code' => $member->member_code ?: '—',
                'familia' => $member->familia?->name ?: '—',
                'jumuiya' => $member->familia?->jumuiya?->name ?: '—',
                'gender' => $this->localizedGender($member->gender),
                'baptized' => $member->is_baptized ? db_trans('yes') : db_trans('no'),
                'communion' => $member->has_communion ? db_trans('yes') : db_trans('no'),
                'confirmation' => $member->has_confirmation ? db_trans('yes') : db_trans('no'),
                'married' => $member->is_married ? db_trans('yes') : db_trans('no'),
                'eucharist' => $member->receives_eucharist ? db_trans('yes') : db_trans('no'),
            ];
        });
}

public function getKandaMemberExportPdfData(Kanda $kanda, User $user, array $filters = []): array
{
    $rows = $this->getKandaMemberExportRows($kanda, $user, $filters);

    return [
        'pageTitle' => db_trans('member_sacrament_details'),
        'reportTitle' => db_trans('taarifa_za_sakramenti_za_waumini_wa_kanda_ya') . ' ' . $kanda->name,
        'kanda' => $kanda,
        'rows' => $rows,
        'metaItems' => [
            ['label' => db_trans('kanda'), 'value' => $kanda->name],
            ['label' => db_trans('members'), 'value' => number_format($rows->count())],
            ['label' => db_trans('baptized'), 'value' => number_format($rows->where('baptized', db_trans('yes'))->count())],
            ['label' => db_trans('generated_on'), 'value' => now()->translatedFormat('d M Y')],
        ],
    ];
}

public function getJumuiyaExportRows(User $user, array $filters = []): Collection
{
    return $this->buildJumuiyaRows($user, $filters)
        ->values()
        ->map(function (Collection $row, int $index) {
            return (object) [
                'sn' => $index + 1,
                'name' => $row['name'],
                'kanda_name' => $row['kanda_name'] ?: '—',
                'familias' => (int) $row['familias'],
                'members' => (int) $row['members'],
                'baptized' => (int) $row['baptized'],
                'communion' => (int) $row['communion'],
                'confirmation' => (int) $row['confirmation'],
                'married' => (int) $row['married'],
                'eucharist' => (int) $row['eucharist'],
            ];
        });
}

public function getJumuiyaExportPdfData(User $user, array $filters = []): array
{
    $rows = $this->getJumuiyaExportRows($user, $filters);

    return [
        'pageTitle' => db_trans('jumuiya_sacrament_summary'),
        'reportTitle' => db_trans('jumuiya_sacrament_summary'),
        'rows' => $rows,
        'metaItems' => [
            ['label' => db_trans('jumuiyas'), 'value' => number_format($rows->count())],
            ['label' => db_trans('members'), 'value' => number_format($rows->sum('members'))],
            ['label' => db_trans('baptized'), 'value' => number_format($rows->sum('baptized'))],
            ['label' => db_trans('generated_on'), 'value' => now()->translatedFormat('d M Y')],
        ],
    ];
}


public function getJumuiyaMemberExportRows(Jumuiya $jumuiya, User $user, array $filters = []): Collection
{
    $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);

    return $this->applyMemberFilters(
            $this->membersForJumuiya($jumuiya, $user),
            $filters
        )
        ->with(['familia.jumuiya.kanda'])
        ->orderBy('familias.name')
        ->orderBy('members.first_name')
        ->orderBy('members.middle_name')
        ->orderBy('members.last_name')
        ->get()
        ->values()
        ->map(function (Member $member, int $index) {
            return (object) [
                'sn' => $index + 1,
                'member' => $member->full_name,
                'member_code' => $member->member_code ?: '—',
                'familia' => $member->familia?->name ?: '—',
                'gender' => $this->localizedGender($member->gender),
                'phone' => $member->phone ?: '—',
                'baptized' => $member->is_baptized ? db_trans('yes') : db_trans('no'),
                'communion' => $member->has_communion ? db_trans('yes') : db_trans('no'),
                'confirmation' => $member->has_confirmation ? db_trans('yes') : db_trans('no'),
                'married' => $member->is_married ? db_trans('yes') : db_trans('no'),
                'eucharist' => $member->receives_eucharist ? db_trans('yes') : db_trans('no'),
            ];
        });
}

public function getJumuiyaMemberExportPdfData(Jumuiya $jumuiya, User $user, array $filters = []): array
{
    $jumuiya->loadMissing('kanda');

    $rows = $this->getJumuiyaMemberExportRows($jumuiya, $user, $filters);

    return [
        'pageTitle' => db_trans('member_sacrament_details'),
        'reportTitle' => db_trans('taarifa_za_sakramenti_za_jumuiya_ya') . ' ' . $jumuiya->name,
        'jumuiya' => $jumuiya,
        'rows' => $rows,
        'metaItems' => [
            ['label' => db_trans('jumuiya'), 'value' => $jumuiya->name],
            ['label' => db_trans('kanda'), 'value' => $jumuiya->kanda?->name ?: '—'],
            ['label' => db_trans('members'), 'value' => number_format($rows->count())],
            ['label' => db_trans('generated_on'), 'value' => now()->translatedFormat('d M Y')],
        ],
    ];
}

protected function localizedGender(?string $gender): string
{
    return match (strtolower(trim((string) $gender))) {
        'male', 'mwanaume' => db_trans('male'),
        'female', 'mwanamke' => db_trans('female'),
        default => $gender ?: '—',
    };
}

    protected function membersForKanda(Kanda $kanda, User $user): Builder
    {
        $this->scopeAccessGate->authorizeKanda($user, $kanda);

        return Member::query()
            ->select('members.*')
            ->leftJoin('familias', 'familias.id', '=', 'members.familia_id')
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'familias.jumuiya_id')
            ->where('jumuiyas.kanda_id', $kanda->id);
    }

    protected function membersForJumuiya(Jumuiya $jumuiya, User $user): Builder
    {
        $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);

        return Member::query()
            ->select('members.*')
            ->leftJoin('familias', 'familias.id', '=', 'members.familia_id')
            ->where('familias.jumuiya_id', $jumuiya->id);
    }
}