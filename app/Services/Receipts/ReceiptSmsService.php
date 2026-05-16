<?php

namespace App\Services\Receipts;

use App\Models\ReceiptDeliveryLog;
use App\Models\ReceiptIssue;
use App\Models\User;
use App\Services\Communication\Providers\BeemSmsService;
use App\Services\Communication\CommunicationSmsTrackingService;
use App\Services\Communication\CommunicationSmsBalanceService;
use App\Services\Communication\CommunicationSmsSettingsService;
use App\Services\Communication\Support\SmsSegmentCalculatorService;
use App\Services\Communication\SmsTemplateMessageService;
use App\Support\Receipts\ReceiptStatuses;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReceiptSmsService
{
    public function __construct(
        protected ?BeemSmsService $beemSms = null,
        protected ?SmsSegmentCalculatorService $segmentCalculator = null,
        protected ?CommunicationSmsSettingsService $settingsService = null,
        protected ?CommunicationSmsBalanceService $balanceService = null,
    ) {
    }

    /**
     * Compatible with both old calls: send($receipt, $phone, $message)
     * and new calls: send($receipt, $sender, $phone, $message).
     */
    public function send(ReceiptIssue $receipt, User|string|null $senderOrPhone = null, ?string $phone = null, ?string $customMessage = null): void
    {
        $sender = $senderOrPhone instanceof User ? $senderOrPhone : auth()->user();
        $phone = $senderOrPhone instanceof User
            ? ($phone ?: ($receipt->member?->phone ?? data_get($receipt->meta, 'phone')))
            : ((string) ($senderOrPhone ?: $phone ?: $receipt->member?->phone ?: data_get($receipt->meta, 'phone')));
        $message = $customMessage ?: $this->buildMessage($receipt);

        if (blank($phone) || blank($message)) {
            return;
        }

        $segmentCalculator = $this->segmentCalculator ?: app(SmsSegmentCalculatorService::class);
        $settingsService = $this->settingsService ?: app(CommunicationSmsSettingsService::class);
        $balanceService = $this->balanceService ?: app(CommunicationSmsBalanceService::class);
        $segmentsToSend = max(1, (int) ($segmentCalculator->analyze($message)['segments'] ?? 1));
        $costToSend = $segmentsToSend * $settingsService->smsUnitPrice();

        if (! $balanceService->hasEnough($costToSend)) {
            Log::warning('Receipt SMS skipped because SMS balance is not enough.', [
                'receipt_id' => $receipt->id,
                'required_cost' => $costToSend,
                'current_balance' => $balanceService->currentBalance(),
            ]);
            return;
        }

        try {
            $service = $this->beemSms ?: app(BeemSmsService::class);
            $result = $service->sendSingle($message, (string) $phone, [
                'recipient_id' => (string) ($receipt->member_id ?: $receipt->id),
            ]);

            $segments = (int) ($result['estimated_total_segments'] ?? 1);
            $sentPhone = data_get($result, 'payload.recipients.0.dest_addr', $phone);

            if ($receipt->member) {
                app(CommunicationSmsTrackingService::class)->recordDirectSms($receipt->member, $message, $sender, $result, 'Receipt SMS', 'receipt_ready_link');
            }

            ReceiptDeliveryLog::query()->create([
                'receipt_issue_id' => $receipt->id,
                'channel' => 'sms_link',
                'recipient' => $sentPhone,
                'status' => 'sent',
                'provider' => 'beem',
                'message_snapshot' => $message,
                'sent_by' => $sender?->id,
                'sent_at' => now(),
                'provider_response' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'meta' => $result,
            ]);

            $receipt->forceFill([
                'status' => ReceiptStatuses::SMS_SENT,
                'sms_sent_at' => now(),
                'sms_sent_to' => $sentPhone,
                'sms_sent_by' => $sender?->id,
                'delivery_status' => 'sms_sent',
                'last_delivery_channel' => 'sms_link',
                'delivery_attempts' => (int) ($receipt->delivery_attempts ?? 0) + 1,
            ])->save();
        } catch (\Throwable $e) {
            ReceiptDeliveryLog::query()->create([
                'receipt_issue_id' => $receipt->id,
                'channel' => 'sms_link',
                'recipient' => $phone,
                'status' => 'failed',
                'provider' => 'beem',
                'message_snapshot' => $message,
                'sent_by' => $sender?->id,
                'sent_at' => now(),
                'provider_response' => $e->getMessage(),
                'meta' => ['error' => $e->getMessage()],
            ]);

            Log::warning('Receipt SMS failed', [
                'receipt_id' => $receipt->id,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function buildMessage(ReceiptIssue $receipt): string
    {
        $link = ! blank($receipt->access_token)
            ? $receipt->access_url
            : Str::limit(route('receipt.verify.search', ['receipt_no' => $receipt->receipt_no]), 250, '');

        $fallback = sprintf(
            'Mpendwa %s, risiti yako %s ya TZS %s ipo tayari. Fungua: %s',
            $receipt->recipient_name,
            $receipt->receipt_no,
            number_format((float) $receipt->amount, 2),
            $link
        );

        return app(SmsTemplateMessageService::class)->render('receipt_ready_link', [
            'member_name' => $receipt->recipient_name,
            'receipt_no' => $receipt->receipt_no,
            'amount' => number_format((float) $receipt->amount, 2),
            'link' => $link,
            'verification_code' => $receipt->verification_code ?? '',
        ], $fallback);
    }
}
