<?php

namespace App\Services\Communication;

use App\Models\CommunicationAutomation;
use App\Models\CommunicationCampaign;
use App\Models\CommunicationMessage;
use App\Models\CommunicationTemplate;
use App\Models\Member;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GeneralAutomationExecutionService
{
    public function __construct(
        protected CommunicationPreferenceService $preferenceService,
        protected SmsTemplateRenderService $templateRenderer,
        protected RecipientResolverService $recipientResolver,
    ) {
    }

    public function handle(string $eventKey, array $context = []): ?CommunicationCampaign
    {
        $automation = CommunicationAutomation::query()
            ->where('event_key', $eventKey)
            ->where('is_enabled', true)
            ->first();

        if (! $automation) {
            return null;
        }

        $template = CommunicationTemplate::find($automation->template_id);

        if (! $template || $template->status !== 'active') {
            return null;
        }

        $members = $this->resolveMembers($automation, $context);
        if ($members->isEmpty()) {
            return null;
        }

        return DB::transaction(function () use ($automation, $template, $members, $eventKey, $context) {
            $campaign = CommunicationCampaign::create([
                'title' => $automation->name,
                'type' => 'automation',
                'channel' => 'sms',
                'template_id' => $template->id,
                'automation_id' => $automation->id,
                'source_event_key' => $eventKey,
                'audience_type' => $automation->audience_type ?: 'member',
                'message_body' => $template->body,
                'status' => $automation->trigger_mode === 'scheduled' ? 'scheduled' : 'processing',
                'scheduled_at' => $automation->trigger_mode === 'scheduled' && !empty($automation->delay_minutes)
                    ? now()->addMinutes((int) $automation->delay_minutes)
                    : null,
                'render_locale' => $template->locale,
                'total_recipients' => $members->count(),
                'valid_recipients' => 0,
                'invalid_recipients' => 0,
                'notes' => 'Generated automatically from event: '.$eventKey,
                'created_by' => null,
            ]);

            $valid = 0;
            $invalid = 0;

            foreach ($members as $member) {
                if (! $member instanceof Member) {
                    $invalid++;
                    continue;
                }

                if (! $this->preferenceService->canReceiveAutomatedSms($member, $eventKey)) {
                    $invalid++;
                    continue;
                }

                $phone = $this->preferenceService->resolvePreferredPhone($member);
                if (! $phone) {
                    $invalid++;
                    continue;
                }

                $payload = $this->buildTemplatePayload($member, $context);
                $rendered = $this->templateRenderer->render($template->body, $payload);

                CommunicationMessage::create([
                    'campaign_id' => $campaign->id,
                    'template_id' => $template->id,
                    'member_id' => $member->id,
                    'familia_id' => $member->familia_id ?? null,
                    'jumuiya_id' => $member->jumuiya_id ?? null,
                    'kanda_id' => $member->kanda_id ?? null,
                    'recipient_name' => $member->name ?? trim(($member->first_name ?? '').' '.($member->last_name ?? '')),
                    'recipient_phone' => $phone,
                    'recipient_phone_normalized' => $phone,
                    'recipient_type' => 'member',
                    'locale' => $template->locale,
                    'message_body' => $rendered,
                    'segment_count' => 1,
                    'status' => $automation->trigger_mode === 'scheduled' ? 'pending' : 'queued',
                    'delivery_status' => 'pending',
                    'provider' => 'beem',
                    'meta' => [
                        'event_key' => $eventKey,
                        'context' => Arr::except($context, ['member', 'assignment', 'membership', 'announcement']),
                    ],
                    'queued_at' => $automation->trigger_mode === 'scheduled' ? null : now(),
                ]);

                $valid++;
            }

            $campaign->update([
                'valid_recipients' => $valid,
                'invalid_recipients' => $invalid,
                'total_messages' => $valid,
                'total_segments' => $valid,
            ]);

            return $campaign;
        });
    }

    protected function resolveMembers(CommunicationAutomation $automation, array $context)
    {
        return match ($automation->event_key) {
            'member.created', 'member.updated', 'member.phone_changed' => collect(array_filter([$context['member'] ?? null])),
            'leadership.assigned' => collect(array_filter([$context['assignment']?->member ?? null])),
            'apostolic_group.joined' => collect(array_filter([$context['membership']?->member ?? null])),
            'announcement.published' => $this->resolveAnnouncementAudience($automation, $context),
            default => collect(),
        };
    }

    protected function resolveAnnouncementAudience(CommunicationAutomation $automation, array $context)
    {
        $filters = is_array($automation->conditions) ? $automation->conditions : [];
        $audience = $filters['audience'] ?? ['audience_type' => 'all_members'];

        return collect($this->recipientResolver->resolve($audience))
            ->pluck('member_id')
            ->filter()
            ->unique()
            ->values()
            ->pipe(fn ($ids) => Member::query()->whereIn('id', $ids)->get());
    }

    protected function buildTemplatePayload(Member $member, array $context): array
    {
        return [
            'member_name' => $member->name ?? trim(($member->first_name ?? '').' '.($member->last_name ?? '')),
            'first_name' => $member->first_name ?? $member->name ?? 'Member',
            'phone' => $this->preferenceService->resolvePreferredPhone($member),
            'familia_name' => $member->familia->name ?? '',
            'jumuiya_name' => $member->jumuiya->name ?? '',
            'kanda_name' => $member->kanda->name ?? '',
            'announcement_title' => $context['announcement']?->title ?? '',
            'leadership_position' => $context['assignment']?->position?->name ?? '',
            'apostolic_group_name' => $context['membership']?->apostolicGroup?->name ?? '',
            'today_date' => now()->format('Y-m-d'),
        ];
    }
}
