<?php

namespace App\Services\Finance;

use App\Models\BankAccount;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BankAccountService
{
    public function indexData(): array
    {
        return [
            'bankAccounts' => $this->accounts(),
            'statuses' => BankAccount::statuses(),
        ];
    }

    public function exportData(): array
    {
        $accounts = $this->accounts();

        return [
            'pageTitle' => db_trans('bank_accounts'),
            'reportTitle' => db_trans('bank_accounts'),
            'sectionTitle' => db_trans('bank_accounts'),
            'metaItems' => [
                ['label' => db_trans('records'), 'value' => number_format($accounts->count())],
                ['label' => db_trans('active'), 'value' => number_format($accounts->where('is_active', true)->count())],
                ['label' => db_trans('inactive'), 'value' => number_format($accounts->where('is_active', false)->count())],
                ['label' => db_trans('generated_on'), 'value' => now()->translatedFormat('d F Y')],
            ],
            'columns' => $this->exportColumns(),
            'rows' => $this->exportBodyRows($accounts),
            'issuedAtText' => now()->translatedFormat('d F Y'),
            'locale' => app()->getLocale(),
        ];
    }

    public function exportRows(): array
    {
        return array_merge(
            [$this->exportColumns()],
            $this->exportBodyRows($this->accounts())
        );
    }

    public function create(array $data): BankAccount
    {
        return DB::transaction(function () use ($data) {
            return BankAccount::create($this->payload($data));
        });
    }

    public function update(BankAccount $bankAccount, array $data): BankAccount
    {
        return DB::transaction(function () use ($bankAccount, $data) {
            $bankAccount->update($this->payload($data));

            return $bankAccount->fresh();
        });
    }

    public function delete(BankAccount $bankAccount): void
    {
        DB::transaction(function () use ($bankAccount) {
            if ($bankAccount->bankContributions()->exists()) {
                throw new \RuntimeException('This bank account cannot be deleted because contributions are already linked to it.');
            }

            $bankAccount->delete();
        });
    }

    protected function accounts(): Collection
    {
        return BankAccount::query()
            ->latest()
            ->get();
    }

    protected function exportColumns(): array
    {
        return [
            '#',
            db_trans('bank_name'),
            db_trans('account_name'),
            db_trans('account_number'),
            db_trans('branch_name'),
            db_trans('status'),
            db_trans('active'),
        ];
    }

    protected function exportBodyRows(Collection $accounts): array
    {
        return $accounts
            ->values()
            ->map(function (BankAccount $account, int $index): array {
                $normalizedStatus = BankAccount::normalizeStatus($account->status ?? null);

                return [
                    $index + 1,
                    $account->bank_name,
                    $account->account_name,
                    $account->account_number,
                    $account->branch_name ?: '—',
                    db_trans($normalizedStatus),
                    $account->is_active ? db_trans('yes') : db_trans('no'),
                ];
            })
            ->all();
    }

    protected function payload(array $data): array
    {
        return Arr::only([
            'bank_name' => trim((string) ($data['bank_name'] ?? '')),
            'account_name' => trim((string) ($data['account_name'] ?? '')),
            'account_number' => preg_replace('/\s+/', '', (string) ($data['account_number'] ?? '')),
            'branch_name' => filled($data['branch_name'] ?? null) ? trim((string) $data['branch_name']) : null,
            'status' => BankAccount::normalizeStatus($data['status'] ?? BankAccount::STATUS_ACTIVE),
            'description' => filled($data['description'] ?? null) ? trim((string) $data['description']) : null,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ], [
            'bank_name',
            'account_name',
            'account_number',
            'branch_name',
            'status',
            'description',
            'is_active',
        ]);
    }
}
