<?php

namespace App\Services\Communication\Support;

class PhoneNumberService
{
    public function normalize(?string $phone, ?string $countryCode = null): ?string
    {
        if (blank($phone)) {
            return null;
        }

        $countryCode ??= config('communication_center.phone.default_country_code', '255');
        $original = trim($phone);
        $clean = preg_replace('/\D+/', '', $original ?? '');

        if ($clean === '') {
            return null;
        }

        if (str_starts_with($original, '+')) {
            $clean = ltrim($original, '+');
            $clean = preg_replace('/\D+/', '', $clean ?? '');
        }

        if (str_starts_with($clean, '00')) {
            $clean = substr($clean, 2);
        }

        if (preg_match('/^0\d{9}$/', $clean)) {
            return $countryCode . substr($clean, 1);
        }

        if (preg_match('/^' . preg_quote($countryCode, '/') . '\d{9}$/', $clean)) {
            return $clean;
        }

        if (preg_match('/^\d{9}$/', $clean)) {
            return $countryCode . $clean;
        }

        return null;
    }

    public function isValid(?string $phone, ?string $countryCode = null): bool
    {
        $normalized = $this->normalize($phone, $countryCode);

        if ($normalized === null) {
            return false;
        }

        $countryCode ??= config('communication_center.phone.default_country_code', '255');

        return (bool) preg_match('/^' . preg_quote($countryCode, '/') . '\d{9}$/', $normalized);
    }

    public function uniqueNormalized(array $phones, ?string $countryCode = null): array
    {
        $normalized = [];

        foreach ($phones as $phone) {
            $value = $this->normalize((string) $phone, $countryCode);

            if ($value !== null) {
                $normalized[$value] = $value;
            }
        }

        return array_values($normalized);
    }

    public function toBeemRecipients(array $phones, ?string $countryCode = null, int $startingId = 1): array
    {
        $result = [];
        $counter = $startingId;

        foreach ($this->uniqueNormalized($phones, $countryCode) as $phone) {
            $result[] = [
                'recipient_id' => (string) $counter++,
                'dest_addr' => $phone,
            ];
        }

        return $result;
    }
}
