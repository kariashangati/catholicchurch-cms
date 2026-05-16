<?php

namespace App\Services\Reports;

use App\Models\BankContribution;
use App\Models\BudgetEstimateExpense;
use App\Models\BudgetEstimateIncome;
use App\Models\CashContribution;
use App\Models\Offering;
use App\Models\ProjectTransaction;
use App\Models\Tithe;
use App\Models\User;
use App\Services\Access\UserScopeResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BudgetEstimateService
{
    public const INCOME_SOURCE_OFFERINGS = 'offerings';
    public const INCOME_SOURCE_TITHES = 'tithes';
    public const INCOME_SOURCE_CASH_CONTRIBUTIONS = 'cash_contributions';
    public const INCOME_SOURCE_BANK_CONTRIBUTIONS = 'bank_contributions';
    public const INCOME_SOURCE_PROJECT_INCOME = 'project_income';

    public const EXPENSE_SOURCE_PROJECT_EXPENSES = 'project_expenses';

    public const GROUP_ORDINARY = 'kawaida';
    public const GROUP_DEVELOPMENT = 'maendeleo';

    public function __construct(
        protected UserScopeResolver $scopeResolver,
    ) {
    }

    public static function incomeSourceOptions(): array
    {
        return [
            self::INCOME_SOURCE_OFFERINGS => db_trans('offerings'),
            self::INCOME_SOURCE_TITHES => db_trans('tithes'),
            self::INCOME_SOURCE_CASH_CONTRIBUTIONS => db_trans('cash_contributions'),
            self::INCOME_SOURCE_BANK_CONTRIBUTIONS => db_trans('bank_contributions'),
            self::INCOME_SOURCE_PROJECT_INCOME => db_trans('project_income'),
        ];
    }

    public static function expenseSourceOptions(): array
    {
        return [
            self::EXPENSE_SOURCE_PROJECT_EXPENSES => db_trans('project_expenses'),
        ];
    }

    public static function groupOptions(): array
    {
        return [
            self::GROUP_ORDINARY => db_trans('ordinary'),
            self::GROUP_DEVELOPMENT => db_trans('development'),
        ];
    }

    public static function allowedIncomeSourceCodes(): array
    {
        return array_keys(self::incomeSourceOptions());
    }

    public static function allowedExpenseSourceCodes(): array
    {
        return array_keys(self::expenseSourceOptions());
    }

    public static function allowedGroupCodes(): array
    {
        return array_keys(self::groupOptions());
    }

    public function dashboardData(User $user, int $year): array
    {
        $incomeBudgets = BudgetEstimateIncome::query()
            ->where('budget_year', $year)
            ->where('is_active', true)
            ->orderBy('group_name')
            ->orderBy('category_name')
            ->get();

        $expenseBudgets = BudgetEstimateExpense::query()
            ->where('budget_year', $year)
            ->where('is_active', true)
            ->orderBy('group_name')
            ->orderBy('category_name')
            ->get();

        $incomeRows = $incomeBudgets->map(function (BudgetEstimateIncome $item) use ($user, $year) {
            $actual = $this->actualIncomeForCategory($user, $item->category_code, $year);

            return [
                'id' => $item->id,
                'category_code' => $item->category_code,
                'category_name' => $item->category_name,
                'category_label' => $this->incomeSourceLabel($item->category_code),
                'group_name' => $item->group_name,
                'group_label' => $this->groupLabel($item->group_name),
                'budget_amount' => (float) $item->amount,
                'actual_amount' => $actual,
                'variance' => $actual - (float) $item->amount,
                'notes' => $item->notes,
            ];
        });

        $expenseRows = $expenseBudgets->map(function (BudgetEstimateExpense $item) use ($user, $year) {
            $actual = $this->actualExpenseForCategory($user, $item->category_code, $year);

            return [
                'id' => $item->id,
                'category_code' => $item->category_code,
                'category_name' => $item->category_name,
                'category_label' => $this->expenseSourceLabel($item->category_code),
                'group_name' => $item->group_name,
                'group_label' => $this->groupLabel($item->group_name),
                'budget_amount' => (float) $item->amount,
                'actual_amount' => $actual,
                'variance' => $actual - (float) $item->amount,
                'notes' => $item->notes,
            ];
        });

        return [
            'pageTitle' => db_trans('budget_vs_actual'),
            'year' => $year,
            'incomeRows' => $incomeRows,
            'expenseRows' => $expenseRows,
            'incomeBudgetTotal' => $incomeRows->sum('budget_amount'),
            'incomeActualTotal' => $incomeRows->sum('actual_amount'),
            'expenseBudgetTotal' => $expenseRows->sum('budget_amount'),
            'expenseActualTotal' => $expenseRows->sum('actual_amount'),
            'incomeSourceOptions' => self::incomeSourceOptions(),
            'expenseSourceOptions' => self::expenseSourceOptions(),
            'groupOptions' => self::groupOptions(),
            'availableYears' => collect(range(now()->year + 1, now()->year - 5))->sortDesc()->values(),
        ];
    }

    public function createIncome(array $data, User $user): BudgetEstimateIncome
    {
        $data = $this->normalizeBudgetData($data, self::incomeSourceOptions());
        $data['created_by'] = $user->id;
        $data['updated_by'] = $user->id;
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return BudgetEstimateIncome::create($data);
    }

    public function updateIncome(BudgetEstimateIncome $budgetEstimateIncome, array $data, User $user): BudgetEstimateIncome
    {
        $data = $this->normalizeBudgetData($data, self::incomeSourceOptions());
        $data['updated_by'] = $user->id;
        $budgetEstimateIncome->update($data);

        return $budgetEstimateIncome->refresh();
    }

    public function createExpense(array $data, User $user): BudgetEstimateExpense
    {
        $data = $this->normalizeBudgetData($data, self::expenseSourceOptions());
        $data['created_by'] = $user->id;
        $data['updated_by'] = $user->id;
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        return BudgetEstimateExpense::create($data);
    }

    public function updateExpense(BudgetEstimateExpense $budgetEstimateExpense, array $data, User $user): BudgetEstimateExpense
    {
        $data = $this->normalizeBudgetData($data, self::expenseSourceOptions());
        $data['updated_by'] = $user->id;
        $budgetEstimateExpense->update($data);

        return $budgetEstimateExpense->refresh();
    }

    protected function normalizeBudgetData(array $data, array $sourceOptions): array
    {
        $code = (string) ($data['category_code'] ?? '');
        $label = $sourceOptions[$code] ?? null;

        $data['category_name'] = trim((string) ($data['category_name'] ?? ''));
        if ($data['category_name'] === '' && $label) {
            $data['category_name'] = $label;
        }

        if (! empty($data['group_name']) && in_array($data['group_name'], ['ordinary', 'development'], true)) {
            $data['group_name'] = match ($data['group_name']) {
                'development' => self::GROUP_DEVELOPMENT,
                default => self::GROUP_ORDINARY,
            };
        }

        $data['group_name'] = $data['group_name'] ?? self::GROUP_ORDINARY;

        return $data;
    }

    protected function incomeSourceLabel(?string $code): string
    {
        return self::incomeSourceOptions()[$code] ?? Str::headline((string) $code);
    }

    protected function expenseSourceLabel(?string $code): string
    {
        return self::expenseSourceOptions()[$code] ?? Str::headline((string) $code);
    }

    protected function groupLabel(?string $group): string
    {
        return self::groupOptions()[$group] ?? db_trans($group ?: self::GROUP_ORDINARY);
    }

    protected function actualIncomeForCategory(User $user, ?string $code, int $year): float
    {
        return match ($code) {
            self::INCOME_SOURCE_OFFERINGS => (float) $this->scopedOfferingsQuery($user)
                ->whereYear('collection_date', $year)
                ->where('status', Offering::STATUS_APPROVED)
                ->sum('amount'),

            self::INCOME_SOURCE_TITHES => (float) $this->scopedTithesQuery($user)
                ->whereYear('contribution_date', $year)
                ->where('status', Tithe::STATUS_APPROVED)
                ->sum('amount'),

            self::INCOME_SOURCE_CASH_CONTRIBUTIONS => (float) $this->scopedCashContributionsQuery($user)
                ->whereYear('contribution_date', $year)
                ->where('status', CashContribution::STATUS_APPROVED)
                ->sum('amount'),

            self::INCOME_SOURCE_BANK_CONTRIBUTIONS => (float) $this->scopedBankContributionsQuery($user)
                ->whereYear('contribution_date', $year)
                ->where('status', BankContribution::STATUS_VERIFIED)
                ->sum('amount'),

            self::INCOME_SOURCE_PROJECT_INCOME => (float) $this->scopedProjectTransactionsQuery()
                ->whereYear('transaction_date', $year)
                ->where('transaction_type', ProjectTransaction::TYPE_INCOME)
                ->where('status', ProjectTransaction::STATUS_APPROVED)
                ->sum('amount'),

            default => 0.0,
        };
    }

    protected function actualExpenseForCategory(User $user, ?string $code, int $year): float
    {
        return match ($code) {
            self::EXPENSE_SOURCE_PROJECT_EXPENSES => (float) $this->scopedProjectTransactionsQuery()
                ->whereYear('transaction_date', $year)
                ->where('transaction_type', ProjectTransaction::TYPE_EXPENSE)
                ->where('status', ProjectTransaction::STATUS_APPROVED)
                ->sum('amount'),

            default => 0.0,
        };
    }

    protected function scopedOfferingsQuery(User $user): Builder
    {
        $scope = $this->scopeResolver->resolve($user);
        $query = Offering::query();

        if ($scope->isInvalid()) {
            return $query->whereRaw('1 = 0');
        }

        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->where(function (Builder $builder) use ($scope) {
                $builder->where('kanda_id', $scope->kandaId)
                    ->orWhereHas('jumuiya', fn (Builder $jumuiyaQuery) => $jumuiyaQuery->where('kanda_id', $scope->kandaId));
            });
        }

        if ($scope->isJumuiya()) {
            return $query->where('jumuiya_id', $scope->jumuiyaId);
        }

        return $query->whereRaw('1 = 0');
    }

    protected function scopedTithesQuery(User $user): Builder
    {
        $scope = $this->scopeResolver->resolve($user);
        $query = Tithe::query();

        if ($scope->isInvalid()) {
            return $query->whereRaw('1 = 0');
        }

        if ($scope->isGlobal()) {
            return $query;
        }

        if ($scope->isKanda()) {
            return $query->whereHas('jumuiya', fn (Builder $jumuiyaQuery) => $jumuiyaQuery->where('kanda_id', $scope->kandaId));
        }

        if ($scope->isJumuiya()) {
            return $query->where('jumuiya_id', $scope->jumuiyaId);
        }

        return $query->whereRaw('1 = 0');
    }

    protected function scopedCashContributionsQuery(User $user): Builder
    {
        $scope = $this->scopeResolver->resolve($user);
        $query = CashContribution::query();

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
            return $query->where('jumuiya_id', $scope->jumuiyaId);
        }

        return $query->whereRaw('1 = 0');
    }

    protected function scopedBankContributionsQuery(User $user): Builder
    {
        $scope = $this->scopeResolver->resolve($user);
        $query = BankContribution::query();

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
            return $query->where('jumuiya_id', $scope->jumuiyaId);
        }

        return $query->whereRaw('1 = 0');
    }

    protected function scopedProjectTransactionsQuery(): Builder
    {
        return ProjectTransaction::query();
    }
}
