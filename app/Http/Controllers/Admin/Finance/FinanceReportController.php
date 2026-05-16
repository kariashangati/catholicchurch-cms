<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\Reports\ContributionComplianceFilterRequest;
use App\Http\Requests\Finance\Reports\FinancialSummaryFilterRequest;
use App\Services\Finance\ContributionComplianceService;
use App\Services\Finance\FinanceReportService;

class FinanceReportController extends Controller
{
    public function __construct(
        protected ContributionComplianceService $complianceService,
        protected FinanceReportService $reportService,
    ) {
    }

    public function compliance(ContributionComplianceFilterRequest $request)
    {
        return view('admin.finance.reports.compliance', $this->complianceService->getPageData($request->user(), $request->validated()));
    }

    public function financialSummary(FinancialSummaryFilterRequest $request)
    {
        return view('admin.finance.reports.financial-summary', $this->reportService->getFinancialSummary($request->user(), (int) ($request->validated()['year'] ?? now()->year)));
    }

    public function incomeBreakdown(FinancialSummaryFilterRequest $request)
    {
        $year = (int) ($request->validated()['year'] ?? now()->year);
        return view('admin.finance.reports.income-breakdown', ['year' => $year, 'rows' => $this->reportService->incomeBreakdown($request->user(), $year)]);
    }

    public function expenseBreakdown(FinancialSummaryFilterRequest $request)
    {
        $year = (int) ($request->validated()['year'] ?? now()->year);
        return view('admin.finance.reports.expense-breakdown', ['year' => $year, 'rows' => $this->reportService->expenseBreakdown($year)]);
    }
}
