<?php

namespace App\Http\Controllers;

use App\Exports\JumuiyaFinancialReportExport;
use App\Models\Jumuiya;
use App\Services\Jumuiya\JumuiyaReportService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JumuiyaReportController extends Controller
{
    public function __construct(
        protected JumuiyaReportService $jumuiyaReportService
    ) {
    }

    public function index(Request $request): View
    {
        $data = $this->jumuiyaReportService->getIndexData(
            $request->user(),
            $request->only(['year', 'month'])
        );

        return view('admin.jumuiya-reports.index', $data);
    }

    public function show(Request $request, Jumuiya $jumuiya): View
    {
        $data = $this->jumuiyaReportService->getShowData(
            $jumuiya,
            $request->user(),
            $request->only(['year', 'month'])
        );

        return view('admin.jumuiya-reports.show', $data);
    }

    public function exportIndexPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()?->can('jumuiya-reports.view'), 403);

        $data = $this->jumuiyaReportService->getIndexExportData(
            $request->user(),
            $request->only(['year', 'month'])
        );

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.jumuiya-financial-report',
            $data,
            'jumuiya-financial-reports'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportIndexExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('jumuiya-reports.view'), 403);

        $data = $this->jumuiyaReportService->getIndexExportData(
            $request->user(),
            $request->only(['year', 'month'])
        );

        return Excel::download(
            new JumuiyaFinancialReportExport([
                db_trans('jumuiya_financial_reports') => $this->jumuiyaReportService->indexExportRows($data),
            ]),
            'jumuiya-financial-reports-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function exportShowPdf(Request $request, Jumuiya $jumuiya, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()?->can('jumuiya-reports.view'), 403);

        $data = $this->jumuiyaReportService->getShowExportData(
            $jumuiya,
            $request->user(),
            $request->only(['year', 'month'])
        );

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.jumuiya-financial-report',
            $data,
            'jumuiya-financial-report-' . $jumuiya->id
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportShowExcel(Request $request, Jumuiya $jumuiya): BinaryFileResponse
    {
        abort_unless($request->user()?->can('jumuiya-reports.view'), 403);

        $data = $this->jumuiyaReportService->getShowExportData(
            $jumuiya,
            $request->user(),
            $request->only(['year', 'month'])
        );

        return Excel::download(
            new JumuiyaFinancialReportExport([
                db_trans('familias') => $this->jumuiyaReportService->showFamilyExportRows($data),
                db_trans('recent_transactions') => $this->jumuiyaReportService->showTransactionsExportRows($data),
            ]),
            'jumuiya-financial-report-' . $jumuiya->id . '-' . now()->format('YmdHis') . '.xlsx'
        );
    }
}