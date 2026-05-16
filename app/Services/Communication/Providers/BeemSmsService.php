<?php

namespace App\Services\Communication\Providers;

use App\Exceptions\Communication\BeemRequestException;
use App\Services\Communication\Support\PhoneNumberService;
use App\Services\Communication\Support\SmsSegmentCalculatorService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BeemSmsService
{
    public function __construct(
        protected PhoneNumberService $phoneNumberService,
        protected SmsSegmentCalculatorService $segmentCalculator,
    ) {
    }

    public function sendSingle(string $message, string $phone, array $options = []): array
    {
        $normalized = $this->phoneNumberService->normalize($phone);

        if ($normalized === null) {
            throw BeemRequestException::invalidResponse('Invalid recipient phone number supplied.');
        }

        $recipients = [[
            'recipient_id' => (string) Arr::get($options, 'recipient_id', '1'),
            'dest_addr' => $normalized,
        ]];

        return $this->sendBulk($message, $recipients, $options);
    }

    public function sendBulk(string $message, array $recipients, array $options = []): array
    {
        if (blank($message)) {
            throw BeemRequestException::invalidResponse('SMS message body cannot be empty.');
        }

        if (empty($recipients)) {
            throw BeemRequestException::invalidResponse('At least one valid recipient is required.');
        }

        $analysis = $this->segmentCalculator->analyze($message);

        $payload = [
            'source_addr' => $this->senderId(),
            'schedule_time' => Arr::get($options, 'schedule_time', ''),
            'encoding' => $analysis['encoding'] === 'GSM-7' ? 0 : 8,
            'message' => $message,
            'recipients' => array_values($recipients),
        ];

        $response = $this->client()->post($this->sendUrl(), $payload);
        $json = $response->json();

        if (! is_array($json)) {
            throw BeemRequestException::invalidResponse('Beem send response is not valid JSON.');
        }

        $normalized = $this->normalizeSendResponse($json, $payload, $analysis);

        if (! $normalized['success']) {
            throw BeemRequestException::transportError(
                json_encode($json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        }

        return $normalized;
    }

    public function getBalance(): array
    {
        $response = $this->client()->get($this->balanceUrl());
        $json = $response->json();

        if (! is_array($json)) {
            throw BeemRequestException::invalidResponse('Beem balance response is not valid JSON.');
        }

        return [
            'provider' => 'beem',
            'success' => true,
            'balance' => Arr::get($json, 'data.credit_balance', Arr::get($json, 'credit_balance')),
            'currency' => Arr::get($json, 'data.currency', Arr::get($json, 'currency')),
            'raw' => $json,
        ];
    }

    protected function client(): PendingRequest
    {
        $apiKey = $this->apiKey();
        $secretKey = $this->secretKey();
        $verifySsl = (bool) config('communication_center.sms.beem.verify_ssl', true);
        $retryTimes = (int) config('communication_center.sms.beem.retry_times', 2);
        $retrySleepMs = (int) config('communication_center.sms.beem.retry_sleep_ms', 300);

        $authHeader = 'Basic ' . base64_encode($apiKey . ':' . $secretKey);

        return Http::acceptJson()
            ->withHeaders([
                'Authorization' => $authHeader,
                'Content-Type' => 'application/json',
            ])
            ->timeout((int) config('communication_center.sms.beem.timeout', 30))
            ->connectTimeout((int) config('communication_center.sms.beem.connect_timeout', 10))
            ->retry($retryTimes, $retrySleepMs, function ($exception) {
                return $exception instanceof ConnectionException || $exception instanceof RequestException;
            }, throw: false)
            ->withOptions([
                'verify' => $verifySsl,
            ])
            ->throw(function ($response, $e) {
                Log::error('Beem SMS request failed.', [
                    'status' => $response?->status(),
                    'body' => $response?->body(),
                ]);

                throw BeemRequestException::transportError(
                    $response?->body() ?: ($e?->getMessage() ?: 'Unknown Beem SMS error.')
                );
            });
    }

    protected function normalizeSendResponse(array $json, array $payload, array $analysis): array
    {
        $code = (string) Arr::get($json, 'code', Arr::get($json, 'data.code', ''));
        $success = in_array($code, ['100'], true)
            || Arr::get($json, 'successful') === true;

        return [
            'provider' => 'beem',
            'success' => $success,
            'code' => $code,
            'message' => Arr::get($json, 'message', Arr::get($json, 'data.message')),
            'message_id' => Arr::get($json, 'request_id', Arr::get($json, 'data.request_id')),
            'valid' => Arr::get($json, 'valid', count($payload['recipients'])),
            'invalid' => Arr::get($json, 'invalid', 0),
            'segments_per_message' => $analysis['segments'],
            'estimated_total_segments' => $analysis['segments'] * count($payload['recipients']),
            'raw' => $json,
            'payload' => $payload,
        ];
    }

    protected function sendUrl(): string
    {
        return (string) config(
            'communication_center.sms.beem.send_url',
            'https://apisms.beem.africa/v1/send'
        );
    }

    protected function balanceUrl(): string
    {
        return (string) config(
            'communication_center.sms.beem.balance_url',
            'https://apisms.beem.africa/public/v1/vendors/balance'
        );
    }

    protected function apiKey(): string
    {
        $value = trim((string) config('communication_center.sms.beem.api_key'));

        if ($value === '') {
            throw BeemRequestException::missingConfiguration('BONGO_LIVE_KEY');
        }

        return $value;
    }

    protected function secretKey(): string
    {
        $value = trim((string) config('communication_center.sms.beem.secret_key'));

        if ($value === '') {
            throw BeemRequestException::missingConfiguration('BONGO_LIVE_SECRET');
        }

        return $value;
    }

    protected function senderId(): string
    {
        $value = trim((string) config('communication_center.sms.beem.sender_id'));

        if ($value === '') {
            throw BeemRequestException::missingConfiguration('BONGO_SENDER_ID');
        }

        return $value;
    }
}