<?php

namespace App\Http\Controllers;

use App\Exports\MemberListExport;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Services\Member\MemberService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MemberController extends Controller
{
    public function __construct(
        protected MemberService $memberService
    ) {}

    public function index(): View
    {
        $data = $this->memberService->getIndexData(auth()->user(), request()->all());

        return view('admin.members.index', $data);
    }

    public function create(): RedirectResponse
    {
        return redirect()
            ->route('members.index', ['open' => 'create'])
            ->with('success', db_trans('use_add_member_modal_to_continue'));
    }

    public function store(StoreMemberRequest $request): RedirectResponse
    {
        try {
            $this->memberService->store($request->validated(), auth()->user());

            return redirect()
                ->route('members.index')
                ->with('success', db_trans('member_created_successfully'));
        } catch (\Throwable $e) {
            Log::error('Failed to create member', ['message' => $e->getMessage()]);

            return back()->withInput()->with('error', db_trans('action_failed_try_again'));
        }
    }

    public function show(Member $member): View
    {
        $data = $this->memberService->getShowData($member, auth()->user());

        return view('admin.members.show', $data);
    }

    public function edit(Member $member): RedirectResponse
    {
        return redirect()->route('members.index', [
            'open' => 'edit',
            'member' => $member->id,
        ]);
    }

    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        try {
            $this->memberService->update($member, $request->validated(), auth()->user());

            return redirect()->route('members.index')->with('success', db_trans('member_updated_successfully'));
        } catch (\Throwable $e) {
            Log::error('Failed to update member', [
                'member_id' => $member->id,
                'message' => $e->getMessage(),
            ]);

            return back()->withInput()->with('error', db_trans('action_failed_try_again'));
        }
    }

    public function destroy(Member $member): RedirectResponse
    {
        try {
            $this->memberService->delete($member, auth()->user());

            return redirect()->route('members.index')->with('success', db_trans('member_deleted_successfully'));
        } catch (\Throwable $e) {
            Log::error('Failed to delete member', [
                'member_id' => $member->id,
                'message' => $e->getMessage(),
            ]);

            return back()->with('error', db_trans('action_failed_try_again'));
        }
    }

    public function exportPdf(PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('members.view'), 403);

        $data = $this->memberService->getExportPdfData(auth()->user(), request()->all());

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.member-list',
            $data,
            'member-list'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportProfilePdf(Member $member, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('members.view'), 403);

        $data = $this->memberService->getProfilePdfData($member, auth()->user());

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.member-profile',
            $data,
            'member-profile'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(): BinaryFileResponse
    {
        abort_unless(auth()->user()->can('members.view'), 403);

        return Excel::download(
            new MemberListExport($this->memberService, auth()->user(), request()->all()),
            'member-list-' . now()->format('YmdHis') . '.xlsx'
        );
    }
}