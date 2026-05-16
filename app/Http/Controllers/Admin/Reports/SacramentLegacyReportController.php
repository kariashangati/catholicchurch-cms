<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Exports\SacramentLegacyReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\SacramentLegacyReportRequest;
use App\Services\Pdf\PdfReportService;
use App\Services\Reports\SacramentLegacyReportsService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SacramentLegacyReportController extends Controller
{
    public function __construct(protected SacramentLegacyReportsService $service)
    {
    }

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('reports.sacraments.view'), 403);

        return view('admin.reports.sacraments.index', $this->service->indexData($request->user()));
    }

    public function generate(SacramentLegacyReportRequest $request): View
    {
        abort_unless($request->user()?->can('reports.sacraments.view'), 403);

        $report = $this->service->generate($request->user(), $request->validated());

        return view($report['view'], $report['data']);
    }

    public function exportPdf(
        SacramentLegacyReportRequest $request,
        PdfReportService $pdfReportService
    ): BinaryFileResponse {
        abort_unless($request->user()?->can('reports.sacraments.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.sacrament-legacy-report',
            $this->service->exportData($request->user(), $request->validated()),
            'sacrament-report'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(SacramentLegacyReportRequest $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('reports.sacraments.view'), 403);

        return Excel::download(
            new SacramentLegacyReportExport(
                $this->service,
                $request->user(),
                $request->validated()
            ),
            'sacrament-report-' . now()->format('YmdHis') . '.xlsx'
        );
    }
}