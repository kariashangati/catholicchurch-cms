<?php

namespace App\Services\Finance;

use App\Models\Member;
use App\Models\User;
use App\Services\Communication\CommunicationSmsBalanceService;
use App\Services\Communication\CommunicationSmsSettingsService;
use App\Services\Communication\CommunicationSmsTrackingService;
use App\Services\Communication\Providers\BeemSmsService;
use App\Services\Communication\SmsTemplateMessageService;
use App\Services\Communication\Support\SmsSegmentCalculatorService;
use Illuminate\Support\Facades\Log;

class ContributionSmsService
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
            Log::warning('Contribution SMS skipped because SMS balance is not enough.', [
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

            $this->trackingService->recordDirectSms($member, $message, $sender, $result, 'Contribution SMS', 'finance_contribution_acknowledgement');
        } catch (\Throwable $e) {
            Log::warning('Contribution SMS acknowledgement failed', [
                'member_id' => $member->id,
                'phone' => $member->phone,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function cashMessage(Member $member, object $contribution): string
    {
        $typeName = $contribution->contributionType?->name ?: db_trans('contribution');

        $fallback = sprintf(
            'Mpendwa %s, tumepokea mchango wako wa %s kiasi cha TZS %s tarehe %s. Asante sana.',
            $member->full_name ?? $member->name ?? '',
            $typeName,
            number_format((float) $contribution->amount, 2),
            optional($contribution->contribution_date)->format('d/m/Y')
        );

        return app(SmsTemplateMessageService::class)->render('finance_cash_contribution_acknowledgement', [
            'member_name' => $member->full_name ?? $member->name ?? '',
            'contribution_type' => $typeName,
            'amount' => number_format((float) $contribution->amount, 2),
            'date' => optional($contribution->contribution_date)->format('d/m/Y'),
            'reference' => $contribution->reference_no ?: '-',
        ], $fallback);
    }

    public function bankMessage(Member $member, object $contribution): string
    {
        $typeName = $contribution->contributionType?->name ?: db_trans('contribution');

        $fallback = sprintf(
            'Mpendwa %s, tumepokea mchango wako wa benki wa %s kiasi cha TZS %s tarehe %s. Kumbukumbu: %s. Asante sana.',
            $member->full_name ?? $member->name ?? '',
            $typeName,
            number_format((float) $contribution->amount, 2),
            optional($contribution->contribution_date)->format('d/m/Y'),
            $contribution->reference_no ?: '-'
        );

        return app(SmsTemplateMessageService::class)->render('finance_bank_contribution_acknowledgement', [
            'member_name' => $member->full_name ?? $member->name ?? '',
            'contribution_type' => $typeName,
            'amount' => number_format((float) $contribution->amount, 2),
            'date' => optional($contribution->contribution_date)->format('d/m/Y'),
            'reference' => $contribution->reference_no ?: '-',
        ], $fallback);
    }
}
