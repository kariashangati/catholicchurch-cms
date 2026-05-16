<?php

namespace App\Services\Member;

use App\Models\CashContribution;
use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\Tithe;
use App\Models\User;
use App\Models\Offering;
use App\Services\Access\ScopeAccessGate;
use App\Services\Access\UserScopeResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MemberService
{
    public function __construct(
        protected UserScopeResolver $scopeResolver,
        protected ScopeAccessGate $scopeAccessGate,
    ) {
    }

    public function getIndexData(User $user, array $filters = []): array
    {
        $filters = $this->normalizeDashboardFilters($user, $filters);

        $membersQuery = $this->applyDashboardFilters($this->scopedMembersQuery($user), $filters);

        if (! empty($filters['status'])) {
            $membersQuery->where('members.is_active', $filters['status'] === 'active');
        }

        if (! empty($filters['family_role'])) {
            $roleValues = $this->familyRoleEquivalentValues($filters['family_role']);
            $membersQuery->whereIn('members.family_role', $roleValues);
        }

        if (! empty($filters['gender'])) {
            $genderValues = $this->genderEquivalentValues($filters['gender']);
            $membersQuery->whereIn('members.gender', $genderValues);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $membersQuery->where(function (Builder $query) use ($search) {
                $query->where('members.first_name', 'like', "%{$search}%")
                    ->orWhere('members.middle_name', 'like', "%{$search}%")
                    ->orWhere('members.last_name', 'like', "%{$search}%")
                    ->orWhere('members.phone', 'like', "%{$search}%")
                    ->orWhere('members.member_code', 'like', "%{$search}%")
                    ->orWhere('familias.name', 'like', "%{$search}%")
                    ->orWhere('jumuiyas.name', 'like', "%{$search}%")
                    ->orWhere('kandas.name', 'like', "%{$search}%");
            });
        }

        $members = (clone $membersQuery)
            ->with(['familia.jumuiya.kanda'])
            ->orderBy('members.first_name')
            ->orderBy('members.last_name')
            ->get();

        $baseStatsQuery = $this->applyDashboardFilters($this->scopedMembersBaseQuery($user), $filters);

        $totalMembers = (clone $baseStatsQuery)->count('members.id');

        $stats = [
            'total_members' => $totalMembers,
            'male_members' => (clone $baseStatsQuery)
                ->whereIn('members.gender', $this->genderEquivalentValues('mwanaume'))
                ->count('members.id'),
            'female_members' => (clone $baseStatsQuery)
                ->whereIn('members.gender', $this->genderEquivalentValues('mwanamke'))
                ->count('members.id'),
            'fathers' => (clone $baseStatsQuery)
                ->whereIn('members.family_role', $this->familyRoleEquivalentValues('baba'))
                ->count('members.id'),
            'mothers' => (clone $baseStatsQuery)
                ->whereIn('members.family_role', $this->familyRoleEquivalentValues('mama'))
                ->count('members.id'),
            'children' => (clone $baseStatsQuery)
                ->whereIn('members.family_role', $this->familyRoleEquivalentValues('mtoto'))
                ->count('members.id'),
            'active_members' => (clone $baseStatsQuery)->where('members.is_active', true)->count('members.id'),
            'inactive_members' => (clone $baseStatsQuery)->where('members.is_active', false)->count('members.id'),
            'new_members_this_month' => (clone $baseStatsQuery)
                ->whereBetween('members.created_at', [now()->copy()->startOfMonth(), now()->copy()->endOfMonth()])
                ->count('members.id'),
            'members_without_phone' => (clone $baseStatsQuery)
                ->where(function (Builder $query) {
                    $query->whereNull('members.phone')
                        ->orWhere('members.phone', '');
                })
                ->count('members.id'),
            'members_missing_sacrament_data' => (clone $baseStatsQuery)
                ->where(function (Builder $query) {
                    $query->whereNull('members.is_baptized')
                        ->orWhereNull('members.has_communion')
                        ->orWhereNull('members.has_confirmation')
                        ->orWhereNull('members.receives_eucharist')
                        ->orWhereNull('members.is_married');
                })
                ->count('members.id'),
            'baptized_members' => (clone $baseStatsQuery)->where('members.is_baptized', true)->count('members.id'),
            'communion_members' => (clone $baseStatsQuery)->where('members.has_communion', true)->count('members.id'),
            'confirmation_members' => (clone $baseStatsQuery)->where('members.has_confirmation', true)->count('members.id'),
            'eucharist_members' => (clone $baseStatsQuery)->where('members.receives_eucharist', true)->count('members.id'),
            'married_members' => (clone $baseStatsQuery)->where('members.is_married', true)->count('members.id'),
        ];

        $rangeStart = now()->copy()->startOfMonth()->subMonths(5);
        $rangeEnd = now()->copy()->endOfMonth();

        $monthlyGrowth = $this->applyDashboardFilters($this->scopedMembersBaseQuery($user), array_merge($filters, ['year' => null, 'month' => null]))
            ->selectRaw("DATE_FORMAT(members.created_at, '%b %Y') as label")
            ->selectRaw("DATE_FORMAT(members.created_at, '%Y-%m') as month_key")
            ->selectRaw('COUNT(members.id) as total')
            ->whereBetween('members.created_at', [$rangeStart, $rangeEnd])
            ->groupBy('month_key', 'label')
            ->orderBy('month_key')
            ->get();

        $chartLabels = collect(range(5, 0))
            ->map(fn ($monthsAgo) => $rangeEnd->copy()->subMonths($monthsAgo)->format('M Y'))
            ->values();

        $growthMap = $monthlyGrowth->pluck('total', 'label');

        $chartData = $chartLabels
            ->map(fn ($label) => (int) ($growthMap[$label] ?? 0))
            ->values();

        return [
            'pageTitle' => db_trans('members'),
            'members' => $members,
            'familias' => $this->availableFamiliasForUser($user, $filters),
            'kandas' => $this->availableKandasForUser($user),
            'jumuiyas' => $this->availableJumuiyasForUser($user, $filters),
            'filters' => $filters,
            'stats' => $stats,
            'familyRoles' => ['baba', 'mama', 'mtoto', 'nyingine'],
            'genders' => ['mwanaume', 'mwanamke'],
            'memberGrowthChart' => [
                'labels' => $chartLabels,
                'data' => $chartData,
            ],
            'sacramentOverview' => [
                ['key' => 'baptized_members', 'label' => db_trans('baptized'), 'value' => $stats['baptized_members']],
                ['key' => 'communion_members', 'label' => db_trans('communion'), 'value' => $stats['communion_members']],
                ['key' => 'confirmation_members', 'label' => db_trans('confirmation'), 'value' => $stats['confirmation_members']],
                ['key' => 'eucharist_members', 'label' => db_trans('eucharist'), 'value' => $stats['eucharist_members']],
                ['key' => 'married_members', 'label' => db_trans('married'), 'value' => $stats['married_members']],
            ],
            'sacramentOverviewChart' => [
                'labels' => [db_trans('baptized'), db_trans('communion'), db_trans('confirmation'), db_trans('eucharist'), db_trans('married')],
                'data' => [
                    (int) $stats['baptized_members'],
                    (int) $stats['communion_members'],
                    (int) $stats['confirmation_members'],
                    (int) $stats['eucharist_members'],
                    (int) $stats['married_members'],
                ],
            ],
        ];
    }

    public function getCreateData(User $user, array $filters = []): array
    {
        return [
            'pageTitle' => db_trans('add_member'),
            'familias' => $this->availableFamiliasForUser($user, $filters),
        ];
    }

    public function getEditData(Member $member, User $user): array
    {
        $this->scopeAccessGate->authorizeMember($user, $member);

        return [
            'pageTitle' => db_trans('edit_member'),
            'member' => $member->load('familia.jumuiya.kanda'),
            'familias' => $this->availableFamiliasForUser($user),
        ];
    }

    public function getShowData(Member $member, User $user): array
    {
        $this->scopeAccessGate->authorizeMember($user, $member);

        $member->load('familia.jumuiya.kanda');

        $currentYear = now()->year;
        $previousYear = now()->copy()->subYear()->year;

        $zakaCurrent = Tithe::where('member_id', $member->id)
            ->whereYear('contribution_date', $currentYear)
            ->sum('amount');

        $zakaPrevious = Tithe::where('member_id', $member->id)
            ->whereYear('contribution_date', $previousYear)
            ->sum('amount');

        $mavunoCurrent = CashContribution::where('member_id', $member->id)
            ->whereYear('contribution_date', $currentYear)
            ->whereHas('contributionType', fn ($q) => $q->where('slug', 'mavuno'))
            ->sum('amount');

        $mavunoPrevious = CashContribution::where('member_id', $member->id)
            ->whereYear('contribution_date', $previousYear)
            ->whereHas('contributionType', fn ($q) => $q->where('slug', 'mavuno'))
            ->sum('amount');

        $otherCurrent = CashContribution::where('member_id', $member->id)
            ->whereYear('contribution_date', $currentYear)
            ->whereHas('contributionType', fn ($q) => $q->where('slug', '!=', 'mavuno'))
            ->sum('amount');

        $otherPrevious = CashContribution::where('member_id', $member->id)
            ->whereYear('contribution_date', $previousYear)
            ->whereHas('contributionType', fn ($q) => $q->where('slug', '!=', 'mavuno'))
            ->sum('amount');

        $offeringCurrent = $this->memberApplicableOfferingsQuery($member)
            ->whereYear('collection_date', $currentYear)
            ->sum('amount');

        $offeringPrevious = $this->memberApplicableOfferingsQuery($member)
            ->whereYear('collection_date', $previousYear)
            ->sum('amount');

        return [
            'pageTitle' => db_trans('member_details'),
            'member' => $member,
            'currentYear' => $currentYear,
            'previousYear' => $previousYear,
            'finance' => [
                'zaka_current' => $zakaCurrent,
                'zaka_previous' => $zakaPrevious,
                'mavuno_current' => $mavunoCurrent,
                'mavuno_previous' => $mavunoPrevious,
                'other_current' => $otherCurrent,
                'other_previous' => $otherPrevious,
                'offering_current' => $offeringCurrent,
                'offering_previous' => $offeringPrevious,
                'total_current' => $zakaCurrent + $mavunoCurrent + $otherCurrent + $offeringCurrent,
                'total_previous' => $zakaPrevious + $mavunoPrevious + $otherPrevious + $offeringPrevious,
            ],
        ];
    }

    public function getProfilePdfData(Member $member, User $user): array
    {
        $showData = $this->getShowData($member, $user);

        $member = $showData['member'];
        $finance = $showData['finance'];

        return [
            'pageTitle' => db_trans('member_profile_report'),
            'reportTitle' => db_trans('taarifa_za') . ' ' . $member->full_name,
            'member' => $member,
            'currentYear' => $showData['currentYear'],
            'previousYear' => $showData['previousYear'],
            'finance' => $finance,
            'metaItems' => [
                ['label' => db_trans('member_code'), 'value' => $member->member_code ?: '—'],
                ['label' => db_trans('familia'), 'value' => $member->familia?->name ?: '—'],
                ['label' => db_trans('jumuiya'), 'value' => $member->familia?->jumuiya?->name ?: '—'],
                ['label' => db_trans('generated_on'), 'value' => now()->translatedFormat('d M Y')],
            ],
        ];
    }

    protected function memberApplicableOfferingsQuery(Member $member): Builder
    {
        $member->loadMissing('familia.jumuiya.kanda');

        $jumuiyaId = $member->familia?->jumuiya_id;
        $kandaId = $member->familia?->jumuiya?->kanda_id;

        return Offering::query()
            ->where('status', Offering::STATUS_APPROVED)
            ->where(function (Builder $query) use ($jumuiyaId, $kandaId) {
                $query->where('collection_scope', Offering::SCOPE_PARISH);

                if ($kandaId) {
                    $query->orWhere(function (Builder $q) use ($kandaId) {
                        $q->where('collection_scope', Offering::SCOPE_KANDA)
                            ->where('kanda_id', $kandaId);
                    });
                }

                if ($jumuiyaId) {
                    $query->orWhere(function (Builder $q) use ($jumuiyaId) {
                        $q->where('collection_scope', Offering::SCOPE_JUMUIYA)
                            ->where('jumuiya_id', $jumuiyaId);
                    });
                }
            });
    }

    public function store(array $data, User $user): Member
    {
        $familia = Familia::findOrFail($data['familia_id']);
        $this->scopeAccessGate->authorizeFamilia($user, $familia);

        $data['member_code'] = $data['member_code'] ?? $this->generateMemberCode($familia);

        $data['gender'] = $this->normalizeGenderForStorage($data['gender'] ?? null);
        $data['family_role'] = $this->normalizeFamilyRoleForStorage($data['family_role'] ?? null);

        return Member::create($data);
    }

    public function update(Member $member, array $data, User $user): Member
    {
        $this->scopeAccessGate->authorizeMember($user, $member);

        if (! empty($data['familia_id'])) {
            $familia = Familia::findOrFail($data['familia_id']);
            $this->scopeAccessGate->authorizeFamilia($user, $familia);
        }

        $data['gender'] = $this->normalizeGenderForStorage($data['gender'] ?? null);
        $data['family_role'] = $this->normalizeFamilyRoleForStorage($data['family_role'] ?? null);

        $member->update($data);

        return $member->refresh();
    }

    public function delete(Member $member, User $user): bool
    {
        $this->scopeAccessGate->authorizeMember($user, $member);

        return (bool) $member->delete();
    }

    public function getExportRows(User $user, array $filters = []): Collection
    {
        $filters = $this->normalizeDashboardFilters($user, $filters);
        $query = $this->applyDashboardFilters($this->scopedMembersQuery($user), $filters);

        if (! empty($filters['status'])) {
            $query->where('members.is_active', $filters['status'] === 'active');
        }

        if (! empty($filters['family_role'])) {
            $query->whereIn('members.family_role', $this->familyRoleEquivalentValues($filters['family_role']));
        }

        if (! empty($filters['gender'])) {
            $query->whereIn('members.gender', $this->genderEquivalentValues($filters['gender']));
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function (Builder $query) use ($search) {
                $query->where('members.first_name', 'like', "%{$search}%")
                    ->orWhere('members.middle_name', 'like', "%{$search}%")
                    ->orWhere('members.last_name', 'like', "%{$search}%")
                    ->orWhere('members.phone', 'like', "%{$search}%")
                    ->orWhere('members.member_code', 'like', "%{$search}%")
                    ->orWhere('familias.name', 'like', "%{$search}%")
                    ->orWhere('jumuiyas.name', 'like', "%{$search}%")
                    ->orWhere('kandas.name', 'like', "%{$search}%");
            });
        }

        return $query
            ->with('familia.jumuiya.kanda')
            ->orderBy('kandas.name')
            ->orderBy('jumuiyas.name')
            ->orderBy('members.first_name')
            ->orderBy('members.middle_name')
            ->orderBy('members.last_name')
            ->get()
            ->values()
            ->map(function (Member $member, int $index) {
                $sacraments = collect([
                    $member->is_baptized ? db_trans('baptized_short') : null,
                    $member->has_communion ? db_trans('communion_short') : null,
                    $member->has_confirmation ? db_trans('confirmation_short') : null,
                    $member->receives_eucharist ? db_trans('eucharist_short') : null,
                    $member->is_married ? db_trans('married_short') : null,
                ])->filter()->implode(', ');

                return (object) [
                    'sn' => $index + 1,
                    'name' => $member->full_name,
                    'member_code' => $member->member_code ?: '—',
                    'phone' => $member->phone ?: '—',
                    'familia_name' => $member->familia?->name ?? '—',
                    'jumuiya_name' => $member->familia?->jumuiya?->name ?? '—',
                    'kanda_name' => $member->familia?->jumuiya?->kanda?->name ?? '—',
                    'sacraments' => $sacraments ?: '—',
                    'status' => $member->is_active ? db_trans('active') : db_trans('inactive'),
                ];
            });
    }

    public function getExportPdfData(User $user, array $filters = []): array
    {
        $rows = $this->getExportRows($user, $filters);

        return [
            'pageTitle' => db_trans('members'),
            'reportTitle' => db_trans('waumini_katika_parokia_ya') . ' ' . config('app.name'),
            'rows' => $rows,
            'metaItems' => [
                ['label' => db_trans('members'), 'value' => number_format($rows->count())],
                ['label' => db_trans('kanda'), 'value' => number_format($rows->pluck('kanda_name')->filter(fn ($v) => $v !== '—')->unique()->count())],
                ['label' => db_trans('jumuiya'), 'value' => number_format($rows->pluck('jumuiya_name')->filter(fn ($v) => $v !== '—')->unique()->count())],
                ['label' => db_trans('generated_on'), 'value' => now()->translatedFormat('d M Y')],
            ],
        ];
    }

    protected function normalizeDashboardFilters(User $user, array $filters = []): array
    {
        $scope = $this->scopeResolver->resolve($user);

        $normalized = [
            'year' => ! empty($filters['year']) ? (int) $filters['year'] : now()->year,
            'month' => ! empty($filters['month']) ? (int) $filters['month'] : null,
            'kanda_id' => ! empty($filters['kanda_id']) ? (int) $filters['kanda_id'] : null,
            'jumuiya_id' => ! empty($filters['jumuiya_id']) ? (int) $filters['jumuiya_id'] : null,
            'status' => $filters['status'] ?? null,
            'family_role' => $filters['family_role'] ?? null,
            'gender' => $filters['gender'] ?? null,
            'search' => $filters['search'] ?? null,
        ];

        if ($normalized['month'] && ($normalized['month'] < 1 || $normalized['month'] > 12)) {
            $normalized['month'] = null;
        }

        if ($scope->isKanda()) {
            $normalized['kanda_id'] = (int) $scope->kandaId;
        }

        if ($scope->isJumuiya()) {
            $normalized['jumuiya_id'] = (int) $scope->jumuiyaId;
            $jumuiya = Jumuiya::query()->find($scope->jumuiyaId);
            $normalized['kanda_id'] = $jumuiya?->kanda_id ? (int) $jumuiya->kanda_id : $normalized['kanda_id'];
        }

        if ($normalized['jumuiya_id']) {
            $jumuiya = Jumuiya::query()->find($normalized['jumuiya_id']);
            if ($jumuiya && ! $normalized['kanda_id']) {
                $normalized['kanda_id'] = (int) $jumuiya->kanda_id;
            }
        }

        return $normalized;
    }

    protected function applyDashboardFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['year'])) {
            $query->whereYear('members.created_at', (int) $filters['year']);
        }

        if (! empty($filters['month'])) {
            $query->whereMonth('members.created_at', (int) $filters['month']);
        }

        if (! empty($filters['kanda_id'])) {
            $query->where('jumuiyas.kanda_id', (int) $filters['kanda_id']);
        }

        if (! empty($filters['jumuiya_id'])) {
            $query->where('familias.jumuiya_id', (int) $filters['jumuiya_id']);
        }

        return $query;
    }

    protected function availableKandasForUser(User $user): Collection
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            return collect();
        }

        $query = Kanda::query()->where('is_active', true)->orderBy('name');

        if ($scope->isKanda()) {
            $query->where('id', $scope->kandaId);
        }

        if ($scope->isJumuiya()) {
            $jumuiya = Jumuiya::query()->find($scope->jumuiyaId);
            $query->where('id', $jumuiya?->kanda_id ?: 0);
        }

        return $query->get();
    }

    protected function availableJumuiyasForUser(User $user, array $filters = []): Collection
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            return collect();
        }

        $query = Jumuiya::query()->with('kanda')->where('is_active', true)->orderBy('name');

        if ($scope->isKanda()) {
            $query->where('kanda_id', $scope->kandaId);
        } elseif ($scope->isJumuiya()) {
            $query->where('id', $scope->jumuiyaId);
        } elseif (! empty($filters['kanda_id'])) {
            $query->where('kanda_id', (int) $filters['kanda_id']);
        }

        return $query->get();
    }

    protected function scopedMembersQuery(User $user): Builder
    {
        return $this->scopedMembersBaseQuery($user)->select('members.*');
    }

    protected function scopedMembersBaseQuery(User $user): Builder
    {
        $scope = $this->scopeResolver->resolve($user);

        $query = Member::query()
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

    protected function availableFamiliasForUser(User $user, array $filters = []): Collection
    {
        $scope = $this->scopeResolver->resolve($user);

        $query = Familia::query()
            ->with('jumuiya.kanda')
            ->orderBy('name');

        if (! empty($filters['jumuiya_id'])) {
            $query->where('jumuiya_id', $filters['jumuiya_id']);
        }

        if ($scope->isInvalid()) {
            return collect();
        }

        if ($scope->isGlobal()) {
            return $query->get();
        }

        if ($scope->isKanda()) {
            return $query->whereHas('jumuiya', function ($q) use ($scope) {
                $q->where('kanda_id', $scope->kandaId);
            })->get();
        }

        if ($scope->isJumuiya()) {
            return $query->where('jumuiya_id', $scope->jumuiyaId)->get();
        }

        return collect();
    }

    protected function normalizeGenderForStorage(?string $value): ?string
    {
        $value = Str::lower(trim((string) $value));

        return match ($value) {
            'male', 'mwanaume' => 'mwanaume',
            'female', 'mwanamke' => 'mwanamke',
            default => filled($value) ? $value : null,
        };
    }

    protected function normalizeFamilyRoleForStorage(?string $value): ?string
    {
        $value = Str::lower(trim((string) $value));

        return match ($value) {
            'father', 'baba' => 'baba',
            'mother', 'mama' => 'mama',
            'child', 'mtoto' => 'mtoto',
            'other', 'nyingine' => 'nyingine',
            default => filled($value) ? $value : null,
        };
    }

    protected function genderEquivalentValues(?string $value): array
    {
        $value = Str::lower(trim((string) $value));

        return match ($value) {
            'male', 'mwanaume' => ['Male', 'male', 'mwanaume'],
            'female', 'mwanamke' => ['Female', 'female', 'mwanamke'],
            default => filled($value) ? [$value] : [],
        };
    }

    protected function familyRoleEquivalentValues(?string $value): array
    {
        $value = Str::lower(trim((string) $value));

        return match ($value) {
            'father', 'baba' => ['Father', 'father', 'baba'],
            'mother', 'mama' => ['Mother', 'mother', 'mama'],
            'child', 'mtoto' => ['Child', 'child', 'mtoto'],
            'other', 'nyingine' => ['Other', 'other', 'nyingine'],
            default => filled($value) ? [$value] : [],
        };
    }

    protected function generateMemberCode(Familia $familia): string
    {
        $familia->loadMissing('jumuiya.kanda');
        $prefix = optional($familia->jumuiya?->kanda)->code ?: 'MBR';
        $latestId = (Member::max('id') ?? 0) + 1;

        return strtoupper($prefix) . str_pad((string) $latestId, 5, '0', STR_PAD_LEFT);
    }
}