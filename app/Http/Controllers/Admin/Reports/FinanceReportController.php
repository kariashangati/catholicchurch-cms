<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Exports\FinanceSummaryReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\FinanceReportsFilterRequest;
use App\Services\Pdf\PdfReportService;
use App\Services\Reports\FinanceReportsService;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinanceReportController extends Controller
{
    public function __construct(protected FinanceReportsService $service)
    {
    }

    public function index(FinanceReportsFilterRequest $request): View
    {
        return $this->financialSummary($request);
    }

    public function financialSummary(FinanceReportsFilterRequest $request): View
    {
        abort_unless($request->user()?->can('finance.reports.summary.view'), 403);

        return view('admin.reports.finance.financial-summary', $this->service->summary(
            $request->user(),
            $request->validated()
        ));
    }

    public function exportFinancialSummaryPdf(
        FinanceReportsFilterRequest $request,
        PdfReportService $pdfReportService
    ): BinaryFileResponse {
        abort_unless($request->user()?->can('finance.reports.summary.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.financial-summary-report',
            $this->service->financialSummaryExportData(
                $request->user(),
                $request->validated()
            ),
            'financial-summary'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportFinancialSummaryExcel(FinanceReportsFilterRequest $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('finance.reports.summary.view'), 403);

        return Excel::download(
            new FinanceSummaryReportExport(
                $this->service,
                $request->user(),
                $request->validated()
            ),
            'financial-summary-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function incomeBreakdown(FinanceReportsFilterRequest $request): View
    {
        return $this->financialSummary($request);
    }

    public function expenseBreakdown(FinanceReportsFilterRequest $request): View
    {
        return $this->financialSummary($request);
    }

    public function compliance(FinanceReportsFilterRequest $request): View
    {
        abort_unless($request->user()?->can('finance.reports.compliance.view'), 403);

        return view('admin.reports.finance.compliance', $this->service->compliance(
            $request->user(),
            $request->validated()
        ));
    }

    public function complianceCsv(FinanceReportsFilterRequest $request): StreamedResponse
    {
        abort_unless($request->user()?->can('finance.reports.compliance.export'), 403);

        $data = $this->service->compliance($request->user(), $request->validated());
        $csv = $this->service->exportComplianceCsv($data['rows']->all());

        return response()->streamDownload(function () use ($csv): void {
            echo $csv;
        }, 'monthly-contribution-compliance.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}