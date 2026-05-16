<?php

namespace App\Services\Kanda;

use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\ContributionType;
use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\Offering;
use App\Models\Tithe;
use App\Models\User;
use App\Services\Access\ScopeAccessGate;
use App\Services\Access\UserScopeResolver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class KandaService
{
    public function __construct(
        protected UserScopeResolver $scopeResolver,
        protected ScopeAccessGate $scopeAccessGate,
    ) {
    }

    // ========== NEW HELPER METHODS (Guide A) ==========
    protected function resolveRouteIfAuthorized(User $user, string $permission, string $routeName, mixed $parameter = null): ?string
    {
        if (! $user->can($permission) || ! Route::has($routeName)) {
            return null;
        }

        return $parameter !== null
            ? route($routeName, $parameter)
            : route($routeName);
    }

    protected function makeCard(
        User $user,
        string $permission,
        ?string $routeName,
        array $card
    ): array {
        $route = null;

        if ($routeName && Route::has($routeName) && $user->can($permission)) {
            $route = route($routeName);
        }

        $card['route'] = $route;

        return $card;
    }
    // ========== END NEW HELPERS ==========

    public function getIndexData(User $user): array
    {
        $kandas = $this->scopedKandasQuery($user)
            ->withCount('jumuiyas')
            ->latest()
            ->get();

        $hierarchy = $this->preloadHierarchy($kandas);
        $jumuiyas = $hierarchy['jumuiyas'];
        $familias = $hierarchy['familias'];
        $members = $hierarchy['members'];

        $breakdownContributionTypes = $this->getBreakdownContributionTypes();

        $kandas = $kandas->map(function (Kanda $kanda) use ($jumuiyas, $familias, $members, $breakdownContributionTypes) {
            $summary = $this->buildKandaSummary($kanda, $jumuiyas, $familias, $members);

            $kandaJumuiyas = $jumuiyas->where('kanda_id', $kanda->id);
            $kandaFamilias = $familias->whereIn('jumuiya_id', $kandaJumuiyas->pluck('id'));
            $kandaMembers = $members->whereIn('familia_id', $kandaFamilias->pluck('id'));
            $kandaMemberIds = $kandaMembers->pluck('id');

            $kanda->familias_count = $summary['familias_count'];
            $kanda->members_count = $summary['members_count'];
            $kanda->male_members_count = $summary['male_members_count'];
            $kanda->female_members_count = $summary['female_members_count'];
            $kanda->tithe_total = $summary['tithe_total'];
            $kanda->mavuno_total = $summary['mavuno_total'];
            $kanda->cash_total = $summary['cash_total'];
            $kanda->bank_total = $summary['bank_total'];
            $kanda->offering_total = $summary['offering_total'];
            $kanda->grand_total = $summary['grand_total'];
            $kanda->contribution_type_totals = $this->getContributionTypeTotalsForMembers($kandaMemberIds, $breakdownContributionTypes);

            return $kanda;
        });

        $stats = [
            'total_kandas' => $kandas->count(),
            'active_kandas' => $kandas->where('is_active', true)->count(),
            'total_jumuiyas' => $kandas->sum('jumuiyas_count'),
            'total_familias' => $kandas->sum('familias_count'),
            'total_members' => $kandas->sum('members_count'),
            'male_members' => $kandas->sum('male_members_count'),
            'female_members' => $kandas->sum('female_members_count'),
            'total_tithes' => (float) $kandas->sum('tithe_total'),
            'total_mavuno' => (float) $kandas->sum('mavuno_total'),
            'total_cash_contributions' => (float) $kandas->sum('cash_total'),
            'total_bank_contributions' => (float) $kandas->sum('bank_total'),
            'total_offerings' => (float) $kandas->sum('offering_total'),
            'grand_total' => (float) $kandas->sum('grand_total'),
        ];

        return [
            'pageTitle' => db_trans('kandas'),
            'hero' => $this->getHero($user, $stats),
            'heroLinks' => $this->getHeroLinks($user),
            'stats' => $stats,
            'breakdownContributionTypes' => $breakdownContributionTypes,
            'summaryCards' => $this->getSummaryCards($stats, $user),
            'financeCards' => $this->getFinanceCards($stats, $user),
            'quickLinks' => $this->getQuickLinks($user),
            'chartActions' => $this->getChartActions($user),
            'chartData' => [
                'labels' => $kandas->pluck('name')->values(),
                'members' => $kandas->pluck('members_count')->map(fn ($v) => (int) $v)->values(),
                'jumuiyas' => $kandas->pluck('jumuiyas_count')->map(fn ($v) => (int) $v)->values(),
                'familias' => $kandas->pluck('familias_count')->map(fn ($v) => (int) $v)->values(),
            ],
            'financeByKandaChart' => [
                'labels' => $kandas->pluck('name')->values(),
                'tithes' => $kandas->pluck('tithe_total')->map(fn ($v) => (float) $v)->values(),
                'offerings' => $kandas->pluck('offering_total')->map(fn ($v) => (float) $v)->values(),
                'cash' => $kandas->pluck('cash_total')->map(fn ($v) => (float) $v)->values(),
                'bank' => $kandas->pluck('bank_total')->map(fn ($v) => (float) $v)->values(),
            ],
            'monthlyFinanceChart' => $this->getMonthlyFinanceChartForVisibleKandas($kandas),
            'financeMixChart' => [
                'labels' => [
                    db_trans('tithes'),
                    db_trans('offerings'),
                    db_trans('cash_contributions'),
                    db_trans('bank_contributions'),
                ],
                'values' => [
                    (float) $stats['total_tithes'],
                    (float) $stats['total_offerings'],
                    (float) $stats['total_cash_contributions'],
                    (float) $stats['total_bank_contributions'],
                ],
            ],
            'contributionTypeChart' => $this->getContributionTypeChartForVisibleKandas($members),
            'kandas' => $kandas,
            'breakdownSummaryRows' => $this->getBreakdownSummaryRows($kandas, $breakdownContributionTypes),
            'kandaBreakdown' => $this->buildKandaBreakdown($kandas, $jumuiyas, $familias, $members, $breakdownContributionTypes),
            'liveFeed' => $this->getLiveFeedForVisibleKandas($jumuiyas, $familias, $members),
            'alerts' => $this->getAlertsForVisibleKandas($user, $jumuiyas, $familias, $members),
        ];
    }

    public function store(array $data): Kanda
    {
        return Kanda::create([
            'name' => Str::upper(trim($data['name'])),
            'code' => Str::upper(trim($data['code'])),
            'slug' => Str::slug($data['name']),
            'comment' => $data['comment'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
    }



public function getSingleBreakdownPdfData(Kanda $kanda, User $user, ?string $fromDate, ?string $toDate): array
{
    $this->scopeAccessGate->authorizeKanda($user, $kanda);

    [$from, $to] = $this->normalizeDateRange($fromDate, $toDate);

    $jumuiyas = Jumuiya::where('kanda_id', $kanda->id)->orderBy('name')->get();
    $familias = Familia::whereIn('jumuiya_id', $jumuiyas->pluck('id'))->get();
    $members = Member::whereIn('familia_id', $familias->pluck('id'))->get();

    $types = $this->getBreakdownContributionTypes();

    $jumuiyaRows = collect();
    $jumuiyaSerial = 1;

    $totals = [
        'tithes' => 0.0,
        'offerings' => 0.0,
        'contribution_type_totals' => [],
        'grand_total' => 0.0,
    ];

    foreach ($types as $type) {
        $totals['contribution_type_totals'][$type['slug']] = [
            'id' => $type['id'],
            'name' => $type['name'],
            'slug' => $type['slug'],
            'amount' => 0.0,
        ];
    }

    foreach ($jumuiyas as $jumuiya) {
        $jumuiyaFamilias = $familias->where('jumuiya_id', $jumuiya->id);
        $jumuiyaMembers = $members->whereIn('familia_id', $jumuiyaFamilias->pluck('id'));
        $memberIds = $jumuiyaMembers->pluck('id');

        $tithes = $this->sumTithesForJumuiyaIds(collect([$jumuiya->id]), $from, $to);
        $offerings = $this->sumOfferingsForScope(
            collect([$jumuiya->id]),
            collect([$kanda->id]),
            $from,
            $to,
            false
        );

        $typeTotals = $this->getContributionTypeTotalsForMembersByDate($memberIds, $types, $from, $to);
        $dynamicTotal = collect($typeTotals)->sum(fn ($row) => (float) ($row['amount'] ?? 0));
        $grandTotal = $tithes + $offerings + $dynamicTotal;

        foreach ($types as $type) {
            $totals['contribution_type_totals'][$type['slug']]['amount'] += (float) ($typeTotals[$type['slug']]['amount'] ?? 0);
        }

        $totals['tithes'] += $tithes;
        $totals['offerings'] += $offerings;
        $totals['grand_total'] += $grandTotal;

        $jumuiyaRows->push([
            'sn' => $jumuiyaSerial++,
            'id' => $jumuiya->id,
            'name' => $jumuiya->name,
            'familias_count' => $jumuiyaFamilias->count(),
            'members_count' => $jumuiyaMembers->count(),
            'tithes' => $tithes,
            'offerings' => $offerings,
            'contribution_type_totals' => $typeTotals,
            'grand_total' => $grandTotal,
        ]);
    }

    $matrixRow = [
        'sn' => 1,
        'id' => $kanda->id,
        'name' => $kanda->name,
        'code' => $kanda->code,
        'jumuiyas_count' => $jumuiyas->count(),
        'jumuiya_rows' => $jumuiyaRows->values(),
        'totals' => $totals,
    ];

    return [
        'pageTitle' => db_trans('kanda_and_jumuiya_breakdown'),
        'reportTitle' => db_trans('kanda_and_jumuiya_breakdown') . ' - ' . $kanda->name,
        'scopeLabel' => $this->resolveScopeLabel($user),
        'kanda' => $kanda,
        'breakdownContributionTypes' => $types,
        'pdfContributionHeading' => $types->pluck('name')->values(),
        'pdfMatrixRows' => collect([$matrixRow]),
        'kandaBreakdownRows' => collect([$matrixRow]),
        'globalTotals' => $totals,
        'pdfCharts' => [
            'top_kandas' => collect([
                ['label' => $kanda->name, 'value' => (float) $totals['grand_total']],
            ]),
            'top_jumuiyas' => $jumuiyaRows
                ->sortByDesc('grand_total')
                ->take(5)
                ->map(fn ($row) => [
                    'label' => $row['name'],
                    'value' => (float) $row['grand_total'],
                ])->values(),
            'income_mix' => collect([
                ['label' => db_trans('offerings'), 'value' => (float) $totals['offerings']],
                ['label' => db_trans('tithes'), 'value' => (float) $totals['tithes']],
            ])->merge(
                $types->map(fn ($type) => [
                    'label' => $type['name'],
                    'value' => (float) ($totals['contribution_type_totals'][$type['slug']]['amount'] ?? 0),
                ])
            )->filter(fn ($row) => $row['value'] > 0)->values(),
        ],
        'dateFilters' => [
            'from_date' => $from?->toDateString(),
            'to_date' => $to?->toDateString(),
            'period_label' => $this->formatDateRangeLabel($from, $to),
        ],
        'issuedAtText' => now()->translatedFormat('d F Y'),
    ];
}

    public function update(Kanda $kanda, array $data): Kanda
    {
        $kanda->update([
            'name' => Str::upper(trim($data['name'])),
            'code' => Str::upper(trim($data['code'])),
            'slug' => Str::slug($data['name']),
            'comment' => $data['comment'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return $kanda->refresh();
    }

    public function delete(Kanda $kanda): bool
    {
        return (bool) $kanda->delete();
    }

    public function authorizeKandaAccess(Kanda $kanda, User $user): void
    {
        $this->scopeAccessGate->authorizeKanda($user, $kanda);
    }

    public function getShowData(Kanda $kanda, User $user, array $filters = []): array
    {
        $this->scopeAccessGate->authorizeKanda($user, $kanda);

        $normalizedFilters = $this->normalizeShowFilters($kanda, $filters);

        [$from, $to] = $this->dateRangeFromYearMonth(
            $normalizedFilters['year'],
            $normalizedFilters['month']
        );

        $jumuiyas = Jumuiya::where('kanda_id', $kanda->id)
            ->when($normalizedFilters['jumuiya_id'], fn ($query) => $query->whereKey($normalizedFilters['jumuiya_id']))
            ->orderBy('name')
            ->get();

        $allJumuiyasForFilter = Jumuiya::where('kanda_id', $kanda->id)
            ->orderBy('name')
            ->get();

        $familias = Familia::whereIn('jumuiya_id', $jumuiyas->pluck('id'))->get();
        $members = Member::whereIn('familia_id', $familias->pluck('id'))->get();

        $summary = $this->buildKandaSummaryByDate($kanda, $jumuiyas, $familias, $members, $from, $to);

        $breakdownContributionTypes = $this->getBreakdownContributionTypes();

        $jumuiyasBreakdown = $this->buildJumuiyaBreakdownByDate(
            $kanda,
            $jumuiyas,
            $familias,
            $members,
            $breakdownContributionTypes,
            $from,
            $to
        );

        $monthlyChart = $this->getMonthlyFinanceChartByFilters(
            $kanda,
            $normalizedFilters['year'],
            $normalizedFilters['jumuiya_id'],
            $breakdownContributionTypes
        );

        $membersByJumuiyaChart = $this->getMembersByJumuiyaChart($jumuiyas, $familias, $members);
        $recentMembers = $this->getRecentMembersByFilters($kanda, $normalizedFilters['jumuiya_id']);

        $summaryCards = [
            [
                'title' => db_trans('jumuiyas'),
                'value' => number_format($summary['jumuiyas_count']),
                'icon' => 'fas fa-layer-group',
                'tone' => 'primary',
                'route' => $this->resolveRouteIfAuthorized($user, 'jumuiyas.view', 'jumuiyas.index'),
            ],
            [
                'title' => db_trans('familias'),
                'value' => number_format($summary['familias_count']),
                'icon' => 'fas fa-home',
                'tone' => 'info',
                'route' => $this->resolveRouteIfAuthorized($user, 'familias.view', 'familias.index'),
            ],
            [
                'title' => db_trans('members'),
                'value' => number_format($summary['members_count']),
                'icon' => 'fas fa-users',
                'tone' => 'success',
                'route' => $this->resolveRouteIfAuthorized($user, 'members.view', 'members.index'),
            ],
            [
                'title' => db_trans('grand_total'),
                'value' => number_format($summary['grand_total'], 2),
                'icon' => 'fas fa-chart-line',
                'tone' => 'dark',
                'route' => $user->can('finance.view') ? url('/finance') : null,
            ],
        ];

        return [
            'pageTitle' => db_trans('kanda_details'),
            'kanda' => $kanda,
            'hero' => [
                'eyebrow' => db_trans('kanda_details'),
                'title' => $kanda->name,
                'subtitle' => db_trans('code') . ': ' . $kanda->code . ' · ' . ($kanda->is_active ? db_trans('active') : db_trans('inactive')),
                'scope_badge' => db_trans('kanda_scope'),
                'today' => now()->translatedFormat('l, d M Y'),
            ],
            'filters' => $normalizedFilters,
            'filterOptions' => [
                'years' => range((int) now()->year - 5, (int) now()->year + 1),
                'months' => collect(range(1, 12))->map(fn ($month) => [
                    'value' => $month,
                    'label' => Carbon::create(null, $month, 1)->translatedFormat('F'),
                ])->values(),
                'jumuiyas' => $allJumuiyasForFilter,
            ],
            'dateFilters' => [
                'from_date' => $from?->toDateString(),
                'to_date' => $to?->toDateString(),
                'period_label' => $this->formatDateRangeLabel($from, $to),
            ],
            'stats' => $summary,
            'summaryCards' => $summaryCards,
            'breakdownContributionTypes' => $breakdownContributionTypes,
            'jumuiyas' => $jumuiyasBreakdown,
            'monthlyChart' => $monthlyChart,
            'membersByJumuiyaChart' => $membersByJumuiyaChart,
            'recentMembers' => $recentMembers,
            'showActions' => [
                'back' => Route::has('kandas.index') ? route('kandas.index') : '#',
                'edit' => $this->resolveRouteIfAuthorized($user, 'kandas.update', 'kandas.edit', $kanda),
                'report' => $this->resolveRouteIfAuthorized($user, 'kanda-reports.view', 'kanda-reports.show', $kanda),
                'pdf_export' => $user->can('kanda-reports.view') && Route::has('pdf.kanda-reports.kanda-breakdown')
                    ? route('pdf.kanda-reports.kanda-breakdown', $kanda)
                    : null,
                'excel_export' => $user->can('kandas.view') && Route::has('kandas.export.single.excel')
                    ? route('kandas.export.single.excel', $kanda)
                    : null,
                'recent_members_pdf_export' => $user->can('kandas.view') && Route::has('pdf.kandas.recent-members.export')
                    ? route('pdf.kandas.recent-members.export', $kanda)
                    : null,
                'recent_members_excel_export' => $user->can('kandas.view') && Route::has('kandas.recent-members.export.excel')
                    ? route('kandas.recent-members.export.excel', $kanda)
                    : null,
            ],
        ];
    }

    // ========== REPLACED buildJumuiyaBreakdown (Guide C) ==========
    protected function buildJumuiyaBreakdown(
        Kanda $kanda,
        Collection $jumuiyas,
        Collection $familias,
        Collection $members,
        Collection $types
    ): Collection {
        $kandaJumuiyas = $jumuiyas->where('kanda_id', $kanda->id)->sortBy('name')->values();

        return $kandaJumuiyas->map(function (Jumuiya $jumuiya) use ($familias, $members, $types) {
            $jumuiyaFamilias = $familias->where('jumuiya_id', $jumuiya->id);
            $jumuiyaMembers = $members->whereIn('familia_id', $jumuiyaFamilias->pluck('id'));
            $memberIds = $jumuiyaMembers->pluck('id');

            $titheTotal = Schema::hasTable('tithes')
                ? (float) Tithe::where('jumuiya_id', $jumuiya->id)->sum('amount')
                : 0.0;

            $offeringTotal = Schema::hasTable('offerings')
                ? (float) Offering::where('jumuiya_id', $jumuiya->id)->sum('amount')
                : 0.0;

            $typeTotals = $this->getContributionTypeTotalsForMembers($memberIds, $types);
            $dynamicTotal = collect($typeTotals)->sum(fn ($row) => (float) ($row['amount'] ?? 0));

            return [
                'id' => $jumuiya->id,
                'name' => $jumuiya->name,
                'code' => $jumuiya->code,
                'familias_count' => $jumuiyaFamilias->count(),
                'members_count' => $jumuiyaMembers->count(),
                'offering_total' => $offeringTotal,
                'tithe_total' => $titheTotal,
                'contribution_type_totals' => $typeTotals,
                'grand_total' => $offeringTotal + $titheTotal + $dynamicTotal,
                'details_url' => Route::has('jumuiyas.show') ? route('jumuiyas.show', $jumuiya) : '#',
                'report_url' => Route::has('jumuiya-reports.show') ? route('jumuiya-reports.show', $jumuiya) : '#',
            ];
        })->values();
    }

    // ========== NEW EXPORT METHOD (Guide D) ==========
    public function getKandaBreakdownExportData(Kanda $kanda, User $user, ?string $fromDate, ?string $toDate): array
    {
        $this->scopeAccessGate->authorizeKanda($user, $kanda);

        [$from, $to] = $this->normalizeDateRange($fromDate, $toDate);

        $jumuiyas = Jumuiya::where('kanda_id', $kanda->id)->orderBy('name')->get();
        $familias = Familia::whereIn('jumuiya_id', $jumuiyas->pluck('id'))->get();
        $members = Member::whereIn('familia_id', $familias->pluck('id'))->get();
        $types = $this->getBreakdownContributionTypes();

        $rows = collect();
        $serial = 1;

        foreach ($jumuiyas as $jumuiya) {
            $jumuiyaFamilias = $familias->where('jumuiya_id', $jumuiya->id);
            $jumuiyaMembers = $members->whereIn('familia_id', $jumuiyaFamilias->pluck('id'));
            $memberIds = $jumuiyaMembers->pluck('id');

            $offerings = $this->sumOfferingsForScope(collect([$jumuiya->id]), collect([$kanda->id]), $from, $to, false);
            $tithes = $this->sumTithesForJumuiyaIds(collect([$jumuiya->id]), $from, $to);
            $typeTotals = $this->getContributionTypeTotalsForMembersByDate($memberIds, $types, $from, $to);
            $dynamicTotal = collect($typeTotals)->sum(fn ($row) => (float) ($row['amount'] ?? 0));

            $rows->push([
                'sn' => $serial++,
                'jumuiya_name' => $jumuiya->name,
                'familias_count' => $jumuiyaFamilias->count(),
                'members_count' => $jumuiyaMembers->count(),
                'offerings' => $offerings,
                'tithes' => $tithes,
                'contribution_type_totals' => $typeTotals,
                'grand_total' => $offerings + $tithes + $dynamicTotal,
            ]);
        }

        return [
            'kanda' => $kanda,
            'types' => $types,
            'rows' => $rows->values(),
            'dateFilters' => [
                'from_date' => $from?->toDateString(),
                'to_date' => $to?->toDateString(),
                'period_label' => $this->formatDateRangeLabel($from, $to),
            ],
            'pdfCharts' => [
                'monthly_finance' => $this->getMonthlyFinanceChart($kanda),
                'income_mix' => collect([
                    ['label' => db_trans('offerings'), 'value' => (float) $rows->sum('offerings')],
                    ['label' => db_trans('tithes'), 'value' => (float) $rows->sum('tithes')],
                ])->merge(
                    $types->map(function ($type) use ($rows) {
                        return [
                            'label' => $type['name'],
                            'value' => (float) $rows->sum(fn ($row) => $row['contribution_type_totals'][$type['slug']]['amount'] ?? 0),
                        ];
                    })
                )->filter(fn ($row) => $row['value'] > 0)->values(),
            ],
        ];
    }

    public function getBreakdownPdfData(User $user, ?string $fromDate, ?string $toDate): array
    {
        [$from, $to] = $this->normalizeDateRange($fromDate, $toDate);

        $kandas = $this->scopedKandasQuery($user)
            ->orderBy('name')
            ->get();

        $hierarchy = $this->preloadHierarchy($kandas);
        $jumuiyas = $hierarchy['jumuiyas'];
        $familias = $hierarchy['familias'];
        $members = $hierarchy['members'];

        $breakdownContributionTypes = $this->getBreakdownContributionTypes();

        $globalTotals = [
            'offerings' => 0.0,
            'tithes' => 0.0,
            'contribution_type_totals' => [],
            'grand_total' => 0.0,
        ];

        foreach ($breakdownContributionTypes as $type) {
            $globalTotals['contribution_type_totals'][$type['slug']] = [
                'id' => $type['id'],
                'name' => $type['name'],
                'slug' => $type['slug'],
                'amount' => 0.0,
            ];
        }

        $kandaRows = collect();
        $kandaSerial = 1;

        foreach ($kandas as $kanda) {
            $kandaJumuiyas = $jumuiyas
                ->where('kanda_id', $kanda->id)
                ->sortBy(fn (Jumuiya $jumuiya) => mb_strtoupper($jumuiya->name))
                ->values();

            $kandaSubtotal = [
                'tithes' => 0.0,
                'offerings' => 0.0,
                'contribution_type_totals' => [],
                'grand_total' => 0.0,
            ];

            foreach ($breakdownContributionTypes as $type) {
                $kandaSubtotal['contribution_type_totals'][$type['slug']] = [
                    'id' => $type['id'],
                    'name' => $type['name'],
                    'slug' => $type['slug'],
                    'amount' => 0.0,
                ];
            }

            $jumuiyaRows = collect();
            $jumuiyaSerial = 1;

            foreach ($kandaJumuiyas as $jumuiya) {
                $jumuiyaFamilias = $familias->where('jumuiya_id', $jumuiya->id);
                $jumuiyaMembers = $members->whereIn('familia_id', $jumuiyaFamilias->pluck('id'));
                $memberIds = $jumuiyaMembers->pluck('id');

                $titheTotal = $this->sumTithesForJumuiyaIds(collect([$jumuiya->id]), $from, $to);
                $offeringTotal = $this->sumOfferingsForScope(
                    collect([$jumuiya->id]),
                    collect([$kanda->id]),
                    $from,
                    $to,
                    false
                );

                $typeTotals = $this->getContributionTypeTotalsForMembersByDate(
                    $memberIds,
                    $breakdownContributionTypes,
                    $from,
                    $to
                );

                $typesGrandTotal = collect($typeTotals)->sum(fn ($row) => (float) ($row['amount'] ?? 0));
                $rowGrandTotal = $titheTotal + $offeringTotal + $typesGrandTotal;

                foreach ($breakdownContributionTypes as $type) {
                    $amount = (float) ($typeTotals[$type['slug']]['amount'] ?? 0);

                    $kandaSubtotal['contribution_type_totals'][$type['slug']]['amount'] += $amount;
                    $globalTotals['contribution_type_totals'][$type['slug']]['amount'] += $amount;
                }

                $kandaSubtotal['tithes'] += $titheTotal;
                $kandaSubtotal['offerings'] += $offeringTotal;
                $kandaSubtotal['grand_total'] += $rowGrandTotal;

                $globalTotals['tithes'] += $titheTotal;
                $globalTotals['offerings'] += $offeringTotal;
                $globalTotals['grand_total'] += $rowGrandTotal;

                $jumuiyaRows->push([
                    'sn' => $jumuiyaSerial++,
                    'id' => $jumuiya->id,
                    'name' => $jumuiya->name,
                    'familias_count' => $jumuiyaFamilias->count(),
                    'members_count' => $jumuiyaMembers->count(),
                    'tithes' => $titheTotal,
                    'offerings' => $offeringTotal,
                    'contribution_type_totals' => $typeTotals,
                    'grand_total' => $rowGrandTotal,
                ]);
            }

            $kandaRows->push([
                'sn' => $kandaSerial++,
                'id' => $kanda->id,
                'name' => $kanda->name,
                'code' => $kanda->code,
                'jumuiyas_count' => $kandaJumuiyas->count(),
                'jumuiya_rows' => $jumuiyaRows->values(),
                'totals' => $kandaSubtotal,
            ]);
        }

        return [
            'pageTitle' => db_trans('kanda_and_jumuiya_breakdown'),
            'reportTitle' => db_trans('kanda_and_jumuiya_breakdown'),
            'scopeLabel' => $this->resolveScopeLabel($user),
            'breakdownContributionTypes' => $breakdownContributionTypes,
            'pdfContributionHeading' => $breakdownContributionTypes->pluck('name')->values(),
            'pdfMatrixRows' => $kandaRows->values(),
            'kandaBreakdownRows' => $kandaRows->values(),
            'globalTotals' => $globalTotals,
            'pdfCharts' => $this->buildBreakdownPdfCharts(
                $kandas,
                $jumuiyas,
                $familias,
                $members,
                $breakdownContributionTypes,
                $from,
                $to
            ),
            'dateFilters' => [
                'from_date' => $from?->toDateString(),
                'to_date' => $to?->toDateString(),
                'period_label' => $this->formatDateRangeLabel($from, $to),
            ],
            'issuedAtText' => now()->translatedFormat('d F Y'),
        ];
    }

    public function getBreakdownExcelData(User $user, ?string $fromDate, ?string $toDate): array
    {
        [$from, $to] = $this->normalizeDateRange($fromDate, $toDate);

        $kandas = $this->scopedKandasQuery($user)
            ->orderBy('name')
            ->get();

        $hierarchy = $this->preloadHierarchy($kandas);
        $jumuiyas = $hierarchy['jumuiyas'];
        $familias = $hierarchy['familias'];
        $members = $hierarchy['members'];

        $breakdownContributionTypes = $this->getBreakdownContributionTypes();

        $rows = collect();
        $serial = 1;

        foreach ($kandas as $kanda) {
            $kandaJumuiyas = $jumuiyas
                ->where('kanda_id', $kanda->id)
                ->sortBy(fn (Jumuiya $jumuiya) => mb_strtoupper($jumuiya->name))
                ->values();

            if ($kandaJumuiyas->isEmpty()) {
                $emptyTypeTotals = [];

                foreach ($breakdownContributionTypes as $type) {
                    $emptyTypeTotals[$type['slug']] = [
                        'id' => $type['id'],
                        'name' => $type['name'],
                        'slug' => $type['slug'],
                        'amount' => 0.0,
                    ];
                }

                $rows->push([
                    'sn' => $serial++,
                    'kanda_name' => $kanda->name,
                    'jumuiya_name' => db_trans('no_jumuiyas_found'),
                    'familias_count' => 0,
                    'members_count' => 0,
                    'tithes' => 0.0,
                    'offerings' => 0.0,
                    'contribution_type_totals' => $emptyTypeTotals,
                    'grand_total' => 0.0,
                ]);

                continue;
            }

            foreach ($kandaJumuiyas as $jumuiya) {
                $jumuiyaFamilias = $familias->where('jumuiya_id', $jumuiya->id);
                $jumuiyaMembers = $members->whereIn('familia_id', $jumuiyaFamilias->pluck('id'));
                $memberIds = $jumuiyaMembers->pluck('id');

                $titheTotal = $this->sumTithesForJumuiyaIds(collect([$jumuiya->id]), $from, $to);

                $offeringTotal = $this->sumOfferingsForScope(
                    collect([$jumuiya->id]),
                    collect([$kanda->id]),
                    $from,
                    $to,
                    false
                );

                $typeTotals = $this->getContributionTypeTotalsForMembersByDate(
                    $memberIds,
                    $breakdownContributionTypes,
                    $from,
                    $to
                );

                $contributionsGrandTotal = collect($typeTotals)->sum(fn ($row) => (float) ($row['amount'] ?? 0));

                $rows->push([
                    'sn' => $serial++,
                    'kanda_name' => $kanda->name,
                    'jumuiya_name' => $jumuiya->name,
                    'familias_count' => $jumuiyaFamilias->count(),
                    'members_count' => $jumuiyaMembers->count(),
                    'tithes' => $titheTotal,
                    'offerings' => $offeringTotal,
                    'contribution_type_totals' => $typeTotals,
                    'grand_total' => $titheTotal + $offeringTotal + $contributionsGrandTotal,
                ]);
            }
        }

        return [
            'types' => $breakdownContributionTypes,
            'rows' => $rows->values(),
        ];
    }

    protected function buildBreakdownPdfCharts(
        Collection $kandas,
        Collection $jumuiyas,
        Collection $familias,
        Collection $members,
        Collection $types,
        ?Carbon $from,
        ?Carbon $to
    ): array {
        $kandaBars = $kandas
            ->map(function (Kanda $kanda) use ($jumuiyas, $familias, $members, $types, $from, $to) {
                return [
                    'label' => $kanda->name,
                    'value' => $this->sumKandaGrandTotalByDate($kanda, $jumuiyas, $familias, $members, $types, $from, $to),
                ];
            })
            ->filter(fn (array $row) => $row['value'] > 0)
            ->sortByDesc('value')
            ->take(8)
            ->values();

        $jumuiyaBars = $jumuiyas
            ->sortBy(fn (Jumuiya $jumuiya) => mb_strtoupper($jumuiya->name))
            ->map(function (Jumuiya $jumuiya) use ($familias, $members, $types, $from, $to) {
                return [
                    'label' => $jumuiya->name,
                    'value' => $this->sumJumuiyaGrandTotalByDate($jumuiya, $familias, $members, $types, $from, $to),
                ];
            })
            ->filter(fn (array $row) => $row['value'] > 0)
            ->sortByDesc('value')
            ->take(10)
            ->values();

        $totalTithes = 0.0;
        $totalOfferings = 0.0;
        $typeTotals = [];

        foreach ($types as $type) {
            $typeTotals[$type['slug']] = [
                'label' => $type['name'],
                'value' => 0.0,
            ];
        }

        foreach ($jumuiyas as $jumuiya) {
            $totalTithes += $this->sumTithesForJumuiyaIds(collect([$jumuiya->id]), $from, $to);

            $kandaId = $jumuiya->kanda_id ? collect([$jumuiya->kanda_id]) : collect();
            $totalOfferings += $this->sumOfferingsForScope(
                collect([$jumuiya->id]),
                $kandaId,
                $from,
                $to,
                false
            );

            $jumuiyaFamilias = $familias->where('jumuiya_id', $jumuiya->id);
            $jumuiyaMembers = $members->whereIn('familia_id', $jumuiyaFamilias->pluck('id'));
            $memberIds = $jumuiyaMembers->pluck('id');

            $totals = $this->getContributionTypeTotalsForMembersByDate($memberIds, $types, $from, $to);

            foreach ($types as $type) {
                $typeTotals[$type['slug']]['value'] += (float) ($totals[$type['slug']]['amount'] ?? 0);
            }
        }

        $pieData = collect([
            [
                'label' => db_trans('tithes'),
                'value' => $totalTithes,
            ],
            [
                'label' => db_trans('offerings'),
                'value' => $totalOfferings,
            ],
        ])->merge(collect($typeTotals)->values())
          ->filter(fn (array $row) => $row['value'] > 0)
          ->values();

        return [
            'top_kandas' => $kandaBars,
            'top_jumuiyas' => $jumuiyaBars,
            'income_mix' => $pieData,
        ];
    }

    protected function sumKandaGrandTotalByDate(
        Kanda $kanda,
        Collection $jumuiyas,
        Collection $familias,
        Collection $members,
        Collection $types,
        ?Carbon $from,
        ?Carbon $to
    ): float {
        $kandaJumuiyas = $jumuiyas->where('kanda_id', $kanda->id);
        $kandaFamilias = $familias->whereIn('jumuiya_id', $kandaJumuiyas->pluck('id'));
        $kandaMembers = $members->whereIn('familia_id', $kandaFamilias->pluck('id'));
        $memberIds = $kandaMembers->pluck('id');

        $tithes = $this->sumTithesForJumuiyaIds($kandaJumuiyas->pluck('id'), $from, $to);
        $offerings = $this->sumOfferingsForScope($kandaJumuiyas->pluck('id'), collect([$kanda->id]), $from, $to, true);
        $typeTotals = $this->getContributionTypeTotalsForMembersByDate($memberIds, $types, $from, $to);
        $contributions = collect($typeTotals)->sum(fn ($row) => (float) ($row['amount'] ?? 0));

        return $tithes + $offerings + $contributions;
    }

    protected function sumJumuiyaGrandTotalByDate(
        Jumuiya $jumuiya,
        Collection $familias,
        Collection $members,
        Collection $types,
        ?Carbon $from,
        ?Carbon $to
    ): float {
        $jumuiyaFamilias = $familias->where('jumuiya_id', $jumuiya->id);
        $jumuiyaMembers = $members->whereIn('familia_id', $jumuiyaFamilias->pluck('id'));
        $memberIds = $jumuiyaMembers->pluck('id');

        $tithes = $this->sumTithesForJumuiyaIds(collect([$jumuiya->id]), $from, $to);
        $offerings = $this->sumOfferingsForScope(collect([$jumuiya->id]), collect(), $from, $to, false);
        $typeTotals = $this->getContributionTypeTotalsForMembersByDate($memberIds, $types, $from, $to);
        $contributions = collect($typeTotals)->sum(fn ($row) => (float) ($row['amount'] ?? 0));

        return $tithes + $offerings + $contributions;
    }

    protected function preloadHierarchy(Collection $kandas): array
    {
        $kandaIds = $kandas->pluck('id');

        $jumuiyas = Jumuiya::whereIn('kanda_id', $kandaIds)->get();
        $familias = Familia::whereIn('jumuiya_id', $jumuiyas->pluck('id'))->get();
        $members = Member::whereIn('familia_id', $familias->pluck('id'))->get();

        return [
            'kandaIds' => $kandaIds,
            'jumuiyas' => $jumuiyas,
            'familias' => $familias,
            'members' => $members,
        ];
    }

    protected function getHero(User $user, array $stats): array
    {
        return [
            'eyebrow' => db_trans('kanda_analytics'),
            'title' => db_trans('kanda_dashboard_title'),
            'subtitle' => db_trans('kanda_dashboard_subtitle'),
            'scope_label' => $this->resolveScopeLabel($user),
            'pending_items' => $this->getPendingItemCountForUser($user),
            'today' => now()->translatedFormat('l, d M Y'),
            'grand_total' => number_format((float) $stats['grand_total'], 2),
        ];
    }

    protected function getSummaryCards(array $stats, User $user): array
    {
        return [
            [
                'title' => db_trans('total_kandas'),
                'value' => number_format($stats['total_kandas']),
                'meta' => db_trans('regional_units_in_scope'),
                'icon' => 'fas fa-map-marked-alt',
                'tone' => 'primary',
                'route' => $user->can('kandas.view') && Route::has('kandas.index') ? route('kandas.index') : null,
            ],
            [
                'title' => db_trans('total_jumuiyas'),
                'value' => number_format($stats['total_jumuiyas']),
                'meta' => db_trans('communities_under_kanda'),
                'icon' => 'fas fa-layer-group',
                'tone' => 'info',
                'route' => $user->can('jumuiyas.view') && Route::has('jumuiyas.index') ? route('jumuiyas.index') : null,
            ],
            [
                'title' => db_trans('total_familias'),
                'value' => number_format($stats['total_familias']),
                'meta' => db_trans('familias_under_pastoral_care'),
                'icon' => 'fas fa-home',
                'tone' => 'secondary',
                'route' => $user->can('familias.view') && Route::has('familias.index')
                    ? route('familias.index')
                    : null,
            ],
            [
                'title' => db_trans('total_members'),
                'value' => number_format($stats['total_members']),
                'meta' => db_trans('registered_members_in_scope'),
                'icon' => 'fas fa-users',
                'tone' => 'success',
                'route' => $user->can('members.view') && Route::has('members.index') ? route('members.index') : null,
            ],
            [
                'title' => db_trans('male_members'),
                'value' => number_format($stats['male_members']),
                'meta' => db_trans('gender_distribution'),
                'icon' => 'fas fa-male',
                'tone' => 'dark',
                'route' => $user->can('members.view') && Route::has('members.index') ? route('members.index') : null,
            ],
            [
                'title' => db_trans('female_members'),
                'value' => number_format($stats['female_members']),
                'meta' => db_trans('gender_distribution'),
                'icon' => 'fas fa-female',
                'tone' => 'warning',
                'route' => $user->can('members.view') && Route::has('members.index') ? route('members.index') : null,
            ],
        ];
    }

    protected function getFinanceCards(array $stats, User $user): array
    {
        return [
            [
                'title' => db_trans('total_tithes'),
                'value' => number_format((float) $stats['total_tithes'], 2),
                'meta' => db_trans('tithe_collections_in_scope'),
                'icon' => 'fas fa-coins',
                'tone' => 'success',
                'route' => $user->can('kanda-reports.view') && Route::has('kanda-reports.index')
                    ? route('kanda-reports.index')
                    : null,
            ],
            [
                'title' => db_trans('total_offerings'),
                'value' => number_format((float) $stats['total_offerings'], 2),
                'meta' => db_trans('offerings_recorded_in_scope'),
                'icon' => 'fas fa-hand-holding-heart',
                'tone' => 'primary',
                'route' => $user->can('kanda-reports.view') && Route::has('kanda-reports.index')
                    ? route('kanda-reports.index')
                    : null,
            ],
            [
                'title' => db_trans('total_cash_contributions'),
                'value' => number_format((float) $stats['total_cash_contributions'], 2),
                'meta' => db_trans('cash_contributions_recorded'),
                'icon' => 'fas fa-wallet',
                'tone' => 'warning',
                'route' => $user->can('finance.contributions.cash.view') && Route::has('finance.contributions.cash.index')
                    ? route('finance.contributions.cash.index')
                    : null,
            ],
            [
                'title' => db_trans('total_bank_contributions'),
                'value' => number_format((float) $stats['total_bank_contributions'], 2),
                'meta' => db_trans('bank_contributions_recorded'),
                'icon' => 'fas fa-university',
                'tone' => 'info',
                'route' => $user->can('finance.contributions.bank.view') && Route::has('finance.contributions.bank.index')
                    ? route('finance.contributions.bank.index')
                    : null,
            ],
            [
                'title' => db_trans('total_mavuno'),
                'value' => number_format((float) $stats['total_mavuno'], 2),
                'meta' => db_trans('mavuno_contributions_in_scope'),
                'icon' => 'fas fa-seedling',
                'tone' => 'secondary',
                'route' => $user->can('finance.contributions.cash.view') && Route::has('finance.contributions.cash.index')
                    ? route('finance.contributions.cash.index')
                    : null,
            ],
            [
                'title' => db_trans('grand_total'),
                'value' => number_format((float) $stats['grand_total'], 2),
                'meta' => db_trans('all_major_income_streams_combined'),
                'icon' => 'fas fa-chart-line',
                'tone' => 'dark',
                'route' => $user->can('kanda-reports.view') && Route::has('kanda-reports.index')
                    ? route('kanda-reports.index')
                    : null,
            ],
        ];
    }

    protected function getQuickLinks(User $user): array
    {
        $links = [];

        if ($user->can('kandas.create') && Route::has('kandas.create')) {
            $links[] = [
                'label' => db_trans('create_kanda'),
                'route' => route('kandas.create'),
                'icon' => 'fas fa-plus-circle',
            ];
        }

        if ($user->can('kanda-reports.view') && Route::has('kanda-reports.index')) {
            $links[] = [
                'label' => db_trans('financial_reports'),
                'route' => route('kanda-reports.index'),
                'icon' => 'fas fa-chart-pie',
            ];
        }

        if ($user->can('members.view') && Route::has('members.index')) {
            $links[] = [
                'label' => db_trans('members'),
                'route' => route('members.index'),
                'icon' => 'fas fa-users',
            ];
        }

        if ($user->can('jumuiyas.view') && Route::has('jumuiyas.index')) {
            $links[] = [
                'label' => db_trans('jumuiyas'),
                'route' => route('jumuiyas.index'),
                'icon' => 'fas fa-sitemap',
            ];
        }

        if ($user->can('finance.contributions.cash.view') && Route::has('finance.contributions.cash.index')) {
            $links[] = [
                'label' => db_trans('cash_contributions'),
                'route' => route('finance.contributions.cash.index'),
                'icon' => 'fas fa-wallet',
            ];
        }

        if ($user->can('finance.contributions.bank.view') && Route::has('finance.contributions.bank.index')) {
            $links[] = [
                'label' => db_trans('bank_contributions'),
                'route' => route('finance.contributions.bank.index'),
                'icon' => 'fas fa-university',
            ];
        }

        return $links;
    }

    protected function buildKandaBreakdown(
        Collection $kandas,
        Collection $jumuiyas,
        Collection $familias,
        Collection $members,
        Collection $types
    ): Collection {
        return $kandas->map(function (Kanda $kanda) use ($jumuiyas, $familias, $members, $types) {
            $kandaJumuiyasCollection = $jumuiyas->where('kanda_id', $kanda->id);
            $kandaFamilias = $familias->whereIn('jumuiya_id', $kandaJumuiyasCollection->pluck('id'));
            $kandaMembers = $members->whereIn('familia_id', $kandaFamilias->pluck('id'));
            $kandaMemberIds = $kandaMembers->pluck('id');

            $typeTotals = $this->getContributionTypeTotalsForMembers($kandaMemberIds, $types);

            $kandaJumuiyas = $this->buildJumuiyaBreakdown($kanda, $jumuiyas, $familias, $members, $types);

            return [
                'id' => $kanda->id,
                'name' => $kanda->name,
                'code' => $kanda->code,
                'is_active' => (bool) $kanda->is_active,
                'jumuiya_count' => $kanda->jumuiyas_count ?? 0,
                'familias_count' => $kanda->familias_count ?? 0,
                'members_count' => $kanda->members_count ?? 0,
                'male_members_count' => $kanda->male_members_count ?? 0,
                'female_members_count' => $kanda->female_members_count ?? 0,
                'tithe_total' => (float) ($kanda->tithe_total ?? 0),
                'offering_total' => (float) ($kanda->offering_total ?? 0),
                'cash_total' => (float) ($kanda->cash_total ?? 0),
                'bank_total' => (float) ($kanda->bank_total ?? 0),
                'mavuno_total' => (float) ($kanda->mavuno_total ?? 0),
                'contribution_type_totals' => $typeTotals,
                'grand_total' => (float) ($kanda->grand_total ?? 0),
                'details_url' => Route::has('kandas.show') ? route('kandas.show', $kanda) : '#',
                'report_url' => Route::has('kanda-reports.show') ? route('kanda-reports.show', $kanda) : '#',
                'jumuiyas' => $kandaJumuiyas,
            ];
        });
    }

    protected function getLiveFeedForVisibleKandas(Collection $jumuiyas, Collection $familias, Collection $members): array
    {
        $jumuiyaIds = $jumuiyas->pluck('id');
        $familiaIds = $familias->pluck('id');
        $memberIds = $members->pluck('id');

        $feed = collect();

        Member::query()
            ->whereIn('id', $memberIds)
            ->latest('created_at')
            ->limit(4)
            ->get()
            ->each(function (Member $member) use ($feed) {
                $feed->push([
                    'icon' => 'fas fa-user-plus',
                    'tone' => 'primary',
                    'title' => db_trans('new_member_registered'),
                    'text' => trim(collect([$member->first_name, $member->middle_name, $member->last_name])->filter()->implode(' ')),
                    'time' => optional($member->created_at)->diffForHumans(),
                    'sort_key' => optional($member->created_at)?->timestamp ?? 0,
                ]);
            });

        if (Schema::hasTable('tithes')) {
            Tithe::query()
                ->whereIn('jumuiya_id', $jumuiyaIds)
                ->latest('created_at')
                ->limit(4)
                ->get()
                ->each(function (Tithe $tithe) use ($feed) {
                    $feed->push([
                        'icon' => 'fas fa-coins',
                        'tone' => 'success',
                        'title' => db_trans('tithe_recorded'),
                        'text' => number_format((float) $tithe->amount, 2),
                        'time' => optional($tithe->created_at)->diffForHumans(),
                        'sort_key' => optional($tithe->created_at)?->timestamp ?? 0,
                    ]);
                });
        }

        if (Schema::hasTable('cash_contributions')) {
            CashContribution::query()
                ->whereIn('member_id', $memberIds)
                ->latest('created_at')
                ->limit(4)
                ->get()
                ->each(function (CashContribution $contribution) use ($feed) {
                    $feed->push([
                        'icon' => 'fas fa-wallet',
                        'tone' => 'warning',
                        'title' => db_trans('cash_contribution_recorded'),
                        'text' => number_format((float) $contribution->amount, 2),
                        'time' => optional($contribution->created_at)->diffForHumans(),
                        'sort_key' => optional($contribution->created_at)?->timestamp ?? 0,
                    ]);
                });
        }

        if (Schema::hasTable('bank_contributions')) {
            BankContribution::query()
                ->whereIn('member_id', $memberIds)
                ->latest('created_at')
                ->limit(4)
                ->get()
                ->each(function (BankContribution $contribution) use ($feed) {
                    $feed->push([
                        'icon' => 'fas fa-university',
                        'tone' => 'info',
                        'title' => db_trans('bank_contribution_recorded'),
                        'text' => number_format((float) $contribution->amount, 2),
                        'time' => optional($contribution->created_at)->diffForHumans(),
                        'sort_key' => optional($contribution->created_at)?->timestamp ?? 0,
                    ]);
                });
        }

        if (Schema::hasTable('offerings')) {
            $kandaIds = $jumuiyas->pluck('kanda_id')->unique();
            Offering::query()
                ->where(function ($query) use ($jumuiyaIds, $kandaIds) {
                    $query->whereIn('jumuiya_id', $jumuiyaIds)
                        ->orWhereIn('kanda_id', $kandaIds);
                })
                ->latest('created_at')
                ->limit(4)
                ->get()
                ->each(function (Offering $offering) use ($feed) {
                    $feed->push([
                        'icon' => 'fas fa-hand-holding-heart',
                        'tone' => 'danger',
                        'title' => db_trans('offering_recorded'),
                        'text' => number_format((float) $offering->amount, 2),
                        'time' => optional($offering->created_at)->diffForHumans(),
                        'sort_key' => optional($offering->created_at)?->timestamp ?? 0,
                    ]);
                });
        }

        return $feed->sortByDesc('sort_key')->take(10)->values()->map(function ($item) {
            unset($item['sort_key']);

            return $item;
        })->all();
    }

    protected function getAlertsForVisibleKandas(User $user, Collection $jumuiyas, Collection $familias, Collection $members): array
    {
        $membersWithoutPhone = $members
            ->filter(fn ($m) => empty($m->phone))
            ->count();

        $inactiveJumuiyas = $jumuiyas
            ->where('is_active', false)
            ->count();

        $familiasWithoutMembers = $familias
            ->filter(fn ($familia) => !$members->contains('familia_id', $familia->id))
            ->count();

        return [
            [
                'label' => db_trans('members_without_phone'),
                'value' => $membersWithoutPhone,
                'route' => $user->can('members.view') && Route::has('members.index')
                    ? route('members.index')
                    : '#',
            ],
            [
                'label' => db_trans('inactive_jumuiyas'),
                'value' => $inactiveJumuiyas,
                'route' => $user->can('jumuiyas.view') && Route::has('jumuiyas.index')
                    ? route('jumuiyas.index')
                    : '#',
            ],
            [
                'label' => db_trans('familias_without_members'),
                'value' => $familiasWithoutMembers,
                'route' => $user->can('familias.view') && Route::has('familias.index')
                    ? route('familias.index')
                    : '#',
            ],
        ];
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
            if ($scope->kandaId) {
                return $query->whereKey($scope->kandaId);
            }

            return $query->whereRaw('1 = 0');
        }

        return $query->whereRaw('1 = 0');
    }

    protected function buildKandaSummary(Kanda $kanda, Collection $jumuiyas, Collection $familias, Collection $members): array
    {
        $kandaJumuiyas = $jumuiyas->where('kanda_id', $kanda->id);
        $kandaFamilias = $familias->whereIn('jumuiya_id', $kandaJumuiyas->pluck('id'));
        $kandaMembers = $members->whereIn('familia_id', $kandaFamilias->pluck('id'));

        $memberIds = $kandaMembers->pluck('id');

        $titheTotal = Schema::hasTable('tithes')
            ? (float) Tithe::whereIn('jumuiya_id', $kandaJumuiyas->pluck('id'))->sum('amount')
            : 0;

        $cashTotal = Schema::hasTable('cash_contributions')
            ? (float) CashContribution::whereIn('member_id', $memberIds)->sum('amount')
            : 0;

        $mavunoTotal = Schema::hasTable('cash_contributions')
            ? (float) CashContribution::whereIn('member_id', $memberIds)
                ->whereHas('contributionType', fn ($q) => $q->where('slug', 'mavuno'))
                ->sum('amount')
            : 0;

        $bankTotal = Schema::hasTable('bank_contributions')
            ? (float) BankContribution::whereIn('member_id', $memberIds)->sum('amount')
            : 0;

        $offeringTotal = Schema::hasTable('offerings')
            ? (float) Offering::where(function ($query) use ($kandaJumuiyas, $kanda) {
                $query->whereIn('jumuiya_id', $kandaJumuiyas->pluck('id'))
                    ->orWhere('kanda_id', $kanda->id);
            })->sum('amount')
            : 0;

        return [
            'jumuiyas_count' => $kandaJumuiyas->count(),
            'familias_count' => $kandaFamilias->count(),
            'members_count' => $kandaMembers->count(),
            'male_members_count' => $kandaMembers->where('gender', 'Male')->count(),
            'female_members_count' => $kandaMembers->where('gender', 'Female')->count(),
            'tithe_total' => $titheTotal,
            'mavuno_total' => $mavunoTotal,
            'cash_total' => $cashTotal,
            'bank_total' => $bankTotal,
            'offering_total' => $offeringTotal,
            'grand_total' => $titheTotal + $cashTotal + $bankTotal + $offeringTotal,
        ];
    }

    protected function getRecentMembers(Kanda $kanda): Collection
    {
        return Member::query()
            ->select('members.*')
            ->join('familias', 'familias.id', '=', 'members.familia_id')
            ->join('jumuiyas', 'jumuiyas.id', '=', 'familias.jumuiya_id')
            ->where('jumuiyas.kanda_id', $kanda->id)
            ->with('familia.jumuiya')
            ->latest('members.created_at')
            ->limit(8)
            ->get();
    }

    protected function getMonthlyFinanceChart(Kanda $kanda): array
    {
        $jumuiyaIds = Jumuiya::where('kanda_id', $kanda->id)->pluck('id');
        $familiaIds = Familia::whereIn('jumuiya_id', $jumuiyaIds)->pluck('id');
        $memberIds = Member::whereIn('familia_id', $familiaIds)->pluck('id');

        $labels = [];
        $tithes = [];
        $offerings = [];
        $cash = [];
        $bank = [];

        for ($month = 1; $month <= 12; $month++) {
            $labels[] = now()->copy()->month($month)->translatedFormat('M');

            $tithes[] = Schema::hasTable('tithes')
                ? (float) Tithe::whereIn('jumuiya_id', $jumuiyaIds)
                    ->whereYear('contribution_date', now()->year)
                    ->whereMonth('contribution_date', $month)
                    ->sum('amount')
                : 0;

            $offerings[] = Schema::hasTable('offerings')
                ? (float) Offering::where(function ($query) use ($jumuiyaIds, $kanda) {
                    $query->whereIn('jumuiya_id', $jumuiyaIds)
                        ->orWhere('kanda_id', $kanda->id);
                })
                    ->whereYear('collection_date', now()->year)
                    ->whereMonth('collection_date', $month)
                    ->sum('amount')
                : 0;

            $cash[] = Schema::hasTable('cash_contributions')
                ? (float) CashContribution::whereIn('member_id', $memberIds)
                    ->whereYear('contribution_date', now()->year)
                    ->whereMonth('contribution_date', $month)
                    ->sum('amount')
                : 0;

            $bank[] = Schema::hasTable('bank_contributions')
                ? (float) BankContribution::whereIn('member_id', $memberIds)
                    ->whereYear('contribution_date', now()->year)
                    ->whereMonth('contribution_date', $month)
                    ->sum('amount')
                : 0;
        }

        return [
            'labels' => $labels,
            'tithes' => $tithes,
            'offerings' => $offerings,
            'cash' => $cash,
            'bank' => $bank,
        ];
    }

    protected function getMonthlyFinanceChartForVisibleKandas(Collection $kandas): array
    {
        $kandaIds = $kandas->pluck('id');
        $jumuiyaIds = Jumuiya::whereIn('kanda_id', $kandaIds)->pluck('id');
        $familiaIds = Familia::whereIn('jumuiya_id', $jumuiyaIds)->pluck('id');
        $memberIds = Member::whereIn('familia_id', $familiaIds)->pluck('id');

        $labels = [];
        $tithes = [];
        $offerings = [];
        $cash = [];
        $bank = [];

        for ($month = 1; $month <= 12; $month++) {
            $labels[] = now()->copy()->month($month)->translatedFormat('M');

            $tithes[] = Schema::hasTable('tithes')
                ? (float) Tithe::whereIn('jumuiya_id', $jumuiyaIds)
                    ->whereYear('contribution_date', now()->year)
                    ->whereMonth('contribution_date', $month)
                    ->sum('amount')
                : 0;

            $offerings[] = Schema::hasTable('offerings')
                ? (float) Offering::where(function ($query) use ($jumuiyaIds, $kandaIds) {
                    $query->whereIn('jumuiya_id', $jumuiyaIds)
                        ->orWhereIn('kanda_id', $kandaIds);
                })
                    ->whereYear('collection_date', now()->year)
                    ->whereMonth('collection_date', $month)
                    ->sum('amount')
                : 0;

            $cash[] = Schema::hasTable('cash_contributions')
                ? (float) CashContribution::whereIn('member_id', $memberIds)
                    ->whereYear('contribution_date', now()->year)
                    ->whereMonth('contribution_date', $month)
                    ->sum('amount')
                : 0;

            $bank[] = Schema::hasTable('bank_contributions')
                ? (float) BankContribution::whereIn('member_id', $memberIds)
                    ->whereYear('contribution_date', now()->year)
                    ->whereMonth('contribution_date', $month)
                    ->sum('amount')
                : 0;
        }

        return [
            'labels' => $labels,
            'tithes' => $tithes,
            'offerings' => $offerings,
            'cash' => $cash,
            'bank' => $bank,
        ];
    }

    protected function getContributionTypeChartForVisibleKandas(Collection $members): array
    {
        if (!Schema::hasTable('contribution_types')) {
            return [
                'labels' => collect(),
                'cash' => collect(),
                'bank' => collect(),
                'totals' => collect(),
            ];
        }

        $memberIds = $members->pluck('id');

        $types = ContributionType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $rows = $types->map(function (ContributionType $type) use ($memberIds) {
            $cash = Schema::hasTable('cash_contributions')
                ? (float) CashContribution::query()
                    ->whereIn('member_id', $memberIds)
                    ->where('contribution_type_id', $type->id)
                    ->sum('amount')
                : 0;

            $bank = Schema::hasTable('bank_contributions')
                ? (float) BankContribution::query()
                    ->whereIn('member_id', $memberIds)
                    ->where('contribution_type_id', $type->id)
                    ->sum('amount')
                : 0;

            return [
                'name' => $type->name,
                'cash_total' => $cash,
                'bank_total' => $bank,
                'total' => $cash + $bank,
            ];
        })
        ->filter(fn (array $row) => $row['total'] > 0)
        ->sortByDesc('total')
        ->values();

        return [
            'labels' => $rows->pluck('name')->values(),
            'cash' => $rows->pluck('cash_total')->map(fn ($v) => (float) $v)->values(),
            'bank' => $rows->pluck('bank_total')->map(fn ($v) => (float) $v)->values(),
            'totals' => $rows->pluck('total')->map(fn ($v) => (float) $v)->values(),
        ];
    }

    protected function getPendingItemCountForUser(User $user): int
    {
        $kandas = $this->scopedKandasQuery($user)->pluck('id');
        $jumuiyaIds = Jumuiya::whereIn('kanda_id', $kandas)->pluck('id');
        $familiaIds = Familia::whereIn('jumuiya_id', $jumuiyaIds)->pluck('id');
        $memberIds = Member::whereIn('familia_id', $familiaIds)->pluck('id');

        $count = 0;

        if (Schema::hasTable('tithes')) {
            $count += Tithe::whereIn('jumuiya_id', $jumuiyaIds)->where('status', Tithe::STATUS_PENDING)->count();
        }

        if (Schema::hasTable('cash_contributions')) {
            $count += CashContribution::whereIn('member_id', $memberIds)->where('status', CashContribution::STATUS_PENDING)->count();
        }

        if (Schema::hasTable('offerings')) {
            $count += Offering::where(function ($query) use ($jumuiyaIds, $kandas) {
                $query->whereIn('jumuiya_id', $jumuiyaIds)
                    ->orWhereIn('kanda_id', $kandas);
            })->where('status', Offering::STATUS_PENDING)->count();
        }

        return $count;
    }

    protected function resolveScopeLabel(User $user): string
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            return db_trans('invalid_scope');
        }

        if ($scope->isGlobal()) {
            return db_trans('parish_scope');
        }

        if ($scope->isKanda()) {
            $kanda = Kanda::find($scope->kandaId);

            return $kanda ? $kanda->name : db_trans('kanda_scope');
        }

        if ($scope->isJumuiya()) {
            $jumuiya = Jumuiya::find($scope->jumuiyaId);

            return $jumuiya ? $jumuiya->name : db_trans('jumuiya_scope');
        }

        return db_trans('parish_scope');
    }

    protected function getHeroLinks(User $user): array
    {
        $links = [];

        if ($user->can('kandas.create') && Route::has('kandas.create')) {
            $links[] = [
                'label' => db_trans('create_kanda'),
                'route' => route('kandas.create'),
                'icon' => 'fas fa-plus-circle',
            ];
        }

        if ($user->can('kandas.view') && Route::has('kandas.index')) {
            $links[] = [
                'label' => db_trans('view_all_kandas'),
                'route' => route('kandas.index'),
                'icon' => 'fas fa-map-marked-alt',
            ];
        }

        if ($user->can('kanda-reports.view') && Route::has('kanda-reports.index')) {
            $links[] = [
                'label' => db_trans('financial_reports'),
                'route' => route('kanda-reports.index'),
                'icon' => 'fas fa-chart-pie',
            ];
        }

        if ($user->can('members.view') && Route::has('members.index')) {
            $links[] = [
                'label' => db_trans('members'),
                'route' => route('members.index'),
                'icon' => 'fas fa-users',
            ];
        }

        if ($user->can('jumuiyas.view') && Route::has('jumuiyas.index')) {
            $links[] = [
                'label' => db_trans('jumuiyas'),
                'route' => route('jumuiyas.index'),
                'icon' => 'fas fa-sitemap',
            ];
        }

        return $links;
    }

    protected function getChartActions(User $user): array
    {
        return [
            'monthlyFinance' => $user->can('kanda-reports.view') && Route::has('kanda-reports.index') ? [
                [
                    'label' => db_trans('view_reports'),
                    'route' => route('kanda-reports.index'),
                ],
            ] : [],

            'financeByKanda' => $user->can('kanda-reports.view') && Route::has('kanda-reports.index') ? [
                [
                    'label' => db_trans('view_reports'),
                    'route' => route('kanda-reports.index'),
                ],
            ] : [],

            'membershipMix' => $user->can('members.view') && Route::has('members.index') ? [
                [
                    'label' => db_trans('view_members'),
                    'route' => route('members.index'),
                ],
            ] : [],

            'contributionTypes' => $user->can('kanda-reports.view') && Route::has('kanda-reports.index') ? [
                [
                    'label' => db_trans('view_reports'),
                    'route' => route('kanda-reports.index'),
                ],
            ] : [],
        ];
    }

    protected function getBreakdownSummaryRows(Collection $kandas, Collection $types): Collection
    {
        return $kandas->map(function (Kanda $kanda) use ($types) {
            $typeTotals = [];

            foreach ($types as $type) {
                $typeTotals[$type['slug']] = [
                    'id' => $type['id'],
                    'name' => $type['name'],
                    'slug' => $type['slug'],
                    'amount' => 0,
                ];
            }

            foreach (($kanda->contribution_type_totals ?? []) as $slug => $row) {
                if (isset($typeTotals[$slug])) {
                    $typeTotals[$slug]['amount'] = (float) ($row['amount'] ?? 0);
                }
            }

            return [
                'id' => $kanda->id,
                'name' => $kanda->name,
                'jumuiyas_count' => (int) ($kanda->jumuiyas_count ?? 0),
                'familias_count' => (int) ($kanda->familias_count ?? 0),
                'members_count' => (int) ($kanda->members_count ?? 0),
                'offering_total' => (float) ($kanda->offering_total ?? 0),
                'tithe_total' => (float) ($kanda->tithe_total ?? 0),
                'contribution_type_totals' => $typeTotals,
                'grand_total' => (float) ($kanda->grand_total ?? 0),
                'details_url' => Route::has('kandas.show') ? route('kandas.show', $kanda) : '#',
                'report_url' => Route::has('kanda-reports.show') ? route('kanda-reports.show', $kanda) : '#',
            ];
        })->values();
    }

    protected function getBreakdownContributionTypes(): Collection
    {
        if (! Schema::hasTable('contribution_types')) {
            return collect();
        }

        return ContributionType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (ContributionType $type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                ];
            })
            ->values();
    }

    protected function getContributionTypeTotalsForMembers(Collection $memberIds, Collection $types): array
    {
        return $this->getContributionTypeTotalsForMembersByDate($memberIds, $types, null, null);
    }

    protected function normalizeDateRange(?string $fromDate, ?string $toDate): array
    {
        $from = $fromDate ? Carbon::parse($fromDate)->startOfDay() : null;
        $to = $toDate ? Carbon::parse($toDate)->endOfDay() : null;

        if ($from && $to && $from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
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

    protected function sumTithesForJumuiyaIds(Collection $jumuiyaIds, ?Carbon $from, ?Carbon $to): float
    {
        if ($jumuiyaIds->isEmpty() || !Schema::hasTable('tithes')) {
            return 0.0;
        }

        $query = Tithe::query()->whereIn('jumuiya_id', $jumuiyaIds);
        $this->applyDateRange($query, 'contribution_date', $from, $to);

        return (float) $query->sum('amount');
    }

    protected function sumOfferingsForScope(
        Collection $jumuiyaIds,
        Collection $kandaIds,
        ?Carbon $from,
        ?Carbon $to,
        bool $includeKandaLevel = true
    ): float {
        if (!Schema::hasTable('offerings')) {
            return 0.0;
        }

        $query = Offering::query()->where(function ($q) use ($jumuiyaIds, $kandaIds, $includeKandaLevel) {
            if ($jumuiyaIds->isNotEmpty()) {
                $q->whereIn('jumuiya_id', $jumuiyaIds);
            }

            if ($includeKandaLevel && $kandaIds->isNotEmpty()) {
                $q->orWhereIn('kanda_id', $kandaIds);
            }
        });

        $this->applyDateRange($query, 'collection_date', $from, $to);

        return (float) $query->sum('amount');
    }

    protected function getContributionTypeTotalsForMembersByDate(
        Collection $memberIds,
        Collection $types,
        ?Carbon $from,
        ?Carbon $to
    ): array {
        if ($memberIds->isEmpty() || $types->isEmpty()) {
            return [];
        }

        $totals = [];

        foreach ($types as $type) {
            $cash = 0.0;
            $bank = 0.0;

            if (Schema::hasTable('cash_contributions')) {
                $cashQuery = CashContribution::query()
                    ->whereIn('member_id', $memberIds)
                    ->where('contribution_type_id', $type['id']);

                $this->applyDateRange($cashQuery, 'contribution_date', $from, $to);
                $cash = (float) $cashQuery->sum('amount');
            }

            if (Schema::hasTable('bank_contributions')) {
                $bankQuery = BankContribution::query()
                    ->whereIn('member_id', $memberIds)
                    ->where('contribution_type_id', $type['id']);

                $this->applyDateRange($bankQuery, 'contribution_date', $from, $to);
                $bank = (float) $bankQuery->sum('amount');
            }

            $totals[$type['slug']] = [
                'id' => $type['id'],
                'name' => $type['name'],
                'slug' => $type['slug'],
                'amount' => $cash + $bank,
            ];
        }

        return $totals;
    }

    protected function normalizeShowFilters(Kanda $kanda, array $filters): array
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

        $jumuiyaId = isset($filters['jumuiya_id']) && $filters['jumuiya_id'] !== ''
            ? (int) $filters['jumuiya_id']
            : null;

        if ($jumuiyaId) {
            $exists = Jumuiya::where('kanda_id', $kanda->id)
                ->whereKey($jumuiyaId)
                ->exists();

            if (! $exists) {
                $jumuiyaId = null;
            }
        }

        return [
            'year' => $year,
            'month' => $month,
            'jumuiya_id' => $jumuiyaId,
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

    protected function buildKandaSummaryByDate(
        Kanda $kanda,
        Collection $jumuiyas,
        Collection $familias,
        Collection $members,
        ?Carbon $from,
        ?Carbon $to
    ): array {
        $memberIds = $members->pluck('id');
        $jumuiyaIds = $jumuiyas->pluck('id');

        $titheTotal = $this->sumTithesForJumuiyaIds($jumuiyaIds, $from, $to);

        $offeringTotal = $this->sumOfferingsForScope(
            $jumuiyaIds,
            collect([$kanda->id]),
            $from,
            $to,
            true
        );

        $cashTotal = 0.0;

        if (Schema::hasTable('cash_contributions') && $memberIds->isNotEmpty()) {
            $cashQuery = CashContribution::whereIn('member_id', $memberIds);
            $this->applyDateRange($cashQuery, 'contribution_date', $from, $to);
            $cashTotal = (float) $cashQuery->sum('amount');
        }

        $bankTotal = 0.0;

        if (Schema::hasTable('bank_contributions') && $memberIds->isNotEmpty()) {
            $bankQuery = BankContribution::whereIn('member_id', $memberIds);
            $this->applyDateRange($bankQuery, 'contribution_date', $from, $to);
            $bankTotal = (float) $bankQuery->sum('amount');
        }

        return [
            'jumuiyas_count' => $jumuiyas->count(),
            'familias_count' => $familias->count(),
            'members_count' => $members->count(),
            'male_members_count' => $members->where('gender', 'Male')->count(),
            'female_members_count' => $members->where('gender', 'Female')->count(),
            'tithe_total' => $titheTotal,
            'cash_total' => $cashTotal,
            'bank_total' => $bankTotal,
            'offering_total' => $offeringTotal,
            'grand_total' => $titheTotal + $cashTotal + $bankTotal + $offeringTotal,
        ];
    }

    protected function buildJumuiyaBreakdownByDate(
        Kanda $kanda,
        Collection $jumuiyas,
        Collection $familias,
        Collection $members,
        Collection $types,
        ?Carbon $from,
        ?Carbon $to
    ): Collection {
        return $jumuiyas->sortBy('name')->map(function (Jumuiya $jumuiya) use ($kanda, $familias, $members, $types, $from, $to) {
            $jumuiyaFamilias = $familias->where('jumuiya_id', $jumuiya->id);
            $jumuiyaMembers = $members->whereIn('familia_id', $jumuiyaFamilias->pluck('id'));
            $memberIds = $jumuiyaMembers->pluck('id');

            $titheTotal = $this->sumTithesForJumuiyaIds(collect([$jumuiya->id]), $from, $to);

            $offeringTotal = $this->sumOfferingsForScope(
                collect([$jumuiya->id]),
                collect([$kanda->id]),
                $from,
                $to,
                false
            );

            $typeTotals = $this->getContributionTypeTotalsForMembersByDate($memberIds, $types, $from, $to);
            $dynamicTotal = collect($typeTotals)->sum(fn ($row) => (float) ($row['amount'] ?? 0));

            return [
                'id' => $jumuiya->id,
                'name' => $jumuiya->name,
                'code' => $jumuiya->code,
                'familias_count' => $jumuiyaFamilias->count(),
                'members_count' => $jumuiyaMembers->count(),
                'offering_total' => $offeringTotal,
                'tithe_total' => $titheTotal,
                'contribution_type_totals' => $typeTotals,
                'grand_total' => $offeringTotal + $titheTotal + $dynamicTotal,
                'details_url' => Route::has('jumuiyas.show') ? route('jumuiyas.show', $jumuiya) : '#',
                'report_url' => Route::has('jumuiya-reports.show') ? route('jumuiya-reports.show', $jumuiya) : '#',
            ];
        })->values();
    }

    protected function getMonthlyFinanceChartByFilters(
        Kanda $kanda,
        int $year,
        ?int $jumuiyaId,
        Collection $types
    ): array {
        $jumuiyaIds = Jumuiya::where('kanda_id', $kanda->id)
            ->when($jumuiyaId, fn ($query) => $query->whereKey($jumuiyaId))
            ->pluck('id');

        $familiaIds = Familia::whereIn('jumuiya_id', $jumuiyaIds)->pluck('id');
        $memberIds = Member::whereIn('familia_id', $familiaIds)->pluck('id');

        $labels = [];
        $tithes = [];
        $offerings = [];
        $contributionSeries = [];

        foreach ($types as $type) {
            $contributionSeries[$type['slug']] = [
                'label' => $type['name'],
                'data' => [],
            ];
        }

        for ($month = 1; $month <= 12; $month++) {
            $labels[] = Carbon::create(null, $month, 1)->translatedFormat('M');

            $tithes[] = Schema::hasTable('tithes')
                ? (float) Tithe::whereIn('jumuiya_id', $jumuiyaIds)
                    ->whereYear('contribution_date', $year)
                    ->whereMonth('contribution_date', $month)
                    ->sum('amount')
                : 0;

            $offerings[] = Schema::hasTable('offerings')
                ? (float) Offering::where(function ($query) use ($jumuiyaIds, $kanda, $jumuiyaId) {
                    $query->whereIn('jumuiya_id', $jumuiyaIds);

                    if (! $jumuiyaId) {
                        $query->orWhere('kanda_id', $kanda->id);
                    }
                })
                    ->whereYear('collection_date', $year)
                    ->whereMonth('collection_date', $month)
                    ->sum('amount')
                : 0;

            foreach ($types as $type) {
                $cash = 0.0;
                $bank = 0.0;

                if (Schema::hasTable('cash_contributions')) {
                    $cash = (float) CashContribution::whereIn('member_id', $memberIds)
                        ->where('contribution_type_id', $type['id'])
                        ->whereYear('contribution_date', $year)
                        ->whereMonth('contribution_date', $month)
                        ->sum('amount');
                }

                if (Schema::hasTable('bank_contributions')) {
                    $bank = (float) BankContribution::whereIn('member_id', $memberIds)
                        ->where('contribution_type_id', $type['id'])
                        ->whereYear('contribution_date', $year)
                        ->whereMonth('contribution_date', $month)
                        ->sum('amount');
                }

                $contributionSeries[$type['slug']]['data'][] = $cash + $bank;
            }
        }

        return [
            'labels' => $labels,
            'tithes' => $tithes,
            'offerings' => $offerings,
            'contributions' => collect($contributionSeries)->values(),
        ];
    }

    protected function getMembersByJumuiyaChart(Collection $jumuiyas, Collection $familias, Collection $members): array
    {
        $rows = $jumuiyas->map(function (Jumuiya $jumuiya) use ($familias, $members) {
            $jumuiyaFamilias = $familias->where('jumuiya_id', $jumuiya->id);
            $count = $members->whereIn('familia_id', $jumuiyaFamilias->pluck('id'))->count();

            return [
                'label' => $jumuiya->name,
                'value' => $count,
            ];
        })->values();

        return [
            'labels' => $rows->pluck('label')->values(),
            'values' => $rows->pluck('value')->map(fn ($value) => (int) $value)->values(),
        ];
    }

    protected function getRecentMembersByFilters(Kanda $kanda, ?int $jumuiyaId = null): Collection
    {
        return Member::query()
            ->select('members.*')
            ->join('familias', 'familias.id', '=', 'members.familia_id')
            ->join('jumuiyas', 'jumuiyas.id', '=', 'familias.jumuiya_id')
            ->where('jumuiyas.kanda_id', $kanda->id)
            ->when($jumuiyaId, fn ($query) => $query->where('jumuiyas.id', $jumuiyaId))
            ->with('familia.jumuiya')
            ->latest('members.created_at')
            ->get();
    }

    public function getRecentMembersExportRows(Kanda $kanda, User $user, array $filters = []): Collection
    {
        $this->scopeAccessGate->authorizeKanda($user, $kanda);

        $normalizedFilters = $this->normalizeShowFilters($kanda, $filters);

        return $this->getRecentMembersByFilters($kanda, $normalizedFilters['jumuiya_id'])
            ->values()
            ->map(function (Member $member, int $index) {
                return (object) [
                    'sn' => $index + 1,
                    'member' => $member->full_name ?: '—',
                    'jumuiya' => $member->familia?->jumuiya?->name ?: '—',
                    'gender' => $this->localizedGender($member->gender),
                    'phone' => $member->phone ?: '—',
                    'joined' => $member->created_at?->translatedFormat('d M Y') ?: '—',
                ];
            });
    }

    public function getRecentMembersExportPdfData(Kanda $kanda, User $user, array $filters = []): array
    {
        $rows = $this->getRecentMembersExportRows($kanda, $user, $filters);

        return [
            'pageTitle' => db_trans('recent_members'),
            'reportTitle' => db_trans('waumini_wa_kanda_ya') . ' ' . $kanda->name,
            'kanda' => $kanda,
            'rows' => $rows,
            'metaItems' => [
                ['label' => db_trans('kanda'), 'value' => $kanda->name],
                ['label' => db_trans('members'), 'value' => number_format($rows->count())],
                ['label' => db_trans('generated_on'), 'value' => now()->translatedFormat('d M Y')],
            ],
        ];
    }

    protected function localizedGender(?string $gender): string
    {
        return match (strtolower((string) $gender)) {
            'male' => db_trans('male'),
            'female' => db_trans('female'),
            default => $gender ?: '—',
        };
    }
}