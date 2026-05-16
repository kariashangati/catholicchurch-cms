<?php

namespace App\Http\Controllers\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Communication\ScheduleCommunicationCampaignRequest;
use App\Models\CommunicationCampaign;
use App\Services\Communication\CommunicationSchedulingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CommunicationScheduleController extends Controller
{
    public function __construct(
        protected CommunicationSchedulingService $schedulingService
    ) {
    }

    public function index(): View
    {
        abort_unless(auth()->user()?->can('communication.schedule'), 403);

        $campaigns = $this->schedulingService->getScheduledCampaigns();

        return view('admin.communication.schedules.index', compact('campaigns'));
    }

    public function schedule(
        ScheduleCommunicationCampaignRequest $request,
        CommunicationCampaign $campaign
    ): RedirectResponse {
        abort_unless(auth()->user()?->can('communication.schedule'), 403);

        $this->ensureSchedulable($campaign);

        $this->schedulingService->scheduleCampaign(
            $campaign,
            $request->string('scheduled_at')->toString(),
            $request->string('schedule_timezone')->toString(),
            $request->input('schedule_notes'),
            auth()->id()
        );

        return back()->with('success', db_trans('communication.schedule_success'));
    }

    public function reschedule(
        ScheduleCommunicationCampaignRequest $request,
        CommunicationCampaign $campaign
    ): RedirectResponse {
        abort_unless(auth()->user()?->can('communication.schedule'), 403);

        if ($campaign->status !== 'scheduled') {
            return back()->with('error', db_trans('communication.only_scheduled_campaign_can_be_rescheduled'));
        }

        $this->schedulingService->scheduleCampaign(
            $campaign,
            $request->string('scheduled_at')->toString(),
            $request->string('schedule_timezone')->toString(),
            $request->input('schedule_notes'),
            auth()->id()
        );

        return back()->with('success', db_trans('communication.reschedule_success'));
    }

    public function cancel(CommunicationCampaign $campaign): RedirectResponse
    {
        abort_unless(auth()->user()?->can('communication.schedule'), 403);

        if ($campaign->status !== 'scheduled') {
            return back()->with('error', db_trans('communication.only_scheduled_campaign_can_be_cancelled'));
        }

        $this->schedulingService->cancelScheduledCampaign($campaign);

        return back()->with('success', db_trans('communication.cancel_schedule_success'));
    }

    protected function ensureSchedulable(CommunicationCampaign $campaign): void
    {
        if (! in_array($campaign->status, ['draft', 'approved', 'failed', 'partially_failed'], true)) {
            abort(422, db_trans('communication.campaign_status_is_not_schedulable'));
        }
    }
}
