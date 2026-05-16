<?php

namespace App\Services\Finance;

use App\Models\BudgetExpenseEstimate;
use App\Models\BudgetIncomeEstimate;
use App\Models\ContributionType;
use App\Models\OfferingType;
use Illuminate\Support\Collection;

class BudgetEstimateService
{
    public function dashboardData(int $year): array
    {
        $income = (float) BudgetIncomeEstimate::query()->where('budget_year', $year)->sum('amount');
        $expense = (float) BudgetExpenseEstimate::query()->where('budget_year', $year)->sum('amount');

        return [
            'year' => $year,
            'income_total' => $income,
            'expense_total' => $expense,
            'net_total' => $income - $expense,
        ];
    }

    public function incomeIndexData(int $year): array
    {
        return [
            'year' => $year,
            'records' => BudgetIncomeEstimate::query()
                ->where('budget_year', $year)
                ->latest()
                ->get(),
            'total' => (float) BudgetIncomeEstimate::query()
                ->where('budget_year', $year)
                ->sum('amount'),
            'sourceOptions' => BudgetIncomeEstimate::sourceOptions(),
            'groupOptions' => BudgetIncomeEstimate::groupOptions(),
            'suggestedCategories' => collect(['Zaka', 'Sadaka Kuu'])
                ->merge(OfferingType::query()->where('is_active', true)->pluck('name'))
                ->merge(ContributionType::query()->where('is_active', true)->pluck('name'))
                ->filter()
                ->unique()
                ->values(),
        ];
    }

    public function expenseIndexData(int $year): array
    {
        return [
            'year' => $year,
            'records' => BudgetExpenseEstimate::query()
                ->where('budget_year', $year)
                ->latest()
                ->get(),
            'total' => (float) BudgetExpenseEstimate::query()
                ->where('budget_year', $year)
                ->sum('amount'),
            'groupOptions' => BudgetExpenseEstimate::groupOptions(),
            'suggestedCategories' => collect([
                'Matengenezo',
                'Huduma za Umeme na Maji',
                'Misaada',
                'Usafiri',
                'Uendeshaji wa Ofisi',
                'Huduma ya Vijana',
            ])->unique()->values(),
        ];
    }

    public function getIncomeExportRows(int $year): Collection
    {
        return BudgetIncomeEstimate::query()
            ->where('budget_year', $year)
            ->latest()
            ->get()
            ->map(function (BudgetIncomeEstimate $record, int $index) {
                return (object) [
                    'sn' => $index + 1,
                    'source_type' => $record->source_label,
                    'category' => $record->category_name,
                    'group' => $record->group_label,
                    'amount' => (float) $record->amount,
                    'year' => (int) $record->budget_year,
                ];
            })
            ->values();
    }

    public function getExpenseExportRows(int $year): Collection
    {
        return BudgetExpenseEstimate::query()
            ->where('budget_year', $year)
            ->latest()
            ->get()
            ->map(function (BudgetExpenseEstimate $record, int $index) {
                return (object) [
                    'sn' => $index + 1,
                    'category' => $record->category_name,
                    'group' => $record->group_label,
                    'amount' => (float) $record->amount,
                    'year' => (int) $record->budget_year,
                ];
            })
            ->values();
    }

    public function getIncomeExportPdfData(int $year): array
    {
        $rows = $this->getIncomeExportRows($year);

        return [
            'pageTitle' => db_trans('income_budget_estimates'),
            'reportTitle' => db_trans('income_budget_estimates_report_title_for') . ' ' . $year,
            'metaItems' => [
                [
                    'label' => db_trans('year'),
                    'value' => $year,
                ],
                [
                    'label' => db_trans('records'),
                    'value' => number_format($rows->count()),
                ],
                [
                    'label' => db_trans('grand_total'),
                    'value' => number_format((float) $rows->sum('amount'), 2),
                ],
                [
                    'label' => db_trans('generated_on'),
                    'value' => now()->translatedFormat('d F Y'),
                ],
            ],
            'rows' => $rows,
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function getExpenseExportPdfData(int $year): array
    {
        $rows = $this->getExpenseExportRows($year);

        return [
            'pageTitle' => db_trans('expense_budget_estimates'),
            'reportTitle' => db_trans('expense_budget_estimates_report_title_for') . ' ' . $year,
            'metaItems' => [
                [
                    'label' => db_trans('year'),
                    'value' => $year,
                ],
                [
                    'label' => db_trans('records'),
                    'value' => number_format($rows->count()),
                ],
                [
                    'label' => db_trans('grand_total'),
                    'value' => number_format((float) $rows->sum('amount'), 2),
                ],
                [
                    'label' => db_trans('generated_on'),
                    'value' => now()->translatedFormat('d F Y'),
                ],
            ],
            'rows' => $rows,
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }
}