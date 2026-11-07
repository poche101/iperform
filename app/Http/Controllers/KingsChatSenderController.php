<?php

namespace App\Http\Controllers;

use App\Models\KingsChatToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KingsChatSenderController extends Controller
{
    public function show()
    {
        abort_unless(Auth::user()->isStaffPerformance(), 403);

        return view('staff_performance.kingschat', [
            'connected' => KingsChatToken::exists(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->isStaffPerformance(), 403);

        $data = $request->validate([
            'access_token'      => 'required|string',
            'refresh_token'     => 'nullable|string',
            'expires_in_millis' => 'nullable|integer',
        ]);

        KingsChatToken::query()->delete(); // only one sender account at a time

        KingsChatToken::create([
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? null,
            'expires_at'    => now()->addMilliseconds($data['expires_in_millis'] ?? 3600000),
        ]);

        return response()->json(['ok' => true]);
    }
}
