<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Exports\BudgetIncomeEstimateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\Budgets\StoreBudgetIncomeEstimateRequest;
use App\Http\Requests\Finance\Budgets\UpdateBudgetIncomeEstimateRequest;
use App\Models\BudgetIncomeEstimate;
use App\Services\Finance\BudgetEstimateService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BudgetIncomeEstimateController extends Controller
{
    public function __construct(protected BudgetEstimateService $service)
    {
    }

    public function index(Request $request)
    {
        return view(
            'admin.finance.budgets.income.index',
            $this->service->incomeIndexData($request->integer('year', now()->year))
        );
    }

    public function exportPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()?->can('finance.view'), 403);

        $year = $request->integer('year', now()->year);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.budget-income-estimates',
            $this->service->getIncomeExportPdfData($year),
            'budget-income-estimates-' . $year
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('finance.view'), 403);

        $year = $request->integer('year', now()->year);

        return Excel::download(
            new BudgetIncomeEstimateExport($this->service, $year),
            'budget-income-estimates-' . $year . '-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreBudgetIncomeEstimateRequest $request)
    {
        BudgetIncomeEstimate::query()->create($request->validated() + [
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', db_trans('record_created_successfully'));
    }

    public function update(UpdateBudgetIncomeEstimateRequest $request, BudgetIncomeEstimate $budgetIncomeEstimate)
    {
        $budgetIncomeEstimate->update($request->validated() + [
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', db_trans('record_updated_successfully'));
    }

    public function destroy(BudgetIncomeEstimate $budgetIncomeEstimate)
    {
        abort_unless(request()->user()?->can('finance.budgets.income.delete'), 403);

        $budgetIncomeEstimate->delete();

        return back()->with('success', db_trans('record_deleted_successfully'));
    }
}