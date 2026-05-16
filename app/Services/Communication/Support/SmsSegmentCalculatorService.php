<?php

namespace App\Services\Communication\Support;

class SmsSegmentCalculatorService
{
    /**
     * GSM-7 basic + extension table characters.
     */
    private const GSM_7_REGEX = '/^[@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ\\\\ÆæßÉ !"#¤%&\'()*+,\-.\/0-9:;<=>?A-ZÄÖÑÜ§¿a-zäöñüà^{}\\\\\[~\]|€]*$/u';

    public function analyze(string $message): array
    {
        $message = (string) $message;
        $length = mb_strlen($message, 'UTF-8');
        $isGsm7 = $this->isGsm7($message);

        $singleLimit = $isGsm7
            ? (int) config('communication_center.segments.gsm_single', 160)
            : (int) config('communication_center.segments.unicode_single', 70);

        $multiLimit = $isGsm7
            ? (int) config('communication_center.segments.gsm_multi', 153)
            : (int) config('communication_center.segments.unicode_multi', 67);

        $segments = $length === 0
            ? 0
            : ($length <= $singleLimit ? 1 : (int) ceil($length / $multiLimit));

        return [
            'encoding' => $isGsm7 ? 'GSM-7' : 'UNICODE',
            'length' => $length,
            'single_limit' => $singleLimit,
            'multi_limit' => $multiLimit,
            'segments' => $segments,
        ];
    }

    public function calculate(string $message): array
    {
        return $this->analyze($message);
    }

    public function segmentCount(string $message): int
    {
        return $this->analyze($message)['segments'];
    }

    public function isGsm7(string $message): bool
    {
        return preg_match(self::GSM_7_REGEX, $message) === 1;
    }

    public function estimateBulk(array $messages): array
    {
        $totalSegments = 0;
        $totalMessages = 0;
        $analysis = [];

        foreach ($messages as $key => $message) {
            $item = $this->analyze((string) $message);
            $totalSegments += $item['segments'];
            $totalMessages++;
            $analysis[$key] = $item;
        }

        return [
            'total_messages' => $totalMessages,
            'total_segments' => $totalSegments,
            'analysis' => $analysis,
        ];
    }
}
