<?php

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Communication\ApproveCommunicationCampaignRequest;
use App\Http\Requests\Communication\CancelCommunicationCampaignRequest;
use App\Http\Requests\Communication\RejectCommunicationCampaignRequest;
use App\Http\Requests\Communication\RetryFailedCommunicationMessagesRequest;
use App\Models\CommunicationCampaign;
use App\Services\Communication\CommunicationApprovalService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CommunicationApprovalController extends Controller
{
    public function __construct(
        private readonly CommunicationApprovalService $approvalService,
    ) {}

    public function index(): View
    {
        $campaigns = CommunicationCampaign::query()
            ->where(function ($query): void {
                $query->where('approval_status', 'pending')
                    ->orWhere('status', 'pending_approval');
            })
            ->latest()
            ->paginate(20);

        return view('admin.communication.approvals.index', compact('campaigns'));
    }

    public function approve(ApproveCommunicationCampaignRequest $request, CommunicationCampaign $campaign): RedirectResponse
    {
        $this->authorize('approve', $campaign);

        $this->approvalService->approve($campaign, $request->user()?->id, $request->input('approval_notes'));

        return back()->with('success', db_trans('communication.approvals.approved_success'));
    }

    public function reject(RejectCommunicationCampaignRequest $request, CommunicationCampaign $campaign): RedirectResponse
    {
        $this->authorize('approve', $campaign);

        $this->approvalService->reject($campaign, $request->user()?->id, $request->input('approval_notes'));

        return back()->with('success', db_trans('communication.approvals.rejected_success'));
    }

    public function cancel(CancelCommunicationCampaignRequest $request, CommunicationCampaign $campaign): RedirectResponse
    {
        $this->authorize('cancel', $campaign);

        $this->approvalService->cancel($campaign, $request->user()?->id, $request->input('cancellation_reason'));

        return back()->with('success', db_trans('communication.approvals.cancelled_success'));
    }

    public function retryFailed(RetryFailedCommunicationMessagesRequest $request, CommunicationCampaign $campaign): RedirectResponse
    {
        $this->authorize('retryFailed', $campaign);

        $this->approvalService->retryFailed($campaign, $request->user()?->id);

        return back()->with('success', db_trans('communication.approvals.retry_requested_success'));
    }
}
