<?php
namespace App\Services\Sermons;
use App\Models\Sermon;
use App\Models\SermonAccessToken;
use App\Models\SermonRecipient;
use Illuminate\Support\Str;

class SermonTokenService
{
    public function ensureTokens(Sermon $sermon, ?int $daysValid = null): int
    {
        $created = 0;
        $sermon->recipients()->with('token')->chunkById(100, function ($recipients) use ($daysValid, &$created): void {
            foreach ($recipients as $recipient) {
                if (! $recipient->token) {
                    $this->createToken($recipient, $daysValid);
                    $created++;
                }
            }
        });
        return $created;
    }

    public function createToken(SermonRecipient $recipient, ?int $daysValid = null): SermonAccessToken
    {
        do { $token = Str::random(10); }
        while (SermonAccessToken::where('token', $token)->exists());

        return SermonAccessToken::create([
            'sermon_id' => $recipient->sermon_id,
            'sermon_recipient_id' => $recipient->id,
            'member_id' => $recipient->member_id,
            'token' => $token,
            'expires_at' => $daysValid ? now()->addDays($daysValid) : null,
            'open_count' => 0,
            'is_active' => true,
        ]);
    }

    public function publicUrl(SermonAccessToken|string $token): string
    {
        $tokenValue = $token instanceof SermonAccessToken ? $token->token : $token;
        return url('/'.$tokenValue);
    }
}
