<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class MpesaService
{
    protected string $host;
    protected string $market;
    protected string $envPath;

    public function __construct()
    {
        $this->host = rtrim(config('services.mpesa.host'), '/');
        $this->market = config('services.mpesa.market');

        $this->envPath = config('services.mpesa.env') === 'openapi'
            ? 'openapi'
            : 'sandbox';
    }

    public function encryptBearerToken(string $plainText): string
    {
        $publicKey = config('services.mpesa.public_key');

        if (!$publicKey) {
            throw new RuntimeException('M-Pesa public key is missing.');
        }

        /*
         * Clean key in case it was copied with quotes, spaces, new lines,
         * or PEM header/footer.
         */
        $publicKey = trim($publicKey);
        $publicKey = str_replace(["\r", "\n", " ", '"', "'"], '', $publicKey);
        $publicKey = str_replace('-----BEGINPUBLICKEY-----', '', $publicKey);
        $publicKey = str_replace('-----ENDPUBLICKEY-----', '', $publicKey);

        $pem = "-----BEGIN PUBLIC KEY-----\n"
            . chunk_split($publicKey, 64, "\n")
            . "-----END PUBLIC KEY-----\n";

        $keyResource = openssl_pkey_get_public($pem);

        if (!$keyResource) {
            throw new RuntimeException('Invalid M-Pesa public key: '.openssl_error_string());
        }

        $encrypted = null;

        $success = openssl_public_encrypt(
            $plainText,
            $encrypted,
            $keyResource,
            OPENSSL_PKCS1_PADDING
        );

        if (!$success) {
            throw new RuntimeException('Failed to encrypt M-Pesa token: '.openssl_error_string());
        }

        return base64_encode($encrypted);
    }

    public function getSessionKey(): string
    {
        $apiKey = config('services.mpesa.api_key');

        if (!$apiKey) {
            throw new RuntimeException('M-Pesa API key is missing.');
        }

        $encryptedApiKey = $this->encryptBearerToken($apiKey);

        $url = "{$this->host}/{$this->envPath}/ipg/v2/{$this->market}/getSession/";

        $response = Http::timeout(60)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$encryptedApiKey,
                'Origin' => config('services.mpesa.origin'),
            ])
            ->get($url);

        $bodyJson = $response->json();
        $bodyRaw = $response->body();

        if (!$response->successful()) {
            Log::error('M-Pesa getSession HTTP error', [
                'url' => $url,
                'env' => config('services.mpesa.env'),
                'market' => config('services.mpesa.market'),
                'origin' => config('services.mpesa.origin'),
                'status' => $response->status(),
                'body_json' => $bodyJson,
                'body_raw' => $bodyRaw,
            ]);

            throw new RuntimeException(
                'M-Pesa getSession failed. HTTP status: '
                .$response->status()
                .' Body: '
                .$bodyRaw
            );
        }

        if (($bodyJson['output_ResponseCode'] ?? null) !== 'INS-0') {
            Log::error('M-Pesa getSession API error', [
                'url' => $url,
                'body_json' => $bodyJson,
                'body_raw' => $bodyRaw,
            ]);

            throw new RuntimeException(
                $bodyJson['output_ResponseDesc']
                ?? $bodyRaw
                ?? 'M-Pesa session failed.'
            );
        }

        if (empty($bodyJson['output_SessionID'])) {
            Log::error('M-Pesa getSession missing output_SessionID', [
                'body_json' => $bodyJson,
                'body_raw' => $bodyRaw,
            ]);

            throw new RuntimeException('M-Pesa session response missing output_SessionID.');
        }

        return $bodyJson['output_SessionID'];
    }

    public function c2bMultiStage(array $payload): array
    {
        $sessionKey = $this->getSessionKey();

        /*
         * M-Pesa documentation says SessionID can take up to 30 seconds to become active.
         * For production, this should be handled better with retry/queue,
         * but this is okay for testing.
         */
        sleep(30);

        $encryptedSessionKey = $this->encryptBearerToken($sessionKey);

        $url = "{$this->host}/{$this->envPath}/ipg/v2/{$this->market}/c2bPayment/multiStage/";

        $response = Http::timeout(60)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$encryptedSessionKey,
                'Origin' => config('services.mpesa.origin'),
            ])
            ->post($url, $payload);

        return [
            'url' => $url,
            'http_status' => $response->status(),
            'successful' => $response->successful(),
            'body' => $response->json(),
            'raw_body' => $response->body(),
        ];
    }
}