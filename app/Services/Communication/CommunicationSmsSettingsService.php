<?php

namespace App\Services\Communication;

use App\Models\CommunicationSmsSetting;
use Illuminate\Support\Facades\Schema;

class CommunicationSmsSettingsService
{
    public function current(): array
    {
        $defaults = [
            'provider' => 'beem',
            'sender_id' => config('communication_center.sms.beem.sender_id', 'PAROKIANI'),
            'segment_length' => 160,
            'unicode_segment_length' => 70,
            'sms_unit_price' => 40.00,
            'currency' => 'TZS',
            'is_active' => true,
            'notes' => null,
        ];

        if (! Schema::hasTable('communication_sms_settings')) {
            return $defaults;
        }

        $record = CommunicationSmsSetting::query()
            ->where('is_active', true)
            ->latest('id')
            ->first();

        if (! $record) {
            return $defaults;
        }

        return array_merge($defaults, [
            'provider' => $record->provider ?: $defaults['provider'],
            'sender_id' => $record->sender_id ?: $defaults['sender_id'],
            'segment_length' => (int) ($record->segment_length ?: $defaults['segment_length']),
            'unicode_segment_length' => (int) ($record->unicode_segment_length ?: $defaults['unicode_segment_length']),
            'sms_unit_price' => (float) ($record->sms_unit_price ?: $defaults['sms_unit_price']),
            'currency' => $record->currency ?: $defaults['currency'],
            'is_active' => (bool) $record->is_active,
            'notes' => $record->notes,
        ]);
    }

    public function smsUnitPrice(): float
    {
        return (float) ($this->current()['sms_unit_price'] ?? 40.00);
    }

    public function currency(): string
    {
        return (string) ($this->current()['currency'] ?? 'TZS');
    }

    public function save(array $payload, ?int $userId = null): CommunicationSmsSetting
    {
        if (! Schema::hasTable('communication_sms_settings')) {
            throw new \RuntimeException('communication_sms_settings table is missing. Run the SQL file included in this package first.');
        }

        CommunicationSmsSetting::query()->update(['is_active' => false]);

        return CommunicationSmsSetting::query()->create([
            'provider' => $payload['provider'] ?? 'beem',
            'sender_id' => $payload['sender_id'] ?? null,
            'segment_length' => (int) ($payload['segment_length'] ?? 160),
            'unicode_segment_length' => (int) ($payload['unicode_segment_length'] ?? 70),
            'sms_unit_price' => (float) ($payload['sms_unit_price'] ?? 40),
            'currency' => $payload['currency'] ?? 'TZS',
            'is_active' => true,
            'notes' => $payload['notes'] ?? null,
            'updated_by' => $userId,
        ]);
    }
}
