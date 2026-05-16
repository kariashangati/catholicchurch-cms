<?php

namespace App\Services\Finance;

use App\Models\Offering;
use App\Models\OfferingType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FinanceDashboardService
{
    public function __construct(protected FinanceAccessService $access)
    {
    }

    public function getUnifiedActivityExportRows(User $user, array $filters = []): Collection
{
    $data = $this->getDashboardData($user, $filters);

    $activities = collect($data['recentActivities'] ?? []);

    if ($activities->isEmpty() && !empty($data['recentOfferings'])) {
        $activities = collect($data['recentOfferings'])->map(function ($offering) {
            return [
                'type' => db_trans('offering'),
                'title' => $offering->offeringType?->name
                    ?? $offering->massType?->name
                    ?? db_trans('offering'),
                'meta' => $offering->jumuiya?->name
                    ?? $offering->kanda?->name
                    ?? $offering->centreDetail?->centre_name
                    ?? db_trans('parish'),
                'date' => $offering->collection_date,
                'status' => $offering->status,
                'amount' => $offering->amount,
            ];
        });
    }

    return $activities
        ->map(function ($item) {
            $type = $item['type'] ?? '—';
            $description = $item['title'] ?? $item['description'] ?? '—';
            $location = $item['meta'] ?? $item['location'] ?? '—';
            $date = $item['date'] ?? null;
            $status = (string) ($item['status'] ?? '—');

            return (object) [
                'type' => $this->translateFinanceActivityType($type),
                'description' => $description,
                'location' => $location,
                'date' => $date instanceof \Carbon\Carbon
                    ? $date->format('M d, Y')
                    : ($date ? Carbon::parse($date)->format('M d, Y') : '—'),
                'status' => $status !== '—' ? db_trans($status) : '—',
                'amount' => (float) ($item['amount'] ?? 0),
            ];
        })
        ->values();
}

public function getUnifiedActivityExportPdfData(User $user, array $filters = []): array
{
    $rows = $this->getUnifiedActivityExportRows($user, $filters);

    return [
        'pageTitle' => db_trans('unified_finance_activity'),
        'reportTitle' => db_trans('unified_finance_activity_of') . ' ' . $this->financeActivityPeriodLabel($filters),
        'metaItems' => [
            [
                'label' => db_trans('period'),
                'value' => $this->financeActivityPeriodLabel($filters),
            ],
            [
                'label' => db_trans('total_records'),
                'value' => number_format($rows->count()),
            ],
            [
                'label' => db_trans('amount'),
                'value' => number_format((float) $rows->sum('amount'), 2),
            ],
            [
                'label' => db_trans('status'),
                'value' => db_trans('overview'),
            ],
        ],
        'rows' => $rows,
        'issuedAtText' => now()->translatedFormat('d F Y'),
        'locale' => app()->getLocale(),
    ];
}

protected function financeActivityPeriodLabel(array $filters = []): string
{
    $year = !empty($filters['year']) ? (int) $filters['year'] : null;
    $month = !empty($filters['month']) ? (int) $filters['month'] : null;

    if ($year && $month) {
        return Carbon::create($year, $month, 1)->translatedFormat('F Y');
    }

    if ($year) {
        return (string) $year;
    }

    return db_trans('all_time');
}

protected function translateFinanceActivityType(string $type): string
{
    $key = strtolower(trim($type));
    $key = str_replace([' ', '-'], '_', $key);

    return match ($key) {
        'cash_contribution' => db_trans('cash_contribution'),
        'bank_contribution' => db_trans('bank_contribution'),
        'offering' => db_trans('offering'),
        'tithe' => db_trans('tithe'),
        'project', 'projects' => db_trans('projects'),
        'budget' => db_trans('budget'),
        default => db_trans($key) ?: $type,
    };
}

    public function getDashboardData(User $user, array $filters = []): array
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;

        $baseQuery = $this->buildScopedOfferingsQuery($user, $filters, true)
            ->with(['offeringType', 'massType', 'kanda', 'jumuiya', 'centreDetail', 'recorder']);

        $aggregateBaseQuery = $this->buildScopedOfferingsQuery($user, $filters, false);

        $currentMonthTotal = (clone $aggregateBaseQuery)
            ->whereMonth('collection_date', $month ?: now()->month)
            ->sum('amount');

        $yearTotal = (clone $aggregateBaseQuery)->sum('amount');

        $approvedTotal = (clone $aggregateBaseQuery)
            ->where('status', Offering::STATUS_APPROVED)
            ->sum('amount');

        $pendingTotal = (clone $aggregateBaseQuery)
            ->where('status', Offering::STATUS_PENDING)
            ->sum('amount');

        $recordCount = (clone $aggregateBaseQuery)->count();

        $pendingCount = (clone $aggregateBaseQuery)
            ->where('status', Offering::STATUS_PENDING)
            ->count();

        $monthlyTrend = collect(range(1, 12))->map(function (int $monthNumber) use ($user, $year, $filters) {
            $chartFilters = $filters;
            unset($chartFilters['month']);
            $chartFilters['year'] = $year;

            $query = $this->buildScopedOfferingsQuery($user, $chartFilters, false)
                ->whereMonth('collection_date', $monthNumber);

            return [
                'label' => Carbon::create()->month($monthNumber)->format('M'),
                'amount' => (float) $query->sum('amount'),
            ];
        });

        $typeBreakdown = $this->getTypeBreakdown($user, $filters, $year);
        $scopeBreakdown = $this->getScopeBreakdown($user, $filters, $year);
        $topLocations = $this->getTopLocations($user, $filters, $year);

        $recentOfferings = (clone $baseQuery)
            ->latest('collection_date')
            ->latest('id')
            ->limit(8)
            ->get();

        return [
            'pageTitle' => db_trans('finance_dashboard'),
            'filters' => $filters,
            'stats' => [
                'current_month_total' => $currentMonthTotal,
                'year_total' => $yearTotal,
                'approved_total' => $approvedTotal,
                'pending_total' => $pendingTotal,
                'pending_count' => $pendingCount,
                'records_count' => $recordCount,
                'average_record' => $recordCount > 0 ? ($yearTotal / $recordCount) : 0,
            ],
            'monthlyChart' => [
                'labels' => $monthlyTrend->pluck('label')->values(),
                'amounts' => $monthlyTrend->pluck('amount')->values(),
            ],
            'typeChart' => [
                'labels' => $typeBreakdown->pluck('name')->values(),
                'amounts' => $typeBreakdown->pluck('amount')->values(),
            ],
            'scopeChart' => [
                'labels' => [db_trans('parish'), db_trans('kanda'), db_trans('jumuiya')],
                'amounts' => [
                    $scopeBreakdown['parish'],
                    $scopeBreakdown['kanda'],
                    $scopeBreakdown['jumuiya'],
                ],
            ],
            'typeBreakdown' => $typeBreakdown,
            'topLocations' => $topLocations,
            'recentOfferings' => $recentOfferings,
        ];
    }

    public function getOfferingsIndexData(User $user, array $filters = []): array
    {
        $year = (int) ($filters['year'] ?? now()->year);

        $query = $this->buildScopedOfferingsQuery($user, $filters, true)
            ->with(['offeringType', 'massType', 'kanda', 'jumuiya', 'centreDetail', 'recorder', 'approver'])
            ->latest('collection_date')
            ->latest('id');

        $sumQuery = $this->buildScopedOfferingsQuery($user, $filters, false);
        $recordsCount = (clone $sumQuery)->count();
        $approvedTotal = (clone $sumQuery)->where('status', Offering::STATUS_APPROVED)->sum('amount');
        $pendingTotal = (clone $sumQuery)->where('status', Offering::STATUS_PENDING)->sum('amount');
        $rejectedTotal = (clone $sumQuery)->where('status', Offering::STATUS_REJECTED)->sum('amount');
        $monthTotal = (clone $sumQuery)->whereMonth('collection_date', now()->month)->sum('amount');
        $pendingCount = (clone $sumQuery)->where('status', Offering::STATUS_PENDING)->count();

        $typeBreakdown = $this->getTypeBreakdown($user, $filters, $year)->take(6);
        $scopeBreakdown = $this->getScopeBreakdown($user, $filters, $year);
        $topLocations = $this->getTopLocations($user, $filters, $year);
        $monthlyTrend = $this->getMonthlyTrend($user, $filters, $year);
        $recentOfferings = (clone $query)->limit(5)->get();

        return [
            'pageTitle' => db_trans('offerings'),
            'filters' => $filters,
            'items' => $query->paginate(20)->withQueryString(),
            'total' => (float) (clone $sumQuery)->sum('amount'),
            'stats' => [
                'records_count' => $recordsCount,
                'year_total' => (float) (clone $sumQuery)->sum('amount'),
                'month_total' => (float) $monthTotal,
                'approved_total' => (float) $approvedTotal,
                'pending_total' => (float) $pendingTotal,
                'rejected_total' => (float) $rejectedTotal,
                'pending_count' => $pendingCount,
                'average_record' => $recordsCount > 0 ? ((float) (clone $sumQuery)->sum('amount') / $recordsCount) : 0,
            ],
            'monthlyChart' => [
                'labels' => $monthlyTrend->pluck('label')->values(),
                'amounts' => $monthlyTrend->pluck('amount')->values(),
            ],
            'typeChart' => [
                'labels' => $typeBreakdown->pluck('name')->values(),
                'amounts' => $typeBreakdown->pluck('amount')->values(),
            ],
            'scopeChart' => [
                'labels' => [db_trans('parish'), db_trans('kanda'), db_trans('jumuiya')],
                'amounts' => [
                    $scopeBreakdown['parish'],
                    $scopeBreakdown['kanda'],
                    $scopeBreakdown['jumuiya'],
                ],
            ],
            'typeBreakdown' => $typeBreakdown,
            'topLocations' => $topLocations,
            'recentOfferings' => $recentOfferings,
        ];
    }

    protected function buildScopedOfferingsQuery(User $user, array $filters = [], bool $selectAllColumns = true): Builder
    {
        $scope = $this->access->scopeForUser($user);

        $query = Offering::query();

        if ($selectAllColumns) {
            $query->select('offerings.*');
        }

        $this->access->applyOfferingScope($query, $scope);

        if (! empty($filters['year'])) {
            $query->whereYear('collection_date', (int) $filters['year']);
        }

        if (! empty($filters['month'])) {
            $query->whereMonth('collection_date', (int) $filters['month']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['offering_type_id'])) {
            $query->where('offering_type_id', (int) $filters['offering_type_id']);
        }

        if (! empty($filters['mass_type_id'])) {
            $query->where('mass_type_id', (int) $filters['mass_type_id']);
        }

        if (! empty($filters['collection_scope'])) {
            $query->where('collection_scope', $filters['collection_scope']);
        }

        if (! empty($filters['kanda_id'])) {
            $query->where('kanda_id', (int) $filters['kanda_id']);
        }

        if (! empty($filters['jumuiya_id'])) {
            $query->where('jumuiya_id', (int) $filters['jumuiya_id']);
        }

        return $query;
    }

    protected function getMonthlyTrend(User $user, array $filters, int $year): Collection
    {
        return collect(range(1, 12))->map(function (int $monthNumber) use ($user, $year, $filters) {
            $chartFilters = $filters;
            unset($chartFilters['month']);
            $chartFilters['year'] = $year;

            return [
                'label' => Carbon::create()->month($monthNumber)->format('M'),
                'amount' => (float) $this->buildScopedOfferingsQuery($user, $chartFilters, false)
                    ->whereMonth('collection_date', $monthNumber)
                    ->sum('amount'),
            ];
        });
    }

    protected function getTypeBreakdown(User $user, array $filters, int $year): Collection
    {
        return OfferingType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (OfferingType $type) use ($user, $year, $filters) {
                $typeFilters = $filters;
                unset($typeFilters['offering_type_id'], $typeFilters['month']);
                $typeFilters['year'] = $year;

                $query = $this->buildScopedOfferingsQuery($user, $typeFilters, false)
                    ->where('offering_type_id', $type->id);

                return [
                    'name' => $type->name,
                    'slug' => $type->slug,
                    'amount' => (float) $query->sum('amount'),
                ];
            })
            ->filter(fn (array $row) => $row['amount'] > 0)
            ->values();
    }

    protected function getScopeBreakdown(User $user, array $filters, int $year): array
    {
        $scopeFilters = $filters;
        unset($scopeFilters['collection_scope'], $scopeFilters['month']);
        $scopeFilters['year'] = $year;

        return [
            'parish' => (float) $this->buildScopedOfferingsQuery($user, $scopeFilters, false)
                ->where('collection_scope', Offering::SCOPE_PARISH)
                ->sum('amount'),
            'kanda' => (float) $this->buildScopedOfferingsQuery($user, $scopeFilters, false)
                ->where('collection_scope', Offering::SCOPE_KANDA)
                ->sum('amount'),
            'jumuiya' => (float) $this->buildScopedOfferingsQuery($user, $scopeFilters, false)
                ->where('collection_scope', Offering::SCOPE_JUMUIYA)
                ->sum('amount'),
        ];
    }

    protected function getTopLocations(User $user, array $filters, int $year): Collection
    {
        $locationFilters = $filters;
        unset($locationFilters['month']);
        $locationFilters['year'] = $year;

        return $this->buildScopedOfferingsQuery($user, $locationFilters, false)
            ->leftJoin('centre_details', 'centre_details.id', '=', 'offerings.centre_detail_id')
            ->leftJoin('kandas', 'kandas.id', '=', 'offerings.kanda_id')
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'offerings.jumuiya_id')
            ->selectRaw("CASE
                    WHEN offerings.collection_scope = 'parish' THEN COALESCE(centre_details.centre_name, 'Parish')
                    WHEN offerings.collection_scope = 'kanda' THEN COALESCE(kandas.name, 'Kanda')
                    WHEN offerings.collection_scope = 'jumuiya' THEN COALESCE(jumuiyas.name, 'Jumuiya')
                    ELSE 'Unknown'
                END as location_name")
            ->selectRaw('SUM(offerings.amount) as total_amount')
            ->groupBy('location_name')
            ->orderByDesc('total_amount')
            ->limit(6)
            ->get();
    }
}
