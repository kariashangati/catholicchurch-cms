<?php

namespace App\Support\Receipts;

class ReceiptStatuses
{
    public const PENDING_ISSUE = 'inasubiri_kuchapishwa';
    public const ISSUED = 'imetolewa';
    public const PRINTED = 'imechapishwa';
    public const SMS_SENT = 'sms_imetumwa';
    public const OPENED = 'imefunguliwa';
    public const DOWNLOADED = 'imepakuliwa';
    public const REPRINTED = 'imechapishwa_tena';
    public const VOIDED = 'imebatilishwa';
    public const EXCEPTION = 'hitilafu';

    public static function values(): array
    {
        return [
            self::PENDING_ISSUE,
            self::ISSUED,
            self::PRINTED,
            self::SMS_SENT,
            self::OPENED,
            self::DOWNLOADED,
            self::REPRINTED,
            self::VOIDED,
            self::EXCEPTION,
        ];
    }

    public static function normalize(?string $status): string
    {
        return match ($status) {
            'pending', 'pending_issue', self::PENDING_ISSUE => self::PENDING_ISSUE,
            'issued', self::ISSUED => self::ISSUED,
            'printed', self::PRINTED => self::PRINTED,
            'sms_sent', self::SMS_SENT => self::SMS_SENT,
            'opened', self::OPENED => self::OPENED,
            'downloaded', self::DOWNLOADED => self::DOWNLOADED,
            'reprinted', self::REPRINTED => self::REPRINTED,
            'voided', 'void', self::VOIDED => self::VOIDED,
            'exception', self::EXCEPTION => self::EXCEPTION,
            default => self::PENDING_ISSUE,
        };
    }
}
