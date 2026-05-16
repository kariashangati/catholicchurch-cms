<?php

namespace App\Services\Finance;

use App\Models\BankAccount;
use App\Models\BankContribution;
use App\Models\BudgetExpenseEstimate;
use App\Models\BudgetIncomeEstimate;
use App\Models\CashContribution;
use App\Models\Jumuiya;
use App\Models\Offering;
use App\Models\OfferingType;
use App\Models\ProjectTransaction;
use App\Models\Tithe;
use App\Models\User;
use App\Services\Access\ScopeAccessGate;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FinanceDashboardService
{
    public function __construct(
        protected FinanceAccessService $access,
        protected ScopeAccessGate $scopeAccessGate,
    ) {
    }

    public function getDashboardData(User $user, array $filters = []): array
    {
        $filters = $this->normalizeHierarchyFilters($user, $filters);

        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;
        $selectedMonth = $month ?: now()->month;
        $bankPendingStatus = $this->resolveBankPendingStatus();

        $offeringAggregate = $this->buildScopedOfferingsQuery($user, $filters, false);
        $titheAggregate = $this->buildScopedTithesQuery($user, $filters, false);
        $cashAggregate = $this->buildScopedCashContributionsQuery($user, $filters, false);
        $bankAggregate = $this->buildScopedBankContributionsQuery($user, $filters, false);
        $projectAggregate = $this->buildProjectTransactionsQuery($user, $filters);
        $budgetIncomeAggregate = $this->buildBudgetIncomeQuery($filters);
        $budgetExpenseAggregate = $this->buildBudgetExpenseQuery($filters);

        $offeringsYear = (float) (clone $offeringAggregate)->sum('amount');
        $tithesYear = (float) (clone $titheAggregate)->sum('amount');
        $cashYear = (float) (clone $cashAggregate)->sum('amount');
        $bankYear = (float) (clone $bankAggregate)->sum('amount');
        $projectIncomeYear = (float) (clone $projectAggregate)
            ->where('transaction_type', ProjectTransaction::TYPE_INCOME)
            ->sum('amount');
        $projectExpenseYear = (float) (clone $projectAggregate)
            ->where('transaction_type', ProjectTransaction::TYPE_EXPENSE)
            ->sum('amount');

        $monthFilters = array_merge($filters, ['month' => $selectedMonth]);

        $offeringsMonth = (float) $this->buildScopedOfferingsQuery($user, $monthFilters, false)->sum('amount');
        $tithesMonth = (float) $this->buildScopedTithesQuery($user, $monthFilters, false)->sum('amount');
        $cashMonth = (float) $this->buildScopedCashContributionsQuery($user, $monthFilters, false)->sum('amount');
        $bankMonth = (float) $this->buildScopedBankContributionsQuery($user, $monthFilters, false)->sum('amount');
        $projectIncomeMonth = (float) $this->buildProjectTransactionsQuery($user, $monthFilters)
            ->where('transaction_type', ProjectTransaction::TYPE_INCOME)
            ->sum('amount');
        $projectExpenseMonth = (float) $this->buildProjectTransactionsQuery($user, $monthFilters)
            ->where('transaction_type', ProjectTransaction::TYPE_EXPENSE)
            ->sum('amount');

        $actualIncomeYear = $offeringsYear + $tithesYear + $cashYear + $bankYear + $projectIncomeYear;
        $actualIncomeMonth = $offeringsMonth + $tithesMonth + $cashMonth + $bankMonth + $projectIncomeMonth;

        $approvedTotal =
            (float) (clone $offeringAggregate)->where('status', Offering::STATUS_APPROVED)->sum('amount')
            + (float) (clone $titheAggregate)->where('status', Tithe::STATUS_APPROVED)->sum('amount')
            + (float) (clone $cashAggregate)->where('status', CashContribution::STATUS_APPROVED)->sum('amount')
            + (float) (clone $bankAggregate)->where('status', BankContribution::STATUS_VERIFIED)->sum('amount')
            + (float) (clone $projectAggregate)->where('status', ProjectTransaction::STATUS_APPROVED)->sum('amount');

        $pendingTotal =
            (float) (clone $offeringAggregate)->where('status', Offering::STATUS_PENDING)->sum('amount')
            + (float) (clone $titheAggregate)->where('status', Tithe::STATUS_PENDING)->sum('amount')
            + (float) (clone $cashAggregate)->where('status', CashContribution::STATUS_PENDING)->sum('amount')
            + (float) (clone $bankAggregate)->where('status', $bankPendingStatus)->sum('amount')
            + (float) (clone $projectAggregate)->where('status', ProjectTransaction::STATUS_PENDING)->sum('amount');

        $recordCount =
            (int) (clone $offeringAggregate)->count()
            + (int) (clone $titheAggregate)->count()
            + (int) (clone $cashAggregate)->count()
            + (int) (clone $bankAggregate)->count()
            + (int) (clone $projectAggregate)->count();

        $pendingCount =
            (int) (clone $offeringAggregate)->where('status', Offering::STATUS_PENDING)->count()
            + (int) (clone $titheAggregate)->where('status', Tithe::STATUS_PENDING)->count()
            + (int) (clone $cashAggregate)->where('status', CashContribution::STATUS_PENDING)->count()
            + (int) (clone $bankAggregate)->where('status', $bankPendingStatus)->count()
            + (int) (clone $projectAggregate)->where('status', ProjectTransaction::STATUS_PENDING)->count();

        $budgetIncomeYear = (float) (clone $budgetIncomeAggregate)->sum('amount');
        $budgetExpenseYear = (float) (clone $budgetExpenseAggregate)->sum('amount');

        $sourceCards = [
            [
                'key' => 'offerings',
                'label' => db_trans('offerings'),
                'month' => $offeringsMonth,
                'year' => $offeringsYear,
                'icon' => 'fas fa-hand-holding-heart',
                'tone' => 'purple',
            ],
            [
                'key' => 'tithes',
                'label' => db_trans('tithes'),
                'month' => $tithesMonth,
                'year' => $tithesYear,
                'icon' => 'fas fa-sack-dollar',
                'tone' => 'blue',
            ],
            [
                'key' => 'cash_contributions',
                'label' => db_trans('cash_contributions'),
                'month' => $cashMonth,
                'year' => $cashYear,
                'icon' => 'fas fa-money-bill-wave',
                'tone' => 'green',
            ],
            [
                'key' => 'bank_contributions',
                'label' => db_trans('bank_contributions'),
                'month' => $bankMonth,
                'year' => $bankYear,
                'icon' => 'fas fa-building-columns',
                'tone' => 'amber',
            ],
            [
                'key' => 'project_income',
                'label' => db_trans('project_income'),
                'month' => $projectIncomeMonth,
                'year' => $projectIncomeYear,
                'icon' => 'fas fa-diagram-project',
                'tone' => 'teal',
            ],
        ];

        $monthlyTrend = $this->buildUnifiedMonthlyTrend($user, $year, $filters);

        $sourceMix = [
            'labels' => collect($sourceCards)->pluck('label')->values(),
            'amounts' => collect($sourceCards)->pluck('year')->values(),
        ];

        $budgetVsActual = [
            'labels' => [db_trans('income'), db_trans('expense')],
            'budget' => [$budgetIncomeYear, $budgetExpenseYear],
            'actual' => [$actualIncomeYear, $projectExpenseYear],
            'variance' => [$actualIncomeYear - $budgetIncomeYear, $projectExpenseYear - $budgetExpenseYear],
        ];

        $typeBreakdown = $this->getTypeBreakdown($user, $filters, $year);
        $scopeBreakdown = $this->getScopeBreakdown($user, $filters, $year);
        $offeringMonthlyTrend = $this->getMonthlyTrend($user, $filters, $year);
        $topOfferingLocations = $this->getTopLocations($user, $filters, $year);

        $recentOfferings = $this->buildScopedOfferingsQuery($user, $filters, true)
            ->with(['offeringType', 'massType', 'kanda', 'jumuiya', 'centreDetail', 'recorder', 'approver'])
            ->latest('collection_date')
            ->latest('id')
            ->limit(8)
            ->get();

        $topKandas = $this->buildKandaLeaderboard($user, $filters);
        $topJumuiyas = $this->buildJumuiyaLeaderboard($user, $filters);
        $recentActivities = $this->buildRecentActivities($user, $filters);
        $attentionItems = $this->buildAttentionItems($user, $filters, $budgetIncomeYear, $actualIncomeYear);

        $bankHealth = [
            'active_accounts' => BankAccount::query()->where('status', BankAccount::STATUS_ACTIVE)->count(),
            'inactive_accounts' => BankAccount::query()->where('status', BankAccount::STATUS_INACTIVE)->count(),
            'closed_accounts' => BankAccount::query()->where('status', BankAccount::STATUS_CLOSED)->count(),
        ];

        return [
            'pageTitle' => db_trans('finance_dashboard'),
            'filters' => $filters,
            'hero' => [
                'current_year' => $year,
                'selected_scope' => $this->resolveSelectedScopeLabel($filters),
                'current_month_net' => $actualIncomeMonth - $projectExpenseMonth,
                'pending_items' => $pendingCount,
            ],
            'stats' => [
                'total_inflow_month' => $actualIncomeMonth,
                'total_inflow_year' => $actualIncomeYear,
                'approved_total' => $approvedTotal,
                'pending_total' => $pendingTotal,
                'pending_count' => $pendingCount,
                'records_count' => $recordCount,
                'average_record' => $recordCount > 0 ? ($actualIncomeYear / max($recordCount, 1)) : 0,
                'project_expense_year' => $projectExpenseYear,
                'budget_gap' => $actualIncomeYear - $budgetIncomeYear,
                'budget_income_year' => $budgetIncomeYear,
                'budget_expense_year' => $budgetExpenseYear,
            ],
            'sourceCards' => $sourceCards,
            'monthlyChart' => $monthlyTrend,
            'sourceMixChart' => $sourceMix,
            'budgetVsActualChart' => $budgetVsActual,
            'typeBreakdown' => $typeBreakdown,
            'topKandas' => $topKandas,
            'topJumuiyas' => $topJumuiyas,
            'recentActivities' => $recentActivities,
            'attentionItems' => $attentionItems,
            'bankHealth' => $bankHealth,
            'offeringMonthlyChart' => [
                'labels' => $offeringMonthlyTrend->pluck('label')->values(),
                'amounts' => $offeringMonthlyTrend->pluck('amount')->values(),
            ],
            'offeringTypeChart' => [
                'labels' => $typeBreakdown->pluck('name')->values(),
                'amounts' => $typeBreakdown->pluck('amount')->values(),
            ],
            'offeringScopeChart' => [
                'labels' => [db_trans('parish'), db_trans('kanda'), db_trans('jumuiya')],
                'amounts' => [
                    $scopeBreakdown['parish'],
                    $scopeBreakdown['kanda'],
                    $scopeBreakdown['jumuiya'],
                ],
            ],
            'topOfferingLocations' => $topOfferingLocations,
            'recentOfferings' => $recentOfferings,
        ];
    }

    public function getOfferingsIndexData(User $user, array $filters = []): array
    {
        $filters = $this->normalizeHierarchyFilters($user, $filters);
        $year = (int) ($filters['year'] ?? now()->year);

        $query = $this->buildScopedOfferingsQuery($user, $filters, true)
            ->with(['offeringType', 'massType', 'kanda', 'jumuiya', 'centreDetail', 'recorder', 'approver'])
            ->latest('collection_date')
            ->latest('id');

        $sumQuery = $this->buildScopedOfferingsQuery($user, $filters, false);

        $recordsCount = (int) (clone $sumQuery)->count();
        $yearTotal = (float) (clone $sumQuery)->sum('amount');
        $approvedTotal = (float) (clone $sumQuery)->where('status', Offering::STATUS_APPROVED)->sum('amount');
        $pendingTotal = (float) (clone $sumQuery)->where('status', Offering::STATUS_PENDING)->sum('amount');
        $rejectedTotal = defined(Offering::class . '::STATUS_REJECTED')
            ? (float) (clone $sumQuery)->where('status', Offering::STATUS_REJECTED)->sum('amount')
            : 0.0;
        $monthTotal = (float) (clone $sumQuery)->whereMonth('collection_date', now()->month)->sum('amount');
        $pendingCount = (int) (clone $sumQuery)->where('status', Offering::STATUS_PENDING)->count();

        $typeBreakdown = $this->getTypeBreakdown($user, $filters, $year)->take(6);
        $scopeBreakdown = $this->getScopeBreakdown($user, $filters, $year);
        $topLocations = $this->getTopLocations($user, $filters, $year);
        $monthlyTrend = $this->getMonthlyTrend($user, $filters, $year);
        $recentOfferings = (clone $query)->limit(5)->get();

        return [
            'pageTitle' => db_trans('offerings'),
            'filters' => $filters,
            'items' => $query->paginate(20)->withQueryString(),
            'total' => $yearTotal,
            'stats' => [
                'records_count' => $recordsCount,
                'year_total' => $yearTotal,
                'month_total' => $monthTotal,
                'approved_total' => $approvedTotal,
                'pending_total' => $pendingTotal,
                'rejected_total' => $rejectedTotal,
                'pending_count' => $pendingCount,
                'average_record' => $recordsCount > 0 ? ($yearTotal / $recordsCount) : 0,
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

    protected function buildUnifiedMonthlyTrend(User $user, int $year, array $filters): array
    {
        $labels = collect(range(1, 12))
            ->map(fn (int $monthNumber) => Carbon::create()->month($monthNumber)->format('M'))
            ->values();

        $datasets = [
            'offerings' => [],
            'tithes' => [],
            'cash' => [],
            'bank' => [],
            'projects' => [],
        ];

        foreach (range(1, 12) as $monthNumber) {
            $chartFilters = array_merge($filters, ['year' => $year, 'month' => $monthNumber]);

            $datasets['offerings'][] = (float) $this->buildScopedOfferingsQuery($user, $chartFilters, false)->sum('amount');
            $datasets['tithes'][] = (float) $this->buildScopedTithesQuery($user, $chartFilters, false)->sum('amount');
            $datasets['cash'][] = (float) $this->buildScopedCashContributionsQuery($user, $chartFilters, false)->sum('amount');
            $datasets['bank'][] = (float) $this->buildScopedBankContributionsQuery($user, $chartFilters, false)->sum('amount');
            $datasets['projects'][] = (float) $this->buildProjectTransactionsQuery($user, $chartFilters)
                ->where('transaction_type', ProjectTransaction::TYPE_INCOME)
                ->sum('amount');
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    protected function buildRecentActivities(User $user, array $filters): Collection
    {
        $activities = collect();

        $activities = $activities
            ->merge(
                $this->buildScopedOfferingsQuery($user, $filters, true)
                    ->with(['offeringType', 'kanda', 'jumuiya', 'centreDetail'])
                    ->latest('collection_date')
                    ->limit(5)
                    ->get()
                    ->map(fn (Offering $item) => [
                        'type' => db_trans('offering'),
                        'title' => $item->offeringType?->name ?? db_trans('offering'),
                        'meta' => $item->location_name,
                        'date' => $item->collection_date,
                        'amount' => (float) $item->amount,
                        'status' => $item->status,
                        'tone' => 'purple',
                    ])
            )
            ->merge(
                $this->buildScopedTithesQuery($user, $filters, true)
                    ->with(['member', 'jumuiya'])
                    ->latest('contribution_date')
                    ->limit(5)
                    ->get()
                    ->map(fn (Tithe $item) => [
                        'type' => db_trans('tithe'),
                        'title' => $item->member?->full_name ?? db_trans('member_tithe'),
                        'meta' => $item->jumuiya?->name ?? '—',
                        'date' => $item->contribution_date,
                        'amount' => (float) $item->amount,
                        'status' => $item->status,
                        'tone' => 'blue',
                    ])
            )
            ->merge(
                $this->buildScopedCashContributionsQuery($user, $filters, true)
                    ->with(['member', 'contributionType'])
                    ->latest('contribution_date')
                    ->limit(5)
                    ->get()
                    ->map(fn (CashContribution $item) => [
                        'type' => db_trans('cash_contribution'),
                        'title' => $item->contributionType?->name ?? db_trans('contribution'),
                        'meta' => $item->member?->full_name ?? '—',
                        'date' => $item->contribution_date,
                        'amount' => (float) $item->amount,
                        'status' => $item->status,
                        'tone' => 'green',
                    ])
            )
            ->merge(
                $this->buildScopedBankContributionsQuery($user, $filters, true)
                    ->with(['member', 'bankAccount', 'contributionType'])
                    ->latest('contribution_date')
                    ->limit(5)
                    ->get()
                    ->map(fn (BankContribution $item) => [
                        'type' => db_trans('bank_contribution'),
                        'title' => $item->contributionType?->name ?? db_trans('bank_contribution'),
                        'meta' => $item->bankAccount?->display_name ?? ($item->member?->full_name ?? '—'),
                        'date' => $item->contribution_date,
                        'amount' => (float) $item->amount,
                        'status' => $item->status,
                        'tone' => 'amber',
                    ])
            )
            ->merge(
                $this->buildProjectTransactionsQuery($user, $filters)
                    ->with(['project'])
                    ->latest('transaction_date')
                    ->limit(5)
                    ->get()
                    ->map(fn (ProjectTransaction $item) => [
                        'type' => db_trans('project_transaction'),
                        'title' => $item->project?->name ?? db_trans('project'),
                        'meta' => db_trans($item->transaction_type),
                        'date' => $item->transaction_date,
                        'amount' => (float) $item->amount,
                        'status' => $item->status,
                        'tone' => 'teal',
                    ])
            );

        return $activities
            ->sortByDesc(function (array $row) {
                if (empty($row['date'])) {
                    return 0;
                }

                return Carbon::parse($row['date'])->timestamp;
            })
            ->values()
            ->take(10);
    }

    protected function buildAttentionItems(User $user, array $filters, float $budgetIncomeYear, float $actualIncomeYear): array
    {
        $bankPendingStatus = $this->resolveBankPendingStatus();

        $inactiveAccounts = BankAccount::query()
            ->where('status', BankAccount::STATUS_INACTIVE)
            ->count();

        $pendingOfferings = $this->buildScopedOfferingsQuery($user, $filters, false)
            ->where('status', Offering::STATUS_PENDING)
            ->count();

        $pendingTithes = $this->buildScopedTithesQuery($user, $filters, false)
            ->where('status', Tithe::STATUS_PENDING)
            ->count();

        $pendingCash = $this->buildScopedCashContributionsQuery($user, $filters, false)
            ->where('status', CashContribution::STATUS_PENDING)
            ->count();

        $pendingBank = $this->buildScopedBankContributionsQuery($user, $filters, false)
            ->where('status', $bankPendingStatus)
            ->count();

        $pendingProjects = $this->buildProjectTransactionsQuery($user, $filters)
            ->where('status', ProjectTransaction::STATUS_PENDING)
            ->count();

        return [
            [
                'label' => db_trans('pending_finance_actions'),
                'value' => $pendingOfferings + $pendingTithes + $pendingCash + $pendingBank + $pendingProjects,
                'hint' => db_trans('records_waiting_for_review'),
                'icon' => 'fas fa-hourglass-half',
                'tone' => 'amber',
            ],
            [
                'label' => db_trans('inactive_bank_accounts'),
                'value' => $inactiveAccounts,
                'hint' => db_trans('bank_accounts_need_follow_up'),
                'icon' => 'fas fa-building-columns',
                'tone' => 'blue',
            ],
            [
                'label' => db_trans('budget_gap'),
                'value' => $actualIncomeYear - $budgetIncomeYear,
                'hint' => db_trans('actual_income_vs_budgeted_income'),
                'icon' => 'fas fa-scale-balanced',
                'tone' => $actualIncomeYear >= $budgetIncomeYear ? 'green' : 'rose',
            ],
        ];
    }

    protected function buildKandaLeaderboard(User $user, array $filters): Collection
    {
        $rows = collect();

        $offeringRows = $this->buildScopedOfferingsQuery($user, $filters, false)
            ->leftJoin('kandas', 'kandas.id', '=', 'offerings.kanda_id')
            ->whereNotNull('offerings.kanda_id')
            ->groupBy('kandas.id', 'kandas.name')
            ->selectRaw('kandas.id as scope_id, kandas.name as name, SUM(offerings.amount) as total_amount')
            ->get();

        $titheRows = $this->buildScopedTithesQuery($user, $filters, false)
            ->join('jumuiyas', 'jumuiyas.id', '=', 'tithes.jumuiya_id')
            ->join('kandas', 'kandas.id', '=', 'jumuiyas.kanda_id')
            ->groupBy('kandas.id', 'kandas.name')
            ->selectRaw('kandas.id as scope_id, kandas.name as name, SUM(tithes.amount) as total_amount')
            ->get();

        $cashRows = $this->buildScopedCashContributionsQuery($user, $filters, false)
            ->join('members', 'members.id', '=', 'cash_contributions.member_id')
            ->join('familias', 'familias.id', '=', 'members.familia_id')
            ->join('jumuiyas', 'jumuiyas.id', '=', 'familias.jumuiya_id')
            ->join('kandas', 'kandas.id', '=', 'jumuiyas.kanda_id')
            ->groupBy('kandas.id', 'kandas.name')
            ->selectRaw('kandas.id as scope_id, kandas.name as name, SUM(cash_contributions.amount) as total_amount')
            ->get();

        $bankRows = $this->buildScopedBankContributionsQuery($user, $filters, false)
            ->leftJoin('kandas', 'kandas.id', '=', 'bank_contributions.kanda_id')
            ->whereNotNull('bank_contributions.kanda_id')
            ->groupBy('kandas.id', 'kandas.name')
            ->selectRaw('kandas.id as scope_id, kandas.name as name, SUM(bank_contributions.amount) as total_amount')
            ->get();

        foreach ([$offeringRows, $titheRows, $cashRows, $bankRows] as $collection) {
            foreach ($collection as $row) {
                $key = 'kanda_' . ($row->scope_id ?? md5((string) $row->name));

                $rows[$key] = [
                    'name' => $row->name ?: '—',
                    'amount' => ($rows[$key]['amount'] ?? 0) + (float) $row->total_amount,
                ];
            }
        }

        return collect($rows)
            ->sortByDesc('amount')
            ->values()
            ->take(6);
    }

    protected function buildJumuiyaLeaderboard(User $user, array $filters): Collection
    {
        $rows = collect();

        $offeringRows = $this->buildScopedOfferingsQuery($user, $filters, false)
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'offerings.jumuiya_id')
            ->whereNotNull('offerings.jumuiya_id')
            ->groupBy('jumuiyas.id', 'jumuiyas.name')
            ->selectRaw('jumuiyas.id as scope_id, jumuiyas.name as name, SUM(offerings.amount) as total_amount')
            ->get();

        $titheRows = $this->buildScopedTithesQuery($user, $filters, false)
            ->join('jumuiyas', 'jumuiyas.id', '=', 'tithes.jumuiya_id')
            ->groupBy('jumuiyas.id', 'jumuiyas.name')
            ->selectRaw('jumuiyas.id as scope_id, jumuiyas.name as name, SUM(tithes.amount) as total_amount')
            ->get();

        $cashRows = $this->buildScopedCashContributionsQuery($user, $filters, false)
            ->join('members', 'members.id', '=', 'cash_contributions.member_id')
            ->join('familias', 'familias.id', '=', 'members.familia_id')
            ->join('jumuiyas', 'jumuiyas.id', '=', 'familias.jumuiya_id')
            ->groupBy('jumuiyas.id', 'jumuiyas.name')
            ->selectRaw('jumuiyas.id as scope_id, jumuiyas.name as name, SUM(cash_contributions.amount) as total_amount')
            ->get();

        $bankRows = $this->buildScopedBankContributionsQuery($user, $filters, false)
            ->leftJoin('jumuiyas', 'jumuiyas.id', '=', 'bank_contributions.jumuiya_id')
            ->whereNotNull('bank_contributions.jumuiya_id')
            ->groupBy('jumuiyas.id', 'jumuiyas.name')
            ->selectRaw('jumuiyas.id as scope_id, jumuiyas.name as name, SUM(bank_contributions.amount) as total_amount')
            ->get();

        foreach ([$offeringRows, $titheRows, $cashRows, $bankRows] as $collection) {
            foreach ($collection as $row) {
                $key = 'jumuiya_' . ($row->scope_id ?? md5((string) $row->name));

                $rows[$key] = [
                    'name' => $row->name ?: '—',
                    'amount' => ($rows[$key]['amount'] ?? 0) + (float) $row->total_amount,
                ];
            }
        }

        return collect($rows)
            ->sortByDesc('amount')
            ->values()
            ->take(6);
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

    protected function buildScopedTithesQuery(User $user, array $filters = [], bool $selectAllColumns = true): Builder
    {
        $scope = $this->access->scopeForUser($user);
        $query = Tithe::query();

        if ($selectAllColumns) {
            $query->select('tithes.*');
        }

        $query = $this->access->applyTitheScope($query, $scope);

        if (! empty($filters['year'])) {
            $query->whereYear('contribution_date', (int) $filters['year']);
        }

        if (! empty($filters['month'])) {
            $query->whereMonth('contribution_date', (int) $filters['month']);
        }

        if (! empty($filters['jumuiya_id'])) {
            $query->where('jumuiya_id', (int) $filters['jumuiya_id']);
        }

        if (! empty($filters['kanda_id'])) {
            $query->whereHas('jumuiya', function (Builder $jumuiyaQuery) use ($filters) {
                $jumuiyaQuery->where('kanda_id', (int) $filters['kanda_id']);
            });
        }

        return $query;
    }

    protected function buildScopedCashContributionsQuery(User $user, array $filters = [], bool $selectAllColumns = true): Builder
    {
        $query = CashContribution::query();

        if ($selectAllColumns) {
            $query->select('cash_contributions.*');
        }

        $query = $this->access->applyCashScope($query, $this->access->scopeForUser($user));

        if (! empty($filters['year'])) {
            $query->whereYear('contribution_date', (int) $filters['year']);
        }

        if (! empty($filters['month'])) {
            $query->whereMonth('contribution_date', (int) $filters['month']);
        }

        if (! empty($filters['jumuiya_id'])) {
            $query->whereHas('member.familia', function (Builder $familiaQuery) use ($filters) {
                $familiaQuery->where('jumuiya_id', (int) $filters['jumuiya_id']);
            });
        }

        if (! empty($filters['kanda_id'])) {
            $query->whereHas('member.familia.jumuiya', function (Builder $jumuiyaQuery) use ($filters) {
                $jumuiyaQuery->where('kanda_id', (int) $filters['kanda_id']);
            });
        }

        return $query;
    }

    protected function buildScopedBankContributionsQuery(User $user, array $filters = [], bool $selectAllColumns = true): Builder
    {
        $scope = $this->access->scopeForUser($user);
        $query = BankContribution::query();

        if ($selectAllColumns) {
            $query->select('bank_contributions.*');
        }

        $query = $this->access->applyBankScope($query, $scope);

        if (! empty($filters['year'])) {
            $query->whereYear('contribution_date', (int) $filters['year']);
        }

        if (! empty($filters['month'])) {
            $query->whereMonth('contribution_date', (int) $filters['month']);
        }

        if (! empty($filters['kanda_id'])) {
            $query->where('kanda_id', (int) $filters['kanda_id']);
        }

        if (! empty($filters['jumuiya_id'])) {
            $query->where('jumuiya_id', (int) $filters['jumuiya_id']);
        }

        return $query;
    }

    protected function buildProjectTransactionsQuery(User $user, array $filters = []): Builder
    {
        $scope = $this->access->scopeForUser($user);

        $query = ProjectTransaction::query()->select('project_transactions.*');
        $query = $this->access->applyProjectScope($query, $scope);

        if (! empty($filters['year'])) {
            $query->whereYear('transaction_date', (int) $filters['year']);
        }

        if (! empty($filters['month'])) {
            $query->whereMonth('transaction_date', (int) $filters['month']);
        }

        if (! empty($filters['kanda_id'])) {
            $query->where('kanda_id', (int) $filters['kanda_id']);
        }

        if (! empty($filters['jumuiya_id'])) {
            $query->where('jumuiya_id', (int) $filters['jumuiya_id']);
        }

        return $query;
    }

    protected function buildBudgetIncomeQuery(array $filters = []): Builder
    {
        return BudgetIncomeEstimate::query()
            ->where('budget_year', (int) ($filters['year'] ?? now()->year));
    }

    protected function buildBudgetExpenseQuery(array $filters = []): Builder
    {
        return BudgetExpenseEstimate::query()
            ->where('budget_year', (int) ($filters['year'] ?? now()->year));
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

    protected function resolveSelectedScopeLabel(array $filters): string
    {
        if (! empty($filters['jumuiya_id'])) {
            return db_trans('jumuiya_focus');
        }

        if (! empty($filters['kanda_id'])) {
            return db_trans('kanda_focus');
        }

        if (! empty($filters['collection_scope'])) {
            return db_trans($filters['collection_scope']);
        }

        return db_trans('all_finance_streams');
    }

    protected function resolveBankPendingStatus(): string
    {
        $constant = BankContribution::class . '::STATUS_PENDING';

        if (defined($constant)) {
            return constant($constant);
        }

        return 'pending';
    }

  protected function normalizeHierarchyFilters(User $user, array $filters): array
{
    $filters['kanda_id'] = ! empty($filters['kanda_id']) ? (int) $filters['kanda_id'] : null;
    $filters['jumuiya_id'] = ! empty($filters['jumuiya_id']) ? (int) $filters['jumuiya_id'] : null;

    $resolved = $this->access->scopeForUser($user);

    if ($resolved->isInvalid()) {
        throw new AuthorizationException($resolved->reason ?? 'This account has an invalid finance scope.');
    }

    if ($resolved->isGlobal()) {
        if ($filters['kanda_id']) {
            $kanda = \App\Models\Kanda::query()->findOrFail($filters['kanda_id']);
            $this->scopeAccessGate->ensureCanAccessKanda($user, $kanda);
        }

        if ($filters['jumuiya_id']) {
            $jumuiya = Jumuiya::query()->findOrFail($filters['jumuiya_id']);
            $this->scopeAccessGate->ensureCanAccessJumuiya($user, $jumuiya);

            if ($filters['kanda_id'] && (int) $jumuiya->kanda_id !== (int) $filters['kanda_id']) {
                throw new AuthorizationException('Selected jumuiya does not belong to the selected kanda.');
            }
        }

        return $filters;
    }

    if ($resolved->isKanda()) {
        if ($filters['kanda_id'] && (int) $filters['kanda_id'] !== (int) $resolved->kandaId) {
            throw new AuthorizationException('You cannot access another kanda financial scope.');
        }

        $filters['kanda_id'] = (int) $resolved->kandaId;

        if ($filters['jumuiya_id']) {
            $jumuiya = Jumuiya::query()->findOrFail($filters['jumuiya_id']);
            $this->scopeAccessGate->ensureCanAccessJumuiya($user, $jumuiya);
        }

        return $filters;
    }

    if ($resolved->isJumuiya()) {
        if ($filters['kanda_id'] && (int) $filters['kanda_id'] !== (int) $resolved->kandaId) {
            throw new AuthorizationException('You cannot access another kanda financial scope.');
        }

        if ($filters['jumuiya_id'] && (int) $filters['jumuiya_id'] !== (int) $resolved->jumuiyaId) {
            throw new AuthorizationException('You cannot access another jumuiya financial scope.');
        }

        $filters['kanda_id'] = (int) $resolved->kandaId;
        $filters['jumuiya_id'] = (int) $resolved->jumuiyaId;

        return $filters;
    }

    return $filters;
}
}