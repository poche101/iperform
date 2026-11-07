<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends KingsChat direct messages through the KingsChat Developer API, as the project.
 *
 *   POST https://connect.kingsch.at/developer/api/messages
 *   Header: api-key: <project API key>      (no user access token needed)
 *   Body:   { "recipient_id": "<profile.id>", "message": { "body": { "text": { "body": "..." } } } }
 *
 * The recipient must have authorized the project (signed in with KingsChat) or be in a
 * direct chat with the project profile; otherwise KingsChat answers recipient_not_authorized.
 * Never throws: a KingsChat problem must not break an appraisal action.
 */
class KingsChatMessenger
{
    private const ENDPOINT = 'https://connect.kingsch.at/developer/api/messages';

    public function send(User $user, string $text): bool
    {
        if (! $user->kc_user_id) {
            return false; // never signed in with KingsChat
        }

        return $this->sendToId((string) $user->kc_user_id, $text, $user->id);
    }

    /** Send to a raw KingsChat user ID (also handy for tinker tests). */
    public function sendToId(string $recipientId, string $text, ?int $userId = null): bool
    {
        $apiKey = config('services.kingschat.api_key');

        if (! $apiKey) {
            Log::warning('KingsChat: KINGSCHAT_API_KEY is not set in .env');
            return false;
        }

        try {
            $res = Http::withHeaders(['api-key' => $apiKey])
                ->acceptJson()
                ->asJson()
                ->timeout(10)
                ->post(self::ENDPOINT, [
                    'recipient_id' => $recipientId,
                    'message'      => ['body' => ['text' => ['body' => $text]]],
                ]);
        } catch (\Throwable $e) {
            Log::warning('KingsChat send error: ' . $e->getMessage());
            return false;
        }

        if ($res->failed()) {
            Log::warning('KingsChat send failed', [
                'user'   => $userId,
                'status' => $res->status(),
                'error'  => $res->json('error'),
                'body'   => $res->body(),
            ]);
            return false;
        }

        return true;
    }
}
