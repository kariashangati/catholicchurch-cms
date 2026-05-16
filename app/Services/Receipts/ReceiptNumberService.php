<?php

namespace App\Services\Receipts;

use App\Models\ReceiptIssue;
use Illuminate\Support\Str;

class ReceiptNumberService
{
    public function make(string $type, int $year, ?int $month = null): string
    {
        $prefix = match ($type) {
            'zaka' => 'ZK',
            'mchango' => 'MC',
            default => 'RC',
        };

        $period = $month ? sprintf('%04d%02d', $year, $month) : (string) $year;

        do {
            $number = $prefix . '-' . $period . '-' . strtoupper(Str::random(6));
        } while (ReceiptIssue::query()->where('receipt_no', $number)->exists());

        return $number;
    }

    public function verificationCode(): string
    {
        return strtoupper(Str::random(8));
    }

    public function accessToken(): string
    {
        return Str::random(80);
    }
}
