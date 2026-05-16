<?php

namespace App\Jobs\Communication;

use App\Jobs\Communication\SendCommunicationMessageJob;
use App\Models\CommunicationCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RetryFailedCommunicationMessagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $campaignId,
        public readonly ?int $requestedBy = null,
    ) {}

    public function handle(): void
    {
        $campaign = CommunicationCampaign::query()->find($this->campaignId);

        if (! $campaign || ! ($campaign->allow_retry_failed ?? true)) {
            return;
        }

        $failedMessages = $campaign->messages()
            ->where('status', 'failed')
            ->get();

        foreach ($failedMessages as $message) {
            $message->update([
                'status' => 'queued',
                'error_code' => null,
                'error_message' => null,
                'failed_at' => null,
                'queued_at' => now(),
            ]);

            SendCommunicationMessageJob::dispatch($message->id);
        }
    }
}
