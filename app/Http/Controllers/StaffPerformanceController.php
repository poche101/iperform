<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use App\Models\AppraisalCycle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffPerformanceController extends Controller
{
    public function dashboard()
    {
        $cycle    = AppraisalCycle::where('is_active', true)->first();
        $allStaff = User::where('role', 'staff')->with('supervisor')->get();

        $appraisals = $cycle
            ? Appraisal::where('cycle_id', $cycle->id)->get()->keyBy('staff_id')
            : collect();

        $stats = [
            'total'                  => $allStaff->count(),
            'approved'               => $appraisals->where('status', 'approved')->count(),
            'with_staff performance' => $appraisals->where('status', 'staff_performance')->count(),
            'submitted'              => $appraisals->where('status', 'submitted')->count(),
            'drafting'               => $allStaff->count()
                                        - $appraisals->where('status', 'approved')->count()
                                        - $appraisals->where('status', 'with_staff_performance')->count()
                                        - $appraisals->where('status', 'submitted')->count(),
            'avg_score'              => $appraisals->whereNotNull('staff_performance_overall')->avg('staff_performance_overall'),
        ];

        return view('staff_performance.dashboard', compact('cycle', 'allStaff', 'appraisals', 'stats'));
    }

    public function users()
    {
        $users       = User::with('supervisor')->orderBy('role')->get();
        $supervisors = User::where('role', 'supervisor')->get();

        return view('staff_performance.users', compact('users', 'supervisors'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|unique:users',
            'password'      => 'required|string|min:6',
            'role'          => 'required|in:staff,supervisor,staff_performance',
            'department'    => 'nullable|string',
            'designation'   => 'nullable|string',
            'supervisor_id' => 'nullable|exists:users,id',
        ]);

        User::create([
            'name'          => $request->name,
            'username'      => $request->username,
            'password'      => Hash::make($request->password),
            'role'          => $request->role,
            'department'    => $request->department,
            'designation'   => $request->designation,
            'supervisor_id' => $request->supervisor_id,
        ]);

        return back()->with('success', 'User created successfully.');
    }

    public function deleteUser(User $user)
    {
        abort_if($user->id === Auth::id(), 403, 'Cannot delete yourself.');
        $user->delete();

        return back()->with('success', 'User deleted.');
    }

    public function assignments()
    {
        $allStaff    = User::where('role', 'staff')->with('supervisor')->get();
        $supervisors = User::where('role', 'supervisor')->get();

        return view('staff_performance.assignments', compact('allStaff', 'supervisors'));
    }

    public function updateAssignment(Request $request, User $user)
    {
        $request->validate(['supervisor_id' => 'required|exists:users,id']);
        $user->update(['supervisor_id' => $request->supervisor_id]);

        return back()->with('success', 'Assignment updated.');
    }

    public function cycles()
    {
        $cycles = AppraisalCycle::orderByDesc('created_at')->get();

        return view('staff_performance.cycles', compact('cycles'));
    }

    public function storeCycle(Request $request)
    {
        $request->validate([
            'name'       => 'required',
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
            'deadline'   => 'required|date',
        ]);

        if ($request->boolean('is_active')) {
            AppraisalCycle::where('is_active', true)->update(['is_active' => false]);
        }

        AppraisalCycle::create(
            $request->only('name', 'start_date', 'end_date', 'deadline')
            + ['is_active' => $request->boolean('is_active')]
        );

        return back()->with('success', 'Appraisal cycle created.');
    }
}
