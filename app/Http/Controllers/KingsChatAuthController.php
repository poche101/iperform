<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * "Continue with KingsChat" using the KingsChat Developer API (authorization-code flow).
 *
 *  1. start():    send the browser to KingsChat's login page with our client_id and a one-time `origin` state.
 *  2. KingsChat:  after login, form-POSTs `code` and `origin` to the redirect URL registered on the app
 *                 (the site root, POST /).
 *  3. callback(): check the state, exchange the code for tokens, read the profile, then sign in the
 *                 existing iPerform account and save the person's permanent KingsChat ID (kc_user_id).
 *
 * Never creates accounts. The iPerform username must equal the person's KingsChat handle.
 */
class KingsChatAuthController extends Controller
{
    private const API = 'https://connect.kingsch.at/developer/api';

    public function start()
    {
        $clientId = config('services.kingschat.client_id');
        $loginUrl = config('services.kingschat.login_url');

        if (! $clientId || ! $loginUrl) {
            return redirect()->route('login')
                ->withErrors(['username' => 'KingsChat sign-in is not set up yet.']);
        }

        // One-time state. Stored in the cache, not the session: KingsChat's POST comes from another
        // site, so the browser will not send our (SameSite=Lax) session cookie with it.
        $state = Str::random(40);
        Cache::put('kc_state:' . $state, true, now()->addMinutes(10));

        $separator = str_contains($loginUrl, '?') ? '&' : '?';

        return redirect()->away($loginUrl . $separator . http_build_query([
            'client_id' => $clientId,
            'origin'    => $state,
        ]));
    }

    public function callback(Request $request)
    {
        $fail = fn () => redirect()->route('login')->withErrors([
            'username' => 'KingsChat sign-in failed. Your iPerform username must be your KingsChat handle.',
        ]);

        $code  = (string) $request->input('code');
        $state = (string) $request->input('origin');

        // The state must be one we issued, and it can only be used once
        if ($code === '' || $state === '' || ! Cache::pull('kc_state:' . $state)) {
            return $fail();
        }

        try {
            // 1. Exchange the one-time code for tokens (no auth header, no client secret)
            $tokens = Http::acceptJson()->asJson()->timeout(10)->post(self::API . '/oauth2/token', [
                'grant_type' => 'code',
                'client_id'  => config('services.kingschat.client_id'),
                'code'       => $code,
            ]);

            if ($tokens->failed() || ! $tokens->json('access_token')) {
                Log::warning('KingsChat code exchange failed', [
                    'status' => $tokens->status(), 'error' => $tokens->json('error'),
                ]);
                return $fail();
            }

            // 2. Who is this person?
            $res = Http::withToken($tokens->json('access_token'))
                ->acceptJson()->timeout(10)
                ->get(self::API . '/user/profile');
        } catch (\Throwable $e) {
            Log::warning('KingsChat sign-in error: ' . $e->getMessage());
            return $fail();
        }

        if ($res->failed()) {
            Log::warning('KingsChat profile request failed', ['status' => $res->status()]);
            return $fail();
        }

        // Turn on KINGSCHAT_DEBUG=true for the first sign-in to see the real response, then turn it off.
        if (config('services.kingschat.debug')) {
            Log::info('KingsChat profile response', $res->json() ?? []);
        }

        $profile  = $res->json('profile') ?? [];
        $kcUserId = data_get($profile, 'id');
        $kcUserId = $kcUserId !== null && $kcUserId !== '' ? (string) $kcUserId : null;
        $handle   = strtolower(ltrim((string) (data_get($profile, 'username') ?? data_get($profile, 'user.username', '')), '@'));

        if (! $kcUserId || $handle === '') {
            return $fail();
        }

        // 1. Already linked: match on the permanent KingsChat ID (survives handle changes)
        $user = User::where('kc_user_id', $kcUserId)->first();

        // 2. First sign-in: the iPerform username is the KingsChat handle
        if (! $user) {
            $user = User::whereRaw('LOWER(username) = ?', [$handle])->first();

            // That account is already linked to a different KingsChat user: refuse
            if ($user && $user->kc_user_id && $user->kc_user_id !== $kcUserId) {
                return $fail();
            }
        }

        if (! $user) {
            return $fail();
        }

        if ($user->kc_user_id !== $kcUserId) {
            $user->forceFill(['kc_user_id' => $kcUserId])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($this->homeRouteFor($user));
    }

    private function homeRouteFor(User $user): string
    {
        return match ($user->role) {
            'staff_performance' => 'hr.dashboard',
            'supervisor'        => 'supervisor.dashboard',
            default             => 'staff.dashboard',
        };
    }
}
