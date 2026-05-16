<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Exports\ContributionDashboardBankExport;
use App\Exports\ContributionDashboardCashExport;
use App\Http\Controllers\Controller;
use App\Services\Finance\ContributionDashboardService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ContributionDashboardController extends Controller
{
    public function __construct(protected ContributionDashboardService $service)
    {
    }

    public function index(Request $request)
    {
        $data = $this->service->getDashboardData($request->user(), $request->all());

        return view('admin.finance.contributions.dashboard.index', $data);
    }

    public function exportCashPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.contribution-dashboard-cash',
            $this->service->getCashExportPdfData($request->user(), $request->all()),
            'cash-contributions'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportCashExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        return Excel::download(
            new ContributionDashboardCashExport($this->service, $request->user(), $request->all()),
            'cash-contributions-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function exportBankPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.contribution-dashboard-bank',
            $this->service->getBankExportPdfData($request->user(), $request->all()),
            'bank-contributions'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportBankExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        return Excel::download(
            new ContributionDashboardBankExport($this->service, $request->user(), $request->all()),
            'bank-contributions-' . now()->format('YmdHis') . '.xlsx'
        );
    }
}