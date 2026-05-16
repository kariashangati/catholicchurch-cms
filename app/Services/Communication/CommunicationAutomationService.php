<?php

namespace App\Services\Communication;

use App\Models\CommunicationAutomation;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CommunicationAutomationService
{
    public function create(array $data, User $user): CommunicationAutomation
    {
        return CommunicationAutomation::create([
            'name' => $data['name'],
            'code' => Str::slug($data['code'], '_'),
            'event_key' => $data['event_key'],
            'channel' => $data['channel'],
            'template_id' => $data['template_id'] ?? null,
            'is_enabled' => (bool) ($data['is_enabled'] ?? false),
            'trigger_mode' => $data['trigger_mode'],
            'delay_minutes' => $this->normalizeDelay($data),
            'audience_type' => $data['audience_type'] ?? null,
            'conditions' => $this->sanitizeConditions($data['conditions'] ?? []),
            'respect_preferences' => (bool) ($data['respect_preferences'] ?? true),
            'respect_quiet_hours' => (bool) ($data['respect_quiet_hours'] ?? false),
            'send_once_per_entity' => (bool) ($data['send_once_per_entity'] ?? false),
            'notes' => $data['notes'] ?? null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    public function update(CommunicationAutomation $automation, array $data, User $user): CommunicationAutomation
    {
        $automation->update([
            'name' => $data['name'],
            'code' => Str::slug($data['code'], '_'),
            'event_key' => $data['event_key'],
            'channel' => $data['channel'],
            'template_id' => $data['template_id'] ?? null,
            'is_enabled' => (bool) ($data['is_enabled'] ?? false),
            'trigger_mode' => $data['trigger_mode'],
            'delay_minutes' => $this->normalizeDelay($data),
            'audience_type' => $data['audience_type'] ?? null,
            'conditions' => $this->sanitizeConditions($data['conditions'] ?? []),
            'respect_preferences' => (bool) ($data['respect_preferences'] ?? true),
            'respect_quiet_hours' => (bool) ($data['respect_quiet_hours'] ?? false),
            'send_once_per_entity' => (bool) ($data['send_once_per_entity'] ?? false),
            'notes' => $data['notes'] ?? null,
            'updated_by' => $user->id,
        ]);

        return $automation->refresh();
    }

    public function eventOptions(): array
    {
        return [
            'tithe.recorded' => db_trans('communication.automations.events.tithe_recorded'),
            'offering.recorded' => db_trans('communication.automations.events.offering_recorded'),
            'cash_contribution.recorded' => db_trans('communication.automations.events.cash_contribution_recorded'),
            'bank_contribution.approved' => db_trans('communication.automations.events.bank_contribution_approved'),
            'member.created' => db_trans('communication.automations.events.member_created'),
            'member.updated' => db_trans('communication.automations.events.member_updated'),
            'leadership.assigned' => db_trans('communication.automations.events.leadership_assigned'),
            'apostolic_group.joined' => db_trans('communication.automations.events.apostolic_group_joined'),
        ];
    }

    public function triggerModes(): array
    {
        return [
            'immediate' => db_trans('communication.automations.trigger_modes.immediate'),
            'scheduled' => db_trans('communication.automations.trigger_modes.scheduled'),
            'manual_review' => db_trans('communication.automations.trigger_modes.manual_review'),
        ];
    }

    public function audienceTypes(): array
    {
        return [
            'member' => db_trans('communication.audience.member'),
            'familia' => db_trans('communication.audience.familia'),
            'jumuiya' => db_trans('communication.audience.jumuiya'),
            'kanda' => db_trans('communication.audience.kanda'),
            'apostolic_group' => db_trans('communication.audience.apostolic_group'),
            'leadership' => db_trans('communication.audience.leadership'),
            'custom' => db_trans('communication.audience.custom'),
        ];
    }

    public function conditionSchema(?string $eventKey): array
    {
        return match ($eventKey) {
            'tithe.recorded', 'offering.recorded', 'cash_contribution.recorded', 'bank_contribution.approved' => [
                ['key' => 'requires_approval', 'label' => db_trans('communication.automations.schema.requires_approval'), 'type' => 'boolean'],
                ['key' => 'minimum_amount', 'label' => db_trans('communication.automations.schema.minimum_amount'), 'type' => 'number'],
                ['key' => 'only_active_members', 'label' => db_trans('communication.automations.schema.only_active_members'), 'type' => 'boolean'],
            ],
            'member.updated' => [
                ['key' => 'fields', 'label' => db_trans('communication.automations.schema.fields'), 'type' => 'tags'],
                ['key' => 'only_when_phone_changes', 'label' => db_trans('communication.automations.schema.only_when_phone_changes'), 'type' => 'boolean'],
            ],
            default => [
                ['key' => 'only_active_members', 'label' => db_trans('communication.automations.schema.only_active_members'), 'type' => 'boolean'],
            ],
        };
    }

    private function sanitizeConditions(array $conditions): array
    {
        $conditions = collect($conditions)
            ->filter(fn ($value) => ! ($value === null || $value === '' || $value === []))
            ->map(function ($value) {
                return is_string($value) ? trim($value) : $value;
            })
            ->toArray();

        if (Arr::get($conditions, 'fields') && is_string($conditions['fields'])) {
            $conditions['fields'] = collect(explode(',', $conditions['fields']))
                ->map(fn ($item) => trim($item))
                ->filter()
                ->values()
                ->all();
        }

        return $conditions;
    }

    private function normalizeDelay(array $data): ?int
    {
        if (($data['trigger_mode'] ?? 'immediate') !== 'scheduled') {
            return null;
        }

        return (int) ($data['delay_minutes'] ?? 0);
    }
}
