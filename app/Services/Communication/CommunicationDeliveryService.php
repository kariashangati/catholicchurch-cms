<?php

namespace App\Services\Communication;

use App\Models\CommunicationCampaign;
use App\Models\CommunicationMessage;
use App\Services\Communication\Providers\BeemSmsService;
use App\Services\Communication\Support\PhoneNumberService;
use App\Services\Communication\Support\SmsSegmentCalculatorService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class CommunicationDeliveryService
{
    public function __construct(
        protected BeemSmsService $beemSmsService,
        protected PhoneNumberService $phoneNumberService,
        protected SmsSegmentCalculatorService $segmentCalculator,
        protected CommunicationSmsSettingsService $smsSettings,
        protected CommunicationSmsBalanceService $balanceService,
    ) {
    }

    public function sendMessage(CommunicationMessage $message): array
    {
        $normalizedPhone = $this->phoneNumberService->normalize($message->recipient_phone_normalized ?: $message->recipient_phone);

        if (! $normalizedPhone) {
            return $this->markFailed($message, 'invalid_phone', db_trans('invalid_phone_number'));
        }

        $segmentSummary = $this->segmentCalculator->analyze((string) $message->message_body);
        $segments = max(1, (int) ($segmentSummary['segments'] ?? 1));
        $cost = $this->costForSegments($segments);

        if (! $this->balanceService->hasEnough($cost)) {
            return $this->markFailed($message, 'insufficient_sms_balance', db_trans('sms_balance_finished_add_more'));
        }

        try {
            $message->forceFill([
                'recipient_phone_normalized' => $normalizedPhone,
                'segment_count' => $segments,
                'status' => 'sending',
            ])->save();

            $providerResponse = $this->beemSmsService->sendSingle((string) $message->message_body, $normalizedPhone, [
                'recipient_id' => (string) ($message->member_id ?: $message->id),
            ]);

            $providerMessageId = Arr::get($providerResponse, 'message_id')
                ?? Arr::get($providerResponse, 'request_id')
                ?? Arr::get($providerResponse, 'id');

            DB::transaction(function () use ($message, $providerResponse, $providerMessageId, $segmentSummary, $segments, $cost) {
                $message->forceFill([
                    'status' => 'sent',
                    'delivery_status' => 'pending',
                    'provider' => 'beem',
                    'provider_message_id' => $providerMessageId ? (string) $providerMessageId : null,
                    'provider_status_code' => (string) (Arr::get($providerResponse, 'code') ?? Arr::get($providerResponse, 'status_code') ?? 'SUCCESS'),
                    'provider_status_text' => (string) (Arr::get($providerResponse, 'message') ?? Arr::get($providerResponse, 'status') ?? 'Queued to provider'),
                    'meta' => $providerResponse,
                    'segment_count' => $segments,
                    'sent_at' => now(),
                    'failed_at' => null,
                    'error_code' => null,
                    'error_message' => null,
                ])->save();

                if ($message->campaign) {
                    $this->updateCampaignCounters($message->campaign, true, $cost);
                }
            });

            if ($message->campaign) {
                $this->balanceService->debit($cost, [
                    'provider' => 'beem',
                    'reference_type' => 'campaign_message',
                    'reference_id' => $message->id,
                    'campaign_id' => $message->campaign_id,
                    'performed_by' => $message->campaign?->created_by,
                    'description' => 'SMS delivery debit (' . $segments . ' segments)',
                ]);

                $this->finalizeCampaign($message->campaign->fresh());
            }

            return ['status' => 'sent', 'provider_response' => $providerResponse];
        } catch (Throwable $throwable) {
            return $this->markFailed($message, 'provider_error', $throwable->getMessage());
        }
    }

    public function finalizeCampaign(CommunicationCampaign $campaign): CommunicationCampaign
    {
        $pendingCount = $campaign->messages()->whereIn('status', ['pending', 'queued', 'sending'])->count();
        if ($pendingCount > 0) {
            return $campaign;
        }

        $failedCount = (int) $campaign->messages()->where('status', 'failed')->count();
        $sentCount = (int) $campaign->messages()->where('status', 'sent')->count();

        $status = match (true) {
            $failedCount === 0 && $sentCount > 0 => 'completed',
            $failedCount > 0 && $sentCount > 0 => 'partially_failed',
            $failedCount > 0 && $sentCount === 0 => 'failed',
            default => $campaign->status,
        };

        $campaign->forceFill(['status' => $status, 'completed_at' => now()])->save();
        return $campaign->fresh();
    }

    protected function markFailed(CommunicationMessage $message, string $code, string $errorMessage): array
    {
        DB::transaction(function () use ($message, $code, $errorMessage) {
            $message->forceFill([
                'status' => 'failed',
                'delivery_status' => 'rejected',
                'error_code' => $code,
                'error_message' => $errorMessage,
                'failed_at' => now(),
            ])->save();

            if ($message->campaign) {
                $this->updateCampaignCounters($message->campaign, false, 0);
            }
        });

        if ($message->campaign) {
            $this->finalizeCampaign($message->campaign->fresh());
        }

        return ['status' => 'failed', 'error_code' => $code, 'error_message' => $errorMessage];
    }

    protected function updateCampaignCounters(CommunicationCampaign $campaign, bool $sent, float $cost): void
    {
        $campaign->increment($sent ? 'sent_count' : 'failed_count');
        if ($sent && $cost > 0) {
            $campaign->increment('actual_cost_units', $cost);
        }
    }

    protected function costForSegments(int $segments): float
    {
        return $segments * $this->smsSettings->smsUnitPrice();
    }
}
