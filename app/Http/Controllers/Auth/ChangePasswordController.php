<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function edit()
    {
        return view('auth.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'current_password.current_password' => 'Your current password is incorrect.',
            'password.different'                => 'Your new password must be different from your current password.',
        ]);

        $user = Auth::user();

        $user->forceFill([
            'password'       => Hash::make($request->password),
        ])->save();

        return back()->with('success', 'Your password has been updated.');
    }
}
