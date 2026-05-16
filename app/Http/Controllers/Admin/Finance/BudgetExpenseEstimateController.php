<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Exports\BudgetExpenseEstimateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\Budgets\StoreBudgetExpenseEstimateRequest;
use App\Http\Requests\Finance\Budgets\UpdateBudgetExpenseEstimateRequest;
use App\Models\BudgetExpenseEstimate;
use App\Services\Finance\BudgetEstimateService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BudgetExpenseEstimateController extends Controller
{
    public function __construct(protected BudgetEstimateService $service)
    {
    }

    public function index(Request $request)
    {
        return view(
            'admin.finance.budgets.expense.index',
            $this->service->expenseIndexData($request->integer('year', now()->year))
        );
    }

    public function exportPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()?->can('finance.view'), 403);

        $year = $request->integer('year', now()->year);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.budget-expense-estimates',
            $this->service->getExpenseExportPdfData($year),
            'budget-expense-estimates-' . $year
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('finance.view'), 403);

        $year = $request->integer('year', now()->year);

        return Excel::download(
            new BudgetExpenseEstimateExport($this->service, $year),
            'budget-expense-estimates-' . $year . '-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreBudgetExpenseEstimateRequest $request)
    {
        BudgetExpenseEstimate::query()->create($request->validated() + [
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', db_trans('record_created_successfully'));
    }

    public function update(UpdateBudgetExpenseEstimateRequest $request, BudgetExpenseEstimate $budgetExpenseEstimate)
    {
        $budgetExpenseEstimate->update($request->validated() + [
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', db_trans('record_updated_successfully'));
    }

    public function destroy(BudgetExpenseEstimate $budgetExpenseEstimate)
    {
        abort_unless(request()->user()?->can('finance.budgets.expense.delete'), 403);

        $budgetExpenseEstimate->delete();

        return back()->with('success', db_trans('record_deleted_successfully'));
    }
}