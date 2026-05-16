<?php

namespace App\Services\Receipts;

use App\Models\ReceiptIssue;

class ReceiptViewBuilder
{
    public function buildPreviewViewData(array $preview): array
    {
        return [
            'receipt_no' => $preview['generated_number'],
            'receipt_type' => $preview['type'],
            'receipt_layout' => $preview['layout'],
            'source' => $preview['source'],
            'totals' => $preview['totals'],
            'filters' => $preview['filters'] ?? [],
        ];
    }

    public function buildDetailViewData(ReceiptIssue $receipt): array
    {
        return [
            'title' => $receipt->receipt_no,
            'status' => $receipt->status,
            'amount' => $receipt->amount,
            'currency' => $receipt->currency,
            'meta' => $receipt->meta ?? [],
        ];
    }

    public function buildPdfViewData(ReceiptIssue $receipt): array
    {
        return [
            'receipt' => $receipt,
            'meta' => $receipt->meta ?? [],
            'verification' => [
                'receipt_no' => $receipt->receipt_no,
                'verification_code' => $receipt->verification_code,
            ],
        ];
    }

    public function buildStoredMeta(array $preview): array
    {
        return [
            'source' => $preview['source'],
            'totals' => $preview['totals'],
            'filters' => $preview['filters'] ?? [],
            'preview_number' => $preview['generated_number'] ?? null,
        ];
    }
}
