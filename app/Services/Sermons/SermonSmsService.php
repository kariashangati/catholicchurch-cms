<?php

namespace App\Services\Sermons;

use App\Models\Member;
use App\Models\SermonRecipient;
use App\Models\User;
use App\Services\Communication\CommunicationSmsBalanceService;
use App\Services\Communication\CommunicationSmsSettingsService;
use App\Services\Communication\CommunicationSmsTrackingService;
use App\Services\Communication\Providers\BeemSmsService;
use App\Services\Communication\Support\SmsSegmentCalculatorService;
use Illuminate\Support\Facades\Log;
use Throwable;

class SermonSmsService
{
    public function __construct(
        protected SermonTokenService $tokens,
        protected BeemSmsService $beemSms,
        protected CommunicationSmsTrackingService $trackingService,
        protected SmsSegmentCalculatorService $segmentCalculator,
        protected CommunicationSmsSettingsService $settingsService,
        protected CommunicationSmsBalanceService $balanceService,
    ) {
    }

    public function sendRecipientLink(SermonRecipient $recipient, ?User $sender = null): bool
    {
        $recipient->loadMissing(['sermon', 'token', 'member']);

        if (blank($recipient->phone) || ! $recipient->sermon) {
            $recipient->forceFill([
                'sms_status' => SermonRecipient::SMS_FAILED,
                'sms_error' => db_trans('missing_phone_or_sermon'),
            ])->save();

            return false;
        }

        $token = $recipient->token ?: $this->tokens->createToken($recipient);
        $url = $this->tokens->publicUrl($token);

        $body = db_trans('sermon_sms_new_link') . ': '
            . $recipient->sermon->title . '. '
            . db_trans('open_here') . ': '
            . $url;

        if (blank($body)) {
            $recipient->forceFill([
                'sms_status' => SermonRecipient::SMS_FAILED,
                'sms_error' => db_trans('sms_message_body_empty'),
            ])->save();

            return false;
        }

        $segments = max(1, (int) ($this->segmentCalculator->analyze($body)['segments'] ?? 1));
        $cost = $segments * $this->settingsService->smsUnitPrice();

        if (! $this->balanceService->hasEnough($cost)) {
            $error = db_trans('sms_balance_not_enough');

            Log::warning('Sermon SMS skipped because SMS balance is not enough.', [
                'sermon_id' => $recipient->sermon_id,
                'recipient_id' => $recipient->id,
                'member_id' => $recipient->member_id,
                'required_cost' => $cost,
                'current_balance' => $this->balanceService->currentBalance(),
            ]);

            $recipient->forceFill([
                'sms_status' => SermonRecipient::SMS_FAILED,
                'sms_error' => $error,
            ])->save();

            return false;
        }

        try {
            $result = $this->beemSms->sendSingle($body, (string) $recipient->phone, [
                'recipient_id' => (string) $recipient->id,
            ]);

            $recipient->forceFill([
                'sms_status' => SermonRecipient::SMS_SENT,
                'sms_sent_at' => now(),
                'sms_error' => null,
            ])->save();

            /*
             * Tracking service expects a Member model.
             * If this recipient was generated from a member, record normal tracking.
             * If member is missing, SMS is still sent and recipient status is saved.
             */
            $member = $recipient->member;

            if ($member instanceof Member && $sender instanceof User) {
                $this->trackingService->recordDirectSms(
                    $member,
                    $body,
                    $sender,
                    $result,
                    'Sermon SMS',
                    'sermon_secure_link'
                );
            }

            return true;
        } catch (Throwable $e) {
            Log::warning('Sermon SMS failed.', [
                'sermon_id' => $recipient->sermon_id,
                'recipient_id' => $recipient->id,
                'member_id' => $recipient->member_id,
                'phone' => $recipient->phone,
                'message' => $e->getMessage(),
            ]);

            $recipient->forceFill([
                'sms_status' => SermonRecipient::SMS_FAILED,
                'sms_error' => $e->getMessage(),
            ])->save();

            return false;
        }
    }
}