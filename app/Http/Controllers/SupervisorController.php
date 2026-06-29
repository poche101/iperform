<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use App\Models\AppraisalCycle;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class SupervisorController extends Controller
{
    public function dashboard()
    {
        $user  = Auth::user();
        $cycle = AppraisalCycle::where('is_active', true)->first();
        $staff = User::where('supervisor_id', $user->id)->get();

        $appraisals = $cycle
            ? Appraisal::with('staff')
                ->where('cycle_id', $cycle->id)
                ->whereIn('staff_id', $staff->pluck('id'))
                ->get()
                ->keyBy('staff_id')
            : collect();

        $grouped = [
            'drafting'  => $staff->filter(fn($s) => ($appraisals[$s->id]->status ?? 'drafting') === 'drafting'),
            'submitted' => $staff->filter(fn($s) => ($appraisals[$s->id]->status ?? '') === 'submitted'),
            'with_hr'   => $staff->filter(fn($s) => ($appraisals[$s->id]->status ?? '') === 'with_hr'),
            'approved'  => $staff->filter(fn($s) => ($appraisals[$s->id]->status ?? '') === 'approved'),
        ];

        return view('supervisor.dashboard', compact('user', 'cycle', 'staff', 'appraisals', 'grouped'));
    }

    public function supervisors()
    {
        $user  = Auth::user();
        $cycle = AppraisalCycle::where('is_active', true)->first();
        $staff = User::where('supervisor_id', $user->id)->get();

        $appraisals = $cycle
            ? Appraisal::where('cycle_id', $cycle->id)
                ->whereIn('staff_id', $staff->pluck('id'))
                ->get()->keyBy('staff_id')
            : collect();

        return view('supervisor.supervisors', compact('user', 'cycle', 'staff', 'appraisals'));
    }

    public function pipeline()
    {
        $user  = Auth::user();
        $cycle = AppraisalCycle::where('is_active', true)->first();
        $staff = User::where('supervisor_id', $user->id)->get();

        $appraisals = $cycle
            ? Appraisal::with('staff')
                ->where('cycle_id', $cycle->id)
                ->whereIn('staff_id', $staff->pluck('id'))
                ->get()
                ->keyBy('staff_id')
            : collect();

        return view('supervisor.pipeline', compact('cycle', 'staff', 'appraisals'));
    }
}
