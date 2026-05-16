<?php

namespace App\Jobs\Communication;

use App\Models\CommunicationMessage;
use App\Services\Communication\CommunicationDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCommunicationMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public int $backoff = 30;

    public function __construct(public int $messageId)
    {
        $this->onQueue('communication');
    }

    public function handle(CommunicationDeliveryService $deliveryService): void
    {
        $message = CommunicationMessage::query()->with('campaign')->find($this->messageId);

        if (! $message || ! $message->campaign) {
            return;
        }

        if (! in_array($message->status, ['queued', 'pending', 'failed'], true)) {
            return;
        }

        $deliveryService->sendMessage($message);
        $deliveryService->finalizeCampaign($message->campaign->fresh());
    }
}
