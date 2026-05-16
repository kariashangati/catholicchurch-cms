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

class KandaReportService
{
    public function __construct(
        protected UserScopeResolver $scopeResolver,
        protected ScopeAccessGate $scopeAccessGate,
    ) {
    }

    public function getIndexData(User $user, array $filters = []): array
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;
        $contributionTypes = $this->activeContributionTypes();

        $kandas = $this->scopedKandasQuery($user)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Kanda $kanda) => $this->buildKandaReportRow($kanda, $year, $month, $contributionTypes));

        $typeTotals = $this->sumTypeTotals($kandas, $contributionTypes);

        $stats = [
            'total_kandas' => $kandas->count(),
            'total_jumuiyas' => $kandas->sum('jumuiyas_count'),
            'total_familias' => $kandas->sum('familias_count'),
            'total_members' => $kandas->sum('members_count'),
            'total_tithes' => (float) $kandas->sum('tithe_total'),
            'total_offerings' => (float) $kandas->sum('offering_total'),
            'total_contributions' => (float) $kandas->sum('contribution_total'),
            'grand_total' => (float) $kandas->sum('grand_total'),
            'contribution_type_totals' => $typeTotals,
        ];

        return [
            'pageTitle' => db_trans('kanda_financial_reports'),
            'hero' => [
                'eyebrow' => db_trans('kanda_financial_reports'),
                'title' => db_trans('kanda_financial_reports'),
                'scope_label' => $this->resolveScopeLabel($user),
                'period' => $this->formatPeriodLabel($year, $month),
                'grand_total' => number_format($stats['grand_total'], 2),
            ],
            'selectedYear' => $year,
            'selectedMonth' => $month,
            'availableYears' => range(now()->year, now()->year - 5),
            'reports' => $kandas,
            'stats' => $stats,
            'contributionTypes' => $contributionTypes,
            'summaryCards' => [
                [
                    'title' => db_trans('total_kandas'),
                    'value' => number_format($stats['total_kandas']),
                    'icon' => 'fas fa-map-marked-alt',
                    'tone' => 'primary',
                ],
                [
                    'title' => db_trans('total_jumuiyas'),
                    'value' => number_format($stats['total_jumuiyas']),
                    'icon' => 'fas fa-layer-group',
                    'tone' => 'info',
                ],
                [
                    'title' => db_trans('total_familias'),
                    'value' => number_format($stats['total_familias']),
                    'icon' => 'fas fa-home',
                    'tone' => 'secondary',
                ],
                [
                    'title' => db_trans('total_members'),
                    'value' => number_format($stats['total_members']),
                    'icon' => 'fas fa-users',
                    'tone' => 'success',
                ],
            ],
            'financeCards' => $this->financeCards($stats, $contributionTypes),
            'chartData' => [
                'labels' => $kandas->pluck('name')->values(),
                'tithes' => $kandas->pluck('tithe_total')->map(fn ($v) => (float) $v)->values(),
                'offerings' => $kandas->pluck('offering_total')->map(fn ($v) => (float) $v)->values(),
                'contribution_types' => $contributionTypes->map(function (ContributionType $type) use ($kandas) {
                    return [
                        'id' => $type->id,
                        'label' => $type->name,
                        'data' => $kandas->map(fn ($kanda) => (float) data_get($kanda, 'contribution_type_totals.' . $type->id, 0))->values(),
                    ];
                })->values(),
                'grand_total' => $kandas->pluck('grand_total')->map(fn ($v) => (float) $v)->values(),
            ],
            'compositionChart' => [
                'labels' => collect([db_trans('tithes'), db_trans('offerings')])
                    ->merge($contributionTypes->pluck('name'))
                    ->values(),
                'values' => collect([(float) $stats['total_tithes'], (float) $stats['total_offerings']])
                    ->merge($contributionTypes->map(fn ($type) => (float) data_get($typeTotals, $type->id, 0)))
                    ->values(),
            ],
            'quickLinks' => $this->getQuickLinks($user),
        ];
    }

    public function getShowData(Kanda $kanda, User $user, array $filters = []): array
    {
        $this->scopeAccessGate->authorizeKanda($user, $kanda);

        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;
        $contributionTypes = $this->activeContributionTypes();

        $reportRow = $this->buildKandaReportRow($kanda, $year, $month, $contributionTypes);
        $jumuiyas = $this->buildJumuiyaReportRows($kanda, $year, $month, $contributionTypes);
        $summary = $this->buildKandaSummary($reportRow, $contributionTypes);
        $monthlyTrends = $this->buildMonthlyTrends($kanda, $year, $contributionTypes);

        return [
            'pageTitle' => db_trans('kanda_financial_report'),
            'kanda' => $kanda,
            'hero' => [
                'eyebrow' => db_trans('kanda_financial_report'),
                'title' => $kanda->name,
                'scope_label' => $this->resolveScopeLabel($user),
                'period' => $this->formatPeriodLabel($year, $month),
            ],
            'selectedYear' => $year,
            'selectedMonth' => $month,
            'availableYears' => range(now()->year, now()->year - 5),
            'stats' => $summary,
            'contributionTypes' => $contributionTypes,
            'summaryCards' => [
                [
                    'title' => db_trans('jumuiyas'),
                    'value' => number_format($summary['jumuiyas_count']),
                    'icon' => 'fas fa-layer-group',
                    'tone' => 'primary',
                ],
                [
                    'title' => db_trans('familias'),
                    'value' => number_format($summary['familias_count']),
                    'icon' => 'fas fa-home',
                    'tone' => 'info',
                ],
                [
                    'title' => db_trans('members'),
                    'value' => number_format($summary['members_count']),
                    'icon' => 'fas fa-users',
                    'tone' => 'success',
                ],
            ],
            'financeCards' => $this->financeCards($summary, $contributionTypes),
            'jumuiyas' => $jumuiyas,
            'monthlyTrends' => $monthlyTrends,
            'compositionChart' => [
                'labels' => collect([db_trans('tithes'), db_trans('offerings')])
                    ->merge($contributionTypes->pluck('name'))
                    ->values(),
                'values' => collect([(float) $summary['total_tithes'], (float) $summary['total_offerings']])
                    ->merge($contributionTypes->map(fn ($type) => (float) data_get($summary, 'contribution_type_totals.' . $type->id, 0)))
                    ->values(),
            ],
            'recentTopline' => $reportRow,
        ];
    }

    protected function activeContributionTypes(): Collection
    {
        if (! class_exists(ContributionType::class) || ! Schema::hasTable('contribution_types')) {
            return collect();
        }

        return ContributionType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
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

    protected function buildKandaReportRow(Kanda $kanda, int $year, ?int $month, Collection $contributionTypes): Kanda
    {
        $jumuiyaIds = Jumuiya::where('kanda_id', $kanda->id)->pluck('id');
        $familiaIds = Familia::whereIn('jumuiya_id', $jumuiyaIds)->pluck('id');
        $memberIds = Member::whereIn('familia_id', $familiaIds)->pluck('id');

        $titheQuery = Tithe::whereIn('jumuiya_id', $jumuiyaIds)->whereYear('contribution_date', $year);
        $offeringQuery = Schema::hasTable('offerings')
            ? Offering::query()
                ->where(function ($query) use ($jumuiyaIds, $kanda) {
                    $query->whereIn('jumuiya_id', $jumuiyaIds);
                    if (Schema::hasColumn('offerings', 'kanda_id')) {
                        $query->orWhere('kanda_id', $kanda->id);
                    }
                })
                ->whereYear('collection_date', $year)
            : null;

        if ($month) {
            $titheQuery->whereMonth('contribution_date', $month);
            if ($offeringQuery) {
                $offeringQuery->whereMonth('collection_date', $month);
            }
        }

        $typeTotals = $this->contributionTotalsByType($memberIds, $jumuiyaIds, $kanda->id, $year, $month, $contributionTypes);
        $contributionTotal = (float) collect($typeTotals)->sum();
        $tithes = (float) $titheQuery->sum('amount');
        $offerings = $offeringQuery ? (float) $offeringQuery->sum('amount') : 0;

        $kanda->jumuiyas_count = $jumuiyaIds->count();
        $kanda->familias_count = $familiaIds->count();
        $kanda->members_count = $memberIds->count();
        $kanda->tithe_total = $tithes;
        $kanda->offering_total = $offerings;
        $kanda->contribution_type_totals = $typeTotals;
        $kanda->contribution_total = $contributionTotal;
        $kanda->grand_total = $tithes + $offerings + $contributionTotal;

        return $kanda;
    }

    protected function buildKandaSummary(Kanda $row, Collection $contributionTypes): array
    {
        return [
            'jumuiyas_count' => $row->jumuiyas_count,
            'familias_count' => $row->familias_count,
            'members_count' => $row->members_count,
            'total_tithes' => $row->tithe_total,
            'total_offerings' => $row->offering_total,
            'total_contributions' => $row->contribution_total,
            'grand_total' => $row->grand_total,
            'contribution_type_totals' => collect($contributionTypes)->mapWithKeys(fn ($type) => [
                $type->id => (float) data_get($row, 'contribution_type_totals.' . $type->id, 0),
            ])->all(),
        ];
    }

    protected function buildJumuiyaReportRows(Kanda $kanda, int $year, ?int $month, Collection $contributionTypes): Collection
    {
        return Jumuiya::where('kanda_id', $kanda->id)
            ->orderBy('name')
            ->get()
            ->map(function (Jumuiya $jumuiya) use ($year, $month, $kanda, $contributionTypes) {
                $familiaIds = Familia::where('jumuiya_id', $jumuiya->id)->pluck('id');
                $memberIds = Member::whereIn('familia_id', $familiaIds)->pluck('id');
                $jumuiyaIds = collect([$jumuiya->id]);

                $titheQuery = Tithe::where('jumuiya_id', $jumuiya->id)->whereYear('contribution_date', $year);
                $offeringQuery = Schema::hasTable('offerings')
                    ? Offering::where('jumuiya_id', $jumuiya->id)->whereYear('collection_date', $year)
                    : null;

                if ($month) {
                    $titheQuery->whereMonth('contribution_date', $month);
                    if ($offeringQuery) {
                        $offeringQuery->whereMonth('collection_date', $month);
                    }
                }

                $typeTotals = $this->contributionTotalsByType($memberIds, $jumuiyaIds, $kanda->id, $year, $month, $contributionTypes);
                $contributionTotal = (float) collect($typeTotals)->sum();
                $tithes = (float) $titheQuery->sum('amount');
                $offerings = $offeringQuery ? (float) $offeringQuery->sum('amount') : 0;

                return [
                    'id' => $jumuiya->id,
                    'name' => $jumuiya->name,
                    'code' => $jumuiya->code,
                    'familias_count' => $familiaIds->count(),
                    'members_count' => $memberIds->count(),
                    'tithe_total' => $tithes,
                    'offering_total' => $offerings,
                    'contribution_type_totals' => $typeTotals,
                    'contribution_total' => $contributionTotal,
                    'grand_total' => $tithes + $offerings + $contributionTotal,
                ];
            });
    }

    protected function buildMonthlyTrends(Kanda $kanda, int $year, Collection $contributionTypes): array
    {
        $jumuiyaIds = Jumuiya::where('kanda_id', $kanda->id)->pluck('id');
        $familiaIds = Familia::whereIn('jumuiya_id', $jumuiyaIds)->pluck('id');
        $memberIds = Member::whereIn('familia_id', $familiaIds)->pluck('id');

        $labels = [];
        $tithes = [];
        $offerings = [];
        $typeSeries = $contributionTypes->mapWithKeys(fn ($type) => [$type->id => []])->all();

        for ($month = 1; $month <= 12; $month++) {
            $labels[] = Carbon::create(null, $month, 1)->translatedFormat('M');

            $tithes[] = (float) Tithe::whereIn('jumuiya_id', $jumuiyaIds)
                ->whereYear('contribution_date', $year)
                ->whereMonth('contribution_date', $month)
                ->sum('amount');

            $offerings[] = Schema::hasTable('offerings')
                ? (float) Offering::where(function ($query) use ($jumuiyaIds, $kanda) {
                    $query->whereIn('jumuiya_id', $jumuiyaIds);
                    if (Schema::hasColumn('offerings', 'kanda_id')) {
                        $query->orWhere('kanda_id', $kanda->id);
                    }
                })
                ->whereYear('collection_date', $year)
                ->whereMonth('collection_date', $month)
                ->sum('amount')
                : 0;

            $monthlyTypes = $this->contributionTotalsByType($memberIds, $jumuiyaIds, $kanda->id, $year, $month, $contributionTypes);

            foreach ($contributionTypes as $type) {
                $typeSeries[$type->id][] = (float) data_get($monthlyTypes, $type->id, 0);
            }
        }

        return [
            'labels' => $labels,
            'tithes' => $tithes,
            'offerings' => $offerings,
            'contribution_types' => $contributionTypes->map(fn ($type) => [
                'id' => $type->id,
                'label' => $type->name,
                'data' => $typeSeries[$type->id] ?? [],
            ])->values(),
        ];
    }

    protected function contributionTotalsByType(Collection $memberIds, Collection $jumuiyaIds, int $kandaId, int $year, ?int $month, Collection $contributionTypes): array
    {
        $totals = $contributionTypes->mapWithKeys(fn ($type) => [$type->id => 0.0])->all();

        if ($contributionTypes->isEmpty()) {
            return $totals;
        }

        $typeIds = $contributionTypes->pluck('id');

        if (Schema::hasTable('cash_contributions')) {
            $cash = CashContribution::query()
                ->whereIn('contribution_type_id', $typeIds)
                ->whereYear('contribution_date', $year)
                ->when($month, fn ($query) => $query->whereMonth('contribution_date', $month))
                ->where(function ($query) use ($memberIds, $jumuiyaIds, $kandaId) {
                    $query->whereIn('member_id', $memberIds);
                    if (Schema::hasColumn('cash_contributions', 'jumuiya_id')) {
                        $query->orWhereIn('jumuiya_id', $jumuiyaIds);
                    }
                    if (Schema::hasColumn('cash_contributions', 'kanda_id')) {
                        $query->orWhere('kanda_id', $kandaId);
                    }
                })
                ->selectRaw('contribution_type_id, SUM(amount) as amount')
                ->groupBy('contribution_type_id')
                ->pluck('amount', 'contribution_type_id');

            foreach ($cash as $typeId => $amount) {
                $totals[(int) $typeId] = (float) ($totals[(int) $typeId] ?? 0) + (float) $amount;
            }
        }

        if (Schema::hasTable('bank_contributions')) {
            $bank = BankContribution::query()
                ->whereIn('contribution_type_id', $typeIds)
                ->whereYear('contribution_date', $year)
                ->when($month, fn ($query) => $query->whereMonth('contribution_date', $month))
                ->where(function ($query) use ($memberIds, $jumuiyaIds, $kandaId) {
                    $query->whereIn('member_id', $memberIds);
                    if (Schema::hasColumn('bank_contributions', 'jumuiya_id')) {
                        $query->orWhereIn('jumuiya_id', $jumuiyaIds);
                    }
                    if (Schema::hasColumn('bank_contributions', 'kanda_id')) {
                        $query->orWhere('kanda_id', $kandaId);
                    }
                })
                ->selectRaw('contribution_type_id, SUM(amount) as amount')
                ->groupBy('contribution_type_id')
                ->pluck('amount', 'contribution_type_id');

            foreach ($bank as $typeId => $amount) {
                $totals[(int) $typeId] = (float) ($totals[(int) $typeId] ?? 0) + (float) $amount;
            }
        }

        return $totals;
    }

    protected function financeCards(array $stats, Collection $contributionTypes): array
    {
        $cards = [
            [
                'title' => db_trans('total_tithes'),
                'value' => number_format((float) ($stats['total_tithes'] ?? 0), 2),
                'icon' => 'fas fa-coins',
                'tone' => 'success',
            ],
            [
                'title' => db_trans('total_offerings'),
                'value' => number_format((float) ($stats['total_offerings'] ?? 0), 2),
                'icon' => 'fas fa-hand-holding-heart',
                'tone' => 'primary',
            ],
        ];

        $tones = ['warning', 'info', 'secondary', 'primary', 'success', 'dark'];

        foreach ($contributionTypes as $index => $type) {
            $cards[] = [
                'title' => $type->name,
                'value' => number_format((float) data_get($stats, 'contribution_type_totals.' . $type->id, 0), 2),
                'icon' => 'fas fa-tags',
                'tone' => $tones[$index % count($tones)],
            ];
        }

        return $cards;
    }

    protected function sumTypeTotals(Collection $rows, Collection $contributionTypes): array
    {
        return $contributionTypes->mapWithKeys(fn ($type) => [
            $type->id => (float) $rows->sum(fn ($row) => (float) data_get($row, 'contribution_type_totals.' . $type->id, 0)),
        ])->all();
    }

    protected function getQuickLinks(User $user): array
    {
        $links = [];

        if ($user->can('kanda-reports.view') && Route::has('kanda-reports.index')) {
            $links[] = ['label' => db_trans('report_overview'), 'route' => route('kanda-reports.index'), 'icon' => 'fas fa-chart-column'];
        }

        if ($user->can('kandas.view') && Route::has('kandas.index')) {
            $links[] = ['label' => db_trans('kandas'), 'route' => route('kandas.index'), 'icon' => 'fas fa-map-marked-alt'];
        }

        return $links;
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

    protected function formatPeriodLabel(int $year, ?int $month): string
    {
        if ($month) {
            return Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
        }

        return (string) $year;
    }

    public function getIndexExportData(User $user, array $filters = []): array
    {
        $data = $this->getIndexData($user, $filters);

        return [
            'pageTitle' => db_trans('kanda_financial_reports'),
            'reportTitle' => db_trans('kanda_financial_reports') . ' - ' . ($data['hero']['period'] ?? ''),
            'metaItems' => [
                ['label' => db_trans('scope'), 'value' => $data['hero']['scope_label'] ?? '—'],
                ['label' => db_trans('period'), 'value' => $data['hero']['period'] ?? '—'],
                ['label' => db_trans('total_kandas'), 'value' => number_format((int) data_get($data, 'stats.total_kandas', 0))],
                ['label' => db_trans('grand_total'), 'value' => number_format((float) data_get($data, 'stats.grand_total', 0), 2)],
            ],
            'isShowReport' => false,
            'selectedYear' => $data['selectedYear'] ?? now()->year,
            'selectedMonth' => $data['selectedMonth'] ?? null,
            'reports' => collect($data['reports'] ?? []),
            'jumuiyas' => collect(),
            'stats' => $data['stats'] ?? [],
            'contributionTypes' => collect($data['contributionTypes'] ?? []),
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function getShowExportData(Kanda $kanda, User $user, array $filters = []): array
    {
        $data = $this->getShowData($kanda, $user, $filters);

        return [
            'pageTitle' => db_trans('kanda_financial_report'),
            'reportTitle' => ($kanda->name ?? db_trans('kanda_financial_report')) . ' - ' . ($data['hero']['period'] ?? ''),
            'metaItems' => [
                ['label' => db_trans('kanda'), 'value' => $kanda->name ?? '—'],
                ['label' => db_trans('code'), 'value' => $kanda->code ?? '—'],
                ['label' => db_trans('period'), 'value' => $data['hero']['period'] ?? '—'],
                ['label' => db_trans('grand_total'), 'value' => number_format((float) data_get($data, 'stats.grand_total', 0), 2)],
            ],
            'isShowReport' => true,
            'kanda' => $kanda,
            'selectedYear' => $data['selectedYear'] ?? now()->year,
            'selectedMonth' => $data['selectedMonth'] ?? null,
            'reports' => collect(),
            'jumuiyas' => collect($data['jumuiyas'] ?? []),
            'stats' => $data['stats'] ?? [],
            'contributionTypes' => collect($data['contributionTypes'] ?? []),
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function indexExportRows(array $data): array
    {
        $contributionTypes = collect($data['contributionTypes'] ?? []);
        $rows = [];

        $header = [
            db_trans('kanda'),
            db_trans('jumuiyas'),
            db_trans('familias'),
            db_trans('members'),
            db_trans('tithes'),
            db_trans('offerings'),
        ];

        foreach ($contributionTypes as $type) {
            $header[] = $type->name;
        }

        $header[] = db_trans('grand_total');
        $rows[] = $header;

        foreach (collect($data['reports'] ?? []) as $report) {
            $row = [
                $report->name ?? '—',
                (int) ($report->jumuiyas_count ?? 0),
                (int) ($report->familias_count ?? 0),
                (int) ($report->members_count ?? 0),
                (float) ($report->tithe_total ?? 0),
                (float) ($report->offering_total ?? 0),
            ];

            foreach ($contributionTypes as $type) {
                $row[] = (float) data_get($report, 'contribution_type_totals.' . $type->id, 0);
            }

            $row[] = (float) ($report->grand_total ?? 0);
            $rows[] = $row;
        }

        $stats = $data['stats'] ?? [];

        $footer = [
            db_trans('grand_total'),
            (int) data_get($stats, 'total_jumuiyas', 0),
            (int) data_get($stats, 'total_familias', 0),
            (int) data_get($stats, 'total_members', 0),
            (float) data_get($stats, 'total_tithes', 0),
            (float) data_get($stats, 'total_offerings', 0),
        ];

        foreach ($contributionTypes as $type) {
            $footer[] = (float) data_get($stats, 'contribution_type_totals.' . $type->id, 0);
        }

        $footer[] = (float) data_get($stats, 'grand_total', 0);
        $rows[] = $footer;

        return $rows;
    }

    public function showExportRows(array $data): array
    {
        $contributionTypes = collect($data['contributionTypes'] ?? []);
        $rows = [];

        $header = [
            db_trans('jumuiya'),
            db_trans('familias'),
            db_trans('members'),
            db_trans('tithes'),
            db_trans('offerings'),
        ];

        foreach ($contributionTypes as $type) {
            $header[] = $type->name;
        }

        $header[] = db_trans('grand_total');
        $rows[] = $header;

        foreach (collect($data['jumuiyas'] ?? []) as $jumuiya) {
            $row = [
                data_get($jumuiya, 'name', '—'),
                (int) data_get($jumuiya, 'familias_count', 0),
                (int) data_get($jumuiya, 'members_count', 0),
                (float) data_get($jumuiya, 'tithe_total', 0),
                (float) data_get($jumuiya, 'offering_total', 0),
            ];

            foreach ($contributionTypes as $type) {
                $row[] = (float) data_get($jumuiya, 'contribution_type_totals.' . $type->id, 0);
            }

            $row[] = (float) data_get($jumuiya, 'grand_total', 0);
            $rows[] = $row;
        }

        $jumuiyas = collect($data['jumuiyas'] ?? []);

        $footer = [
            db_trans('grand_total'),
            (int) $jumuiyas->sum('familias_count'),
            (int) $jumuiyas->sum('members_count'),
            (float) $jumuiyas->sum('tithe_total'),
            (float) $jumuiyas->sum('offering_total'),
        ];

        foreach ($contributionTypes as $type) {
            $footer[] = (float) $jumuiyas->sum(fn ($row) => (float) data_get($row, 'contribution_type_totals.' . $type->id, 0));
        }

        $footer[] = (float) $jumuiyas->sum('grand_total');
        $rows[] = $footer;

        return $rows;
    }
}