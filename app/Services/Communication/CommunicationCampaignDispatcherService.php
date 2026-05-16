<?php

namespace App\Services\Communication;

use App\Models\CommunicationCampaign;

class CommunicationCampaignDispatcherService
{
    public function __construct(protected CommunicationDeliveryService $deliveryService)
    {
    }

    public function dispatch(CommunicationCampaign $campaign): CommunicationCampaign
    {
        if ($campaign->status === 'scheduled' && $campaign->scheduled_at && $campaign->scheduled_at->isFuture()) {
            return $campaign;
        }

        if (! in_array($campaign->status, ['draft', 'approved', 'processing', 'partially_failed'], true)) {
            return $campaign;
        }

        $campaign->forceFill([
            'status' => 'processing',
            'started_at' => $campaign->started_at ?: now(),
        ])->save();

        $campaign->messages()
            ->whereIn('status', ['pending', 'queued', 'failed'])
            ->orderBy('id')
            ->chunkById(100, function ($messages) {
                foreach ($messages as $message) {
                    $message->forceFill([
                        'status' => 'queued',
                        'queued_at' => now(),
                    ])->save();

                    $this->deliveryService->sendMessage($message->loadMissing('campaign'));
                }
            });

        return $this->deliveryService->finalizeCampaign($campaign->fresh());
    }

    public function dispatchScheduledDueCampaigns(): int
    {
        $count = 0;

        CommunicationCampaign::query()
            ->where('channel', 'sms')
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->chunkById(50, function ($campaigns) use (&$count) {
                foreach ($campaigns as $campaign) {
                    $this->dispatch($campaign);
                    $count++;
                }
            });

        return $count;
    }
}
