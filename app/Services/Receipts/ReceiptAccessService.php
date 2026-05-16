<?php

namespace App\Services\Receipts;

use App\Models\ReceiptAccessLog;
use App\Models\ReceiptIssue;
use App\Support\Receipts\ReceiptStatuses;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReceiptAccessService
{
    public function open(string $token, Request $request): ReceiptIssue
    {
        $receipt = $this->findValid($token);

        ReceiptAccessLog::query()->create([
            'receipt_issue_id' => $receipt->id,
            'access_type' => 'opened',
            'status' => 'success',
            'access_token' => substr($token, -12),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'accessed_at' => now(),
        ]);

        $receipt->forceFill([
            'status' => ReceiptStatuses::OPENED,
            'first_opened_at' => $receipt->first_opened_at ?: now(),
            'last_opened_at' => now(),
            'first_accessed_at' => $receipt->first_accessed_at ?: now(),
            'last_accessed_at' => now(),
            'last_access_ip' => $request->ip(),
        ])->save();

        return $receipt->fresh(['member', 'jumuiya', 'kanda', 'contributionType']);
    }

    public function download(string $token, Request $request): ReceiptIssue
    {
        $receipt = $this->findValid($token);

        ReceiptAccessLog::query()->create([
            'receipt_issue_id' => $receipt->id,
            'access_type' => 'downloaded',
            'status' => 'success',
            'access_token' => substr($token, -12),
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'accessed_at' => now(),
        ]);

        $receipt->forceFill([
            'status' => ReceiptStatuses::DOWNLOADED,
            'downloaded_at' => now(),
            'first_downloaded_at' => $receipt->first_downloaded_at ?: now(),
            'last_downloaded_at' => now(),
            'download_count' => (int) ($receipt->download_count ?? 0) + 1,
        ])->save();

        return $receipt->fresh(['member', 'jumuiya', 'kanda', 'contributionType']);
    }

    protected function findValid(string $token): ReceiptIssue
    {
        $receipt = ReceiptIssue::query()->where('access_token', $token)->first();

        if (! $receipt || ! $receipt->link_enabled || $receipt->link_revoked_at) {
            throw ValidationException::withMessages(['token' => db_trans('invalid_receipt_link')]);
        }

        $expiresAt = $receipt->access_expires_at ?: $receipt->access_token_expires_at;
        if ($expiresAt && now()->greaterThan($expiresAt)) {
            throw ValidationException::withMessages(['token' => db_trans('receipt_link_expired')]);
        }

        return $receipt;
    }
}
