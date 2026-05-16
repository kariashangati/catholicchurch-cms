<?php

namespace App\Services\Receipts;

use App\Models\ReceiptIssue;
use App\Models\ReceiptVerificationLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class ReceiptVerificationService
{
    public function verifyByToken(string $token, array $context = []): array
    {
        $token = $this->cleanLookupValue($token);

        if ($token === '') {
            return $this->buildVerificationResult(null, $context, 'token');
        }

        $query = ReceiptIssue::query()->with(['member', 'jumuiya', 'kanda']);

        $query->where(function (Builder $q) use ($token): void {
            foreach (['verification_token', 'access_token', 'verification_code', 'receipt_no'] as $column) {
                if (Schema::hasColumn('receipt_issues', $column)) {
                    $this->orWhereNormalized($q, $column, $token);
                }
            }
        });

        return $this->buildVerificationResult($query->latest('id')->first(), $context, 'token');
    }

    public function verifyByReference(?string $receiptNo = null, ?string $verificationCode = null, array $context = []): array
    {
        $receiptNo = $this->cleanLookupValue($receiptNo);
        $verificationCode = $this->cleanLookupValue($verificationCode);

        if ($receiptNo === '' && $verificationCode === '') {
            return $this->buildVerificationResult(null, $context, 'manual');
        }

        // First find by receipt number only. This lets us distinguish:
        // 1) receipt missing
        // 2) receipt exists but verification code is wrong
        // 3) receipt and code match
        $receiptByNumber = null;

        if ($receiptNo !== '' && Schema::hasColumn('receipt_issues', 'receipt_no')) {
            $receiptQuery = ReceiptIssue::query()->with(['member', 'jumuiya', 'kanda']);
            $this->whereNormalized($receiptQuery, 'receipt_no', $receiptNo);
            $receiptByNumber = $receiptQuery->latest('id')->first();
        }

        if ($receiptByNumber) {
            if ($verificationCode === '') {
                return $this->buildVerificationResult($receiptByNumber, $context, 'manual');
            }

            $storedCode = $this->cleanLookupValue((string) ($receiptByNumber->verification_code ?? ''));

            if ($storedCode !== '' && $this->normalizeForSqlCompare($storedCode) === $this->normalizeForSqlCompare($verificationCode)) {
                return $this->buildVerificationResult($receiptByNumber, $context, 'manual');
            }

            $this->log($receiptByNumber, 'code_mismatch', 'manual', $context);

            return [
                'valid' => false,
                'status' => 'code_mismatch',
                'message' => db_trans('receipt_verification_code_mismatch'),
                'receipt' => $receiptByNumber,
            ];
        }

        // Fallback: if user only typed code, search by verification_code.
        if ($receiptNo === '' && $verificationCode !== '' && Schema::hasColumn('receipt_issues', 'verification_code')) {
            $codeQuery = ReceiptIssue::query()->with(['member', 'jumuiya', 'kanda']);
            $this->whereNormalized($codeQuery, 'verification_code', $verificationCode);

            return $this->buildVerificationResult($codeQuery->latest('id')->first(), $context, 'manual');
        }

        return $this->buildVerificationResult(null, $context, 'manual');
    }

    protected function buildVerificationResult(?ReceiptIssue $receipt, array $context = [], string $channel = 'manual'): array
    {
        if (! $receipt) {
            $this->log(null, 'not_found', $channel, $context);

            return [
                'valid' => false,
                'status' => 'not_found',
                'message' => db_trans('receipt_not_found'),
                'receipt' => null,
            ];
        }

        $status = $this->mapReceiptStatus($receipt);
        $valid = in_array($status, ['valid', 'reissued'], true);

        $this->log($receipt, $status, $channel, $context);

        return [
            'valid' => $valid,
            'status' => $status,
            'message' => db_trans($valid ? 'receipt_verified_successfully' : 'receipt_not_valid'),
            'receipt' => $receipt,
        ];
    }

    protected function mapReceiptStatus(ReceiptIssue $receipt): string
    {
        $status = (string) ($receipt->status ?? 'issued');

        if ((bool) ($receipt->is_void ?? false) || in_array($status, ['void', 'voided', 'cancelled', 'imebatilishwa'], true)) {
            return 'voided';
        }

        if ((int) ($receipt->reissue_count ?? 0) > 0 || ! empty($receipt->reissued_at)) {
            return 'reissued';
        }

        return 'valid';
    }

    protected function whereNormalized(Builder $query, string $column, string $value): void
    {
        $wrapped = '`' . str_replace('`', '``', $column) . '`';
        $query->whereRaw("UPPER(REPLACE(REPLACE(TRIM($wrapped), ' ', ''), CHAR(160), '')) = ?", [$this->normalizeForSqlCompare($value)]);
    }

    protected function orWhereNormalized(Builder $query, string $column, string $value): void
    {
        $wrapped = '`' . str_replace('`', '``', $column) . '`';
        $query->orWhereRaw("UPPER(REPLACE(REPLACE(TRIM($wrapped), ' ', ''), CHAR(160), '')) = ?", [$this->normalizeForSqlCompare($value)]);
    }

    protected function cleanLookupValue(mixed $value): string
    {
        return trim(str_replace("\xc2\xa0", ' ', (string) $value));
    }

    protected function normalizeForSqlCompare(string $value): string
    {
        $value = str_replace("\xc2\xa0", ' ', $value);
        $value = preg_replace('/\s+/u', '', trim($value));

        return mb_strtoupper($value ?: '', 'UTF-8');
    }

    protected function log(?ReceiptIssue $receipt, string $result, string $channel, array $context = []): void
    {
        if (! class_exists(ReceiptVerificationLog::class)) {
            return;
        }

        try {
            $payload = [
                'receipt_issue_id' => $receipt?->getKey(),
                'ip_address' => $context['ip_address'] ?? null,
                'user_agent' => $context['user_agent'] ?? null,
            ];

            if (Schema::hasColumn('receipt_verification_logs', 'result')) {
                $payload['result'] = $result;
            }

            if (Schema::hasColumn('receipt_verification_logs', 'status')) {
                $payload['status'] = $result === 'valid' || $result === 'reissued' ? 'verified' : $result;
            }

            if (Schema::hasColumn('receipt_verification_logs', 'channel')) {
                $payload['channel'] = $channel;
            }

            if (Schema::hasColumn('receipt_verification_logs', 'verified_at')) {
                $payload['verified_at'] = now();
            }

            ReceiptVerificationLog::query()->create($payload);
        } catch (\Throwable) {
            // Never break receipt verification because of log-table differences.
        }
    }
}
