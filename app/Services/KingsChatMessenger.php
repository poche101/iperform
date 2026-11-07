<?php

namespace App\Services;

use App\Models\KingsChatToken;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KingsChatMessenger
{
    private const BASE = 'https://connect.kingsch.at';

    public function send(User $user, string $text): bool
    {
        if (! $user->kc_user_id) {
            return false; // never signed in with KingsChat
        }

        $token = $this->accessToken();

        if (! $token) {
            Log::warning('KingsChat: no usable sender token. Reconnect the sender account (HR > KingsChat).');
            return false;
        }

        try {
            $res = Http::withToken($token)
                ->acceptJson()
                ->asJson()
                ->timeout(10)
                ->post(self::BASE . "/api/users/{$user->kc_user_id}/new_message", [
                    'message' => ['body' => ['text' => ['body' => $text]]],
                ]);
        } catch (\Throwable $e) {
            Log::warning('KingsChat send error: ' . $e->getMessage());
            return false;
        }

        if ($res->failed()) {
            Log::warning('KingsChat send failed', [
                'user'   => $user->id,
                'status' => $res->status(),
                'body'   => $res->body(),
            ]);
            return false;
        }

        return true;
    }

    private function accessToken(): ?string
    {
        $row = KingsChatToken::latest('id')->first();

        if (! $row) {
            return null;
        }

        if ($row->expires_at && $row->expires_at->copy()->subMinute()->isPast()) {
            try {
                $row = $this->refreshToken($row);
            } catch (\Throwable $e) {
                Log::warning('KingsChat token refresh error: ' . $e->getMessage());
                return null;
            }
        }

        return $row?->access_token;
    }

    private function refreshToken(KingsChatToken $row): ?KingsChatToken
    {
        // The lock stops two requests refreshing at once (refresh tokens may rotate)
        return Cache::lock('kingschat-token-refresh', 15)->block(10, function () use ($row) {
            $row->refresh(); // another request may have refreshed it already

            if (! $row->expires_at || ! $row->expires_at->copy()->subMinute()->isPast()) {
                return $row;
            }

            if (! $row->refresh_token) {
                return null;
            }

            $res = Http::acceptJson()->asJson()->timeout(10)
                ->post(self::BASE . '/oauth2/token', [
                    'client_id'     => config('services.kingschat.client_id'),
                    'grant_type'    => 'refresh_token',
                    'refresh_token' => $row->refresh_token,
                ]);

            if ($res->failed() || ! $res->json('access_token')) {
                Log::warning('KingsChat token refresh failed', [
                    'status' => $res->status(),
                    'body'   => $res->body(),
                ]);
                return null;
            }

            $row->update([
                'access_token'  => $res->json('access_token'),
                'refresh_token' => $res->json('refresh_token', $row->refresh_token),
                'expires_at'    => now()->addMilliseconds((int) $res->json('expires_in_millis', 3600000)),
            ]);

            return $row;
        });
    }
}
