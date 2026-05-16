<?php

namespace App\Http\Controllers;

use App\Exports\JumuiyaFamiliasExport;
use App\Exports\JumuiyaListExport;
use App\Http\Requests\StoreJumuiyaRequest;
use App\Http\Requests\UpdateJumuiyaRequest;
use App\Models\Jumuiya;
use App\Services\Jumuiya\JumuiyaService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class JumuiyaController extends Controller
{
    public function __construct(
        protected JumuiyaService $jumuiyaService
    ) {}

    public function index(): View
    {
        $data = $this->jumuiyaService->getIndexData(auth()->user());

        return view('admin.jumuiyas.index', $data);
    }

    public function show(Request $request, Jumuiya $jumuiya): View
    {
        $data = $this->jumuiyaService->getShowData($jumuiya, auth()->user(), [
            'year' => $request->query('year'),
            'month' => $request->query('month'),
            'familia_id' => $request->query('familia_id'),
        ]);

        return view('admin.jumuiyas.show', $data);
    }

    public function exportPdf(PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('jumuiyas.view'), 403);

        $data = $this->jumuiyaService->getExportPdfData(auth()->user());

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.jumuiya-list',
            $data,
            'jumuiya-list'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('jumuiyas.view'), 403);

        return Excel::download(
            new JumuiyaListExport($this->jumuiyaService, auth()->user()),
            'jumuiya-list-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function exportFamiliasPdf(Request $request, Jumuiya $jumuiya, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('jumuiyas.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.jumuiya-familias',
            $this->jumuiyaService->getFamiliaExportPdfData($jumuiya, auth()->user(), [
                'familia_id' => $request->query('familia_id'),
            ]),
            'jumuiya-familias'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportFamiliasExcel(Request $request, Jumuiya $jumuiya): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('jumuiyas.view'), 403);

        $this->jumuiyaService->ensureUserCanAccessJumuiya(auth()->user(), $jumuiya);

        return Excel::download(
            new JumuiyaFamiliasExport(
                $this->jumuiyaService,
                $jumuiya,
                auth()->user(),
                [
                    'familia_id' => $request->query('familia_id'),
                ]
            ),
            'jumuiya-familias-' . $jumuiya->id . '-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreJumuiyaRequest $request): RedirectResponse
    {
        try {
            $this->jumuiyaService->store($request->validated());

            return redirect()
                ->route('jumuiyas.index')
                ->with('success', db_trans('jumuiya_created_successfully'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('jumuiyas.index')
                ->with('error', db_trans('action_failed_try_again'))
                ->withInput();
        }
    }

    public function edit(Jumuiya $jumuiya): JsonResponse
    {
        abort_unless(auth()->user()->can('jumuiyas.update'), 403);

        $this->jumuiyaService->ensureUserCanAccessJumuiya(auth()->user(), $jumuiya);

        return response()->json([
            'id' => $jumuiya->id,
            'kanda_id' => $jumuiya->kanda_id,
            'name' => $jumuiya->name,
            'comment' => $jumuiya->comment,
            'is_active' => $jumuiya->is_active,
            'image_url' => $jumuiya->image ? asset('storage/' . $jumuiya->image) : null,
        ]);
    }

    public function update(UpdateJumuiyaRequest $request, Jumuiya $jumuiya): RedirectResponse
    {
        $this->jumuiyaService->ensureUserCanAccessJumuiya(auth()->user(), $jumuiya);

        try {
            $this->jumuiyaService->update($jumuiya, $request->validated());

            return redirect()
                ->route('jumuiyas.index')
                ->with('success', db_trans('jumuiya_updated_successfully'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('jumuiyas.index')
                ->with('error', db_trans('action_failed_try_again'))
                ->withInput();
        }
    }

    public function destroy(Jumuiya $jumuiya): RedirectResponse
    {
        abort_unless(auth()->user()->can('jumuiyas.delete'), 403);

        $this->jumuiyaService->ensureUserCanAccessJumuiya(auth()->user(), $jumuiya);

        try {
            $this->jumuiyaService->delete($jumuiya);

            return redirect()
                ->route('jumuiyas.index')
                ->with('success', db_trans('jumuiya_deleted_successfully'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('jumuiyas.index')
                ->with('error', db_trans('action_failed_try_again'));
        }
    }
}