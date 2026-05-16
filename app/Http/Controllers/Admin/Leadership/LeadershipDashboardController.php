<?php

namespace App\Http\Controllers\Admin\Leadership;

use App\Exports\LeadershipReportExport;
use App\Http\Controllers\Controller;
use App\Services\Leadership\LeadershipService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LeadershipDashboardController extends Controller
{
    public function __construct(protected LeadershipService $service)
    {
    }

    public function index(Request $request)
    {
        return view('admin.leadership.dashboard', $this->service->dashboardData());
    }

    public function exportPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(
            $request->user()?->can('leadership.assignments.view') || $request->user()?->can('leadership.positions.view'),
            403
        );

        $data = $this->service->dashboardExportData();

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.leadership-report',
            $data,
            'leadership-dashboard'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        abort_unless(
            $request->user()?->can('leadership.assignments.view') || $request->user()?->can('leadership.positions.view'),
            403
        );

        $data = $this->service->dashboardExportData();

        return Excel::download(
            new LeadershipReportExport(
                $this->service->dashboardExportSheets($data),
                db_trans('leadership_dashboard')
            ),
            'leadership-dashboard-' . now()->format('YmdHis') . '.xlsx'
        );
    }
}
