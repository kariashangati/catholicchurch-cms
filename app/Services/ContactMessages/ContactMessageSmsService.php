<?php

namespace App\Services\ContactMessages;

use App\Models\CommunicationCampaign;
use App\Models\CommunicationMessage as TrackedCommunicationMessage;
use App\Models\ContactMessage;
use App\Models\ContactMessageReply;
use App\Models\User;
use App\Services\Communication\CommunicationSmsBalanceService;
use App\Services\Communication\CommunicationSmsSettingsService;
use App\Services\Communication\Providers\BeemSmsService;
use App\Services\Communication\Support\SmsSegmentCalculatorService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContactMessageSmsService
{
    public function __construct(
        protected BeemSmsService $beemSms,
        protected SmsSegmentCalculatorService $segmentCalculator,
        protected CommunicationSmsSettingsService $settingsService,
        protected CommunicationSmsBalanceService $balanceService,
    ) {
    }

    public function sendReply(ContactMessage $contactMessage, ContactMessageReply $reply, ?User $sender = null): bool
    {
        if (blank($contactMessage->phone) || blank($reply->reply_body)) {
            $this->markFailed($contactMessage, $reply, db_trans('missing_phone_or_reply_message'));
            return false;
        }

        $body = trim($reply->reply_body);
        $analysis = $this->segmentCalculator->analyze($body);
        $segments = max(1, (int) ($analysis['segments'] ?? 1));
        $cost = $segments * $this->settingsService->smsUnitPrice();

        if (! $this->balanceService->hasEnough($cost)) {
            $error = db_trans('sms_balance_not_enough');
            Log::warning('Contact reply SMS skipped because balance is not enough.', [
                'contact_message_id' => $contactMessage->id,
                'required_cost' => $cost,
                'current_balance' => $this->balanceService->currentBalance(),
            ]);
            $this->markFailed($contactMessage, $reply, $error);
            return false;
        }

        try {
            $result = $this->beemSms->sendSingle($body, (string) $contactMessage->phone, [
                'recipient_id' => 'contact-'.$contactMessage->id,
            ]);

            $sent = (bool) ($result['success'] ?? false);

            if (! $sent) {
                $this->markFailed($contactMessage, $reply, $result['message'] ?? db_trans('sms_not_sent_check_communication_settings'), $result);
                return false;
            }

            $reply->forceFill([
                'sms_status' => ContactMessageReply::SMS_SENT,
                'sms_error' => null,
                'sms_sent_at' => now(),
                'provider_response' => $result,
            ])->save();

            $contactMessage->forceFill([
                'last_sms_status' => ContactMessageReply::SMS_SENT,
                'last_sms_error' => null,
                'last_sms_sent_at' => now(),
            ])->save();

            $this->recordTracking($contactMessage, $reply, $sender, $result, $segments, $cost);

            return true;
        } catch (Throwable $e) {
            Log::warning('Contact reply SMS failed.', [
                'contact_message_id' => $contactMessage->id,
                'phone' => $contactMessage->phone,
                'message' => $e->getMessage(),
            ]);

            $this->markFailed($contactMessage, $reply, $e->getMessage());
            return false;
        }
    }

    protected function markFailed(ContactMessage $contactMessage, ContactMessageReply $reply, string $error, array $response = []): void
    {
        $reply->forceFill([
            'sms_status' => ContactMessageReply::SMS_FAILED,
            'sms_error' => $error,
            'provider_response' => $response ?: null,
        ])->save();

        $contactMessage->forceFill([
            'last_sms_status' => ContactMessageReply::SMS_FAILED,
            'last_sms_error' => $error,
        ])->save();
    }

    protected function recordTracking(ContactMessage $contactMessage, ContactMessageReply $reply, ?User $sender, array $providerResponse, int $segments, float $cost): void
    {
        try {
            DB::transaction(function () use ($contactMessage, $reply, $sender, $providerResponse, $segments, $cost): void {
                $campaign = CommunicationCampaign::query()->create([
                    'title' => db_trans('contact_reply_sms').' - '.$contactMessage->full_name.' - '.now()->format('Y-m-d H:i'),
                    'type' => CommunicationCampaign::TYPE_AUTOMATION,
                    'channel' => 'sms',
                    'source_event_key' => 'contact_message_reply',
                    'audience_type' => 'external_contact',
                    'audience_filters' => ['contact_message_id' => $contactMessage->id],
                    'message_body' => $reply->reply_body,
                    'status' => CommunicationCampaign::STATUS_COMPLETED,
                    'created_by' => $sender?->id,
                    'total_recipients' => 1,
                    'valid_recipients' => 1,
                    'invalid_recipients' => 0,
                    'total_messages' => 1,
                    'total_segments' => $segments,
                    'sent_count' => 1,
                    'failed_count' => 0,
                    'estimated_cost_units' => $cost,
                    'actual_cost_units' => $cost,
                    'started_at' => now(),
                    'completed_at' => now(),
                ]);

                TrackedCommunicationMessage::query()->create([
                    'campaign_id' => $campaign->id,
                    'jumuiya_id' => $contactMessage->jumuiya_id,
                    'kanda_id' => $contactMessage->kanda_id,
                    'recipient_name' => $contactMessage->full_name,
                    'recipient_phone' => $contactMessage->phone,
                    'recipient_phone_normalized' => Arr::get($providerResponse, 'payload.recipients.0.dest_addr', $contactMessage->phone),
                    'recipient_type' => 'external_contact',
                    'locale' => app()->getLocale(),
                    'message_body' => $reply->reply_body,
                    'segment_count' => $segments,
                    'status' => TrackedCommunicationMessage::STATUS_SENT,
                    'delivery_status' => 'pending',
                    'provider' => 'beem',
                    'provider_message_id' => Arr::get($providerResponse, 'message_id'),
                    'provider_status_code' => Arr::get($providerResponse, 'code'),
                    'provider_status_text' => Arr::get($providerResponse, 'message'),
                    'meta' => array_merge($providerResponse, [
                        'module' => 'contact_messages',
                        'contact_message_id' => $contactMessage->id,
                        'reply_id' => $reply->id,
                    ]),
                    'sent_at' => now(),
                ]);

                $this->balanceService->debit($cost, [
                    'provider' => 'beem',
                    'reference_type' => 'contact_reply_sms',
                    'reference_id' => $reply->id,
                    'campaign_id' => $campaign->id,
                    'performed_by' => $sender?->id,
                    'description' => 'Contact reply SMS debit ('.$segments.' segments)',
                ]);
            });
        } catch (Throwable $e) {
            Log::warning('Contact reply SMS tracking failed.', ['message' => $e->getMessage()]);
        }
    }
}
