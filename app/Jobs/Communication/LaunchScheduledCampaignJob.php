<?php

namespace App\Jobs\Communication;

use App\Models\CommunicationCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LaunchScheduledCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $campaignId)
    {
        $this->onQueue('communications');
    }

    public function handle(): void
    {
        $campaign = CommunicationCampaign::find($this->campaignId);

        if (! $campaign) {
            return;
        }

        /**
         * IMPORTANT:
         * Replace this class if your Phase 5 campaign job has a different name.
         */
        if (class_exists(\App\Jobs\Communication\ProcessCommunicationCampaignJob::class)) {
            \App\Jobs\Communication\ProcessCommunicationCampaignJob::dispatch($campaign->id)
                ->onQueue('communications');

            return;
        }

        if (class_exists(\App\Jobs\Communication\DispatchCommunicationCampaignJob::class)) {
            \App\Jobs\Communication\DispatchCommunicationCampaignJob::dispatch($campaign->id)
                ->onQueue('communications');

            return;
        }

        throw new \RuntimeException('No campaign processing job class was found for scheduled communication campaigns.');
    }
}
