<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Exports\FinanceActivityExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\FinanceFilterRequest;
use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Models\MassType;
use App\Models\OfferingType;
use App\Services\Finance\FinanceDashboardService;
use App\Services\Pdf\PdfReportService;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FinanceDashboardController extends Controller
{
    public function __construct(protected FinanceDashboardService $service)
    {
    }

    public function index(FinanceFilterRequest $request)
    {
        $data = $this->service->getDashboardData($request->user(), $request->validated());

        $data['offeringTypes'] = OfferingType::where('is_active', true)->orderBy('name')->get();
        $data['massTypes'] = MassType::where('is_active', true)->orderBy('name')->get();
        $data['kandas'] = Kanda::orderBy('name')->get();
        $data['jumuiyas'] = Jumuiya::orderBy('name')->get();

        return view('admin.finance.dashboard', $data);
    }

    public function exportActivityPdf(FinanceFilterRequest $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.finance-activity',
            $this->service->getUnifiedActivityExportPdfData($request->user(), $request->validated()),
            'finance-activity'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportActivityExcel(FinanceFilterRequest $request): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        return Excel::download(
            new FinanceActivityExport($this->service, $request->user(), $request->validated()),
            'finance-activity-' . now()->format('YmdHis') . '.xlsx'
        );
    }
}