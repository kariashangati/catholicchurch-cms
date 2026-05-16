<?php

namespace App\Services\Communication;

use App\Models\CommunicationCampaign;
use App\Models\CommunicationMessage;
use App\Models\Member;
use App\Models\User;
use App\Services\Communication\Support\SmsSegmentCalculatorService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class CommunicationSmsTrackingService
{
    public function __construct(
        protected SmsSegmentCalculatorService $segmentCalculator,
        protected CommunicationSmsSettingsService $settingsService,
        protected CommunicationSmsBalanceService $balanceService,
    ) {
    }

    public function recordDirectSms(Member $member, string $message, ?User $sender, array $providerResponse = [], string $title = 'Direct SMS', string $source = 'manual'): void
    {
        try {
            $analysis = $this->segmentCalculator->analyze($message);
            $segments = max(1, (int) ($providerResponse['estimated_total_segments'] ?? $analysis['segments'] ?? 1));
            $cost = $segments * $this->settingsService->smsUnitPrice();
            $status = data_get($providerResponse, 'success', false) ? 'sent' : 'failed';
            $jumuiya = $member->familia?->jumuiya;
            $campaign = null;

            DB::transaction(function () use ($member, $message, $sender, $providerResponse, $title, $source, $segments, $cost, $status, $jumuiya, &$campaign) {
                $campaign = CommunicationCampaign::query()->create([
                    'title' => $title . ' - ' . ($member->full_name ?: $member->first_name) . ' - ' . now()->format('Y-m-d H:i'),
                    'type' => 'automation',
                    'channel' => 'sms',
                    'source_event_key' => $source,
                    'audience_type' => 'member',
                    'audience_filters' => ['member_id' => $member->id, 'source' => $source],
                    'message_body' => $message,
                    'status' => $status === 'sent' ? 'completed' : 'failed',
                    'created_by' => $sender?->id,
                    'total_recipients' => 1,
                    'valid_recipients' => 1,
                    'invalid_recipients' => 0,
                    'total_messages' => 1,
                    'total_segments' => $segments,
                    'sent_count' => $status === 'sent' ? 1 : 0,
                    'failed_count' => $status === 'failed' ? 1 : 0,
                    'estimated_cost_units' => $cost,
                    'actual_cost_units' => $status === 'sent' ? $cost : 0,
                    'started_at' => now(),
                    'completed_at' => now(),
                ]);

                CommunicationMessage::query()->create([
                    'campaign_id' => $campaign->id,
                    'member_id' => $member->id,
                    'familia_id' => $member->familia_id,
                    'jumuiya_id' => $jumuiya?->id,
                    'kanda_id' => $jumuiya?->kanda_id,
                    'recipient_name' => $member->full_name ?: trim($member->first_name . ' ' . $member->last_name),
                    'recipient_phone' => $member->phone,
                    'recipient_phone_normalized' => Arr::get($providerResponse, 'payload.recipients.0.dest_addr', $member->phone),
                    'recipient_type' => 'member',
                    'locale' => app()->getLocale(),
                    'message_body' => $message,
                    'segment_count' => $segments,
                    'status' => $status,
                    'delivery_status' => $status === 'sent' ? 'pending' : 'rejected',
                    'provider' => 'beem',
                    'provider_message_id' => Arr::get($providerResponse, 'message_id'),
                    'provider_status_code' => Arr::get($providerResponse, 'code'),
                    'provider_status_text' => Arr::get($providerResponse, 'message'),
                    'meta' => $providerResponse,
                    'sent_at' => $status === 'sent' ? now() : null,
                    'failed_at' => $status === 'failed' ? now() : null,
                ]);
            });

            if ($status === 'sent' && $campaign) {
                $this->balanceService->debit($cost, [
                    'provider' => 'beem',
                    'reference_type' => 'direct_sms',
                    'reference_id' => $campaign->id,
                    'campaign_id' => $campaign->id,
                    'performed_by' => $sender?->id,
                    'description' => 'Direct SMS debit (' . $segments . ' segments)',
                ]);
            }
        } catch (Throwable) {
            // Tracking should never block finance or receipt workflows.
        }
    }
}
