<?php

namespace App\Http\Controllers\Admin;

use App\Exports\JumuiyaSacramentMembersExport;
use App\Exports\JumuiyaSacramentSummaryExport;
use App\Exports\KandaSacramentMembersExport;
use App\Exports\KandaSacramentSummaryExport;
use App\Http\Controllers\Controller;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Services\Pdf\PdfReportService;
use App\Services\Sacrament\SacramentReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SacramentReportController extends Controller
{
    public function __construct(
        protected SacramentReportService $service
    ) {
    }

    public function index(Request $request): View
    {
        return view('admin.sacraments.index', $this->service->getDashboardData(auth()->user(), $request->all()));
    }

    public function kandas(Request $request): View
    {
        return view('admin.sacraments.kandas', $this->service->getKandaIndexData(auth()->user(), $request->all()));
    }

    public function showKanda(Request $request, Kanda $kanda): View
    {
        return view('admin.sacraments.kanda-show', $this->service->getKandaShowData($kanda, auth()->user(), $request->all()));
    }

    public function jumuiyas(Request $request): View
    {
        return view('admin.sacraments.jumuiyas', $this->service->getJumuiyaIndexData(auth()->user(), $request->all()));
    }

    public function showJumuiya(Request $request, Jumuiya $jumuiya): View
    {
        return view('admin.sacraments.jumuiya-show', $this->service->getJumuiyaShowData($jumuiya, auth()->user(), $request->all()));
    }

    public function exportKandasPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('sacraments.kanda.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.kanda-sacrament-summary',
            $this->service->getKandaExportPdfData(auth()->user(), $request->all()),
            'kanda-sacrament-summary'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportKandasExcel(Request $request): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('sacraments.kanda.view'), 403);

        return Excel::download(
            new KandaSacramentSummaryExport($this->service, auth()->user(), $request->all()),
            'kanda-sacrament-summary-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function exportKandaMembersPdf(Request $request, Kanda $kanda, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('sacraments.kanda.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.kanda-sacrament-members',
            $this->service->getKandaMemberExportPdfData($kanda, auth()->user(), $request->all()),
            'kanda-sacrament-members'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportKandaMembersExcel(Request $request, Kanda $kanda): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('sacraments.kanda.view'), 403);

        return Excel::download(
            new KandaSacramentMembersExport($this->service, $kanda, auth()->user(), $request->all()),
            'kanda-sacrament-members-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function exportJumuiyasPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('sacraments.jumuiya.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.jumuiya-sacrament-summary',
            $this->service->getJumuiyaExportPdfData(auth()->user(), $request->all()),
            'jumuiya-sacrament-summary'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportJumuiyasExcel(Request $request): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('sacraments.jumuiya.view'), 403);

        return Excel::download(
            new JumuiyaSacramentSummaryExport($this->service, auth()->user(), $request->all()),
            'jumuiya-sacrament-summary-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function exportJumuiyaMembersPdf(Request $request, Jumuiya $jumuiya, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('sacraments.jumuiya.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.jumuiya-sacrament-members',
            $this->service->getJumuiyaMemberExportPdfData($jumuiya, auth()->user(), $request->all()),
            'jumuiya-sacrament-members'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportJumuiyaMembersExcel(Request $request, Jumuiya $jumuiya): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('sacraments.jumuiya.view'), 403);

        return Excel::download(
            new JumuiyaSacramentMembersExport($this->service, $jumuiya, auth()->user(), $request->all()),
            'jumuiya-sacrament-members-' . now()->format('YmdHis') . '.xlsx'
        );
    }
}