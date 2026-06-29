<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use App\Models\AppraisalCycle;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function dashboard()
    {
        $user  = Auth::user();
        $cycle = AppraisalCycle::where('is_active', true)->first();
        $appraisal = $cycle
            ? Appraisal::where('staff_id', $user->id)->where('cycle_id', $cycle->id)->first()
            : null;

        return view('staff.dashboard', compact('user', 'cycle', 'appraisal'));
    }
}
