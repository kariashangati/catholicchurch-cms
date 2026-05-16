<?php

namespace App\Services\Receipts;

use App\Models\ReceiptExceptionLog;
use App\Models\ReceiptIssue;
use Throwable;

class ReceiptExceptionService
{
    public function record(?ReceiptIssue $receipt, string $type, Throwable $throwable, array $context = []): void
    {
        if (!class_exists(ReceiptExceptionLog::class)) {
            report($throwable);
            return;
        }

        ReceiptExceptionLog::query()->create([
            'receipt_issue_id' => $receipt?->getKey(),
            'exception_type' => $type,
            'message' => $throwable->getMessage(),
            'context' => $context,
            'happened_at' => now(),
        ]);

        report($throwable);
    }
}
