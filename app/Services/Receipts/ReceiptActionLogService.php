<?php

namespace App\Services\Receipts;

use App\Models\ReceiptActionLog;
use App\Models\ReceiptIssue;
use App\Models\User;

class ReceiptActionLogService
{
    public function log(ReceiptIssue $receipt, string $action, array $payload = [], ?User $actor = null): void
    {
        if (!class_exists(ReceiptActionLog::class)) {
            return;
        }

        ReceiptActionLog::query()->create([
            'receipt_issue_id' => $receipt->getKey(),
            'action' => $action,
            'payload' => $payload,
            'performed_by' => $actor?->getKey(),
            'performed_at' => now(),
        ]);
    }
}
