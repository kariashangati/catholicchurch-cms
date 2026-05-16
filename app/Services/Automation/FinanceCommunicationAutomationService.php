<?php

namespace App\Services\Communication\Automation;

use App\Models\CommunicationAutomation;
use App\Models\CommunicationCampaign;
use App\Models\CommunicationMessage;
use App\Models\CommunicationPreference;
use App\Models\CommunicationTemplate;
use App\Models\Member;
use App\Services\Communication\Support\PhoneNumberService;
use App\Services\Communication\SmsTemplateRenderService;
use App\Services\Communication\Support\SmsSegmentCalculatorService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class FinanceCommunicationAutomationService
{
    public function __construct(
        protected SmsTemplateRenderService $templateRenderer,
        protected SmsSegmentCalculatorService $segmentCalculator,
        protected PhoneNumberService $phoneNumberService,
    ) {
    }

    public function handleEvent(string $eventKey, mixed $record): ?CommunicationCampaign
    {
        $automation = CommunicationAutomation::query()
            ->where('event_key', $eventKey)
            ->where('channel', 'sms')
            ->where('is_enabled', true)
            ->first();

        if (! $automation) {
            return null;
        }

        if (! $automation->template_id) {
            Log::warning('Communication automation missing template.', [
                'automation_id' => $automation->id,
                'event_key' => $eventKey,
            ]);

            return null;
        }

        $member = $this->resolveMember($record);
        if (! $member) {
            Log::warning('Finance automation could not resolve member.', [
                'event_key' => $eventKey,
                'record_class' => is_object($record) ? get_class($record) : gettype($record),
            ]);

            return null;
        }

        if (! $this->memberAllowsSms($member, $automation)) {
            return null;
        }

        $template = CommunicationTemplate::query()
            ->whereKey($automation->template_id)
            ->where('channel', 'sms')
            ->first();

        if (! $template || ! $template->body) {
            return null;
        }

        $payload = $this->buildPayload($member, $record, $eventKey);
        $messageBody = $this->templateRenderer->render($template->body, $payload);
        $segmentData = $this->segmentCalculator->calculate($messageBody);
        $phone = $this->resolvePhone($member);

        if (! $phone) {
            return null;
        }

        return DB::transaction(function () use ($automation, $template, $member, $record, $eventKey, $messageBody, $segmentData, $phone) {
            $campaign = CommunicationCampaign::create([
                'title' => $this->buildCampaignTitle($automation->name, $member),
                'type' => 'automation',
                'channel' => 'sms',
                'template_id' => $template->id,
                'automation_id' => $automation->id,
                'source_event_key' => $eventKey,
                'audience_type' => 'member',
                'audience_filters' => [
                    'member_id' => $member->id,
                    'finance_record_type' => class_basename($record),
                    'finance_record_id' => $record->id ?? null,
                ],
                'message_body' => $messageBody,
                'render_locale' => $this->resolveLocale($member),
                'status' => $automation->trigger_mode === 'scheduled' ? 'scheduled' : 'processing',
                'scheduled_at' => $automation->trigger_mode === 'scheduled' && $automation->delay_minutes
                    ? now()->addMinutes((int) $automation->delay_minutes)
                    : null,
                'created_by' => $automation->created_by,
                'total_recipients' => 1,
                'valid_recipients' => 1,
                'invalid_recipients' => 0,
                'total_messages' => 1,
                'total_segments' => (int) $segmentData['segments'],
                'estimated_cost_units' => (int) $segmentData['segments'],
                'actual_cost_units' => 0,
            ]);

            CommunicationMessage::create([
                'campaign_id' => $campaign->id,
                'template_id' => $template->id,
                'member_id' => $member->id,
                'familia_id' => $member->familia_id ?? null,
                'jumuiya_id' => $member->jumuiya_id ?? null,
                'kanda_id' => $member->kanda_id ?? null,
                'recipient_name' => $this->memberName($member),
                'recipient_phone' => $phone,
                'recipient_phone_normalized' => $phone,
                'recipient_type' => 'member',
                'locale' => $this->resolveLocale($member),
                'message_body' => $messageBody,
                'segment_count' => (int) $segmentData['segments'],
                'status' => $automation->trigger_mode === 'scheduled' ? 'pending' : 'queued',
                'delivery_status' => 'pending',
                'provider' => 'beem',
                'meta' => [
                    'event_key' => $eventKey,
                    'finance_record_type' => class_basename($record),
                    'finance_record_id' => $record->id ?? null,
                    'render_payload' => $payload ?? [],
                ],
                'queued_at' => $automation->trigger_mode === 'scheduled' ? null : now(),
            ]);

            return $campaign;
        });
    }

    protected function resolveMember(mixed $record): ?Member
    {
        if (! is_object($record)) {
            return null;
        }

        if ($record instanceof Member) {
            return $record;
        }

        $memberId = $record->member_id
            ?? $record->mwanajumuiya_id
            ?? $record->mwanajumuiya
            ?? null;

        if ($memberId) {
            return Member::find($memberId);
        }

        if (method_exists($record, 'member')) {
            try {
                return $record->member;
            } catch (Throwable) {
                return null;
            }
        }

        return null;
    }

    protected function buildPayload(Member $member, mixed $record, string $eventKey): array
    {
        $amount = $record->amount
            ?? $record->kiasi
            ?? $record->total_amount
            ?? 0;

        $date = $record->contribution_date
            ?? $record->tarehe
            ?? $record->created_at
            ?? now();

        return [
            'member_name' => $this->memberName($member),
            'first_name' => trim((string) ($member->first_name ?? Arr::first(explode(' ', $this->memberName($member))))),
            'amount' => number_format((float) $amount, 0),
            'amount_raw' => $amount,
            'event_key' => $eventKey,
            'date' => optional($date)->format ? $date->format('Y-m-d') : (string) $date,
            'month_name' => optional($date)->format ? $date->format('F') : now()->format('F'),
            'familia_name' => $member->familia->name ?? $member->familia->family_name ?? '',
            'jumuiya_name' => $member->jumuiya->name ?? '',
            'kanda_name' => $member->kanda->name ?? '',
            'parish_name' => config('app.name', 'Parokia'),
            'phone' => $member->phone ?? '',
        ];
    }

    protected function memberAllowsSms(Member $member, CommunicationAutomation $automation): bool
    {
        /** @var CommunicationPreference|null $preference */
        $preference = $member->communicationPreference ?? null;

        if (! $automation->respect_preferences) {
            return true;
        }

        if (! $preference) {
            return true;
        }

        if (! $preference->allow_sms) {
            return false;
        }

        if ($preference->opted_out_at) {
            return false;
        }

        if (! $preference->allow_automated_sms) {
            return false;
        }

        $eventKey = (string) $automation->event_key;
        if (str_contains($eventKey, 'tithe') || str_contains($eventKey, 'offering') || str_contains($eventKey, 'contribution')) {
            return (bool) $preference->allow_finance_sms;
        }

        return true;
    }

    protected function resolvePhone(Member $member): ?string
    {
        $preferencePhone = $member->communicationPreference->preferred_phone ?? null;
        $raw = $preferencePhone ?: ($member->phone ?? $member->mawasiliano ?? null);

        if (! $raw) {
            return null;
        }

        return $this->phoneNumberService->normalizeTanzanianNumber($raw);
    }

    protected function resolveLocale(Member $member): string
    {
        return $member->communicationPreference->preferred_locale
            ?? $member->locale
            ?? app()->getLocale()
            ?? 'en';
    }

    protected function memberName(Member $member): string
    {
        return trim((string) (
            $member->full_name
            ?? $member->name
            ?? collect([$member->first_name ?? null, $member->middle_name ?? null, $member->last_name ?? null])->filter()->implode(' ')
        ));
    }

    protected function buildCampaignTitle(string $automationName, Member $member): string
    {
        return sprintf('%s - %s - %s', $automationName, $this->memberName($member), now()->format('Y-m-d H:i'));
    }
}
