<?php

namespace App\Http\Controllers;

use App\Exports\FamiliaListExport;
use App\Http\Requests\StoreFamiliaRequest;
use App\Http\Requests\UpdateFamiliaRequest;
use App\Models\Familia;
use App\Services\Familia\FamiliaService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class FamiliaController extends Controller
{
    public function __construct(
        protected FamiliaService $service
    ) {
    }

    public function index(): View
    {
        return view('admin.familias.index', $this->service->getIndexData(auth()->user()));
    }

    public function show(Familia $familia): View
    {
        return view('admin.familias.show', $this->service->getShowData($familia, auth()->user()));
    }

    public function exportPdf(PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('familias.view'), 403);

        $data = $this->service->getExportPdfData(auth()->user());

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.familia-list',
            $data,
            'familia-list'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('familias.view'), 403);

        return Excel::download(
            new FamiliaListExport($this->service, auth()->user()),
            'familia-list-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreFamiliaRequest $request): RedirectResponse
    {
        $this->service->store($request->validated(), auth()->user());

        return back()->with('success', db_trans('family_created_successfully'));
    }

    public function update(UpdateFamiliaRequest $request, Familia $familia): RedirectResponse
    {
        $this->service->update($familia, $request->validated(), auth()->user());

        return back()->with('success', db_trans('family_updated_successfully'));
    }

    public function destroy(Familia $familia): RedirectResponse
    {
        try {
            $this->service->delete($familia, auth()->user());

            return redirect()->route('familias.index')->with('success', db_trans('family_deleted_successfully'));
        } catch (HttpExceptionInterface $exception) {
            if ($exception->getStatusCode() !== 422) {
                throw $exception;
            }

            return back()->withErrors([$exception->getMessage() ?: db_trans('please_fix_the_following_errors')]);
        }
    }
}