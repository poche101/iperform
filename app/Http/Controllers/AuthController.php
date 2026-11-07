<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = ['username' => $request->username, 'password' => $request->password];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->route($this->homeRouteFor(Auth::user()));
        }

        return back()->withErrors(['username' => 'Invalid credentials.'])->withInput();
    }

    /**
     * Sign in with KingsChat. iPerform usernames are the users' KingsChat handles.
     * Never creates accounts. On first sign-in the account is bound to the person's
     * permanent KingsChat user ID, which is also what notifications are sent to.
     */
    public function kingsChatLogin(Request $request)
    {
        $request->validate(['access_token' => 'required|string']);

        // One generic message for every failure, so the endpoint can't be
        // used to check which usernames exist.
        $fail = fn () => response()->json([
            'message' => 'KingsChat sign-in failed. Your iPerform username must be your KingsChat handle.',
        ], 422);

        // Verify the token with KingsChat server-side; never trust a handle sent by the browser
        try {
            $res = Http::withToken($request->access_token)
                ->acceptJson()
                ->timeout(10)
                ->get('https://connect.kingsch.at/api/profile');
        } catch (\Throwable $e) {
            return $fail();
        }

        if ($res->failed()) {
            return $fail();
        }

        // TEMPORARY: uncomment for the first sign-in to see the real response shape,
        // confirm the field names below, then remove it again.
        // \Log::info('KC profile', $res->json());

        $handle = strtolower(ltrim((string) data_get($res->json(), 'profile.user.username', ''), '@'));

        if ($handle === '') {
            return $fail();
        }

        // Field names are best guesses; confirm with the temporary log above.
        $kcUserId = data_get($res->json(), 'profile.user.user_id')
            ?? data_get($res->json(), 'profile.user.id');
        $kcUserId = $kcUserId ? (string) $kcUserId : null;

        $user = null;

        // 1. Already linked: match on the permanent KingsChat ID (survives handle changes)
        if ($kcUserId) {
            $user = User::where('kc_user_id', $kcUserId)->first();
        }

        // 2. First sign-in: the iPerform username is the KingsChat handle
        if (! $user) {
            $user = User::whereRaw('LOWER(username) = ?', [$handle])->first();

            // That account is already linked to a different KingsChat user: refuse
            if ($user && $kcUserId && $user->kc_user_id && $user->kc_user_id !== $kcUserId) {
                return $fail();
            }
        }

        if (! $user) {
            return $fail();
        }

        // Link (or re-link) the account to this KingsChat ID
        if ($kcUserId && $user->kc_user_id !== $kcUserId) {
            $user->forceFill(['kc_user_id' => $kcUserId])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'redirect' => route($this->homeRouteFor($user)),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /** Dashboard route name for each role, shared by both login methods. */
    private function homeRouteFor(User $user): string
    {
        return match ($user->role) {
            'staff_performance' => 'hr.dashboard',
            'supervisor'        => 'supervisor.dashboard',
            default             => 'staff.dashboard',
        };
    }
}
