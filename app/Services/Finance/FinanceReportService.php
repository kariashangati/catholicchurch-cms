<?php

namespace App\Services\Finance;

use App\Models\BankContribution;
use App\Models\BudgetExpenseEstimate;
use App\Models\BudgetIncomeEstimate;
use App\Models\CashContribution;
use App\Models\Offering;
use App\Models\Tithe;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FinanceReportService
{
    public function __construct(
        protected ReportScopeService $scope
    ) {
    }

    public function getFinancialSummary(User $user, int $year): array
    {
        $income = $this->incomeBreakdown($user, $year);
        $expense = $this->expenseBreakdown($year);
        $monthlyExpenseBudget = round(
            (float) BudgetExpenseEstimate::query()
                ->where('budget_year', $year)
                ->sum('amount') / 12,
            2
        );

        return [
            'year' => $year,
            'income' => $income,
            'expense' => $expense,
            'totals' => [
                'income' => (float) $income->sum('amount'),
                'expense' => (float) $expense->sum('amount'),
                'balance' => (float) $income->sum('amount') - (float) $expense->sum('amount'),
            ],
            'budget' => [
                'income_budget' => (float) BudgetIncomeEstimate::query()
                    ->where('budget_year', $year)
                    ->sum('amount'),
                'expense_budget' => (float) BudgetExpenseEstimate::query()
                    ->where('budget_year', $year)
                    ->sum('amount'),
            ],
            'monthly' => collect(range(1, 12))
                ->map(function (int $month) use ($user, $year, $monthlyExpenseBudget) {
                    return [
                        'month' => Carbon::create()->month($month)->format('M'),
                        'income' => $this->incomeForMonth($user, $year, $month),
                        'expense' => $monthlyExpenseBudget,
                    ];
                })
                ->map(function (array $row) {
                    $row['balance'] = round((float) $row['income'] - (float) $row['expense'], 2);

                    return $row;
                }),
        ];
    }

    public function incomeBreakdown(User $user, int $year): Collection
    {
        $tithe = $this->scopedTitheQuery($user, $year);
        $offering = $this->scopedOfferingQuery($user, $year);
        $cash = $this->scopedCashQuery($user, $year);
        $bank = $this->scopedBankQuery($user, $year);

        return collect([
            [
                'label' => db_trans('tithes'),
                'amount' => (float) (clone $tithe)->sum('amount'),
                'route' => route('finance.tithes.index'),
            ],
            [
                'label' => db_trans('offerings'),
                'amount' => (float) (clone $offering)->sum('amount'),
                'route' => route('finance.offerings.index'),
            ],
            [
                'label' => db_trans('cash_contributions'),
                'amount' => (float) (clone $cash)->sum('amount'),
                'route' => route('finance.contributions.cash.index'),
            ],
            [
                'label' => db_trans('bank_contributions'),
                'amount' => (float) (clone $bank)->sum('amount'),
                'route' => route('finance.contributions.bank.index'),
            ],
        ]);
    }

    public function expenseBreakdown(int $year): Collection
    {
        $rows = BudgetExpenseEstimate::query()
            ->where('budget_year', $year)
            ->selectRaw('category_group, SUM(amount) as amount')
            ->groupBy('category_group')
            ->get();

        if ($rows->isNotEmpty()) {
            return $rows->map(fn ($row) => [
                'label' => $row->category_group === 'development'
                    ? db_trans('development')
                    : db_trans('ordinary'),
                'amount' => (float) $row->amount,
                'route' => route('finance.budgets.expense.index', ['year' => $year]),
            ]);
        }

        return collect([
            [
                'label' => db_trans('ordinary'),
                'amount' => 0.0,
                'route' => route('finance.budgets.expense.index', ['year' => $year]),
            ],
            [
                'label' => db_trans('development'),
                'amount' => 0.0,
                'route' => route('finance.budgets.expense.index', ['year' => $year]),
            ],
        ]);
    }

    protected function incomeForMonth(User $user, int $year, int $month): float
    {
        $tithe = $this->scopedTitheQuery($user, $year, $month);
        $offering = $this->scopedOfferingQuery($user, $year, $month);
        $cash = $this->scopedCashQuery($user, $year, $month);
        $bank = $this->scopedBankQuery($user, $year, $month);

        return (float) (clone $tithe)->sum('amount')
            + (float) (clone $offering)->sum('amount')
            + (float) (clone $cash)->sum('amount')
            + (float) (clone $bank)->sum('amount');
    }

    protected function scopedTitheQuery(User $user, int $year, ?int $month = null): Builder
    {
        $query = Tithe::query()
            ->whereYear('contribution_date', $year);

        if ($month !== null) {
            $query->whereMonth('contribution_date', $month);
        }

        return $this->scope->applyTitheScope($query, $user);
    }

    protected function scopedOfferingQuery(User $user, int $year, ?int $month = null): Builder
    {
        $query = Offering::query()
            ->whereYear('collection_date', $year);

        if ($month !== null) {
            $query->whereMonth('collection_date', $month);
        }

        return $this->scope->applyOfferingScope($query, $user);
    }

    protected function scopedCashQuery(User $user, int $year, ?int $month = null): Builder
    {
        $query = CashContribution::query()
            ->whereYear('contribution_date', $year);

        if ($month !== null) {
            $query->whereMonth('contribution_date', $month);
        }

        return $this->scope->applyMemberScope($query, $user);
    }

    protected function scopedBankQuery(User $user, int $year, ?int $month = null): Builder
    {
        $query = BankContribution::query()
            ->whereYear('contribution_date', $year);

        if ($month !== null) {
            $query->whereMonth('contribution_date', $month);
        }

        return $this->scope->applyMemberScope($query, $user);
    }
}