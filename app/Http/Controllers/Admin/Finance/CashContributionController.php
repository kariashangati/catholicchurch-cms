<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Exports\CashContributionExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreCashContributionRequest;
use App\Http\Requests\Finance\UpdateCashContributionRequest;
use App\Models\CashContribution;
use App\Models\ContributionType;
use App\Models\Member;
use App\Services\Finance\ContributionAccessService;
use App\Services\Finance\ContributionBulkService;
use App\Services\Finance\ContributionDashboardService;
use App\Services\Finance\ContributionSmsService;
use App\Services\Pdf\PdfReportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CashContributionController extends Controller
{
    public function __construct(
        protected ContributionAccessService $access,
        protected ContributionSmsService $sms,
        protected ContributionBulkService $bulkService,
        protected ContributionDashboardService $dashboardService,
    ) {
    }

    public function index(Request $request)
    {
        $filters = [
            'year' => $request->input('year', now()->year),
            'month' => $request->input('month'),
            'contribution_type_id' => $request->input('contribution_type_id'),
            'status' => $request->input('status'),
        ];

        $query = CashContribution::query()
            ->with(['member', 'contributionType', 'recorder', 'approver', 'batch']);

        $this->access->applyMemberScope($query, $request->user());

        if (! empty($filters['year'])) {
            $query->whereYear('contribution_date', (int) $filters['year']);
        }

        if (! empty($filters['month'])) {
            $query->whereMonth('contribution_date', (int) $filters['month']);
        }

        if (! empty($filters['contribution_type_id'])) {
            $query->where('contribution_type_id', (int) $filters['contribution_type_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', CashContribution::normalizeStatus($filters['status']));
        }

        $items = $query
            ->latest('contribution_date')
            ->latest('id')
            ->get();

        $members = Member::query()
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->orderBy('last_name')
            ->get();

        $types = ContributionType::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $statuses = CashContribution::availableStatuses();

        return view('admin.finance.contributions.cash.index', compact(
            'items',
            'members',
            'types',
            'statuses',
            'filters'
        ));
    }

    public function exportPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.cash-contributions',
            $this->dashboardService->getCashExportPdfData($request->user(), $request->all()),
            'cash-contributions'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        return Excel::download(
            new CashContributionExport($this->dashboardService, $request->user(), $request->all()),
            'cash-contributions-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreCashContributionRequest $request)
    {
        $data = $request->validated();
        $sendSms = (bool) ($data['send_sms'] ?? false);
        unset($data['send_sms']);

        $status = CashContribution::normalizeStatus($data['status'] ?? CashContribution::STATUS_APPROVED);

        $contribution = CashContribution::create($data + [
            'status' => $status,
            'recorded_by' => $request->user()->id,
            'approved_by' => $status === CashContribution::STATUS_APPROVED ? $request->user()->id : null,
            'approved_at' => $status === CashContribution::STATUS_APPROVED ? now() : null,
        ]);

        if ($sendSms) {
            $contribution->load(['member', 'contributionType']);

            if ($contribution->member) {
                $this->sms->sendAcknowledgement(
                    $contribution->member,
                    $this->sms->cashMessage($contribution->member, $contribution),
                    $request->user()
                );
            }
        }

        return redirect()
            ->route('finance.contributions.cash.index')
            ->with('success', db_trans('cash_contribution_created_successfully'));
    }

    public function update(UpdateCashContributionRequest $request, CashContribution $cashContribution)
    {
        $this->bulkService->assertContributionIsNotLockedByBatch($cashContribution);

        $data = $request->validated();
        $sendSms = (bool) ($data['send_sms'] ?? false);
        unset($data['send_sms']);

        $status = CashContribution::normalizeStatus($data['status'] ?? $cashContribution->status);

        $cashContribution->update($data + [
            'status' => $status,
            'approved_by' => $status === CashContribution::STATUS_APPROVED ? $request->user()->id : null,
            'approved_at' => $status === CashContribution::STATUS_APPROVED ? ($cashContribution->approved_at ?: now()) : null,
        ]);

        if ($sendSms) {
            $cashContribution->load(['member', 'contributionType']);

            if ($cashContribution->member) {
                $this->sms->sendAcknowledgement(
                    $cashContribution->member,
                    $this->sms->cashMessage($cashContribution->member, $cashContribution),
                    $request->user()
                );
            }
        }

        return redirect()
            ->route('finance.contributions.cash.index')
            ->with('success', db_trans('cash_contribution_updated_successfully'));
    }

    public function destroy(CashContribution $cashContribution)
    {
        $this->bulkService->assertContributionIsNotLockedByBatch($cashContribution);

        $cashContribution->delete();

        return redirect()
            ->route('finance.contributions.cash.index')
            ->with('success', db_trans('cash_contribution_deleted_successfully'));
    }
}