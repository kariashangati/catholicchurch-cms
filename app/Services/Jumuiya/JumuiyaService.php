<?php

namespace App\Services\Jumuiya;

use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\Offering;
use App\Models\Tithe;
use App\Models\User;
use App\Models\ProjectTransaction;
use App\Services\Access\ScopeAccessGate;
use App\Services\Access\UserScopeResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class JumuiyaService
{
    public function __construct(
        protected UserScopeResolver $scopeResolver,
        protected ScopeAccessGate $scopeAccessGate,
    ) {
    }

    public function getIndexData(User $user): array
    {
        $jumuiyas = $this->scopedQuery($user)
            ->with('kanda')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Jumuiya $jumuiya) => $this->enrichJumuiyaOverview($jumuiya));

        $availableKandas = $this->availableKandasForUser($user);
        $topFinancialJumuiyas = $jumuiyas->sortByDesc('grand_total')->take(5)->values();
        $topMemberJumuiyas = $jumuiyas->sortByDesc('members_count')->take(5)->values();

        return [
            'pageTitle' => db_trans('jumuiyas'),
            'jumuiyas' => $jumuiyas,
            'kandas' => $availableKandas,
            'heroActions' => [
                'create' => $user->can('jumuiyas.create'),
                'export_pdf' => $user->can('jumuiyas.view'),
                'export_excel' => $user->can('jumuiyas.view'),
                'kandas' => $user->can('kandas.view'),
            ],
            'stats' => [
                'total_jumuiyas' => $jumuiyas->count(),
                'active_jumuiyas' => $jumuiyas->where('is_active', true)->count(),
                'inactive_jumuiyas' => $jumuiyas->where('is_active', false)->count(),
                'total_familias' => $jumuiyas->sum('familias_count'),
                'total_members' => $jumuiyas->sum('members_count'),
                'total_cash_contributions' => $jumuiyas->sum('cash_total'),
                'total_bank_contributions' => $jumuiyas->sum('bank_total'),
                'total_tithes' => $jumuiyas->sum('tithe_total'),
                'total_offerings' => $jumuiyas->sum('offering_total'),
                'grand_total' => $jumuiyas->sum('grand_total'),
            ],
            'chartData' => [
                'labels' => $jumuiyas->pluck('name')->values(),
                'familias' => $jumuiyas->pluck('familias_count')->map(fn ($value) => (int) $value)->values(),
                'members' => $jumuiyas->pluck('members_count')->map(fn ($value) => (int) $value)->values(),
                'cash' => $jumuiyas->pluck('cash_total')->map(fn ($value) => (float) $value)->values(),
                'bank' => $jumuiyas->pluck('bank_total')->map(fn ($value) => (float) $value)->values(),
                'tithes' => $jumuiyas->pluck('tithe_total')->map(fn ($value) => (float) $value)->values(),
                'offerings' => $jumuiyas->pluck('offering_total')->map(fn ($value) => (float) $value)->values(),
            ],
            'topFinancialJumuiyas' => $topFinancialJumuiyas,
            'topMemberJumuiyas' => $topMemberJumuiyas,
            'insights' => [
                'best_financial_jumuiya' => $topFinancialJumuiyas->first(),
                'largest_jumuiya' => $topMemberJumuiyas->first(),
                'most_active_jumuiya' => $jumuiyas->sortByDesc(fn ($jumuiya) => (
                    $jumuiya->cash_transactions_count
                    + $jumuiya->bank_transactions_count
                    + $jumuiya->tithe_transactions_count
                    + $jumuiya->offering_transactions_count
                ))->first(),
            ],
        ];
    }

    public function getShowData(Jumuiya $jumuiya, User $user, array $filters = []): array
    {
        $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);

        $jumuiya->load('kanda');

        $normalizedFilters = $this->normalizeShowFilters($jumuiya, $filters);

        [$from, $to] = $this->dateRangeFromYearMonth(
            $normalizedFilters['year'],
            $normalizedFilters['month']
        );

        $allFamiliasForFilter = Familia::query()
            ->where('jumuiya_id', $jumuiya->id)
            ->orderBy('name')
            ->get();

        $familias = Familia::query()
            ->where('jumuiya_id', $jumuiya->id)
            ->when($normalizedFilters['familia_id'], fn ($query) => $query->whereKey($normalizedFilters['familia_id']))
            ->withCount('members')
            ->orderBy('name')
            ->get();

        $familiaIds = $familias->pluck('id');

        $members = Member::query()
            ->whereIn('familia_id', $familiaIds)
            ->with('familia')
            ->latest()
            ->get();

        $memberIds = $members->pluck('id');
        $maleCount = $members->where('gender', 'Male')->count();
        $femaleCount = $members->where('gender', 'Female')->count();

        $tithes = Tithe::query()
            ->with(['member.familia'])
            ->where('jumuiya_id', $jumuiya->id)
            ->when($normalizedFilters['familia_id'], function ($query) use ($memberIds) {
                $query->whereIn('member_id', $memberIds);
            });

        $this->applyDateRange($tithes, 'contribution_date', $from, $to);
        $tithes = $tithes->latest('contribution_date')->get();

        $cashContributions = CashContribution::query()
            ->with(['contributionType', 'member.familia'])
            ->whereIn('member_id', $memberIds);

        $this->applyDateRange($cashContributions, 'contribution_date', $from, $to);
        $cashContributions = $cashContributions->latest('contribution_date')->get();

        $bankContributions = BankContribution::query()
            ->with(['contributionType', 'bankAccount', 'member.familia'])
            ->where('jumuiya_id', $jumuiya->id)
            ->when($normalizedFilters['familia_id'], function ($query) use ($memberIds) {
                $query->whereIn('member_id', $memberIds);
            });

        $this->applyDateRange($bankContributions, 'contribution_date', $from, $to);
        $bankContributions = $bankContributions->latest('contribution_date')->get();

        $offerings = Offering::query()
            ->with(['offeringType', 'massType'])
            ->where('collection_scope', Offering::SCOPE_JUMUIYA)
            ->where('jumuiya_id', $jumuiya->id);

        $this->applyDateRange($offerings, 'collection_date', $from, $to);
        $offerings = $offerings->latest('collection_date')->get();

        $projectTotal = $this->sumProjectMoneyForJumuiya($jumuiya, $from, $to);

        $financialSummary = [
            'tithe_total' => (float) $tithes->sum('amount'),
            'cash_total' => (float) $cashContributions->sum('amount'),
            'bank_total' => (float) $bankContributions->sum('amount'),
            'offering_total' => (float) $offerings->sum('amount'),
            'project_total' => (float) $projectTotal,
        ];

        $financialSummary['grand_total'] = array_sum($financialSummary);

        $recentTransactions = $this->buildRecentTransactions($tithes, $cashContributions, $bankContributions, $offerings);
        $bankAccountBreakdown = $this->buildBankAccountBreakdown($bankContributions);
        $contributionTypeBreakdown = $this->buildContributionTypeBreakdown($cashContributions, $bankContributions);
        $familiaFinancials = $this->buildFamiliaFinancials($familias, $members, $cashContributions, $bankContributions, $offerings, $tithes);

        return [
            'pageTitle' => db_trans('jumuiya_details'),
            'jumuiya' => $jumuiya,
            'filters' => $normalizedFilters,
            'filterOptions' => [
                'years' => range((int) now()->year - 5, (int) now()->year + 1),
                'months' => collect(range(1, 12))->map(fn ($month) => [
                    'value' => $month,
                    'label' => Carbon::create(null, $month, 1)->translatedFormat('F'),
                ])->values(),
                'familias' => $allFamiliasForFilter,
            ],
            'dateFilters' => [
                'from_date' => $from?->toDateString(),
                'to_date' => $to?->toDateString(),
                'period_label' => $this->formatDateRangeLabel($from, $to),
            ],
            'stats' => [
                'familias_count' => $familias->count(),
                'members_count' => $members->count(),
                'male_members_count' => $maleCount,
                'female_members_count' => $femaleCount,
                'active_familias_count' => $familias->where('is_active', true)->count(),
                'active_members_count' => $members->where('is_active', true)->count(),
                'tithe_total' => $financialSummary['tithe_total'],
                'cash_total' => $financialSummary['cash_total'],
                'bank_total' => $financialSummary['bank_total'],
                'offering_total' => $financialSummary['offering_total'],
                'project_total' => $financialSummary['project_total'],
                'grand_total' => $financialSummary['grand_total'],
            ],
            'financialSummary' => $financialSummary,
            'familias' => $familias,
            'recentMembers' => $members->take(10)->values(),
            'recentTransactions' => $recentTransactions,
            'bankAccountBreakdown' => $bankAccountBreakdown,
            'contributionTypeBreakdown' => $contributionTypeBreakdown,
            'familiaFinancials' => $familiaFinancials,
            'familiaRows' => $this->buildFamiliaTableRows($familias),
            'financialTrendChart' => $this->buildMonthlyFinanceTrendChart(
                $jumuiya,
                $normalizedFilters['year'],
                $normalizedFilters['familia_id'],
                $allFamiliasForFilter
            ),
            'chartData' => [
                'genderLabels' => [db_trans('male_members'), db_trans('female_members')],
                'genderData' => [$maleCount, $femaleCount],
                'familiaLabels' => $familiaFinancials->take(8)->pluck('name')->values(),
                'familiaTotals' => $familiaFinancials->take(8)->pluck('grand_total')->map(fn ($value) => (float) $value)->values(),
                'financeLabels' => [
                    db_trans('tithes'),
                    db_trans('cash_contributions'),
                    db_trans('bank_contributions'),
                    db_trans('offerings'),
                ],
                'financeData' => [
                    $financialSummary['tithe_total'],
                    $financialSummary['cash_total'],
                    $financialSummary['bank_total'],
                    $financialSummary['offering_total'],
                ],
            ],
            'showActions' => [
                'members' => $user->can('jumuiyas.members.view') && Route::has('jumuiyas.members.index')
                    ? route('jumuiyas.members.index', $jumuiya)
                    : null,
                'create_member' => $user->can('members.create') && Route::has('members.index')
                    ? route('members.index', ['open' => 'create'])
                    : null,
                'report' => $user->can('jumuiya-reports.view') && Route::has('jumuiya-reports.show')
                    ? route('jumuiya-reports.show', $jumuiya)
                    : null,
                'familias_pdf' => $user->can('jumuiyas.view') && Route::has('pdf.jumuiyas.familias.export')
                    ? route('pdf.jumuiyas.familias.export', $jumuiya)
                    : null,
                'familias_excel' => $user->can('jumuiyas.view') && Route::has('jumuiyas.familias.export.excel')
                    ? route('jumuiyas.familias.export.excel', $jumuiya)
                    : null,
                'back' => Route::has('jumuiyas.index') ? route('jumuiyas.index') : null,
            ],
        ];
    }

    public function getMembersData(Jumuiya $jumuiya, User $user): array
    {
        $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);

        $jumuiya->load('kanda');

        $familias = Familia::query()
            ->where('jumuiya_id', $jumuiya->id)
            ->orderBy('name')
            ->get();

        $members = Member::query()
            ->with('familia')
            ->whereIn('familia_id', $familias->pluck('id'))
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->orderBy('last_name')
            ->get();

        $familyRoleSummary = $members
            ->groupBy(fn (Member $member) => $member->family_role ?: db_trans('not_specified'))
            ->map(fn (Collection $group, string $role) => [
                'role' => $role,
                'count' => $group->count(),
            ])
            ->values()
            ->sortByDesc('count')
            ->take(6)
            ->values();

        return [
            'pageTitle' => db_trans('jumuiya_members'),
            'jumuiya' => $jumuiya,
            'members' => $members,
            'stats' => [
                'total_members' => $members->count(),
                'male_members' => $members->where('gender', 'Male')->count(),
                'female_members' => $members->where('gender', 'Female')->count(),
                'active_members' => $members->where('is_active', true)->count(),
                'inactive_members' => $members->where('is_active', false)->count(),
                'familias_count' => $familias->count(),
            ],
            'heroActions' => [
                'members' => $user->can('jumuiyas.members.view') && Route::has('jumuiyas.members.index')
                    ? route('jumuiyas.members.index', $jumuiya)
                    : null,
                'create_member' => $user->can('members.create') && Route::has('members.index')
                    ? route('members.index', ['open' => 'create'])
                    : null,
                'report' => $user->can('jumuiya-reports.view') && Route::has('jumuiya-reports.show')
                    ? route('jumuiya-reports.show', $jumuiya)
                    : null,
                'pdf' => $user->can('jumuiyas.members.view') && Route::has('pdf.jumuiyas.members.pdf')
                    ? route('pdf.jumuiyas.members.pdf', $jumuiya)
                    : null,
                'excel' => $user->can('jumuiyas.members.view') && Route::has('jumuiyas.members.excel')
                    ? route('jumuiyas.members.excel', $jumuiya)
                    : null,
                'back' => Route::has('jumuiyas.show')
                    ? route('jumuiyas.show', $jumuiya)
                    : null,
            ],
            'chartData' => [
                'genderLabels' => [db_trans('male_members'), db_trans('female_members')],
                'genderData' => [
                    $members->where('gender', 'Male')->count(),
                    $members->where('gender', 'Female')->count(),
                ],
                'familiaLabels' => $familias->take(8)->pluck('name')->values(),
                'familiaMemberCounts' => $familias->take(8)->map(
                    fn (Familia $familia) => (int) $members->where('familia_id', $familia->id)->count()
                )->values(),
                'roleLabels' => $familyRoleSummary->pluck('role')->values(),
                'roleData' => $familyRoleSummary->pluck('count')->map(fn ($value) => (int) $value)->values(),
            ],
        ];
    }

    public function getMembersExportPdfData(Jumuiya $jumuiya, User $user): array
    {
        $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);

        $jumuiya->load('kanda');

        $familias = Familia::query()
            ->where('jumuiya_id', $jumuiya->id)
            ->orderBy('name')
            ->get();

        $members = Member::query()
            ->with('familia')
            ->whereIn('familia_id', $familias->pluck('id'))
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->orderBy('last_name')
            ->get()
            ->map(function (Member $member, int $index) {
                return (object) [
                    'sn' => $index + 1,
                    'full_name' => $member->full_name ?? trim(
                        collect([$member->first_name, $member->middle_name, $member->last_name])
                            ->filter()
                            ->implode(' ')
                    ),
                    'member_code' => $member->member_code ?? '—',
                    'gender' => $member->gender ?? '—',
                    'phone' => $member->phone ?? '—',
                    'familia_name' => $member->familia?->name ?? '—',
                    'family_role' => $member->family_role ?? '—',
                    'joined_at' => optional($member->created_at)->format('M d, Y') ?: '—',
                ];
            })
            ->values();

        return [
            'pageTitle' => db_trans('jumuiya_members'),
            'reportTitle' => 'ORODHA YA WANAJUMUIYA WA ' . $jumuiya->name . ' KANDA YA ' . ($jumuiya->kanda?->name ?? '—'),
            'metaItems' => [
                [
                    'label' => db_trans('jumuiya'),
                    'value' => $jumuiya->name,
                ],
                [
                    'label' => db_trans('kanda'),
                    'value' => $jumuiya->kanda?->name ?? '—',
                ],
                [
                    'label' => db_trans('total_members'),
                    'value' => number_format($members->count()),
                ],
                [
                    'label' => db_trans('active_members'),
                    'value' => number_format(
                        $members->filter(fn ($member) => ($member->status ?? null) !== null ? ($member->status === 'active') : true)->count()
                    ),
                ],
            ],
            'rows' => $members,
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function getMembersExportRows(Jumuiya $jumuiya, User $user): Collection
    {
        $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);

        $familias = Familia::query()
            ->where('jumuiya_id', $jumuiya->id)
            ->orderBy('name')
            ->get();

        return Member::query()
            ->with('familia')
            ->whereIn('familia_id', $familias->pluck('id'))
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->orderBy('last_name')
            ->get()
            ->map(function (Member $member, int $index) {
                return [
                    'sn' => $index + 1,
                    'member' => $member->full_name ?? trim(
                        collect([$member->first_name, $member->middle_name, $member->last_name])
                            ->filter()
                            ->implode(' ')
                    ),
                    'member_code' => $member->member_code ?? '—',
                    'gender' => $member->gender ?? '—',
                    'phone' => $member->phone ?? '—',
                    'familia' => $member->familia?->name ?? '—',
                    'family_role' => $member->family_role ?? '—',
                    'joined' => optional($member->created_at)->format('M d, Y') ?: '—',
                ];
            })
            ->values();
    }

    public function getExportRows(User $user): Collection
    {
        return $this->scopedQuery($user)
            ->with('kanda')
            ->orderBy('name')
            ->get()
            ->map(function (Jumuiya $jumuiya) {
                $enriched = $this->enrichJumuiyaOverview($jumuiya);

                return (object) [
                    'name' => $enriched->name,
                    'kanda_name' => $enriched->kanda?->name ?? '—',
                    'familias_count' => (int) $enriched->familias_count,
                    'members_count' => (int) $enriched->members_count,
                    'status_label' => $enriched->is_active ? db_trans('active') : db_trans('inactive'),
                    'created_at_label' => optional($enriched->created_at)->format('M d, Y') ?: '—',
                    'is_active' => (bool) $enriched->is_active,
                ];
            })
            ->values();
    }

    public function getExportPdfData(User $user): array
    {
        $rows = $this->getExportRows($user);

        $topByMembers = $rows->sortByDesc('members_count')->take(8)->values();
        $topByFamilias = $rows->sortByDesc('familias_count')->take(8)->values();

        return [
            'pageTitle' => db_trans('jumuiya_list'),
            'reportTitle' => db_trans('jumuiya_list'),
            'metaItems' => [
                [
                    'label' => db_trans('total_jumuiyas'),
                    'value' => number_format($rows->count()),
                ],
                [
                    'label' => db_trans('members'),
                    'value' => number_format($rows->sum('members_count')),
                ],
                [
                    'label' => db_trans('familias'),
                    'value' => number_format($rows->sum('familias_count')),
                ],
                [
                    'label' => db_trans('status'),
                    'value' => db_trans('overview'),
                ],
            ],
            'rows' => $rows,
            'topByMembers' => $topByMembers,
            'topByFamilias' => $topByFamilias,
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function getFamiliaExportRows(Jumuiya $jumuiya, User $user, array $filters = []): Collection
    {
        $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);

        $familiaId = !empty($filters['familia_id']) ? (int) $filters['familia_id'] : null;

        if ($familiaId) {
            $exists = Familia::query()
                ->where('jumuiya_id', $jumuiya->id)
                ->whereKey($familiaId)
                ->exists();

            if (! $exists) {
                $familiaId = null;
            }
        }

        return Familia::query()
            ->where('jumuiya_id', $jumuiya->id)
            ->when($familiaId, fn ($query) => $query->whereKey($familiaId))
            ->withCount('members')
            ->orderBy('name')
            ->get()
            ->values()
            ->map(function (Familia $familia, int $index) {
                return (object) [
                    'sn' => $index + 1,
                    'familia' => $familia->name ?? '—',
                    'phone' => $familia->phone ?? '—',
                    'members' => (int) ($familia->members_count ?? 0),
                ];
            });
    }

    public function getFamiliaExportPdfData(Jumuiya $jumuiya, User $user, array $filters = []): array
    {
        $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);

        $jumuiya->loadMissing('kanda');

        $rows = $this->getFamiliaExportRows($jumuiya, $user, $filters);

        return [
            'pageTitle' => db_trans('familias'),
            'reportTitle' => db_trans('taarifa_za_familia_za_jumuiya_ya') . ' ' . $jumuiya->name,
            'jumuiya' => $jumuiya,
            'rows' => $rows,
            'metaItems' => [
                [
                    'label' => db_trans('jumuiya'),
                    'value' => $jumuiya->name,
                ],
                [
                    'label' => db_trans('kanda'),
                    'value' => $jumuiya->kanda?->name ?? '—',
                ],
                [
                    'label' => db_trans('familias'),
                    'value' => number_format($rows->count()),
                ],
                [
                    'label' => db_trans('members'),
                    'value' => number_format($rows->sum('members')),
                ],
            ],
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function ensureUserCanAccessJumuiya(User $user, Jumuiya $jumuiya): void
    {
        $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);
    }

    public function store(array $data): Jumuiya
    {
        $imagePath = $this->storeImage($data['image'] ?? null);

        return Jumuiya::create([
            'kanda_id' => $data['kanda_id'],
            'name' => Str::upper(trim($data['name'])),
            'slug' => Str::slug($data['name']),
            'comment' => $data['comment'] ?? null,
            'image' => $imagePath,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }

    public function update(Jumuiya $jumuiya, array $data): Jumuiya
    {
        $imagePath = $jumuiya->image;

        if (! empty($data['image'])) {
            if ($jumuiya->image && Storage::disk('public')->exists($jumuiya->image)) {
                Storage::disk('public')->delete($jumuiya->image);
            }

            $imagePath = $this->storeImage($data['image']);
        }

        $jumuiya->update([
            'kanda_id' => $data['kanda_id'],
            'name' => Str::upper(trim($data['name'])),
            'slug' => Str::slug($data['name']),
            'comment' => $data['comment'] ?? null,
            'image' => $imagePath,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return $jumuiya->refresh();
    }

    public function delete(Jumuiya $jumuiya): bool
    {
        if ($jumuiya->image && Storage::disk('public')->exists($jumuiya->image)) {
            Storage::disk('public')->delete($jumuiya->image);
        }

        return (bool) $jumuiya->delete();
    }

    public function availableKandasForUser(User $user)
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            return collect();
        }

        if ($scope->isGlobal()) {
            return Kanda::where('is_active', true)->orderBy('name')->get();
        }

        if ($scope->isKanda()) {
            return Kanda::where('id', $scope->kandaId)->where('is_active', true)->get();
        }

        if ($scope->isJumuiya() && $scope->kandaId) {
            return Kanda::where('id', $scope->kandaId)->where('is_active', true)->get();
        }

        return collect();
    }

    protected function scopedQuery(User $user): Builder
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

    protected function storeImage(?UploadedFile $image): ?string
    {
        if (! $image) {
            return null;
        }

        return $image->store('jumuiyas', 'public');
    }

    protected function enrichJumuiyaOverview(Jumuiya $jumuiya): Jumuiya
    {
        $familias = Familia::query()
            ->where('jumuiya_id', $jumuiya->id)
            ->get();

        $familiaIds = $familias->pluck('id');
        $members = Member::query()->whereIn('familia_id', $familiaIds)->get();
        $memberIds = $members->pluck('id');

        $cashContributions = CashContribution::query()->whereIn('member_id', $memberIds)->get();
        $bankContributions = BankContribution::query()->where('jumuiya_id', $jumuiya->id)->get();
        $tithes = Tithe::query()->where('jumuiya_id', $jumuiya->id)->get();
        $offerings = Offering::query()
            ->where('collection_scope', Offering::SCOPE_JUMUIYA)
            ->where('jumuiya_id', $jumuiya->id)
            ->get();

        $jumuiya->familias_count = $familias->count();
        $jumuiya->members_count = $members->count();
        $jumuiya->cash_total = (float) $cashContributions->sum('amount');
        $jumuiya->bank_total = (float) $bankContributions->sum('amount');
        $jumuiya->tithe_total = (float) $tithes->sum('amount');
        $jumuiya->offering_total = (float) $offerings->sum('amount');
        $jumuiya->grand_total = $jumuiya->cash_total + $jumuiya->bank_total + $jumuiya->tithe_total + $jumuiya->offering_total;
        $jumuiya->cash_transactions_count = $cashContributions->count();
        $jumuiya->bank_transactions_count = $bankContributions->count();
        $jumuiya->tithe_transactions_count = $tithes->count();
        $jumuiya->offering_transactions_count = $offerings->count();

        return $jumuiya;
    }

    protected function buildRecentTransactions(Collection $tithes, Collection $cashContributions, Collection $bankContributions, Collection $offerings): Collection
    {
        $items = collect();

        foreach ($tithes as $tithe) {
            $items->push((object) [
                'source' => db_trans('tithes'),
                'category' => db_trans('tithe'),
                'reference' => $tithe->receipt_no ?: $tithe->reference_no,
                'member_name' => $tithe->member?->full_name ?: '—',
                'familia_name' => $tithe->member?->familia?->name ?: '—',
                'amount' => (float) $tithe->amount,
                'date' => $tithe->contribution_date,
                'status' => $tithe->status ?: '—',
            ]);
        }

        foreach ($cashContributions as $contribution) {
            $items->push((object) [
                'source' => db_trans('cash_contributions'),
                'category' => $contribution->contributionType?->name ?: db_trans('cash_contribution'),
                'reference' => null,
                'member_name' => $contribution->member?->full_name ?: '—',
                'familia_name' => $contribution->member?->familia?->name ?: '—',
                'amount' => (float) $contribution->amount,
                'date' => $contribution->contribution_date,
                'status' => $contribution->status ?: '—',
            ]);
        }

        foreach ($bankContributions as $contribution) {
            $items->push((object) [
                'source' => db_trans('bank_contributions'),
                'category' => $contribution->contributionType?->name ?: db_trans('bank_contribution'),
                'reference' => $contribution->reference_no ?: $contribution->receipt_no,
                'member_name' => $contribution->member?->full_name ?: '—',
                'familia_name' => $contribution->member?->familia?->name ?: '—',
                'amount' => (float) $contribution->amount,
                'date' => $contribution->contribution_date,
                'status' => $contribution->status ?: '—',
            ]);
        }

        foreach ($offerings as $offering) {
            $items->push((object) [
                'source' => db_trans('offerings'),
                'category' => $offering->offeringType?->name ?: db_trans('offering'),
                'reference' => $offering->reference_no ?: $offering->receipt_no,
                'member_name' => $offering->massType?->name ?: db_trans('mass_collection'),
                'familia_name' => $offering->scope_label,
                'amount' => (float) $offering->amount,
                'date' => $offering->collection_date,
                'status' => $offering->status ?: '—',
            ]);
        }

        return $items
            ->sortByDesc(fn ($item) => optional($item->date)?->timestamp ?? 0)
            ->take(12)
            ->values();
    }

    protected function buildBankAccountBreakdown(Collection $bankContributions): Collection
    {
        return $bankContributions
            ->groupBy(fn ($item) => $item->bankAccount?->display_name ?: db_trans('unassigned_bank_account'))
            ->map(function (Collection $group, string $accountName) {
                return (object) [
                    'bank_account' => $accountName,
                    'transactions_count' => $group->count(),
                    'amount' => (float) $group->sum('amount'),
                ];
            })
            ->sortByDesc('amount')
            ->values();
    }

    protected function buildContributionTypeBreakdown(Collection $cashContributions, Collection $bankContributions): Collection
    {
        $typeRows = collect();

        $cashContributions
            ->groupBy(fn ($item) => $item->contributionType?->name ?: db_trans('cash_contribution'))
            ->each(function (Collection $group, string $typeName) use ($typeRows) {
                $typeRows->push([
                    'type_name' => $typeName,
                    'cash_total' => (float) $group->sum('amount'),
                    'bank_total' => 0.0,
                ]);
            });

        $bankContributions
            ->groupBy(fn ($item) => $item->contributionType?->name ?: db_trans('bank_contribution'))
            ->each(function (Collection $group, string $typeName) use ($typeRows) {
                $existingIndex = $typeRows->search(fn (array $row) => $row['type_name'] === $typeName);

                if ($existingIndex !== false) {
                    $row = $typeRows->get($existingIndex);
                    $row['bank_total'] = (float) $group->sum('amount');
                    $typeRows->put($existingIndex, $row);

                    return;
                }

                $typeRows->push([
                    'type_name' => $typeName,
                    'cash_total' => 0.0,
                    'bank_total' => (float) $group->sum('amount'),
                ]);
            });

        return $typeRows
            ->map(function (array $row) {
                return (object) [
                    'type_name' => $row['type_name'],
                    'cash_total' => $row['cash_total'],
                    'bank_total' => $row['bank_total'],
                    'grand_total' => $row['cash_total'] + $row['bank_total'],
                ];
            })
            ->sortByDesc('grand_total')
            ->values();
    }

    protected function buildFamiliaFinancials(Collection $familias, Collection $members, Collection $cashContributions, Collection $bankContributions, Collection $offerings, Collection $tithes): Collection
    {
        return $familias
            ->map(function (Familia $familia) use ($members, $cashContributions, $bankContributions, $offerings, $tithes) {
                $familiaMembers = $members->where('familia_id', $familia->id);
                $familiaMemberIds = $familiaMembers->pluck('id');

                $familiaCash = $cashContributions->whereIn('member_id', $familiaMemberIds);
                $familiaBank = $bankContributions->where('familia_id', $familia->id);
                $familiaTithes = $tithes->whereIn('member_id', $familiaMemberIds);
                $familia->tithe_total = (float) $familiaTithes->sum('amount');
                $familia->cash_total = (float) $familiaCash->sum('amount');
                $familia->bank_total = (float) $familiaBank->sum('amount');
                $familia->offering_total = 0.0;
                $familia->grand_total = $familia->tithe_total + $familia->cash_total + $familia->bank_total + $familia->offering_total;
                $familia->members_count = $familiaMembers->count();

                return $familia;
            })
            ->sortByDesc('grand_total')
            ->values();
    }

    protected function normalizeShowFilters(Jumuiya $jumuiya, array $filters): array
    {
        $year = isset($filters['year']) && $filters['year']
            ? (int) $filters['year']
            : (int) now()->year;

        $month = isset($filters['month']) && $filters['month'] !== ''
            ? (int) $filters['month']
            : null;

        if ($month && ($month < 1 || $month > 12)) {
            $month = null;
        }

        $familiaId = isset($filters['familia_id']) && $filters['familia_id'] !== ''
            ? (int) $filters['familia_id']
            : null;

        if ($familiaId) {
            $exists = Familia::where('jumuiya_id', $jumuiya->id)
                ->whereKey($familiaId)
                ->exists();

            if (! $exists) {
                $familiaId = null;
            }
        }

        return [
            'year' => $year,
            'month' => $month,
            'familia_id' => $familiaId,
        ];
    }

    protected function dateRangeFromYearMonth(int $year, ?int $month): array
    {
        if ($month) {
            return [
                Carbon::create($year, $month, 1)->startOfMonth(),
                Carbon::create($year, $month, 1)->endOfMonth(),
            ];
        }

        return [
            Carbon::create($year, 1, 1)->startOfYear(),
            Carbon::create($year, 12, 31)->endOfYear(),
        ];
    }

    protected function applyDateRange(Builder $query, string $column, ?Carbon $from, ?Carbon $to): Builder
    {
        if ($from) {
            $query->whereDate($column, '>=', $from->toDateString());
        }

        if ($to) {
            $query->whereDate($column, '<=', $to->toDateString());
        }

        return $query;
    }

    protected function formatDateRangeLabel(?Carbon $from, ?Carbon $to): string
    {
        if ($from && $to) {
            return $from->translatedFormat('d M Y') . ' - ' . $to->translatedFormat('d M Y');
        }

        if ($from) {
            return db_trans('from') . ' ' . $from->translatedFormat('d M Y');
        }

        if ($to) {
            return db_trans('up_to') . ' ' . $to->translatedFormat('d M Y');
        }

        return db_trans('all_time');
    }

    protected function buildFamiliaTableRows(Collection $familias): Collection
    {
        return $familias->map(function (Familia $familia) {
            return [
                'id' => $familia->id,
                'name' => $familia->name,
                'phone' => $familia->phone ?? '—',
                'members_count' => (int) ($familia->members_count ?? 0),
                'details_url' => Route::has('familias.show')
                    ? route('familias.show', $familia)
                    : url('/familias/' . $familia->id),
            ];
        })->values();
    }

    protected function buildMonthlyFinanceTrendChart(
        Jumuiya $jumuiya,
        int $year,
        ?int $familiaId,
        Collection $allFamilias
    ): array {
        $familiaIds = $allFamilias->pluck('id');

        if ($familiaId) {
            $familiaIds = collect([$familiaId]);
        }

        $memberIds = Member::whereIn('familia_id', $familiaIds)->pluck('id');

        $labels = [];
        $tithes = [];
        $offerings = [];
        $projects = [];
        $contributionSeries = [];

        $types = $this->getActiveContributionTypesForChart();

        foreach ($types as $type) {
            $contributionSeries[$type['slug']] = [
                'label' => $type['name'],
                'data' => [],
            ];
        }

        for ($month = 1; $month <= 12; $month++) {
            $labels[] = Carbon::create(null, $month, 1)->translatedFormat('M');

            $tithes[] = (float) Tithe::query()
                ->where('jumuiya_id', $jumuiya->id)
                ->when($familiaId, fn ($query) => $query->whereIn('member_id', $memberIds))
                ->whereYear('contribution_date', $year)
                ->whereMonth('contribution_date', $month)
                ->sum('amount');

            $offerings[] = (float) Offering::query()
                ->where('collection_scope', Offering::SCOPE_JUMUIYA)
                ->where('jumuiya_id', $jumuiya->id)
                ->whereYear('collection_date', $year)
                ->whereMonth('collection_date', $month)
                ->sum('amount');

            $projects[] = $this->sumProjectMoneyForJumuiyaMonth($jumuiya, $year, $month);

            foreach ($types as $type) {
                $cash = (float) CashContribution::query()
                    ->whereIn('member_id', $memberIds)
                    ->where('contribution_type_id', $type['id'])
                    ->whereYear('contribution_date', $year)
                    ->whereMonth('contribution_date', $month)
                    ->sum('amount');

                $bank = (float) BankContribution::query()
                    ->where('jumuiya_id', $jumuiya->id)
                    ->when($familiaId, fn ($query) => $query->whereIn('member_id', $memberIds))
                    ->where('contribution_type_id', $type['id'])
                    ->whereYear('contribution_date', $year)
                    ->whereMonth('contribution_date', $month)
                    ->sum('amount');

                $contributionSeries[$type['slug']]['data'][] = $cash + $bank;
            }
        }

        return [
            'labels' => $labels,
            'tithes' => $tithes,
            'offerings' => $offerings,
            'projects' => $projects,
            'contributions' => collect($contributionSeries)->values(),
        ];
    }

    protected function getActiveContributionTypesForChart(): Collection
    {
        if (! Schema::hasTable('contribution_types')) {
            return collect();
        }

        return \App\Models\ContributionType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn ($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'slug' => $type->slug,
            ])
            ->values();
    }

    protected function sumProjectMoneyForJumuiya(Jumuiya $jumuiya, ?Carbon $from, ?Carbon $to): float
    {
        if (! Schema::hasTable('project_transactions')) {
            return 0.0;
        }

        $query = ProjectTransaction::query()
            ->where('transaction_type', ProjectTransaction::TYPE_INCOME);

        $this->applyDateRange($query, 'transaction_date', $from, $to);

        return (float) $query->sum('amount');
    }

    protected function sumProjectMoneyForJumuiyaMonth(Jumuiya $jumuiya, int $year, int $month): float
    {
        if (! Schema::hasTable('project_transactions')) {
            return 0.0;
        }

        return (float) ProjectTransaction::query()
            ->where('transaction_type', ProjectTransaction::TYPE_INCOME)
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->sum('amount');
    }
}