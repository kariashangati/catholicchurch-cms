<?php

namespace App\Services\Communication;

use App\Models\CommunicationBalanceSnapshot;
use App\Models\CommunicationBalanceTransaction;
use Illuminate\Support\Facades\DB;

class CommunicationSmsBalanceService
{
    public function currentBalance(): float
    {
        return (float) (CommunicationBalanceSnapshot::query()
            ->latest('fetched_at')
            ->value('balance_units') ?? 0);
    }

    public function hasEnough(float $cost): bool
    {
        if ($cost <= 0) {
            return true;
        }

        return $this->currentBalance() >= $cost;
    }

    public function debit(float $cost, array $meta = []): void
    {
        if ($cost <= 0) {
            return;
        }

        DB::transaction(function () use ($cost, $meta) {
            $latest = CommunicationBalanceSnapshot::query()
                ->lockForUpdate()
                ->latest('fetched_at')
                ->first();

            $before = $latest ? (float) $latest->balance_units : 0.0;
            $after = max(0, $before - $cost);
            $provider = (string) ($meta['provider'] ?? ($latest?->provider ?: 'beem'));
            $currency = (string) ($meta['currency'] ?? ($latest?->currency ?: 'TZS'));

            CommunicationBalanceTransaction::query()->create([
                'provider' => $provider,
                'transaction_type' => CommunicationBalanceTransaction::TYPE_DEBIT,
                'reference_type' => $meta['reference_type'] ?? 'sms',
                'reference_id' => $meta['reference_id'] ?? null,
                'campaign_id' => $meta['campaign_id'] ?? null,
                'amount_units' => -1 * $cost,
                'balance_before' => $before,
                'balance_after' => $after,
                'description' => $meta['description'] ?? ('SMS debit - ' . number_format($cost, 2) . ' ' . $currency),
                'performed_by' => $meta['performed_by'] ?? auth()->id(),
            ]);

            CommunicationBalanceSnapshot::query()->create([
                'provider' => $provider,
                'balance_units' => $after,
                'currency' => $currency,
                'raw_response' => [
                    'source' => 'local_sms_debit',
                    'balance_before' => $before,
                    'debit' => $cost,
                    'balance_after' => $after,
                    'meta' => $meta,
                ],
                'fetched_at' => now(),
                'created_by' => $meta['performed_by'] ?? auth()->id(),
            ]);
        });
    }
}
