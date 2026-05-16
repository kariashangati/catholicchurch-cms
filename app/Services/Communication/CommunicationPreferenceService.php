<?php

namespace App\Services\Communication;

use App\Models\CommunicationPreference;
use App\Models\Member;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CommunicationPreferenceService
{
    public function getOrCreateForMember(Member $member): CommunicationPreference
    {
        return CommunicationPreference::firstOrCreate(
            ['member_id' => $member->id],
            $this->defaultAttributes()
        );
    }

    public function updateForMember(Member $member, array $attributes): CommunicationPreference
    {
        return DB::transaction(function () use ($member, $attributes) {
            $preference = $this->getOrCreateForMember($member);

            $preference->fill($this->sanitizeAttributes($attributes));
            $preference->save();

            return $preference->refresh();
        });
    }

    public function optOut(Member $member, ?string $reason = null): CommunicationPreference
    {
        return $this->updateForMember($member, [
            'allow_sms' => false,
            'opted_out_at' => now(),
            'opt_out_reason' => $reason,
        ]);
    }

    public function optIn(Member $member): CommunicationPreference
    {
        return $this->updateForMember($member, [
            'allow_sms' => true,
            'opted_out_at' => null,
            'opt_out_reason' => null,
        ]);
    }

    public function verifyPhone(Member $member): CommunicationPreference
    {
        return $this->updateForMember($member, [
            'is_phone_verified' => true,
            'phone_verified_at' => now(),
        ]);
    }

    protected function sanitizeAttributes(array $attributes): array
    {
        $allowed = [
            'preferred_locale',
            'preferred_phone',
            'alternate_phone',
            'allow_sms',
            'allow_general_sms',
            'allow_finance_sms',
            'allow_reminder_sms',
            'allow_announcement_sms',
            'allow_automated_sms',
            'allow_manual_sms',
            'is_phone_verified',
            'phone_verified_at',
            'opted_out_at',
            'opt_out_reason',
            'last_message_sent_at',
            'notes',
        ];

        return Arr::only($attributes, $allowed);
    }

    protected function defaultAttributes(): array
    {
        return [
            'preferred_locale' => app()->getLocale(),
            'allow_sms' => true,
            'allow_general_sms' => true,
            'allow_finance_sms' => true,
            'allow_reminder_sms' => true,
            'allow_announcement_sms' => true,
            'allow_automated_sms' => true,
            'allow_manual_sms' => true,
            'is_phone_verified' => false,
        ];
    }
}
