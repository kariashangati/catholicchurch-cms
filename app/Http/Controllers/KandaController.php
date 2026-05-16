<?php

namespace App\Http\Controllers;

use App\Exports\KandaBreakdownExport;
use App\Exports\KandaRecentMembersExport;
use App\Http\Requests\ExportKandaBreakdownPdfRequest;
use App\Http\Requests\StoreKandaRequest;
use App\Http\Requests\UpdateKandaRequest;
use App\Models\Kanda;
use App\Services\Kanda\KandaPdfExportService;
use App\Services\Kanda\KandaService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class KandaController extends Controller
{
    public function __construct(
        protected KandaService $kandaService,
        protected KandaPdfExportService $kandaPdfExportService,
    ) {}

    public function index(): View
    {
        return view('admin.kandas.index', $this->kandaService->getIndexData(auth()->user()));
    }

    public function create(): View
    {
        return view('admin.kandas.create');
    }

    public function store(StoreKandaRequest $request): RedirectResponse
    {
        $this->kandaService->store($request->validated());

        return redirect()->route('kandas.index')->with('success', db_trans('kanda_created_successfully'));
    }

    public function show(Request $request, Kanda $kanda): View
    {
        return view('admin.kandas.show', $this->kandaService->getShowData($kanda, auth()->user(), [
            'year' => $request->query('year'),
            'month' => $request->query('month'),
            'jumuiya_id' => $request->query('jumuiya_id'),
        ]));
    }

    public function edit(Kanda $kanda): View
    {
        return view('admin.kandas.edit', compact('kanda'));
    }

    public function update(UpdateKandaRequest $request, Kanda $kanda): RedirectResponse
    {
        $this->kandaService->update($kanda, $request->validated());

        return redirect()->route('kandas.index')->with('success', db_trans('kanda_updated_successfully'));
    }

    public function destroy(Kanda $kanda): RedirectResponse
    {
        $this->kandaService->delete($kanda);

        return redirect()->route('kandas.index')->with('success', db_trans('kanda_deleted_successfully'));
    }

    public function exportBreakdownPdf(ExportKandaBreakdownPdfRequest $request): BinaryFileResponse
    {
        $filters = $request->validatedFilters();

        $pdfPath = $this->kandaPdfExportService->exportBreakdownReport(
            $this->kandaService->getBreakdownPdfData(auth()->user(), $filters['from_date'], $filters['to_date'])
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportSingleBreakdownPdf(ExportKandaBreakdownPdfRequest $request, Kanda $kanda): BinaryFileResponse
    {
        $filters = $request->validatedFilters();

        $pdfPath = $this->kandaPdfExportService->exportKandaBreakdownReport(
            $this->kandaService->getSingleBreakdownPdfData($kanda, auth()->user(), $filters['from_date'], $filters['to_date'])
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        abort_unless(auth()->user()?->can('kandas.view'), 403);

        return Excel::download(
            new KandaBreakdownExport($this->kandaService, auth()->user(), [
                'from_date' => $request->query('from_date'),
                'to_date' => $request->query('to_date'),
            ]),
            'kanda-breakdown-' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    public function exportSingleExcel(Request $request, Kanda $kanda): BinaryFileResponse
    {
        abort_unless(auth()->user()?->can('kandas.view'), 403);

        $this->kandaService->authorizeKandaAccess($kanda, auth()->user());

        return Excel::download(
            new KandaBreakdownExport($this->kandaService, auth()->user(), [
                'from_date' => $request->query('from_date'),
                'to_date' => $request->query('to_date'),
            ], $kanda),
            'kanda-' . $kanda->id . '-breakdown-' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }

    public function exportRecentMembersPdf(Request $request, Kanda $kanda, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(auth()->user()?->can('kandas.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.kanda-recent-members',
            $this->kandaService->getRecentMembersExportPdfData($kanda, auth()->user(), [
                'year' => $request->query('year'),
                'month' => $request->query('month'),
                'jumuiya_id' => $request->query('jumuiya_id'),
            ]),
            'kanda-recent-members'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportRecentMembersExcel(Request $request, Kanda $kanda): BinaryFileResponse
    {
        abort_unless(auth()->user()?->can('kandas.view'), 403);

        return Excel::download(
            new KandaRecentMembersExport($this->kandaService, $kanda, auth()->user(), [
                'year' => $request->query('year'),
                'month' => $request->query('month'),
                'jumuiya_id' => $request->query('jumuiya_id'),
            ]),
            'kanda-recent-members-' . now()->format('Y-m-d-His') . '.xlsx'
        );
    }
}