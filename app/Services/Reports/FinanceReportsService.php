<?php

namespace App\Services\Reports;

use App\Models\BankContribution;
use App\Models\CashContribution;
use App\Models\ContributionType;
use App\Models\Familia;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\Member;
use App\Models\Offering;
use App\Models\ProjectTransaction;
use App\Models\Tithe;
use App\Models\User;
use App\Services\Access\ScopeAccessGate;
use App\Services\Access\UserScopeResolver;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class FinanceReportsService
{



public function financialSummaryExportData(User $user, array $filters = []): array
{
    $data = $this->summary($user, $filters);

    $incomeItems = collect($data['incomeItems'] ?? [])->values();
    $expenseItems = collect($data['expenseItems'] ?? [])->values();
    $monthlyTrend = $data['monthlyTrend'] ?? [
        'labels' => collect(),
        'income' => collect(),
        'expenses' => collect(),
        'balances' => collect(),
    ];

    $contributionTypeBreakdown = $data['contributionTypeBreakdown'] ?? [
        'rows' => collect(),
        'labels' => collect(),
        'amounts' => collect(),
    ];

    $filterLabel = $this->financialSummaryFilterLabel($data['filters'] ?? []);

    return $data + [
        'reportTitle' => db_trans('financial_summary_report_title_for') . ' ' . $filterLabel,
        'filterLabel' => $filterLabel,
        'metaItems' => [
            [
                'label' => db_trans('period'),
                'value' => optional($data['startDate'])->format('Y-m-d') . ' - ' . optional($data['endDate'])->format('Y-m-d'),
            ],
            [
                'label' => db_trans('filters'),
                'value' => $filterLabel,
            ],
            [
                'label' => db_trans('total_income'),
                'value' => number_format((float) data_get($data, 'stats.income_total', 0), 2),
            ],
            [
                'label' => db_trans('total_expense'),
                'value' => number_format((float) data_get($data, 'stats.expense_total', 0), 2),
            ],
            [
                'label' => db_trans('balance'),
                'value' => number_format((float) data_get($data, 'stats.balance', 0), 2),
            ],
            [
                'label' => db_trans('records'),
                'value' => number_format(
                    (int) data_get($data, 'stats.income_records', 0)
                    + (int) data_get($data, 'stats.expense_records', 0)
                ),
            ],
            [
                'label' => db_trans('generated_on'),
                'value' => now()->translatedFormat('d F Y'),
            ],
        ],
        'incomeItems' => $incomeItems,
        'expenseItems' => $expenseItems,
        'monthlyTrendRows' => $this->monthlyTrendRows($monthlyTrend),
        'contributionTypeRows' => collect($contributionTypeBreakdown['rows'] ?? [])->values(),
        'issuedAtText' => now()->translatedFormat('d F Y'),
        'locale' => app()->getLocale(),
    ];
}

public function financialSummaryExportArray(User $user, array $filters = []): array
{
    $data = $this->financialSummaryExportData($user, $filters);

    $rows = [];

    $rows[] = [db_trans('financial_summary')];
    $rows[] = [db_trans('filters'), $data['filterLabel']];
    $rows[] = [db_trans('period'), optional($data['startDate'])->format('Y-m-d') . ' - ' . optional($data['endDate'])->format('Y-m-d')];
    $rows[] = [db_trans('generated_on'), now()->format('Y-m-d H:i:s')];
    $rows[] = [];

    $rows[] = [db_trans('summary')];
    $rows[] = [db_trans('total_income'), (float) data_get($data, 'stats.income_total', 0)];
    $rows[] = [db_trans('total_expense'), (float) data_get($data, 'stats.expense_total', 0)];
    $rows[] = [db_trans('balance'), (float) data_get($data, 'stats.balance', 0)];
    $rows[] = [db_trans('records'), (int) data_get($data, 'stats.income_records', 0) + (int) data_get($data, 'stats.expense_records', 0)];
    $rows[] = [];

    $rows[] = [db_trans('income_breakdown')];
    $rows[] = [db_trans('source'), db_trans('amount')];

    foreach (collect($data['incomeItems'] ?? []) as $item) {
        $rows[] = [
            $item['label'] ?? '—',
            (float) ($item['amount'] ?? 0),
        ];
    }

    $rows[] = [db_trans('total_income'), (float) data_get($data, 'stats.income_total', 0)];
    $rows[] = [];

    $rows[] = [db_trans('expense_breakdown')];
    $rows[] = [db_trans('source'), db_trans('amount')];

    foreach (collect($data['expenseItems'] ?? []) as $item) {
        $rows[] = [
            $item['label'] ?? '—',
            (float) ($item['amount'] ?? 0),
        ];
    }

    $rows[] = [db_trans('total_expense'), (float) data_get($data, 'stats.expense_total', 0)];
    $rows[] = [];

    $rows[] = [db_trans('monthly_finance_trend')];
    $rows[] = [
        db_trans('month'),
        db_trans('income'),
        db_trans('expense'),
        db_trans('balance'),
    ];

    foreach (collect($data['monthlyTrendRows'] ?? []) as $row) {
        $rows[] = [
            $row['label'] ?? '—',
            (float) ($row['income'] ?? 0),
            (float) ($row['expense'] ?? 0),
            (float) ($row['balance'] ?? 0),
        ];
    }

    $rows[] = [];

    $rows[] = [db_trans('contribution_type_breakdown')];
    $rows[] = [
        db_trans('contribution_type'),
        db_trans('cash'),
        db_trans('bank'),
        db_trans('total'),
    ];

    foreach (collect($data['contributionTypeRows'] ?? []) as $row) {
        $rows[] = [
            data_get($row, 'name', '—'),
            (float) data_get($row, 'cash_total', 0),
            (float) data_get($row, 'bank_total', 0),
            (float) data_get($row, 'total', 0),
        ];
    }

    return $rows;
}

protected function monthlyTrendRows(array $monthlyTrend): Collection
{
    $labels = collect($monthlyTrend['labels'] ?? [])->values();
    $income = collect($monthlyTrend['income'] ?? [])->values();
    $expenses = collect($monthlyTrend['expenses'] ?? [])->values();
    $balances = collect($monthlyTrend['balances'] ?? [])->values();

    return $labels->map(function ($label, int $index) use ($income, $expenses, $balances) {
        return [
            'label' => $label,
            'income' => (float) ($income[$index] ?? 0),
            'expense' => (float) ($expenses[$index] ?? 0),
            'balance' => (float) ($balances[$index] ?? 0),
        ];
    })->values();
}

protected function financialSummaryFilterLabel(array $filters = []): string
{
    $parts = [];

    $year = (int) ($filters['year'] ?? now()->year);

    if (! empty($filters['month'])) {
        $parts[] = Carbon::create($year, (int) $filters['month'], 1)->translatedFormat('F Y');
    } else {
        $parts[] = (string) $year;
    }

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

    if (! empty($filters['contribution_type_id'])) {
        $type = ContributionType::query()->find((int) $filters['contribution_type_id']);
        $parts[] = db_trans('contribution_type') . ': ' . ($type?->name ?? '—');
    } else {
        $parts[] = db_trans('contribution_type') . ': ' . db_trans('all');
    }

    return implode(' | ', $parts);
}
    public function __construct(
        protected UserScopeResolver $scopeResolver,
        protected ScopeAccessGate $scopeAccessGate,
    ) {
    }

    public function filtersData(?User $user = null): array
    {
        $kandas = Kanda::query()->orderBy('name');
        $jumuiyas = Jumuiya::query()->orderBy('name');
        $familias = Familia::query()->orderBy('name');
        $members = Member::query()->orderBy('first_name')->orderBy('middle_name')->orderBy('last_name');

        if ($user) {
            $scope = $this->scopeResolver->resolve($user);

            if ($scope->isInvalid()) {
                return [
                    'kandas' => collect(),
                    'jumuiyas' => collect(),
                    'familias' => collect(),
                    'members' => collect(),
                    'contributionTypes' => $this->contributionTypes(),
                    'statuses' => $this->allContributionStatuses(),
                ];
            }

            if ($scope->isKanda()) {
                $kandas->whereKey((int) $scope->kandaId);
                $jumuiyas->where('kanda_id', (int) $scope->kandaId);
                $familias->whereHas('jumuiya', fn (Builder $query) => $query->where('kanda_id', (int) $scope->kandaId));
                $members->whereHas('familia.jumuiya', fn (Builder $query) => $query->where('kanda_id', (int) $scope->kandaId));
            }

            if ($scope->isJumuiya()) {
                $kandas->whereKey((int) $scope->kandaId);
                $jumuiyas->whereKey((int) $scope->jumuiyaId);
                $familias->where('jumuiya_id', (int) $scope->jumuiyaId);
                $members->whereHas('familia', fn (Builder $query) => $query->where('jumuiya_id', (int) $scope->jumuiyaId));
            }
        }

        return [
            'kandas' => $kandas->get(['id', 'name']),
            'jumuiyas' => $jumuiyas->get(['id', 'name', 'kanda_id']),
            'familias' => $familias->get(['id', 'name', 'jumuiya_id']),
            'members' => $members->get(['id', 'first_name', 'middle_name', 'last_name', 'familia_id']),
            'contributionTypes' => $this->contributionTypes(),
            'statuses' => $this->allContributionStatuses(),
        ];
    }

    public function summary(User $user, array $filters = []): array
    {
        $filters = $this->normalizeHierarchyFilters($user, $filters);
        [$startDate, $endDate] = $this->resolveDateRange($filters);

        $offerings = $this->scopedOfferings($user, $filters, $startDate, $endDate);
        $tithes = $this->scopedTithes($user, $filters, $startDate, $endDate);
        $cash = $this->scopedCashContributions($user, $filters, $startDate, $endDate);
        $bank = $this->scopedBankContributions($user, $filters, $startDate, $endDate);
        $projectIncome = $this->scopedProjectTransactions($filters, $startDate, $endDate)
            ->where('transaction_type', ProjectTransaction::TYPE_INCOME)
            ->where('status', ProjectTransaction::STATUS_APPROVED);
        $projectExpenses = $this->scopedProjectTransactions($filters, $startDate, $endDate)
            ->where('transaction_type', ProjectTransaction::TYPE_EXPENSE)
            ->where('status', ProjectTransaction::STATUS_APPROVED);

        $offeringTotal = (float) (clone $offerings)->where('status', Offering::STATUS_APPROVED)->sum('amount');
        $titheTotal = (float) (clone $tithes)->where('status', Tithe::STATUS_APPROVED)->sum('amount');
        $cashTotal = (float) (clone $cash)->where('status', CashContribution::STATUS_APPROVED)->sum('amount');
        $bankTotal = (float) (clone $bank)->where('status', BankContribution::STATUS_VERIFIED)->sum('amount');
        $projectIncomeTotal = (float) (clone $projectIncome)->sum('amount');
        $projectExpenseTotal = (float) (clone $projectExpenses)->sum('amount');

        $incomeItems = collect([
            ['key' => 'offerings', 'label' => db_trans('offerings'), 'amount' => $offeringTotal],
            ['key' => 'tithes', 'label' => db_trans('tithes'), 'amount' => $titheTotal],
            ['key' => 'cash_contributions', 'label' => db_trans('cash_contributions'), 'amount' => $cashTotal],
            ['key' => 'bank_contributions', 'label' => db_trans('bank_contributions'), 'amount' => $bankTotal],
            ['key' => 'project_income', 'label' => db_trans('project_income'), 'amount' => $projectIncomeTotal],
        ]);

        $expenseItems = collect([
            ['key' => 'project_expenses', 'label' => db_trans('project_expenses'), 'amount' => $projectExpenseTotal],
        ]);

        $incomeTotal = (float) $incomeItems->sum('amount');
        $expenseTotal = (float) $expenseItems->sum('amount');

        return [
            'pageTitle' => db_trans('financial_summary'),
            'filters' => $filters,
            'filterData' => $this->filtersData($user),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'incomeItems' => $incomeItems,
            'expenseItems' => $expenseItems,
            'stats' => [
                'income_total' => $incomeTotal,
                'expense_total' => $expenseTotal,
                'balance' => $incomeTotal - $expenseTotal,
                'offerings_total' => $offeringTotal,
                'approved_tithes' => $titheTotal,
                'cash_total' => $cashTotal,
                'bank_total' => $bankTotal,
                'project_income_total' => $projectIncomeTotal,
                'project_expense_total' => $projectExpenseTotal,
                'income_records' => (int) (clone $offerings)->where('status', Offering::STATUS_APPROVED)->count()
                    + (int) (clone $tithes)->where('status', Tithe::STATUS_APPROVED)->count()
                    + (int) (clone $cash)->where('status', CashContribution::STATUS_APPROVED)->count()
                    + (int) (clone $bank)->where('status', BankContribution::STATUS_VERIFIED)->count()
                    + (int) (clone $projectIncome)->count(),
                'expense_records' => (int) (clone $projectExpenses)->count(),
            ],
            'monthlyTrend' => $this->monthlyTrend($user, $filters),
            'contributionTypeBreakdown' => $this->contributionTypeBreakdown($user, $filters, $startDate, $endDate),
        ];
    }

    public function compliance(User $user, array $filters = []): array
    {
        $filters = $this->normalizeHierarchyFilters($user, $filters);

        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : now()->month;

        $memberQuery = Member::query()
            ->with(['familia.jumuiya.kanda'])
            ->where('is_active', true);

        $this->applyResolverScopeToMembers($memberQuery, $user);
        $this->applyHierarchyFiltersToMembers($memberQuery, $filters);

        if (! empty($filters['member_id'])) {
            $memberQuery->where('members.id', (int) $filters['member_id']);
        }

        $members = $memberQuery->get();

        $typeId = ! empty($filters['contribution_type_id']) ? (int) $filters['contribution_type_id'] : null;
        $selectedType = $typeId ? ContributionType::with('plan')->find($typeId) : null;
        $plannedAmount = (float) ($selectedType?->plan?->target_amount ?? 0);
        $installments = max(1, (int) ($selectedType?->plan?->installments_count ?? 1));
        $expectedForPeriod = $month ? round($plannedAmount / $installments, 2) : $plannedAmount;

        $rows = $members->map(function (Member $member) use ($year, $month, $typeId, $expectedForPeriod) {
            $cashAmount = CashContribution::query()
                ->where('member_id', $member->id)
                ->where('status', CashContribution::STATUS_APPROVED)
                ->when($typeId, fn (Builder $query) => $query->where('contribution_type_id', $typeId))
                ->whereYear('contribution_date', $year)
                ->when($month, fn (Builder $query) => $query->whereMonth('contribution_date', $month))
                ->sum('amount');

            $bankAmount = BankContribution::query()
                ->where('member_id', $member->id)
                ->where('status', BankContribution::STATUS_VERIFIED)
                ->when($typeId, fn (Builder $query) => $query->where('contribution_type_id', $typeId))
                ->whereYear('contribution_date', $year)
                ->when($month, fn (Builder $query) => $query->whereMonth('contribution_date', $month))
                ->sum('amount');

            $paid = (float) $cashAmount + (float) $bankAmount;
            $expected = (float) $expectedForPeriod;
            $balance = max($expected - $paid, 0);

            $status = 'unpaid';
            if ($paid > 0 && ($expected <= 0 || $paid >= $expected)) {
                $status = 'paid';
            } elseif ($paid > 0 && $expected > 0 && $paid < $expected) {
                $status = 'partial';
            }

            $source = 'none';
            if ($cashAmount > 0 && $bankAmount > 0) {
                $source = 'mixed';
            } elseif ($cashAmount > 0) {
                $source = 'cash';
            } elseif ($bankAmount > 0) {
                $source = 'bank';
            }

            return [
                'member' => $member,
                'member_id' => $member->id,
                'member_name' => $member->full_name,
                'familia_name' => $member->familia?->name,
                'jumuiya_name' => $member->familia?->jumuiya?->name,
                'kanda_name' => $member->familia?->jumuiya?->kanda?->name,
                'expected_amount' => $expected,
                'cash_paid' => (float) $cashAmount,
                'bank_paid' => (float) $bankAmount,
                'paid_amount' => $paid,
                'balance' => (float) $balance,
                'source' => $source,
                'status' => $status,
            ];
        })->values();

        if (! empty($filters['payment_channel']) && $filters['payment_channel'] !== 'all') {
            $rows = $rows->filter(function (array $row) use ($filters) {
                return $filters['payment_channel'] === 'cash'
                    ? $row['cash_paid'] > 0
                    : $row['bank_paid'] > 0;
            })->values();
        }

        if (! empty($filters['status'])) {
            $rows = $rows->where('status', $filters['status'])->values();
        }

        return [
            'pageTitle' => db_trans('monthly_contribution_compliance'),
            'filters' => $filters,
            'filterData' => $this->filtersData($user),
            'rows' => $rows,
            'stats' => [
                'members_total' => $rows->count(),
                'paid_members' => $rows->where('status', 'paid')->count(),
                'partial_members' => $rows->where('status', 'partial')->count(),
                'unpaid_members' => $rows->where('status', 'unpaid')->count(),
                'expected_total' => (float) $rows->sum('expected_amount'),
                'paid_total' => (float) $rows->sum('paid_amount'),
                'balance_total' => (float) $rows->sum('balance'),
            ],
        ];
    }

    public function exportComplianceCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Member', 'Familia', 'Jumuiya', 'Kanda', 'Expected', 'Cash Paid', 'Bank Paid', 'Total Paid', 'Balance', 'Status']);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['member_name'],
                $row['familia_name'],
                $row['jumuiya_name'],
                $row['kanda_name'],
                $row['expected_amount'],
                $row['cash_paid'],
                $row['bank_paid'],
                $row['paid_amount'],
                $row['balance'],
                $row['status'],
            ]);
        }

        rewind($handle);

        return stream_get_contents($handle);
    }

    protected function monthlyTrend(User $user, array $filters): array
    {
        $year = (int) ($filters['year'] ?? now()->year);

        $months = collect(range(1, 12))->map(fn (int $month) => Carbon::createFromDate($year, $month, 1));

        $income = [];
        $expenses = [];
        $balances = [];

        foreach ($months as $monthDate) {
            $start = $monthDate->copy()->startOfMonth();
            $end = $monthDate->copy()->endOfMonth();

            $monthOfferings = (float) $this->scopedOfferings($user, $filters, $start, $end)
                ->where('status', Offering::STATUS_APPROVED)
                ->sum('amount');
            $monthTithes = (float) $this->scopedTithes($user, $filters, $start, $end)
                ->where('status', Tithe::STATUS_APPROVED)
                ->sum('amount');
            $monthCash = (float) $this->scopedCashContributions($user, $filters, $start, $end)
                ->where('status', CashContribution::STATUS_APPROVED)
                ->sum('amount');
            $monthBank = (float) $this->scopedBankContributions($user, $filters, $start, $end)
                ->where('status', BankContribution::STATUS_VERIFIED)
                ->sum('amount');
            $monthProjectIncome = (float) $this->scopedProjectTransactions($filters, $start, $end)
                ->where('transaction_type', ProjectTransaction::TYPE_INCOME)
                ->where('status', ProjectTransaction::STATUS_APPROVED)
                ->sum('amount');
            $monthProjectExpenses = (float) $this->scopedProjectTransactions($filters, $start, $end)
                ->where('transaction_type', ProjectTransaction::TYPE_EXPENSE)
                ->where('status', ProjectTransaction::STATUS_APPROVED)
                ->sum('amount');

            $income[] = $monthOfferings + $monthTithes + $monthCash + $monthBank + $monthProjectIncome;
            $expenses[] = $monthProjectExpenses;
            $balances[] = ($monthOfferings + $monthTithes + $monthCash + $monthBank + $monthProjectIncome) - $monthProjectExpenses;
        }

        return [
            'labels' => $months->map(fn (Carbon $month) => $month->translatedFormat('M'))->values(),
            'income' => collect($income)->values(),
            'expenses' => collect($expenses)->values(),
            'balances' => collect($balances)->values(),
        ];
    }

    protected function contributionTypeBreakdown(User $user, array $filters, Carbon $startDate, Carbon $endDate): array
    {
        $types = ContributionType::query()->where('is_active', true)->orderBy('name')->get();

        $rows = $types->map(function (ContributionType $type) use ($user, $filters, $startDate, $endDate) {
            $cash = (float) $this->scopedCashContributions($user, $filters, $startDate, $endDate)
                ->where('contribution_type_id', $type->id)
                ->where('status', CashContribution::STATUS_APPROVED)
                ->sum('amount');

            $bank = (float) $this->scopedBankContributions($user, $filters, $startDate, $endDate)
                ->where('contribution_type_id', $type->id)
                ->where('status', BankContribution::STATUS_VERIFIED)
                ->sum('amount');

            return [
                'name' => $type->name,
                'cash_total' => $cash,
                'bank_total' => $bank,
                'total' => $cash + $bank,
            ];
        })->filter(fn (array $row) => $row['total'] > 0)->values();

        return [
            'labels' => $rows->pluck('name')->values(),
            'amounts' => $rows->pluck('total')->values(),
            'rows' => $rows,
        ];
    }

    protected function resolveDateRange(array $filters): array
    {
        if (! empty($filters['start_date']) && ! empty($filters['end_date'])) {
            return [Carbon::parse($filters['start_date'])->startOfDay(), Carbon::parse($filters['end_date'])->endOfDay()];
        }

        $year = (int) ($filters['year'] ?? now()->year);

        if (! empty($filters['month'])) {
            $date = Carbon::createFromDate($year, (int) $filters['month'], 1);
            return [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];
        }

        return [
            Carbon::createFromDate($year, 1, 1)->startOfYear(),
            Carbon::createFromDate($year, 12, 31)->endOfYear(),
        ];
    }

    protected function scopedOfferings(User $user, array $filters, Carbon $startDate, Carbon $endDate): Builder
    {
        $query = Offering::query()->whereBetween('collection_date', [$startDate, $endDate]);
        $this->applyResolverScopeToOfferings($query, $user);
        $this->applyHierarchyFiltersToOfferings($query, $filters);
        return $query;
    }

    protected function scopedTithes(User $user, array $filters, Carbon $startDate, Carbon $endDate): Builder
    {
        $query = Tithe::query()->whereBetween('contribution_date', [$startDate, $endDate]);
        $this->applyResolverScopeToTithes($query, $user);

        if (! empty($filters['kanda_id'])) {
            if ($this->hasColumn('tithes', 'kanda_id')) {
                $query->where('kanda_id', (int) $filters['kanda_id']);
            } else {
                $query->whereHas('jumuiya', fn (Builder $q) => $q->where('kanda_id', (int) $filters['kanda_id']));
            }
        }

        if (! empty($filters['jumuiya_id'])) {
            $query->where('jumuiya_id', (int) $filters['jumuiya_id']);
        }

        if (! empty($filters['member_id'])) {
            $query->where('member_id', (int) $filters['member_id']);
        }

        return $query;
    }

    protected function scopedCashContributions(User $user, array $filters, Carbon $startDate, Carbon $endDate): Builder
    {
        $query = CashContribution::query()->whereBetween('contribution_date', [$startDate, $endDate]);
        $this->applyResolverScopeToContributionQuery($query, $user, 'cash_contributions');
        $this->applyHierarchyFiltersToContributionQuery($query, $filters, 'cash_contributions');

        if (! empty($filters['member_id'])) {
            $query->where('member_id', (int) $filters['member_id']);
        }

        if (! empty($filters['contribution_type_id'])) {
            $query->where('contribution_type_id', (int) $filters['contribution_type_id']);
        }

        return $query;
    }

    protected function scopedBankContributions(User $user, array $filters, Carbon $startDate, Carbon $endDate): Builder
    {
        $query = BankContribution::query()->whereBetween('contribution_date', [$startDate, $endDate]);
        $this->applyResolverScopeToContributionQuery($query, $user, 'bank_contributions');
        $this->applyHierarchyFiltersToContributionQuery($query, $filters, 'bank_contributions');

        if (! empty($filters['member_id'])) {
            $query->where('member_id', (int) $filters['member_id']);
        }

        if (! empty($filters['contribution_type_id'])) {
            $query->where('contribution_type_id', (int) $filters['contribution_type_id']);
        }

        return $query;
    }

    protected function scopedProjectTransactions(array $filters, Carbon $startDate, Carbon $endDate): Builder
    {
        $query = ProjectTransaction::query()->whereBetween('transaction_date', [$startDate, $endDate]);

        if (! empty($filters['kanda_id']) && $this->hasColumn('project_transactions', 'kanda_id')) {
            $query->where('kanda_id', (int) $filters['kanda_id']);
        }

        if (! empty($filters['jumuiya_id']) && $this->hasColumn('project_transactions', 'jumuiya_id')) {
            $query->where('jumuiya_id', (int) $filters['jumuiya_id']);
        }

        return $query;
    }

    protected function applyHierarchyFiltersToMembers(Builder $query, array $filters): void
    {
        if (! empty($filters['familia_id'])) {
            $query->where('familia_id', (int) $filters['familia_id']);
        }

        if (! empty($filters['jumuiya_id'])) {
            $query->whereHas('familia', fn (Builder $q) => $q->where('jumuiya_id', (int) $filters['jumuiya_id']));
        }

        if (! empty($filters['kanda_id'])) {
            $query->whereHas('familia.jumuiya', fn (Builder $q) => $q->where('kanda_id', (int) $filters['kanda_id']));
        }
    }

    protected function applyHierarchyFiltersToContributionQuery(Builder $query, array $filters, string $table): void
    {
        if (! empty($filters['familia_id'])) {
            if ($this->hasColumn($table, 'familia_id')) {
                $query->where('familia_id', (int) $filters['familia_id']);
            } else {
                $query->whereHas('member', fn (Builder $memberQuery) => $memberQuery->where('familia_id', (int) $filters['familia_id']));
            }
        }

        if (! empty($filters['jumuiya_id'])) {
            if ($this->hasColumn($table, 'jumuiya_id')) {
                $query->where('jumuiya_id', (int) $filters['jumuiya_id']);
            } else {
                $query->whereHas('member.familia', fn (Builder $familiaQuery) => $familiaQuery->where('jumuiya_id', (int) $filters['jumuiya_id']));
            }
        }

        if (! empty($filters['kanda_id'])) {
            if ($this->hasColumn($table, 'kanda_id')) {
                $query->where('kanda_id', (int) $filters['kanda_id']);
            } else {
                $query->whereHas('member.familia.jumuiya', fn (Builder $jumuiyaQuery) => $jumuiyaQuery->where('kanda_id', (int) $filters['kanda_id']));
            }
        }
    }

    protected function applyHierarchyFiltersToOfferings(Builder $query, array $filters): void
    {
        if (! empty($filters['jumuiya_id']) && $this->hasColumn('offerings', 'jumuiya_id')) {
            $query->where('jumuiya_id', (int) $filters['jumuiya_id']);
        }

        if (! empty($filters['kanda_id'])) {
            if ($this->hasColumn('offerings', 'kanda_id')) {
                $query->where('kanda_id', (int) $filters['kanda_id']);
            } elseif ($this->hasColumn('offerings', 'jumuiya_id')) {
                $query->whereHas('jumuiya', fn (Builder $jumuiyaQuery) => $jumuiyaQuery->where('kanda_id', (int) $filters['kanda_id']));
            }
        }
    }

    protected function applyResolverScopeToMembers(Builder $query, User $user): void
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($scope->isKanda()) {
            $query->whereHas('familia.jumuiya', fn (Builder $q) => $q->where('kanda_id', (int) $scope->kandaId));
        }

        if ($scope->isJumuiya()) {
            $query->whereHas('familia', fn (Builder $q) => $q->where('jumuiya_id', (int) $scope->jumuiyaId));
        }
    }

    protected function applyResolverScopeToContributionQuery(Builder $query, User $user, string $table): void
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($scope->isKanda()) {
            if ($this->hasColumn($table, 'kanda_id')) {
                $query->where('kanda_id', (int) $scope->kandaId);
            } else {
                $query->whereHas('member.familia.jumuiya', fn (Builder $q) => $q->where('kanda_id', (int) $scope->kandaId));
            }
        }

        if ($scope->isJumuiya()) {
            if ($this->hasColumn($table, 'jumuiya_id')) {
                $query->where('jumuiya_id', (int) $scope->jumuiyaId);
            } else {
                $query->whereHas('member.familia', fn (Builder $q) => $q->where('jumuiya_id', (int) $scope->jumuiyaId));
            }
        }
    }

    protected function applyResolverScopeToOfferings(Builder $query, User $user): void
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($scope->isKanda()) {
            if ($this->hasColumn('offerings', 'kanda_id')) {
                $query->where('kanda_id', (int) $scope->kandaId);
            } elseif ($this->hasColumn('offerings', 'jumuiya_id')) {
                $query->whereHas('jumuiya', fn (Builder $q) => $q->where('kanda_id', (int) $scope->kandaId));
            }
        }

        if ($scope->isJumuiya() && $this->hasColumn('offerings', 'jumuiya_id')) {
            $query->where('jumuiya_id', (int) $scope->jumuiyaId);
        }
    }

    protected function applyResolverScopeToTithes(Builder $query, User $user): void
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($scope->isKanda()) {
            if ($this->hasColumn('tithes', 'kanda_id')) {
                $query->where('kanda_id', (int) $scope->kandaId);
            } else {
                $query->whereHas('jumuiya', fn (Builder $q) => $q->where('kanda_id', (int) $scope->kandaId));
            }
        }

        if ($scope->isJumuiya()) {
            $query->where('jumuiya_id', (int) $scope->jumuiyaId);
        }
    }

    protected function normalizeHierarchyFilters(User $user, array $filters): array
    {
        $scope = $this->scopeResolver->resolve($user);

        if ($scope->isInvalid()) {
            throw new AuthorizationException($scope->reason ?? 'This account has an invalid scope.');
        }

        $filters['year'] = ! empty($filters['year']) ? (int) $filters['year'] : now()->year;
        $filters['month'] = ! empty($filters['month']) ? (int) $filters['month'] : null;
        $filters['kanda_id'] = ! empty($filters['kanda_id']) ? (int) $filters['kanda_id'] : null;
        $filters['jumuiya_id'] = ! empty($filters['jumuiya_id']) ? (int) $filters['jumuiya_id'] : null;
        $filters['familia_id'] = ! empty($filters['familia_id']) ? (int) $filters['familia_id'] : null;
        $filters['member_id'] = ! empty($filters['member_id']) ? (int) $filters['member_id'] : null;
        $filters['contribution_type_id'] = ! empty($filters['contribution_type_id']) ? (int) $filters['contribution_type_id'] : null;
        $filters['payment_channel'] = $filters['payment_channel'] ?? 'all';
        $filters['status'] = $filters['status'] ?? null;
        $filters['start_date'] = $filters['start_date'] ?? null;
        $filters['end_date'] = $filters['end_date'] ?? null;

        if ($scope->isGlobal()) {
            $this->validateRequestedHierarchy($user, $filters);
            return $filters;
        }

        if ($scope->isKanda()) {
            if ($filters['kanda_id'] && (int) $filters['kanda_id'] !== (int) $scope->kandaId) {
                throw new AuthorizationException('You cannot access another kanda financial scope.');
            }

            $filters['kanda_id'] = (int) $scope->kandaId;

            if ($filters['jumuiya_id']) {
                $jumuiya = Jumuiya::query()->findOrFail($filters['jumuiya_id']);
                $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);
            }

            return $filters;
        }

        if ($scope->isJumuiya()) {
            if ($filters['kanda_id'] && (int) $filters['kanda_id'] !== (int) $scope->kandaId) {
                throw new AuthorizationException('You cannot access another kanda financial scope.');
            }

            if ($filters['jumuiya_id'] && (int) $filters['jumuiya_id'] !== (int) $scope->jumuiyaId) {
                throw new AuthorizationException('You cannot access another jumuiya financial scope.');
            }

            $filters['kanda_id'] = (int) $scope->kandaId;
            $filters['jumuiya_id'] = (int) $scope->jumuiyaId;
        }

        return $filters;
    }

    protected function validateRequestedHierarchy(User $user, array $filters): void
    {
        if ($filters['jumuiya_id'] && $filters['kanda_id']) {
            $jumuiya = Jumuiya::query()->findOrFail($filters['jumuiya_id']);

            if ((int) $jumuiya->kanda_id !== (int) $filters['kanda_id']) {
                throw new AuthorizationException('Selected jumuiya does not belong to the selected kanda.');
            }

            $this->scopeAccessGate->authorizeJumuiya($user, $jumuiya);
        }

        if ($filters['familia_id']) {
            $familia = Familia::query()->with('jumuiya')->findOrFail($filters['familia_id']);

            if ($filters['jumuiya_id'] && (int) $familia->jumuiya_id !== (int) $filters['jumuiya_id']) {
                throw new AuthorizationException('Selected familia does not belong to the selected jumuiya.');
            }

            $this->scopeAccessGate->authorizeFamilia($user, $familia);
        }

        if ($filters['member_id']) {
            $member = Member::query()->with('familia.jumuiya')->findOrFail($filters['member_id']);

            if ($filters['familia_id'] && (int) $member->familia_id !== (int) $filters['familia_id']) {
                throw new AuthorizationException('Selected member does not belong to the selected familia.');
            }

            if ($filters['jumuiya_id'] && (int) $member->familia?->jumuiya_id !== (int) $filters['jumuiya_id']) {
                throw new AuthorizationException('Selected member does not belong to the selected jumuiya.');
            }

            if ($filters['kanda_id'] && (int) $member->familia?->jumuiya?->kanda_id !== (int) $filters['kanda_id']) {
                throw new AuthorizationException('Selected member does not belong to the selected kanda.');
            }

            $this->scopeAccessGate->authorizeMember($user, $member);
        }
    }

    protected function contributionTypes(): Collection
    {
        return ContributionType::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
    }

    protected function allContributionStatuses(): array
    {
        return array_values(array_unique(array_merge(
            BankContribution::availableStatuses(),
            CashContribution::availableStatuses(),
            [Tithe::STATUS_APPROVED, Offering::STATUS_APPROVED]
        )));
    }

    protected function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
