<?php

namespace App\Http\Controllers;

use App\Exports\JumuiyaMembersExport;
use App\Models\Jumuiya;
use App\Services\Jumuiya\JumuiyaService;
use App\Services\Pdf\PdfReportService;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JumuiyaMemberController extends Controller
{
    public function __construct(
        protected JumuiyaService $jumuiyaService
    ) {}

    public function index(Jumuiya $jumuiya): View
    {
        $data = $this->jumuiyaService->getMembersData($jumuiya, auth()->user());

        return view('admin.jumuiyas.members', $data);
    }

    public function exportPdf(Jumuiya $jumuiya, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('jumuiyas.members.view'), 403);

        $data = $this->jumuiyaService->getMembersExportPdfData($jumuiya, auth()->user());

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.jumuiya-members-list',
            $data,
            'jumuiya-members-list'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(Jumuiya $jumuiya): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('jumuiyas.members.view'), 403);

        return Excel::download(
            new JumuiyaMembersExport($this->jumuiyaService, $jumuiya, auth()->user()),
            'jumuiya-members-' . $jumuiya->id . '-' . now()->format('YmdHis') . '.xlsx'
        );
    }
}