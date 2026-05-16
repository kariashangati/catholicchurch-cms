<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Exports\GiversNonGiversReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\Reports\GiversNonGiversReportRequest;
use App\Services\Finance\GiversNonGiversReportService;
use App\Services\Pdf\PdfReportService;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GiversNonGiversReportController extends Controller
{
    public function __construct(protected GiversNonGiversReportService $service)
    {
    }

    public function index(GiversNonGiversReportRequest $request)
    {
        return view(
            'admin.finance.reports.waliotoa-wasiotoa',
            $this->service->getPageData($request->user(), $request->validated())
        );
    }

    public function exportPdf(
        GiversNonGiversReportRequest $request,
        PdfReportService $pdfReportService
    ): BinaryFileResponse {
        abort_unless($request->user()?->can('finance.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.givers-non-givers-report',
            $this->service->getExportPdfData($request->user(), $request->validated()),
            'waliotoa-wasiotoa'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(GiversNonGiversReportRequest $request): BinaryFileResponse
    {
        abort_unless($request->user()?->can('finance.view'), 403);

        return Excel::download(
            new GiversNonGiversReportExport(
                $this->service,
                $request->user(),
                $request->validated()
            ),
            'waliotoa-wasiotoa-' . now()->format('YmdHis') . '.xlsx'
        );
    }
}