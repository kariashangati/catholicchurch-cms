<?php

namespace App\Services\Finance;

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\ProjectTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProjectFinanceDashboardService
{
    public function getDashboardData(array $filters = []): array
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;

        $projectsQuery = $this->projectQuery($filters);

        $filteredTransactionsQuery = $this->transactionQuery($filters);
        $yearTransactionsQuery = $this->transactionQuery(array_merge($filters, [
            'year' => $year,
            'month' => null,
        ]));

        $monthTransactionsQuery = $this->transactionQuery(array_merge($filters, [
            'year' => $year,
            'month' => $month ?: now()->month,
        ]));

        $monthIncome = (float) (clone $monthTransactionsQuery)
            ->where('transaction_type', ProjectTransaction::TYPE_INCOME)
            ->sum('amount');

        $monthExpense = (float) (clone $monthTransactionsQuery)
            ->where('transaction_type', ProjectTransaction::TYPE_EXPENSE)
            ->sum('amount');

        $yearIncome = (float) (clone $yearTransactionsQuery)
            ->where('transaction_type', ProjectTransaction::TYPE_INCOME)
            ->sum('amount');

        $yearExpense = (float) (clone $yearTransactionsQuery)
            ->where('transaction_type', ProjectTransaction::TYPE_EXPENSE)
            ->sum('amount');

        $pendingTransactions = (int) (clone $filteredTransactionsQuery)
            ->where('status', ProjectTransaction::STATUS_PENDING)
            ->count();

        $activeProjects = (int) (clone $projectsQuery)
            ->where('status', Project::STATUS_ACTIVE)
            ->count();

        $completedProjects = (int) (clone $projectsQuery)
            ->where('status', Project::STATUS_COMPLETED)
            ->count();

        $projectCount = (int) (clone $projectsQuery)->count();

        $monthlyTrend = collect(range(1, 12))->map(function (int $monthNumber) use ($year, $filters) {
            $chartFilters = $filters;
            $chartFilters['year'] = $year;
            unset($chartFilters['month']);

            $query = $this->transactionQuery($chartFilters);

            return [
                'label' => Carbon::create(null, $monthNumber, 1)->translatedFormat('M'),
                'income' => (float) (clone $query)
                    ->whereMonth('transaction_date', $monthNumber)
                    ->where('transaction_type', ProjectTransaction::TYPE_INCOME)
                    ->sum('amount'),
                'expense' => (float) (clone $query)
                    ->whereMonth('transaction_date', $monthNumber)
                    ->where('transaction_type', ProjectTransaction::TYPE_EXPENSE)
                    ->sum('amount'),
            ];
        });

        $topProjects = $this->topProjects($filters);
        $categoryBreakdown = $this->categoryBreakdown($filters);

        return [
            'pageTitle' => db_trans('project_finance'),
            'filters' => $filters,
            'stats' => [
                'month_income' => $monthIncome,
                'month_expense' => $monthExpense,
                'month_net' => $monthIncome - $monthExpense,
                'year_income' => $yearIncome,
                'year_expense' => $yearExpense,
                'year_net' => $yearIncome - $yearExpense,
                'project_count' => $projectCount,
                'active_projects' => $activeProjects,
                'completed_projects' => $completedProjects,
                'pending_transactions' => $pendingTransactions,
            ],
            'monthlyChart' => [
                'labels' => $monthlyTrend->pluck('label')->values(),
                'income' => $monthlyTrend->pluck('income')->values(),
                'expense' => $monthlyTrend->pluck('expense')->values(),
            ],
            'categoryChart' => [
                'labels' => $categoryBreakdown->pluck('name')->values(),
                'amounts' => $categoryBreakdown->pluck('amount')->values(),
            ],
            'topProjects' => $topProjects,
        ];
    }

    public function getIndexData(array $filters = []): array
    {
        return [
            'pageTitle' => db_trans('project_finance'),
            'filters' => $filters,
            'categories' => ProjectCategory::query()
                ->withCount('projects')
                ->orderBy('name')
                ->get(),
            'projects' => $this->projectRowsQuery($filters)->get(),
            'transactions' => $this->transactionQuery($filters)
                ->with(['project.category', 'recorder', 'approver'])
                ->latest('transaction_date')
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ];
    }

    public function getTopProjectsExportRows(array $filters = []): Collection
    {
        return $this->topProjects($filters)
            ->map(function ($project, int $index) {
                return (object) [
                    'sn' => $index + 1,
                    'project' => $project->name ?? '—',
                    'status' => $this->projectStatusLabel($project->status ?? null),
                    'income' => (float) ($project->income ?? 0),
                    'expense' => (float) ($project->expense ?? 0),
                    'balance' => (float) ($project->net ?? 0),
                ];
            })
            ->values();
    }

    public function getTopProjectsExportPdfData(array $filters = []): array
    {
        $rows = $this->getTopProjectsExportRows($filters);
        $filterLabel = $this->projectFinanceFilterLabel($filters);

        return [
            'pageTitle' => db_trans('project_finance_dashboard'),
            'reportTitle' => db_trans('project_finance_top_projects_report_title_for') . ' ' . $filterLabel,
            'metaItems' => [
                ['label' => db_trans('filters'), 'value' => $filterLabel],
                ['label' => db_trans('projects'), 'value' => number_format($rows->count())],
                ['label' => db_trans('income'), 'value' => number_format((float) $rows->sum('income'), 2)],
                ['label' => db_trans('expense'), 'value' => number_format((float) $rows->sum('expense'), 2)],
                ['label' => db_trans('balance'), 'value' => number_format((float) $rows->sum('balance'), 2)],
            ],
            'rows' => $rows,
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function getProjectsExportRows(array $filters = []): Collection
    {
        return $this->projectRowsQuery($filters)
            ->get()
            ->map(function (Project $project, int $index) {
                $income = (float) ($project->total_income ?? 0);
                $expense = (float) ($project->total_expense ?? 0);
                $balance = $income - $expense;

                return (object) [
                    'sn' => $index + 1,
                    'project' => $project->name ?? '—',
                    'category' => $project->category?->name ?? '—',
                    'status' => $project->status_label ?? $this->projectStatusLabel($project->status ?? null),
                    'budget_amount' => (float) ($project->budget_amount ?? 0),
                    'target_amount' => (float) ($project->target_amount ?? 0),
                    'income' => $income,
                    'expense' => $expense,
                    'balance' => $balance,
                    'start_date' => optional($project->start_date)->format('Y-m-d') ?: '—',
                    'end_date' => optional($project->end_date)->format('Y-m-d') ?: '—',
                ];
            })
            ->values();
    }

    public function getProjectsExportPdfData(array $filters = []): array
    {
        $rows = $this->getProjectsExportRows($filters);
        $filterLabel = $this->projectFinanceFilterLabel($filters);

        return [
            'pageTitle' => db_trans('project_finance'),
            'reportTitle' => db_trans('project_finance_projects_report_title_for') . ' ' . $filterLabel,
            'metaItems' => [
                ['label' => db_trans('filters'), 'value' => $filterLabel],
                ['label' => db_trans('projects'), 'value' => number_format($rows->count())],
                ['label' => db_trans('income'), 'value' => number_format((float) $rows->sum('income'), 2)],
                ['label' => db_trans('expense'), 'value' => number_format((float) $rows->sum('expense'), 2)],
                ['label' => db_trans('balance'), 'value' => number_format((float) $rows->sum('balance'), 2)],
            ],
            'rows' => $rows,
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function getTransactionsExportRows(array $filters = []): Collection
    {
        return $this->transactionQuery($filters)
            ->with(['project.category', 'recorder', 'approver'])
            ->latest('transaction_date')
            ->latest('id')
            ->get()
            ->map(function (ProjectTransaction $transaction, int $index) {
                return (object) [
                    'sn' => $index + 1,
                    'date' => optional($transaction->transaction_date)->format('Y-m-d') ?: '—',
                    'project' => $transaction->project?->name ?? '—',
                    'category' => $transaction->project?->category?->name ?? '—',
                    'type' => $transaction->type_label ?? $this->transactionTypeLabel($transaction->transaction_type ?? null),
                    'amount' => (float) ($transaction->amount ?? 0),
                    'status' => $transaction->status_label ?? $this->transactionStatusLabel($transaction->status ?? null),
                    'payment_method' => $transaction->payment_method_label ?? $this->paymentMethodLabel($transaction->payment_method ?? null),
                    'reference_no' => $transaction->reference_no ?: '—',
                    'recorded_by' => $transaction->recorder?->name ?? '—',
                ];
            })
            ->values();
    }

    public function getTransactionsExportPdfData(array $filters = []): array
    {
        $rows = $this->getTransactionsExportRows($filters);
        $filterLabel = $this->projectFinanceFilterLabel($filters);

        return [
            'pageTitle' => db_trans('project_transactions'),
            'reportTitle' => db_trans('project_finance_transactions_report_title_for') . ' ' . $filterLabel,
            'metaItems' => [
                ['label' => db_trans('filters'), 'value' => $filterLabel],
                ['label' => db_trans('transactions'), 'value' => number_format($rows->count())],
                ['label' => db_trans('income'), 'value' => number_format((float) $rows->filter(fn ($row) => strtolower((string) $row->type) === strtolower(db_trans('income')) || strtolower((string) $row->type) === 'income' || strtolower((string) $row->type) === 'mapato')->sum('amount'), 2)],
                ['label' => db_trans('expense'), 'value' => number_format((float) $rows->filter(fn ($row) => strtolower((string) $row->type) === strtolower(db_trans('expense')) || strtolower((string) $row->type) === 'expense' || strtolower((string) $row->type) === 'matumizi')->sum('amount'), 2)],
                ['label' => db_trans('total'), 'value' => number_format((float) $rows->sum('amount'), 2)],
            ],
            'rows' => $rows,
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    protected function projectRowsQuery(array $filters = []): Builder
    {
        $transactionScope = $this->transactionScopeForProjectSums($filters);

        return $this->projectQuery($filters)
            ->with('category')
            ->withSum([
                'transactions as total_income' => function (Builder $query) use ($transactionScope): void {
                    $transactionScope($query);
                    $query->where('transaction_type', ProjectTransaction::TYPE_INCOME);
                },
            ], 'amount')
            ->withSum([
                'transactions as total_expense' => function (Builder $query) use ($transactionScope): void {
                    $transactionScope($query);
                    $query->where('transaction_type', ProjectTransaction::TYPE_EXPENSE);
                },
            ], 'amount')
            ->latest('id');
    }

    protected function projectFinanceFilterLabel(array $filters = []): string
    {
        $parts = [];

        $year = (int) ($filters['year'] ?? now()->year);

        if (! empty($filters['month'])) {
            $parts[] = Carbon::create($year, (int) $filters['month'], 1)->translatedFormat('F Y');
        } else {
            $parts[] = (string) $year;
        }

        if (! empty($filters['project_category_id'])) {
            $category = ProjectCategory::query()->find((int) $filters['project_category_id']);
            $parts[] = db_trans('project_category') . ': ' . ($category?->name ?? '—');
        } else {
            $parts[] = db_trans('project_category') . ': ' . db_trans('all');
        }

        if (! empty($filters['project_status'])) {
            $parts[] = db_trans('project_status') . ': ' . $this->projectStatusLabel($filters['project_status']);
        } else {
            $parts[] = db_trans('project_status') . ': ' . db_trans('all_statuses');
        }

        return implode(' | ', $parts);
    }

    protected function projectStatusLabel(?string $status): string
    {
        if (blank($status)) {
            return '—';
        }

        return db_trans($status) ?: ucfirst(str_replace('_', ' ', (string) $status));
    }

    protected function transactionTypeLabel(?string $type): string
    {
        if (blank($type)) {
            return '—';
        }

        return db_trans($type) ?: ucfirst(str_replace('_', ' ', (string) $type));
    }

    protected function transactionStatusLabel(?string $status): string
    {
        if (blank($status)) {
            return '—';
        }

        return db_trans($status) ?: ucfirst(str_replace('_', ' ', (string) $status));
    }

    protected function paymentMethodLabel(?string $paymentMethod): string
    {
        if (blank($paymentMethod)) {
            return '—';
        }

        return db_trans($paymentMethod) ?: ucfirst(str_replace('_', ' ', (string) $paymentMethod));
    }

    protected function projectQuery(array $filters = []): Builder
    {
        return Project::query()
            ->when(! empty($filters['project_category_id']), function (Builder $query) use ($filters): void {
                $query->where('project_category_id', (int) $filters['project_category_id']);
            })
            ->when(! empty($filters['project_id']), function (Builder $query) use ($filters): void {
                $query->where('id', (int) $filters['project_id']);
            })
            ->when(! empty($filters['project_status']), function (Builder $query) use ($filters): void {
                $query->where('status', $filters['project_status']);
            });
    }

    protected function transactionQuery(array $filters = []): Builder
    {
        $year = (int) ($filters['year'] ?? now()->year);

        return ProjectTransaction::query()
            ->whereYear('transaction_date', $year)
            ->when(! empty($filters['month']), function (Builder $query) use ($filters): void {
                $query->whereMonth('transaction_date', (int) $filters['month']);
            })
            ->when(! empty($filters['transaction_type']), function (Builder $query) use ($filters): void {
                $query->where('transaction_type', $filters['transaction_type']);
            })
            ->when(! empty($filters['transaction_status']), function (Builder $query) use ($filters): void {
                $query->where('status', $filters['transaction_status']);
            })
            ->when(! empty($filters['project_id']), function (Builder $query) use ($filters): void {
                $query->where('project_id', (int) $filters['project_id']);
            })
            ->when(! empty($filters['project_category_id']), function (Builder $query) use ($filters): void {
                $query->whereHas('project', function (Builder $projectQuery) use ($filters): void {
                    $projectQuery->where('project_category_id', (int) $filters['project_category_id']);
                });
            })
            ->when(! empty($filters['project_status']), function (Builder $query) use ($filters): void {
                $query->whereHas('project', function (Builder $projectQuery) use ($filters): void {
                    $projectQuery->where('status', $filters['project_status']);
                });
            });
    }

    protected function transactionScopeForProjectSums(array $filters = []): callable
    {
        return function (Builder $query) use ($filters): void {
            $query->whereYear('transaction_date', (int) ($filters['year'] ?? now()->year));

            if (! empty($filters['month'])) {
                $query->whereMonth('transaction_date', (int) $filters['month']);
            }

            if (! empty($filters['transaction_status'])) {
                $query->where('status', $filters['transaction_status']);
            }
        };
    }

    protected function topProjects(array $filters = []): Collection
    {
        $transactionScope = $this->transactionScopeForProjectSums($filters);

        return $this->projectQuery($filters)
            ->withSum([
                'transactions as total_income' => function (Builder $query) use ($transactionScope): void {
                    $transactionScope($query);
                    $query->where('transaction_type', ProjectTransaction::TYPE_INCOME);
                },
            ], 'amount')
            ->withSum([
                'transactions as total_expense' => function (Builder $query) use ($transactionScope): void {
                    $transactionScope($query);
                    $query->where('transaction_type', ProjectTransaction::TYPE_EXPENSE);
                },
            ], 'amount')
            ->orderBy('name')
            ->get()
            ->map(function (Project $project) {
                $income = (float) ($project->total_income ?? 0);
                $expense = (float) ($project->total_expense ?? 0);

                return (object) [
                    'id' => $project->id,
                    'name' => $project->name,
                    'status' => $project->status,
                    'income' => $income,
                    'expense' => $expense,
                    'net' => $income - $expense,
                ];
            })
            ->sortByDesc('income')
            ->values();
    }

    protected function categoryBreakdown(array $filters = []): Collection
    {
        return ProjectCategory::query()
            ->orderBy('name')
            ->get()
            ->map(function (ProjectCategory $category) use ($filters) {
                $amount = (float) $this->transactionQuery(array_merge($filters, [
                    'project_category_id' => $category->id,
                ]))
                    ->where('transaction_type', ProjectTransaction::TYPE_INCOME)
                    ->sum('amount');

                return (object) [
                    'name' => $category->name,
                    'amount' => $amount,
                ];
            })
            ->filter(fn ($row) => $row->amount > 0)
            ->values();
    }
}