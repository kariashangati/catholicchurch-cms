<?php

namespace App\Services\Receipts;

use App\Models\ReceiptIssue;
use App\Models\User;

class ReceiptDeliveryService
{
    public function __construct(
        protected ReceiptSmsService $receiptSmsService,
        protected ReceiptActionLogService $actionLogService,
    ) {
    }

    public function sendReceiptSms(ReceiptIssue $receipt, string $phone, ?User $actor = null, ?string $customMessage = null): void
    {
        $this->receiptSmsService->send($receipt, $phone, $customMessage);

        $receipt->forceFill([
            'sms_sent_at' => now(),
            'sms_sent_to' => $phone,
            'delivery_channel' => 'sms',
        ])->save();

        $this->actionLogService->log($receipt, 'delivery_sms_sent', [
            'phone' => $phone,
        ], $actor);
    }

    public function resendReceiptSms(ReceiptIssue $receipt, string $phone, ?User $actor = null, ?string $customMessage = null): void
    {
        $this->receiptSmsService->send($receipt, $phone, $customMessage);

        $receipt->forceFill([
            'sms_last_resent_at' => now(),
            'sms_sent_to' => $phone,
            'delivery_channel' => 'sms',
        ])->save();

        $this->actionLogService->log($receipt, 'delivery_sms_resent', [
            'phone' => $phone,
        ], $actor);
    }
}
