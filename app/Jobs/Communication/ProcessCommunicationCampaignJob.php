<?php

namespace App\Jobs\Communication;

use App\Models\CommunicationCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCommunicationCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public int $campaignId)
    {
        $this->onQueue('communication');
    }

    public function handle(): void
    {
        $campaign = CommunicationCampaign::query()->with('messages')->find($this->campaignId);

        if (! $campaign) {
            return;
        }

        $campaign->messages()
            ->whereIn('status', ['pending', 'failed'])
            ->orderBy('id')
            ->chunkById(100, function ($messages) {
                foreach ($messages as $message) {
                    $message->forceFill([
                        'status' => 'queued',
                        'queued_at' => now(),
                    ])->save();

                    SendCommunicationMessageJob::dispatch($message->id);
                }
            });
    }
}
