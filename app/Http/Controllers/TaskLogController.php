<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use App\Models\AppraisalKra;        // ← ADD
use App\Models\AppraisalTask;       // ← ADD
use App\Models\AppraisalInnovation;
use App\Models\AppraisalCycle;
use App\Models\TaskLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskLogController extends Controller
{
    // -------------------------------------------------------
    // STAFF: view own task log page
    // -------------------------------------------------------
    public function staffIndex()
    {
        $user  = Auth::user();
        $cycle = AppraisalCycle::where('is_active', true)->first();

        $tasks = $cycle
            ? TaskLog::where('staff_id', $user->id)
                ->where('cycle_id', $cycle->id)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get()
            : collect();

        return view('staff.tasks', compact('user', 'cycle', 'tasks'));
    }

    // -------------------------------------------------------
    // STAFF: log a new task
    // -------------------------------------------------------
    public function staffStore(Request $request)
    {
        $request->validate([
            'title'                 => 'required|string|max:255',
            'target'                => 'nullable|string',
            'date'                  => 'required|date',
            'details'               => 'nullable|string',
            'challenge_identified'  => 'nullable|string',
            'challenge_impact'      => 'nullable|string',
            'category'              => ['required', Rule::in([
                'KRA',
                'Routine',
                'Ideas, Innovation & Outstanding Contribution',
            ])],
            'self_score'            => 'nullable|integer|min:0|max:10',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
        ]);

        $cycle = AppraisalCycle::where('is_active', true)->firstOrFail();

        // Get current appraisal for this staff + cycle
        $appraisal = Appraisal::firstOrCreate(
            [
                'staff_id' => Auth::id(),
                'cycle_id' => $cycle->id,
            ],
            [
                'supervisor_id' => Auth::user()->supervisor_id,
                'status'        => 'drafting',
            ]
        );

        $taskLog = TaskLog::create([
            'staff_id'              => Auth::id(),
            'cycle_id'              => $cycle->id,
            'appraisal_id'          => $appraisal->id,           // ← Important
            'title'                 => $request->title,
            'target'                => $request->target,
            'date'                  => $request->date,
            'details'               => $request->details,
            'challenge_identified'  => $request->challenge_identified, // Save challenge info
            'challenge_impact'      => $request->challenge_impact,
            'category'              => $request->category,
            'self_score'            => $request->self_score ?? 0,
            'completion_percentage' => $request->completion_percentage ?? 0,
            'status'                => 'awaiting',
        ]);

        // Automatically sync to the correct appraisal section
        $this->syncToAppraisal($taskLog, $appraisal);

        return back()->with('success', 'Task logged successfully and synced to your Appraisal!');
    }

    private function syncToAppraisal(TaskLog $taskLog, Appraisal $appraisal)
    {
        $model = match($taskLog->category) {
            'KRA'                                          => AppraisalKra::class,
            'Ideas, Innovation & Outstanding Contribution' => AppraisalInnovation::class,
            default                                        => AppraisalTask::class,   // Routine
        };

        // Check if this task is already linked
        $existing = $model::where('appraisal_id', $appraisal->id)
                          ->where('task_log_id', $taskLog->id)  // we'll add this column
                          ->first();

        if ($existing) {
            $existing->update([
                'achievement'           => $taskLog->details,
                'performance'           => $taskLog->details,
                'impact'                => $taskLog->details,
                'completion_percentage' => $taskLog->completion_percentage,
                'staff_score'           => $taskLog->self_score,
            ]);
        } else {
            $sn = $model::where('appraisal_id', $appraisal->id)->max('sn') ?? 0;

            $data = [
                'appraisal_id'          => $appraisal->id,
                'task_log_id'           => $taskLog->id,
                'sn'                    => $sn + 1,
                'completion_percentage' => $taskLog->completion_percentage,
                'staff_score'           => $taskLog->self_score,
            ];

            if ($model === AppraisalKra::class) {
                $data['kra']         = $taskLog->title;
                $data['target']      = $taskLog->target;
                $data['achievement'] = $taskLog->details;
            } elseif ($model === AppraisalInnovation::class) {
                $data['idea']   = $taskLog->title;
                $data['impact'] = $taskLog->details;
            } else {
                $data['task']        = $taskLog->title;
                $data['performance'] = $taskLog->details;
            }

            $model::create($data);
        }
    }

    // -------------------------------------------------------
    // STAFF: delete own draft task
    // -------------------------------------------------------
    public function staffDestroy(TaskLog $taskLog)
    {
        abort_unless($taskLog->staff_id === Auth::id(), 403);
        abort_if($taskLog->status === 'graded', 403, 'Cannot delete a graded task.');
        $taskLog->delete();
        return back()->with('success', 'Task deleted.');
    }

    // -------------------------------------------------------
    // SUPERVISOR: view tasks awaiting review
    // -------------------------------------------------------
    public function supervisorIndex()
    {
        $user  = Auth::user();
        $cycle = AppraisalCycle::where('is_active', true)->first();

        // Get all staff under this supervisor
        $staffIds = User::where('supervisor_id', $user->id)->pluck('id');

        $awaiting = $cycle
            ? TaskLog::with('staff')
                ->whereIn('staff_id', $staffIds)
                ->where('cycle_id', $cycle->id)
                ->where('status', 'awaiting')
                ->orderByDesc('date')
                ->get()
            : collect();

        $recentlyGraded = $cycle
            ? TaskLog::with('staff')
                ->whereIn('staff_id', $staffIds)
                ->where('cycle_id', $cycle->id)
                ->where('status', 'graded')
                ->orderByDesc('reviewed_at')
                ->take(10)
                ->get()
            : collect();

        return view('supervisor.tasks', compact('user', 'cycle', 'awaiting', 'recentlyGraded'));
    }

    // -------------------------------------------------------
    // SUPERVISOR: submit feedback on a task
    // -------------------------------------------------------
    public function supervisorGrade(Request $request, TaskLog $taskLog)
    {
        $user = Auth::user();
        abort_unless($user->isSupervisor(), 403);

        $staffIds = User::where('supervisor_id', $user->id)->pluck('id');
        abort_unless($staffIds->contains($taskLog->staff_id), 403);

        $request->validate([
            'supervisor_score'   => 'required|integer|min:0|max:10',
            'supervisor_comment' => 'nullable|string|max:1000',
        ]);

        // Update TaskLog
        $taskLog->update([
            'supervisor_score'   => $request->supervisor_score,
            'supervisor_comment' => $request->supervisor_comment,
            'reviewed_by'        => $user->id,
            'reviewed_at'        => now(),
            'status'             => 'graded',
        ]);

        // === NEW: Sync score to Appraisal section ===
        $this->syncGradeToAppraisal($taskLog);

        return back()->with('success', 'Feedback submitted and synced to Appraisal!');
    }

    /**
     * Sync supervisor score from TaskLog to the linked Appraisal entry
     */
    private function syncGradeToAppraisal(TaskLog $taskLog)
    {
        if (!$taskLog->appraisal_id) {
            return;
        }

        $model = match($taskLog->category) {
            'KRA'                                          => AppraisalKra::class,
            'Ideas, Innovation & Outstanding Contribution' => AppraisalInnovation::class,
            default                                        => AppraisalTask::class,
        };

        $entry = $model::where('appraisal_id', $taskLog->appraisal_id)
                       ->where('task_log_id', $taskLog->id)
                       ->first();

        if ($entry) {
            $entry->update([
                'supervisor_score' => $taskLog->supervisor_score,
            ]);
        }
    }

    // -------------------------------------------------------
    // HR: view all task logs across all staff
    // -------------------------------------------------------
    public function hrIndex()
    {
        abort_unless(Auth::user()->isHR(), 403);
        $cycle = AppraisalCycle::where('is_active', true)->first();

        $tasks = $cycle
            ? TaskLog::with(['staff', 'reviewer'])
                ->where('cycle_id', $cycle->id)
                ->orderByDesc('date')
                ->get()
                ->groupBy('staff_id')
            : collect();

        $allStaff = User::where('role', 'staff')->with('supervisor')->get();

        return view('hr.tasks', compact('cycle', 'tasks', 'allStaff'));
    }
}
