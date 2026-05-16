<?php

namespace App\Http\Controllers;

use App\Exports\KandaFinancialReportExport;
use App\Models\Kanda;
use App\Services\Kanda\KandaReportService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KandaReportController extends Controller
{
    public function __construct(
        protected KandaReportService $kandaReportService
    ) {
    }

    public function index(Request $request): View
    {
        $data = $this->kandaReportService->getIndexData(
            $request->user(),
            $request->only(['year', 'month'])
        );

        return view('admin.kanda-reports.index', $data);
    }

    public function show(Request $request, Kanda $kanda): View
    {
        $data = $this->kandaReportService->getShowData(
            $kanda,
            $request->user(),
            $request->only(['year', 'month'])
        );

        return view('admin.kanda-reports.show', $data);
    }

    public function exportIndexPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()?->can('kanda-reports.view'), 403);

        $data = $this->kandaReportService->getIndexExportData(
            $request->user(),
            $request->only(['year', 'month'])
        );

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.kanda-financial-report',
            $data,
            'kanda-financial-reports'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportIndexExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('kanda-reports.view'), 403);

        $data = $this->kandaReportService->getIndexExportData(
            $request->user(),
            $request->only(['year', 'month'])
        );

        return Excel::download(
            new KandaFinancialReportExport(
                $this->kandaReportService->indexExportRows($data),
                db_trans('kanda_financial_reports')
            ),
            'kanda-financial-reports-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function exportShowPdf(Request $request, Kanda $kanda, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()?->can('kanda-reports.view'), 403);

        $data = $this->kandaReportService->getShowExportData(
            $kanda,
            $request->user(),
            $request->only(['year', 'month'])
        );

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.kanda-financial-report',
            $data,
            'kanda-financial-report-' . $kanda->id
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportShowExcel(Request $request, Kanda $kanda): BinaryFileResponse
    {
        abort_unless($request->user()?->can('kanda-reports.view'), 403);

        $data = $this->kandaReportService->getShowExportData(
            $kanda,
            $request->user(),
            $request->only(['year', 'month'])
        );

        return Excel::download(
            new KandaFinancialReportExport(
                $this->kandaReportService->showExportRows($data),
                db_trans('kanda_financial_report')
            ),
            'kanda-financial-report-' . $kanda->id . '-' . now()->format('YmdHis') . '.xlsx'
        );
    }
}