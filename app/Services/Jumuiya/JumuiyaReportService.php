<?php

namespace App\Services\Jumuiya;

use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\ContributionType;
use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Member;
use App\Models\Offering;
use App\Models\Tithe;
use App\Models\User;
use App\Services\Access\ScopeAccessGate;
use App\Services\Access\UserScopeResolver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class JumuiyaReportService
{

public function getIndexExportData(User $user, array $filters = []): array
{
    $data = $this->getIndexData($user, $filters);

    return [
        'pageTitle' => db_trans('jumuiya_financial_reports'),
        'reportTitle' => db_trans('jumuiya_financial_reports') . ' - ' . $this->formatPeriodLabel(
            (int) ($data['selectedYear'] ?? now()->year),
            $data['selectedMonth'] ?? null
        ),
        'metaItems' => [
            ['label' => db_trans('period'), 'value' => $this->formatPeriodLabel((int) ($data['selectedYear'] ?? now()->year), $data['selectedMonth'] ?? null)],
            ['label' => db_trans('total_jumuiyas'), 'value' => number_format((int) data_get($data, 'stats.total_jumuiyas', 0))],
            ['label' => db_trans('total_familias'), 'value' => number_format((int) data_get($data, 'stats.total_familias', 0))],
            ['label' => db_trans('grand_total'), 'value' => number_format((float) data_get($data, 'stats.grand_total', 0), 2)],
        ],
        'isShowReport' => false,
        'selectedYear' => $data['selectedYear'] ?? now()->year,
        'selectedMonth' => $data['selectedMonth'] ?? null,
        'reports' => collect($data['reports'] ?? []),
        'familyBreakdown' => collect(),
        'recentTransactions' => collect(),
        'stats' => $data['stats'] ?? [],
        'contributionTypes' => collect($data['contributionTypes'] ?? []),
        'issuedAtText' => now()->translatedFormat('d F Y'),
        'locale' => app()->getLocale(),
    ];
}

public function getShowExportData(Jumuiya $jumuiya, User $user, array $filters = []): array
{
    $data = $this->getShowData($jumuiya, $user, $filters);

    return [
        'pageTitle' => db_trans('jumuiya_financial_report'),
        'reportTitle' => ($jumuiya->name ?? db_trans('jumuiya_financial_report')) . ' - ' . $this->formatPeriodLabel(
            (int) ($data['selectedYear'] ?? now()->year),
            $data['selectedMonth'] ?? null
        ),
        'metaItems' => [
            ['label' => db_trans('jumuiya'), 'value' => $jumuiya->name ?? '—'],
            ['label' => db_trans('kanda'), 'value' => $jumuiya->kanda?->name ?? '—'],
            ['label' => db_trans('period'), 'value' => $this->formatPeriodLabel((int) ($data['selectedYear'] ?? now()->year), $data['selectedMonth'] ?? null)],
            ['label' => db_trans('grand_total'), 'value' => number_format((float) data_get($data, 'stats.grand_total', 0), 2)],
        ],
        'isShowReport' => true,
        'jumuiya' => $jumuiya,
        'selectedYear' => $data['selectedYear'] ?? now()->year,
        'selectedMonth' => $data['selectedMonth'] ?? null,
        'reports' => collect(),
        'familyBreakdown' => collect($data['familyBreakdown'] ?? []),
        'recentTransactions' => collect($data['recentTransactions'] ?? []),
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
        db_trans('jumuiya'),
        db_trans('kanda'),
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
            $report->kanda?->name ?? '—',
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
        '—',
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

public function showFamilyExportRows(array $data): array
{
    $contributionTypes = collect($data['contributionTypes'] ?? []);
    $families = collect($data['familyBreakdown'] ?? []);
    $rows = [];

    $header = [
        db_trans('familia'),
        db_trans('members'),
        db_trans('tithes'),
    ];

    foreach ($contributionTypes as $type) {
        $header[] = $type->name;
    }

    $header[] = db_trans('grand_total');
    $rows[] = $header;

    foreach ($families as $familia) {
        $row = [
            $familia->name ?? '—',
            (int) ($familia->members_count ?? 0),
            (float) ($familia->tithe_total ?? 0),
        ];

        foreach ($contributionTypes as $type) {
            $row[] = (float) data_get($familia, 'contribution_type_totals.' . $type->id, 0);
        }

        $row[] = (float) ($familia->grand_total ?? 0);
        $rows[] = $row;
    }

    $footer = [
        db_trans('grand_total'),
        (int) $families->sum('members_count'),
        (float) $families->sum('tithe_total'),
    ];

    foreach ($contributionTypes as $type) {
        $footer[] = (float) $families->sum(fn ($row) => (float) data_get($row, 'contribution_type_totals.' . $type->id, 0));
    }

    $footer[] = (float) $families->sum('grand_total');
    $rows[] = $footer;

    return $rows;
}

public function showTransactionsExportRows(array $data): array
{
    $rows = [[
        db_trans('source'),
        db_trans('type'),
        db_trans('member'),
        db_trans('familia'),
        db_trans('amount'),
        db_trans('date'),
        db_trans('status'),
    ]];

    foreach (collect($data['recentTransactions'] ?? []) as $item) {
        $rows[] = [
            $item->source ?? '—',
            $item->category ?? '—',
            $item->member_name ?? '—',
            $item->familia_name ?? '—',
            (float) ($item->amount ?? 0),
            $item->date ? \Carbon\Carbon::parse($item->date)->format('Y-m-d') : '—',
            $item->status ?? '—',
        ];
    }

    return $rows;
}

protected function formatPeriodLabel(int $year, ?int $month): string
{
    if ($month) {
        return Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');
    }

    return (string) $year;
}


    public function __construct(
        protected UserScopeResolver $scopeResolver,
        protected ScopeAccessGate $scopeAccessGate,
    ) {
    }

    public function getIndexData(User $user, array $filters = []): array
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;
        $contributionTypes = $this->contributionTypes();

        $jumuiyas = $this->scopedJumuiyasQuery($user)
            ->with('kanda')
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Jumuiya $jumuiya) => $this->buildJumuiyaReportRow($jumuiya, $year, $month, $contributionTypes));

        $contributionTypeTotals = $this->sumContributionTypeTotals($jumuiyas, $contributionTypes);

        return [
            'pageTitle' => db_trans('jumuiya_financial_reports'),
            'selectedYear' => $year,
            'selectedMonth' => $month,
            'availableYears' => range(now()->year, now()->year - 5),
            'reports' => $jumuiyas,
            'contributionTypes' => $contributionTypes,
            'stats' => [
                'total_jumuiyas' => $jumuiyas->count(),
                'total_familias' => $jumuiyas->sum('familias_count'),
                'total_members' => $jumuiyas->sum('members_count'),
                'total_tithes' => $jumuiyas->sum('tithe_total'),
                'total_offerings' => $jumuiyas->sum('offering_total'),
                'contribution_type_totals' => $contributionTypeTotals,
                'total_contributions' => collect($contributionTypeTotals)->sum(),
                'grand_total' => $jumuiyas->sum('grand_total'),
            ],
            'chartData' => $this->buildIndexChartData($jumuiyas, $contributionTypes),
        ];
    }

    public function getShowData(Jumuiya $jumuiya, User $user, array $filters = []): array
    {
        $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);

        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;
        $contributionTypes = $this->contributionTypes();

        $jumuiya->load('kanda');

        $summary = $this->buildJumuiyaSummary($jumuiya, $year, $month, $contributionTypes);
        $monthlyTrends = $this->buildMonthlyTrends($jumuiya, $year, $contributionTypes);
        $familyBreakdown = $this->buildFamilyBreakdown($jumuiya, $year, $month, $contributionTypes);
        $recentTransactions = $this->getRecentTransactions($jumuiya, $year, $month);

        return [
            'pageTitle' => db_trans('jumuiya_financial_report'),
            'jumuiya' => $jumuiya,
            'selectedYear' => $year,
            'selectedMonth' => $month,
            'availableYears' => range(now()->year, now()->year - 5),
            'contributionTypes' => $contributionTypes,
            'stats' => $summary,
            'monthlyTrends' => $monthlyTrends,
            'familyBreakdown' => $familyBreakdown,
            'recentTransactions' => $recentTransactions,
        ];
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

    protected function contributionTypes(): Collection
    {
        return ContributionType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function buildJumuiyaReportRow(Jumuiya $jumuiya, int $year, ?int $month, Collection $contributionTypes): Jumuiya
    {
        $familiaIds = Familia::where('jumuiya_id', $jumuiya->id)->pluck('id');
        $memberIds = Member::whereIn('familia_id', $familiaIds)->pluck('id');
        $contributionTypeTotals = $this->contributionTotalsByType($memberIds, $year, $month, $contributionTypes);

        $titheQuery = Tithe::where('jumuiya_id', $jumuiya->id)->whereYear('contribution_date', $year);
        $offeringQuery = Offering::where('collection_scope', Offering::SCOPE_JUMUIYA)
            ->where('jumuiya_id', $jumuiya->id)
            ->whereYear('collection_date', $year);

        if ($month) {
            $titheQuery->whereMonth('contribution_date', $month);
            $offeringQuery->whereMonth('collection_date', $month);
        }

        $jumuiya->familias_count = $familiaIds->count();
        $jumuiya->members_count = $memberIds->count();
        $jumuiya->tithe_total = (float) $titheQuery->sum('amount');
        $jumuiya->offering_total = (float) $offeringQuery->sum('amount');
        $jumuiya->contribution_type_totals = $contributionTypeTotals;
        $jumuiya->contribution_total = collect($contributionTypeTotals)->sum();
        $jumuiya->grand_total = $jumuiya->tithe_total + $jumuiya->offering_total + $jumuiya->contribution_total;

        return $jumuiya;
    }

    protected function buildJumuiyaSummary(Jumuiya $jumuiya, int $year, ?int $month, Collection $contributionTypes): array
    {
        $row = $this->buildJumuiyaReportRow($jumuiya, $year, $month, $contributionTypes);

        return [
            'familias_count' => $row->familias_count,
            'members_count' => $row->members_count,
            'tithe_total' => $row->tithe_total,
            'offering_total' => $row->offering_total,
            'contribution_type_totals' => $row->contribution_type_totals,
            'contribution_total' => $row->contribution_total,
            'grand_total' => $row->grand_total,
        ];
    }

    protected function buildMonthlyTrends(Jumuiya $jumuiya, int $year, Collection $contributionTypes): array
    {
        $familiaIds = Familia::where('jumuiya_id', $jumuiya->id)->pluck('id');
        $memberIds = Member::whereIn('familia_id', $familiaIds)->pluck('id');

        $labels = [];
        $tithes = [];
        $offerings = [];
        $contributions = [];

        foreach ($contributionTypes as $type) {
            $contributions[$type->id] = [];
        }

        for ($month = 1; $month <= 12; $month++) {
            $labels[] = Carbon::create(null, $month, 1)->translatedFormat('M');

            $tithes[] = (float) Tithe::where('jumuiya_id', $jumuiya->id)
                ->whereYear('contribution_date', $year)
                ->whereMonth('contribution_date', $month)
                ->sum('amount');

            $offerings[] = (float) Offering::where('collection_scope', Offering::SCOPE_JUMUIYA)
                ->where('jumuiya_id', $jumuiya->id)
                ->whereYear('collection_date', $year)
                ->whereMonth('collection_date', $month)
                ->sum('amount');

            $monthTotals = $this->contributionTotalsByType($memberIds, $year, $month, $contributionTypes);

            foreach ($contributionTypes as $type) {
                $contributions[$type->id][] = (float) ($monthTotals[$type->id] ?? 0);
            }
        }

        return [
            'labels' => $labels,
            'tithes' => $tithes,
            'offerings' => $offerings,
            'contributions' => $contributions,
        ];
    }

    protected function buildFamilyBreakdown(Jumuiya $jumuiya, int $year, ?int $month, Collection $contributionTypes): Collection
    {
        return Familia::where('jumuiya_id', $jumuiya->id)
            ->orderBy('name')
            ->get()
            ->map(function (Familia $familia) use ($year, $month, $jumuiya, $contributionTypes) {
                $memberIds = Member::where('familia_id', $familia->id)->pluck('id');
                $contributionTypeTotals = $this->contributionTotalsByType($memberIds, $year, $month, $contributionTypes);

                $titheQuery = Tithe::where('jumuiya_id', $jumuiya->id)
                    ->whereIn('member_id', $memberIds)
                    ->whereYear('contribution_date', $year);

                if ($month) {
                    $titheQuery->whereMonth('contribution_date', $month);
                }

                $familia->members_count = $memberIds->count();
                $familia->tithe_total = (float) $titheQuery->sum('amount');
                $familia->contribution_type_totals = $contributionTypeTotals;
                $familia->contribution_total = collect($contributionTypeTotals)->sum();
                $familia->grand_total = $familia->tithe_total + $familia->contribution_total;

                return $familia;
            })
            ->sortByDesc('grand_total')
            ->values();
    }

    protected function contributionTotalsByType(Collection $memberIds, int $year, ?int $month, Collection $contributionTypes): array
    {
        $totals = $contributionTypes->mapWithKeys(fn ($type) => [$type->id => 0.0])->all();

        if ($memberIds->isEmpty() || $contributionTypes->isEmpty()) {
            return $totals;
        }

        $cashQuery = CashContribution::query()
            ->whereIn('member_id', $memberIds)
            ->whereYear('contribution_date', $year);

        $bankQuery = BankContribution::query()
            ->whereIn('member_id', $memberIds)
            ->whereYear('contribution_date', $year);

        if ($month) {
            $cashQuery->whereMonth('contribution_date', $month);
            $bankQuery->whereMonth('contribution_date', $month);
        }

        $cashQuery->selectRaw('contribution_type_id, SUM(amount) as total')
            ->groupBy('contribution_type_id')
            ->pluck('total', 'contribution_type_id')
            ->each(function ($amount, $typeId) use (&$totals) {
                if (array_key_exists((int) $typeId, $totals)) {
                    $totals[(int) $typeId] += (float) $amount;
                }
            });

        $bankQuery->selectRaw('contribution_type_id, SUM(amount) as total')
            ->groupBy('contribution_type_id')
            ->pluck('total', 'contribution_type_id')
            ->each(function ($amount, $typeId) use (&$totals) {
                if (array_key_exists((int) $typeId, $totals)) {
                    $totals[(int) $typeId] += (float) $amount;
                }
            });

        return $totals;
    }

    protected function sumContributionTypeTotals(Collection $rows, Collection $contributionTypes): array
    {
        $totals = $contributionTypes->mapWithKeys(fn ($type) => [$type->id => 0.0])->all();

        foreach ($rows as $row) {
            foreach (($row->contribution_type_totals ?? []) as $typeId => $amount) {
                if (array_key_exists((int) $typeId, $totals)) {
                    $totals[(int) $typeId] += (float) $amount;
                }
            }
        }

        return $totals;
    }

    protected function buildIndexChartData(Collection $jumuiyas, Collection $contributionTypes): array
    {
        $contributionSeries = [];

        foreach ($contributionTypes as $type) {
            $contributionSeries[$type->id] = $jumuiyas
                ->map(fn ($row) => (float) data_get($row->contribution_type_totals ?? [], $type->id, 0))
                ->values();
        }

        return [
            'labels' => $jumuiyas->pluck('name')->values(),
            'tithes' => $jumuiyas->pluck('tithe_total')->map(fn ($value) => (float) $value)->values(),
            'offerings' => $jumuiyas->pluck('offering_total')->map(fn ($value) => (float) $value)->values(),
            'contributions' => $contributionSeries,
            'grand_total' => $jumuiyas->pluck('grand_total')->map(fn ($value) => (float) $value)->values(),
        ];
    }

    protected function getRecentTransactions(Jumuiya $jumuiya, int $year, ?int $month): Collection
    {
        $familiaIds = Familia::where('jumuiya_id', $jumuiya->id)->pluck('id');
        $memberIds = Member::whereIn('familia_id', $familiaIds)->pluck('id');
        $items = collect();

        $titheQuery = Tithe::query()
            ->with(['member.familia'])
            ->where('jumuiya_id', $jumuiya->id)
            ->whereYear('contribution_date', $year);

        $cashQuery = CashContribution::query()
            ->with(['member.familia', 'contributionType'])
            ->whereIn('member_id', $memberIds)
            ->whereYear('contribution_date', $year);

        $bankQuery = BankContribution::query()
            ->with(['member.familia', 'contributionType'])
            ->whereIn('member_id', $memberIds)
            ->whereYear('contribution_date', $year);

        $offeringQuery = Offering::query()
            ->with(['offeringType'])
            ->where('collection_scope', Offering::SCOPE_JUMUIYA)
            ->where('jumuiya_id', $jumuiya->id)
            ->whereYear('collection_date', $year);

        if ($month) {
            $titheQuery->whereMonth('contribution_date', $month);
            $cashQuery->whereMonth('contribution_date', $month);
            $bankQuery->whereMonth('contribution_date', $month);
            $offeringQuery->whereMonth('collection_date', $month);
        }

        $titheQuery->latest('contribution_date')->take(50)->get()->each(function (Tithe $item) use ($items) {
            $items->push((object) [
                'source' => db_trans('tithes'),
                'category' => db_trans('tithe'),
                'member_name' => $item->member?->full_name ?: '—',
                'familia_name' => $item->member?->familia?->name ?: '—',
                'amount' => (float) $item->amount,
                'date' => $item->contribution_date,
                'status' => $item->status ?: '—',
            ]);
        });

        $cashQuery->latest('contribution_date')->take(50)->get()->each(function (CashContribution $item) use ($items) {
            $items->push((object) [
                'source' => db_trans('cash_contributions'),
                'category' => $item->contributionType?->name ?: db_trans('contribution'),
                'member_name' => $item->member?->full_name ?: '—',
                'familia_name' => $item->member?->familia?->name ?: '—',
                'amount' => (float) $item->amount,
                'date' => $item->contribution_date,
                'status' => $item->status ?: '—',
            ]);
        });

        $bankQuery->latest('contribution_date')->take(50)->get()->each(function (BankContribution $item) use ($items) {
            $items->push((object) [
                'source' => db_trans('bank_contributions'),
                'category' => $item->contributionType?->name ?: db_trans('contribution'),
                'member_name' => $item->member?->full_name ?: '—',
                'familia_name' => $item->member?->familia?->name ?: '—',
                'amount' => (float) $item->amount,
                'date' => $item->contribution_date,
                'status' => $item->status ?: '—',
            ]);
        });

        $offeringQuery->latest('collection_date')->take(50)->get()->each(function (Offering $item) use ($items) {
            $items->push((object) [
                'source' => db_trans('offerings'),
                'category' => $item->offeringType?->name ?: db_trans('offering'),
                'member_name' => db_trans('mass_collection'),
                'familia_name' => $item->scope_label ?? '—',
                'amount' => (float) $item->amount,
                'date' => $item->collection_date,
                'status' => $item->status ?: '—',
            ]);
        });

        return $items
            ->sortByDesc(fn ($item) => optional($item->date)?->timestamp ?? 0)
            ->values();
    }
}
