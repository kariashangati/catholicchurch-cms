<?php

namespace App\Services\Finance;

use App\Models\Member;
use App\Models\User;
use App\Services\Communication\CommunicationSmsBalanceService;
use App\Services\Communication\CommunicationSmsSettingsService;
use App\Services\Communication\CommunicationSmsTrackingService;
use App\Services\Communication\Providers\BeemSmsService;
use App\Services\Communication\Support\SmsSegmentCalculatorService;
use Illuminate\Support\Facades\Log;

class TitheSmsService
{
    public function __construct(
        protected BeemSmsService $beemSms,
        protected CommunicationSmsTrackingService $trackingService,
        protected SmsSegmentCalculatorService $segmentCalculator,
        protected CommunicationSmsSettingsService $settingsService,
        protected CommunicationSmsBalanceService $balanceService,
    ) {
    }

    public function sendAcknowledgement(Member $member, string $message, User $sender): void
    {
        if (blank($member->phone) || blank($message)) {
            return;
        }

        $segments = max(1, (int) ($this->segmentCalculator->analyze($message)['segments'] ?? 1));
        $cost = $segments * $this->settingsService->smsUnitPrice();

        if (! $this->balanceService->hasEnough($cost)) {
            Log::warning('Tithe SMS skipped because SMS balance is not enough.', [
                'member_id' => $member->id,
                'required_cost' => $cost,
                'current_balance' => $this->balanceService->currentBalance(),
            ]);
            return;
        }

        try {
            $result = $this->beemSms->sendSingle($message, (string) $member->phone, [
                'recipient_id' => (string) $member->id,
            ]);

            $this->trackingService->recordDirectSms($member, $message, $sender, $result, 'Tithe SMS', 'finance_tithe_acknowledgement');
        } catch (\Throwable $e) {
            Log::warning('Tithe SMS acknowledgement failed', [
                'member_id' => $member->id,
                'phone' => $member->phone,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
