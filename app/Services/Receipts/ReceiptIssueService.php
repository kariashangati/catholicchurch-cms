<?php

namespace App\Services\Receipts;

use App\Models\ReceiptActionLog;
use App\Models\ReceiptIssue;
use App\Models\User;
use App\Support\Receipts\ReceiptStatuses;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReceiptIssueService
{
    public function __construct(
        protected ReceiptQueryService $queryService,
        protected ReceiptNumberService $numberService,
        protected ReceiptPdfService $pdfService,
        protected ReceiptSmsService $smsService,
    ) {
    }

    public function issueFromFilters(array $filters, User $user): array
    {
        $groups = $this->queryService->groupedRowsForIssue($filters, $user);

        return DB::transaction(function () use ($groups, $filters, $user) {
            $receipts = collect();
            $batchReference = 'RB-' . now()->format('YmdHis') . '-' . $user->id;

            foreach ($groups as $group) {
                $receipts->push($this->createReceipt($group, $filters, $user, $batchReference));
            }

            $pdfPath = $receipts->isNotEmpty()
                ? $this->pdfService->renderBatch($receipts, $batchReference)
                : null;

            if ($pdfPath) {
                ReceiptIssue::query()->whereIn('id', $receipts->pluck('id'))->update([
                    'pdf_path' => $pdfPath,
                    'status' => ReceiptStatuses::PRINTED,
                    'printed_at' => now(),
                    'last_printed_at' => now(),
                    'printed_by' => $user->id,
                    'last_printed_by' => $user->id,
                    'print_count' => 1,
                ]);

                $receipts = ReceiptIssue::query()
                    ->with(['member', 'jumuiya', 'kanda', 'contributionType'])
                    ->whereIn('id', $receipts->pluck('id'))
                    ->get();
            }

            if (! empty($filters['send_sms'])) {
                $receipts->each(fn (ReceiptIssue $receipt) => $this->smsService->send($receipt->fresh(['member']), $user));
            }

            return ['receipts' => $receipts, 'pdf_path' => $pdfPath, 'batch_reference' => $batchReference];
        });
    }

    protected function createReceipt(array $group, array $filters, User $user, string $batchReference): ReceiptIssue
    {
        $year = (int) ($filters['year'] ?? now()->year);
        $month = ! empty($filters['month']) ? (int) $filters['month'] : null;
        $number = $this->numberService->make($group['receipt_type'], $year, $month);
        $verificationCode = $this->numberService->verificationCode();
        $token = $this->numberService->accessToken();

        $receipt = ReceiptIssue::query()->create([
            'receipt_no' => $number,
            'verification_code' => $verificationCode,
            'access_token' => $token,
            'access_expires_at' => now()->addDays(30),
            'access_token_expires_at' => now()->addDays(30),
            'receipt_type' => $group['receipt_type'],
            'receipt_layout' => $group['receipt_layout'],
            'source_type' => count($group['items'] ?? []) === 1 ? $group['items'][0]['source_type'] : $group['source_type'],
            'source_id' => count($group['items'] ?? []) === 1 ? $group['items'][0]['source_id'] : null,
            'member_id' => $group['member_id'],
            'familia_id' => $group['familia_id'],
            'jumuiya_id' => $group['jumuiya_id'],
            'kanda_id' => $group['kanda_id'],
            'contribution_type_id' => $group['contribution_type_id'],
            'period_month' => $month,
            'period_year' => $year,
            'amount' => round((float) $group['amount'], 2),
            'currency' => 'TZS',
            'status' => ReceiptStatuses::ISSUED,
            'delivery_status' => 'pending',
            'issued_by' => $user->id,
            'issued_at' => now(),
            'batch_reference' => $batchReference,
            'meta' => [
                'recipient_name' => $group['recipient_name'],
                'phone' => $group['phone'],
                'source_items' => $group['items'],
                'filters' => $filters,
            ],
        ]);

        ReceiptActionLog::query()->create([
            'receipt_issue_id' => $receipt->id,
            'action' => 'issued',
            'description' => db_trans('receipt_issued'),
            'performed_by' => $user->id,
            'acted_at' => now(),
            'meta' => ['batch_reference' => $batchReference],
        ]);

        return $receipt->fresh(['member', 'jumuiya', 'kanda', 'contributionType']);
    }

    public function markPrinted(ReceiptIssue $receipt, User $user, bool $isDownload = false): ReceiptIssue
    {
        $status = $isDownload ? ReceiptStatuses::DOWNLOADED : ReceiptStatuses::PRINTED;
        $receipt->forceFill([
            'status' => $status,
            'printed_at' => $receipt->printed_at ?: now(),
            'last_printed_at' => now(),
            'printed_by' => $receipt->printed_by ?: $user->id,
            'last_printed_by' => $user->id,
            'print_count' => (int) ($receipt->print_count ?? 0) + 1,
            'downloaded_at' => $isDownload ? now() : $receipt->downloaded_at,
            'first_downloaded_at' => $isDownload ? ($receipt->first_downloaded_at ?: now()) : $receipt->first_downloaded_at,
            'last_downloaded_at' => $isDownload ? now() : $receipt->last_downloaded_at,
            'download_count' => $isDownload ? ((int) ($receipt->download_count ?? 0) + 1) : (int) ($receipt->download_count ?? 0),
        ])->save();

        ReceiptActionLog::query()->create([
            'receipt_issue_id' => $receipt->id,
            'action' => $isDownload ? 'downloaded' : 'printed',
            'description' => $isDownload ? db_trans('receipt_downloaded') : db_trans('receipt_printed'),
            'performed_by' => $user->id,
            'acted_at' => now(),
        ]);

        return $receipt->fresh(['member', 'jumuiya', 'kanda', 'contributionType']);
    }
}
