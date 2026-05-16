<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Exports\BankContributionExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreBankContributionRequest;
use App\Http\Requests\Finance\UpdateBankContributionRequest;
use App\Models\BankAccount;
use App\Models\BankContribution;
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

class BankContributionController extends Controller
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

        $query = BankContribution::query()
            ->with(['member', 'contributionType', 'bankAccount', 'recorder', 'approver', 'batch']);

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
            $query->where('status', BankContribution::normalizeStatus($filters['status']));
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

        $bankAccounts = BankAccount::query()
            ->where('is_active', true)
            ->orderBy('bank_name')
            ->get();

        $statuses = BankContribution::availableStatuses();

        return view('admin.finance.contributions.bank.index', compact(
            'items',
            'members',
            'types',
            'bankAccounts',
            'statuses',
            'filters'
        ));
    }

    public function exportPdf(Request $request, PdfReportService $pdfReportService): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        $pdfPath = $pdfReportService->exportView(
            'pdf.reports.bank-contributions',
            $this->dashboardService->getBankExportPdfData($request->user(), $request->all()),
            'bank-contributions'
        );

        return response()->download($pdfPath)->deleteFileAfterSend(true);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()->can('finance.view'), 403);

        return Excel::download(
            new BankContributionExport($this->dashboardService, $request->user(), $request->all()),
            'bank-contributions-' . now()->format('YmdHis') . '.xlsx'
        );
    }

    public function store(StoreBankContributionRequest $request)
    {
        $data = $request->validated();
        $sendSms = (bool) ($data['send_sms'] ?? false);
        unset($data['send_sms']);

        $status = BankContribution::normalizeStatus($data['status'] ?? BankContribution::STATUS_VERIFIED);

        $contribution = BankContribution::create($data + [
            'status' => $status,
            'recorded_by' => $request->user()->id,
            'approved_by' => $status === BankContribution::STATUS_VERIFIED ? $request->user()->id : null,
            'approved_at' => $status === BankContribution::STATUS_VERIFIED ? now() : null,
        ]);

        if ($sendSms) {
            $contribution->load(['member', 'contributionType']);

            if ($contribution->member) {
                $this->sms->sendAcknowledgement(
                    $contribution->member,
                    $this->sms->bankMessage($contribution->member, $contribution),
                    $request->user()
                );
            }
        }

        return redirect()
            ->route('finance.contributions.bank.index')
            ->with('success', db_trans('bank_contribution_created_successfully'));
    }

    public function update(UpdateBankContributionRequest $request, BankContribution $bankContribution)
    {
        $this->bulkService->assertContributionIsNotLockedByBatch($bankContribution);

        $data = $request->validated();
        $sendSms = (bool) ($data['send_sms'] ?? false);
        unset($data['send_sms']);

        $status = BankContribution::normalizeStatus($data['status'] ?? $bankContribution->status);

        $bankContribution->update($data + [
            'status' => $status,
            'approved_by' => $status === BankContribution::STATUS_VERIFIED ? $request->user()->id : null,
            'approved_at' => $status === BankContribution::STATUS_VERIFIED ? ($bankContribution->approved_at ?: now()) : null,
        ]);

        if ($sendSms) {
            $bankContribution->load(['member', 'contributionType']);

            if ($bankContribution->member) {
                $this->sms->sendAcknowledgement(
                    $bankContribution->member,
                    $this->sms->bankMessage($bankContribution->member, $bankContribution),
                    $request->user()
                );
            }
        }

        return redirect()
            ->route('finance.contributions.bank.index')
            ->with('success', db_trans('bank_contribution_updated_successfully'));
    }

    public function destroy(BankContribution $bankContribution)
    {
        $this->bulkService->assertContributionIsNotLockedByBatch($bankContribution);

        $bankContribution->delete();

        return redirect()
            ->route('finance.contributions.bank.index')
            ->with('success', db_trans('bank_contribution_deleted_successfully'));
    }
}