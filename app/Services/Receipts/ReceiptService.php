<?php

namespace App\Services\Receipts;

use App\Models\ReceiptActionLog;
use App\Models\ReceiptIssue;
use App\Support\Receipts\ReceiptLayouts;
use App\Support\Receipts\ReceiptStatuses;
use App\Support\Receipts\ReceiptTypes;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReceiptService
{
    public function __construct(
        protected ReceiptNumberService $receiptNumberService,
        protected ReceiptSourceResolverService $receiptSourceResolverService,
        protected ReceiptViewBuilder $receiptViewBuilder,
    ) {
    }

    public function supportedReceiptTypes(): array
    {
        return ReceiptTypes::options();
    }

    public function supportedLayouts(): array
    {
        return ReceiptLayouts::options();
    }

    public function buildPreview(array $payload, ?Authenticatable $user = null): array
    {
        $this->validatePayload($payload);

        $source = $this->receiptSourceResolverService->resolveForReceipt($payload, $user);

        return [
            'type' => $payload['receipt_type'],
            'layout' => $payload['receipt_layout'],
            'source' => $source,
            'totals' => $this->calculateTotals($source),
            'generated_number' => $this->receiptNumberService->previewNumber($payload['receipt_type']),
            'requested_by' => $user,
            'filters' => Arr::only($payload, [
                'member_id', 'familia_id', 'jumuiya_id', 'kanda_id', 'date_from', 'date_to', 'month', 'year', 'source_type',
            ]),
        ];
    }

    public function issue(array $payload, ?Authenticatable $user = null): ReceiptIssue
    {
        $preview = $this->buildPreview($payload, $user);

        return DB::transaction(function () use ($payload, $preview, $user): ReceiptIssue {
            /** @var ReceiptIssue $receipt */
            $receipt = ReceiptIssue::query()->create([
                'receipt_no' => $this->receiptNumberService->generate($payload['receipt_type']),
                'receipt_type' => $payload['receipt_type'],
                'receipt_layout' => $payload['receipt_layout'],
                'source_type' => $payload['source_type'],
                'source_id' => $preview['source']['source_id'] ?? null,
                'member_id' => $preview['source']['member_id'] ?? null,
                'familia_id' => $preview['source']['familia_id'] ?? null,
                'jumuiya_id' => $preview['source']['jumuiya_id'] ?? null,
                'kanda_id' => $preview['source']['kanda_id'] ?? null,
                'amount' => $preview['totals']['amount'] ?? 0,
                'currency' => $payload['currency'] ?? 'TZS',
                'status' => ReceiptStatuses::ISSUED,
                'issued_at' => now(),
                'issued_by' => $user?->getAuthIdentifier(),
                'meta' => $this->receiptViewBuilder->buildStoredMeta($preview),
            ]);

            ReceiptActionLog::query()->create([
                'receipt_issue_id' => $receipt->id,
                'action' => 'issued',
                'description' => db_trans('receipts.logs.issued'),
                'performed_by' => $user?->getAuthIdentifier(),
                'meta' => [
                    'receipt_type' => $receipt->receipt_type,
                    'receipt_layout' => $receipt->receipt_layout,
                    'source_type' => $receipt->source_type,
                ],
            ]);

            return $receipt->fresh();
        });
    }

    protected function validatePayload(array $payload): void
    {
        if (! in_array($payload['receipt_type'] ?? null, ReceiptTypes::values(), true)) {
            throw new InvalidArgumentException('Unsupported receipt type provided.');
        }

        if (! in_array($payload['receipt_layout'] ?? null, ReceiptLayouts::values(), true)) {
            throw new InvalidArgumentException('Unsupported receipt layout provided.');
        }
    }

    protected function calculateTotals(array $source): array
    {
        $rows = $source['rows'] ?? [];
        $amount = collect($rows)->sum(fn ($row) => (float) ($row['amount'] ?? 0));

        return [
            'amount' => $amount,
            'records' => count($rows),
        ];
    }
}
