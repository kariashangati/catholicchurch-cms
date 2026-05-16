<?php

namespace App\Http\Controllers;

use App\Exports\FamiliaMembersExport;
use App\Http\Requests\StoreFamiliaMemberRequest;
use App\Http\Requests\UpdateFamiliaMemberRequest;
use App\Models\Familia;
use App\Models\Member;
use App\Services\Familia\FamiliaService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\RedirectResponse;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class FamiliaMemberController extends Controller
{
    public function __construct(
        protected FamiliaService $service
    ) {}

    public function store(StoreFamiliaMemberRequest $request, Familia $familia): RedirectResponse
    {
        $this->service->addMember($familia, $request->validated(), auth()->user());

        return back()->with('success', db_trans('family_member_created_successfully'));
    }

    public function update(UpdateFamiliaMemberRequest $request, Familia $familia, Member $member): RedirectResponse
    {
        $this->service->updateMember($familia, $member, $request->validated(), auth()->user());

        return back()->with('success', db_trans('family_member_updated_successfully'));
    }

    public function destroy(Familia $familia, Member $member): RedirectResponse
    {
        try {
            $this->service->deleteMember($familia, $member, auth()->user());

            return back()->with('success', db_trans('family_member_deleted_successfully'));
        } catch (HttpExceptionInterface $exception) {
            if ($exception->getStatusCode() !== 422) {
                throw $exception;
            }

            return back()->withErrors([$exception->getMessage() ?: db_trans('please_fix_the_following_errors')]);
        }
    }


    public function exportPdf(Familia $familia, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('familias.view'), 403);

        $data = $this->service->getMembersExportPdfData($familia, auth()->user());

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.familia-members-list',
            $data,
            'familia-members-list'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(Familia $familia): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('familias.view'), 403);

        return Excel::download(
            new FamiliaMembersExport($this->service, $familia, auth()->user()),
            'familia-members-' . now()->format('YmdHis') . '.xlsx'
        );
    }
}