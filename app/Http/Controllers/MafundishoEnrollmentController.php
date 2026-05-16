<?php

namespace App\Http\Controllers;

use App\Exports\MafundishoTypeExport;
use App\Http\Requests\StoreMafundishoEnrollmentRequest;
use App\Http\Requests\UpdateMafundishoEnrollmentRequest;
use App\Models\MafundishoEnrollment;
use App\Services\Mafundisho\MafundishoEnrollmentService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MafundishoEnrollmentController extends Controller
{
    public function __construct(
        protected MafundishoEnrollmentService $service
    ) {
    }

    public function summary(): View
    {
        return view('admin.mafundisho.summary', [
            'pageTitle' => db_trans('teaching_enrollments'),
            ...$this->service->getSummaryData(auth()->user()),
        ]);
    }

    public function index(Request $request, string $type, int $year = null): View
    {
        $year = $year ?: (int) now()->year;

        return view('admin.mafundisho.index', [
            'pageTitle' => db_trans('teaching_students'),
            ...$this->service->getTypeIndexData(
                auth()->user(),
                $type,
                $year,
                $request->string('status')->toString() ?: null,
                $request->filled('month') ? (int) $request->get('month') : null
            ),
        ]);
    }

    public function exportTypePdf(
        Request $request,
        string $type,
        int $year,
        PdfReportService $pdfReportService
    ): BinaryFileResponse {
        abort_unless(auth()->user()->can('mafundisho.view'), 403);

        $status = $request->string('status')->toString() ?: null;
        $month = $request->filled('month') ? (int) $request->get('month') : null;

        $data = $this->service->getTypeExportPdfData(
            auth()->user(),
            $type,
            $year,
            $status,
            $month
        );

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.mafundisho-type',
            $data,
            'mafundisho-' . $type . '-' . $year
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportTypeExcel(Request $request, string $type, int $year): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('mafundisho.view'), 403);

        $status = $request->string('status')->toString() ?: null;
        $month = $request->filled('month') ? (int) $request->get('month') : null;

        return Excel::download(
            new MafundishoTypeExport(
                $this->service,
                auth()->user(),
                $type,
                $year,
                $status,
                $month
            ),
            'mafundisho-' . $type . '-' . $year . '-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreMafundishoEnrollmentRequest $request): RedirectResponse
    {
        $this->service->store($request->validated(), auth()->user());

        return back()->with('success', db_trans('teaching_enrollment_created_successfully'));
    }

    public function update(UpdateMafundishoEnrollmentRequest $request, MafundishoEnrollment $mafundisho_enrollment): RedirectResponse
    {
        $this->service->update($mafundisho_enrollment, $request->validated(), auth()->user());

        return back()->with('success', db_trans('teaching_enrollment_updated_successfully'));
    }

    public function destroy(MafundishoEnrollment $mafundisho_enrollment): RedirectResponse
    {
        $this->service->delete($mafundisho_enrollment, auth()->user());

        return back()->with('success', db_trans('teaching_enrollment_deleted_successfully'));
    }
}