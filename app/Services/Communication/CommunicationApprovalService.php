<?php

namespace App\Services\Communication;

use App\Jobs\Communication\LaunchScheduledCampaignJob;
use App\Jobs\Communication\RetryFailedCommunicationMessagesJob;
use App\Models\CommunicationCampaign;
use App\Models\CommunicationApprovalHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CommunicationApprovalService
{
    public function submit(CommunicationCampaign $campaign, ?int $userId = null, ?string $notes = null): CommunicationCampaign
    {
        return DB::transaction(function () use ($campaign, $userId, $notes): CommunicationCampaign {
            $campaign->update([
                'approval_status' => 'pending',
                'submitted_for_approval_at' => now(),
                'status' => $campaign->status === 'draft' ? 'pending_approval' : $campaign->status,
                'approval_notes' => $notes,
            ]);

            $this->history($campaign, 'submitted', $userId, $notes);

            return $campaign->fresh();
        });
    }

    public function approve(CommunicationCampaign $campaign, ?int $userId = null, ?string $notes = null): CommunicationCampaign
    {
        return DB::transaction(function () use ($campaign, $userId, $notes): CommunicationCampaign {
            $status = $campaign->scheduled_at ? 'scheduled' : 'processing';

            $campaign->update([
                'approval_status' => 'approved',
                'approved_by' => $userId,
                'approved_at' => now(),
                'approval_notes' => $notes,
                'status' => $status,
            ]);

            $this->history($campaign, 'approved', $userId, $notes);

            if ($campaign->scheduled_at && Carbon::parse($campaign->scheduled_at)->isFuture()) {
                LaunchScheduledCampaignJob::dispatch($campaign->id)->delay(Carbon::parse($campaign->scheduled_at));
            }

            return $campaign->fresh();
        });
    }

    public function reject(CommunicationCampaign $campaign, ?int $userId = null, string $notes = null): CommunicationCampaign
    {
        return DB::transaction(function () use ($campaign, $userId, $notes): CommunicationCampaign {
            $campaign->update([
                'approval_status' => 'rejected',
                'status' => 'draft',
                'approval_notes' => $notes,
            ]);

            $this->history($campaign, 'rejected', $userId, $notes);

            return $campaign->fresh();
        });
    }

    public function cancel(CommunicationCampaign $campaign, ?int $userId = null, string $reason = null): CommunicationCampaign
    {
        return DB::transaction(function () use ($campaign, $userId, $reason): CommunicationCampaign {
            $campaign->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => $userId,
                'cancellation_reason' => $reason,
            ]);

            $this->history($campaign, 'cancelled', $userId, $reason);

            return $campaign->fresh();
        });
    }

    public function retryFailed(CommunicationCampaign $campaign, ?int $userId = null): void
    {
        RetryFailedCommunicationMessagesJob::dispatch($campaign->id, $userId);
        $this->history($campaign, 'retry_requested', $userId, null);
    }

    private function history(CommunicationCampaign $campaign, string $action, ?int $userId, ?string $notes): void
    {
        if (! class_exists(CommunicationApprovalHistory::class)) {
            return;
        }

        CommunicationApprovalHistory::create([
            'campaign_id' => $campaign->id,
            'acted_by' => $userId,
            'action' => $action,
            'notes' => $notes,
        ]);
    }
}
