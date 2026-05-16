<?php

namespace App\Http\Controllers;

use App\Exports\ApostolicGroupMembersExport;
use App\Exports\ApostolicGroupsExport;
use App\Http\Requests\StoreApostolicGroupRequest;
use App\Http\Requests\UpdateApostolicGroupRequest;
use App\Models\ApostolicGroup;
use App\Services\ApostolicGroup\ApostolicGroupService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApostolicGroupController extends Controller
{
    public function __construct(
        protected ApostolicGroupService $service
    ) {
    }

   public function index(Request $request): View
{
    return view('admin.apostolic-groups.index', $this->service->getIndexData(auth()->user(), [
        'year' => $request->query('year'),
        'month' => $request->query('month'),
    ]));
}

    public function show(ApostolicGroup $apostolicGroup): View
    {
        return view('admin.apostolic-groups.show', $this->service->getShowData($apostolicGroup));
    }

    public function exportPdf(PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('apostolic-groups.view'), 403);

        $data = $this->service->getExportPdfData(auth()->user());

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.apostolic-groups-list',
            $data,
            'apostolic-groups-list'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('apostolic-groups.view'), 403);

        return Excel::download(
            new ApostolicGroupsExport($this->service, auth()->user()),
            'apostolic-groups-list-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function exportMembersPdf(
        ApostolicGroup $apostolicGroup,
        PdfReportService $pdfReportService
    ): BinaryFileResponse {
        abort_unless(auth()->user()->can('apostolic-groups.view'), 403);

        $data = $this->service->getMembersExportPdfData($apostolicGroup, auth()->user());

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.apostolic-group-members-list',
            $data,
            'apostolic-group-members-' . $apostolicGroup->id
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportMembersExcel(ApostolicGroup $apostolicGroup): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('apostolic-groups.view'), 403);

        return Excel::download(
            new ApostolicGroupMembersExport($this->service, $apostolicGroup, auth()->user()),
            'apostolic-group-members-' . $apostolicGroup->id . '-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreApostolicGroupRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return back()->with('success', db_trans('apostolic_group_created_successfully'));
    }

    public function update(UpdateApostolicGroupRequest $request, ApostolicGroup $apostolicGroup): RedirectResponse
    {
        $this->service->update($apostolicGroup, $request->validated());

        return back()->with('success', db_trans('apostolic_group_updated_successfully'));
    }

    public function destroy(ApostolicGroup $apostolicGroup): RedirectResponse
    {
        $this->service->delete($apostolicGroup);

        return back()->with('success', db_trans('apostolic_group_deleted_successfully'));
    }

    public function syncRuleMembers(ApostolicGroup $apostolicGroup): RedirectResponse
    {
        $count = $this->service->syncRuleMembers($apostolicGroup, auth()->user());

        return back()->with('success', db_trans('rule_members_synced_successfully') . ' (' . $count . ')');
    }
}