<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use App\Models\AppraisalKra;
use App\Models\AppraisalTask;
use App\Models\AppraisalInnovation;
use App\Models\AppraisalCycle;
use App\Models\TaskLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskLogController extends Controller
{
    private const IDEAS_CATEGORY = 'Ideas, Innovation & Outstanding Contribution';

    private const LOG_TYPES = ['daily', 'weekly'];

    /**
     * Supervisor/HR pages: pick which cycle to look at.
     * Defaults to the active cycle, or the most recent one if none is active.
     * ?cycle=ID lets them open any past month, so switching the active cycle never hides old task logs.
     */
    private function resolveCycle(Request $request): array
    {
        $cycles   = AppraisalCycle::orderByDesc('start_date')->orderByDesc('id')->get();
        $selected = $request->filled('cycle') ? $cycles->firstWhere('id', (int) $request->input('cycle')) : null;
        $cycle    = $selected ?? $cycles->firstWhere('is_active', true) ?? $cycles->first();

        return [$cycle, $cycles];
    }

    /** Staff only: has this task's cycle passed its deadline? Supervisors and HR are never gated by this. */
    private function staffCycleClosed(TaskLog $taskLog): bool
    {
        $cycle = AppraisalCycle::find($taskLog->cycle_id);
        return $cycle && !$cycle->isOpenForStaff();
    }

    /** Reads ?type=daily|weekly from the request; anything else means "show all". */
    private function resolveLogType(Request $request): ?string
    {
        $type = $request->query('type');
        return in_array($type, self::LOG_TYPES, true) ? $type : null;
    }

    /**
     * Validation rules shared by store and update.
     * Daily  -> `date` is the day the work was done.
     * Weekly -> `date` can be any day in the week being reported; it is normalised to Monday–Sunday.
     */
    private function taskRules(): array
    {
        return [
            'log_type'              => ['required', Rule::in(self::LOG_TYPES)],
            'title'                 => 'required|string|max:255',
            'target'                => 'nullable|string',
            'date'                  => 'required|date',
            'details'               => 'nullable|string',
            'challenge_identified'  => 'nullable|string',
            'challenge_impact'      => 'nullable|string',
            'category'              => ['required', Rule::in([
                'KRA',
                'Routine',
                self::IDEAS_CATEGORY,
            ])],
            'self_score'            => 'nullable|integer|min:0|max:10',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
        ];
    }

    /**
     * Works out the period columns for a log entry.
     * For weekly logs, `date` is stored as the Monday of that week so ordering and
     * existing views keep working; week_start / week_end hold the full range.
     */
    private function periodData(Request $request): array
    {
        $date = Carbon::parse($request->date);

        if ($request->log_type === 'weekly') {
            $start = $date->copy()->startOfWeek(Carbon::MONDAY);
            $end   = $date->copy()->endOfWeek(Carbon::SUNDAY);

            return [
                'log_type'   => 'weekly',
                'date'       => $start->toDateString(),
                'week_start' => $start->toDateString(),
                'week_end'   => $end->toDateString(),
            ];
        }

        return [
            'log_type'   => 'daily',
            'date'       => $date->toDateString(),
            'week_start' => null,
            'week_end'   => null,
        ];
    }

    // -------------------------------------------------------
    // STAFF: view own task log page
    // ?type=daily|weekly filters the list; no type shows both.
    // -------------------------------------------------------
    public function staffIndex(Request $request)
    {
        $user  = Auth::user();
        $cycle = AppraisalCycle::where('is_active', true)->first();
        $type  = $this->resolveLogType($request);

        $allTasks = $cycle
            ? TaskLog::where('staff_id', $user->id)
                ->where('cycle_id', $cycle->id)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get()
            : collect();

        $typeCounts = [
            'all'    => $allTasks->count(),
            'daily'  => $allTasks->where('log_type', 'daily')->count(),
            'weekly' => $allTasks->where('log_type', 'weekly')->count(),
        ];

        $tasks = $type ? $allTasks->where('log_type', $type)->values() : $allTasks;

        return view('staff.tasks', compact('user', 'cycle', 'tasks', 'type', 'typeCounts'));
    }

    // -------------------------------------------------------
    // STAFF: log a new task (daily or weekly)
    // -------------------------------------------------------
    public function staffStore(Request $request)
    {
        $request->validate($this->taskRules());

        $cycle = AppraisalCycle::where('is_active', true)->firstOrFail();

        if (!$cycle->isOpenForStaff()) {
            return back()->with('error', "The deadline for {$cycle->name} has passed, so task logging is closed. Contact HR for an extension.");
        }

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

        $taskLog = TaskLog::create($this->periodData($request) + [
            'staff_id'              => Auth::id(),
            'cycle_id'              => $cycle->id,
            'appraisal_id'          => $appraisal->id,
            'title'                 => $request->title,
            'target'                => $request->target,
            'details'               => $request->details,
            'challenge_identified'  => $request->challenge_identified,
            'challenge_impact'      => $request->challenge_impact,
            'category'              => $request->category,
            'self_score'            => $request->self_score ?? 0,
            'completion_percentage' => $request->completion_percentage ?? 0,
            'status'                => 'awaiting',
        ]);

        // Automatically sync to the correct appraisal section
        $this->syncToAppraisal($taskLog, $appraisal);

        // Notify the assigned supervisor
        if ($supervisor = Auth::user()->supervisor) {
            $supervisor->notify(new \App\Notifications\TaskSubmitted($taskLog));
        }

        $label = $taskLog->log_type === 'weekly' ? 'Weekly' : 'Daily';

        return back()->with('success', "{$label} task logged successfully and synced to your Appraisal!");
    }

    // -------------------------------------------------------
    // STAFF: edit an own task that hasn't been graded yet
    // -------------------------------------------------------
    public function staffUpdate(Request $request, TaskLog $taskLog)
    {
        abort_unless($taskLog->staff_id === Auth::id(), 403);
        abort_if($taskLog->status === 'graded', 403, 'Cannot edit a task your supervisor has already graded.');

        if ($this->staffCycleClosed($taskLog)) {
            return back()->with('error', 'The deadline for this cycle has passed, so this task can no longer be changed.');
        }

        $request->validate($this->taskRules());

        $taskLog->update($this->periodData($request) + [
            'title'                 => $request->title,
            'target'                => $request->target,
            'details'               => $request->details,
            'challenge_identified'  => $request->challenge_identified,
            'challenge_impact'      => $request->challenge_impact,
            'category'              => $request->category,
            'self_score'            => $request->self_score ?? 0,
            'completion_percentage' => $request->completion_percentage ?? 0,
        ]);

        // Keep the linked appraisal entry (KRA/Task/Innovation) in sync with the edit
        if ($taskLog->appraisal_id && $appraisal = Appraisal::find($taskLog->appraisal_id)) {
            $this->syncToAppraisal($taskLog, $appraisal);
        }

        return back()->with('success', 'Task updated successfully.');
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
                          ->where('task_log_id', $taskLog->id)
                          ->first();

        if ($existing) {
            $existing->update([
                'achievement'           => $taskLog->details,
                'performance'           => $taskLog->details,
                'impact'                => $taskLog->details,
                'completion_percentage' => $taskLog->completion_percentage,
                'staff_score'           => $taskLog->self_score,
            ]);

            // Category may have changed on edit — keep the title/target/impact fields aligned too
            if ($model === AppraisalKra::class) {
                $existing->update(['kra' => $taskLog->title, 'target' => $taskLog->target]);
            } elseif ($model === AppraisalInnovation::class) {
                $existing->update(['idea' => $taskLog->title]);
            } else {
                $existing->update(['task' => $taskLog->title]);
            }
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

        if ($this->staffCycleClosed($taskLog)) {
            return back()->with('error', 'The deadline for this cycle has passed, so this task can no longer be deleted.');
        }

        $taskLog->delete();
        return back()->with('success', 'Task deleted.');
    }

    // -------------------------------------------------------
    // SUPERVISOR: Tasks page = list of every staff member under this supervisor.
    // Clicking a staff member opens their appraisal for the selected cycle.
    // -------------------------------------------------------
    public function supervisorIndex(Request $request)
    {
        $user = Auth::user();
        [$cycle, $cycles] = $this->resolveCycle($request);

        $team     = User::where('supervisor_id', $user->id)->orderBy('name')->get();
        $staffIds = $team->pluck('id');

        // Per-staff task counts for the selected cycle (total / awaiting / graded, plus daily / weekly)
        $statsByStaff = $cycle
            ? TaskLog::whereIn('staff_id', $staffIds)
                ->where('cycle_id', $cycle->id)
                ->selectRaw("staff_id,
                    count(*) as total,
                    sum(case when status = 'awaiting' then 1 else 0 end) as awaiting,
                    sum(case when status = 'graded' then 1 else 0 end) as graded,
                    sum(case when log_type = 'daily' then 1 else 0 end) as daily,
                    sum(case when log_type = 'weekly' then 1 else 0 end) as weekly")
                ->groupBy('staff_id')
                ->get()
                ->keyBy('staff_id')
            : collect();

        // Each staff member's appraisal for the selected cycle (if one exists yet)
        $appraisalsByStaff = $cycle
            ? Appraisal::whereIn('staff_id', $staffIds)
                ->where('cycle_id', $cycle->id)
                ->get()
                ->keyBy('staff_id')
            : collect();

        // Ungraded task counts per cycle, so the selector can flag months that still need grading
        $pendingByCycle = TaskLog::whereIn('staff_id', $staffIds)
            ->where('status', 'awaiting')
            ->selectRaw('cycle_id, count(*) as total')
            ->groupBy('cycle_id')
            ->pluck('total', 'cycle_id');

        return view('supervisor.tasks', compact(
            'user', 'cycle', 'cycles', 'team', 'statsByStaff', 'appraisalsByStaff', 'pendingByCycle'
        ));
    }

    // -------------------------------------------------------
    // SUPERVISOR: one staff member's log. Shows KRA / Routine / Ideas cards;
    // ?category=kra|routine|ideas lists the tasks under that category.
    // ?type=daily|weekly narrows everything to one kind of log.
    // -------------------------------------------------------
    public function supervisorStaff(Request $request, User $staff)
    {
        $user = Auth::user();
        abort_unless($user->isSupervisor(), 403);
        abort_unless($staff->supervisor_id === $user->id, 403);

        [$cycle, $cycles] = $this->resolveCycle($request);
        $type = $this->resolveLogType($request);

        $allTasks = $cycle
            ? TaskLog::where('staff_id', $staff->id)
                ->where('cycle_id', $cycle->id)
                ->get()
            : collect();

        $typeCounts = [
            'all'    => $allTasks->count(),
            'daily'  => $allTasks->where('log_type', 'daily')->count(),
            'weekly' => $allTasks->where('log_type', 'weekly')->count(),
        ];

        $tasks = $type ? $allTasks->where('log_type', $type)->values() : $allTasks;

        // Routine is the fallback bucket (same as syncToAppraisal), so an unexpected category never disappears
        $buckets = [
            'kra'     => $tasks->where('category', 'KRA')->values(),
            'routine' => $tasks->whereNotIn('category', ['KRA', self::IDEAS_CATEGORY])->values(),
            'ideas'   => $tasks->where('category', self::IDEAS_CATEGORY)->values(),
        ];

        $meta = [
            'kra'     => ['label' => 'KRA',                            'icon' => 'ti-target',  'hint' => 'Key result area tasks'],
            'routine' => ['label' => 'Routine',                        'icon' => 'ti-repeat',  'hint' => 'Day-to-day tasks'],
            'ideas'   => ['label' => 'Ideas & Outstanding Innovation', 'icon' => 'ti-bulb',    'hint' => 'Ideas, innovation and outstanding contribution'],
        ];

        $sections = [];
        foreach ($buckets as $key => $items) {
            $sections[$key] = $meta[$key] + [
                'total'    => $items->count(),
                'awaiting' => $items->where('status', 'awaiting')->count(),
                'graded'   => $items->where('status', 'graded')->count(),
            ];
        }

        $activeKey = $request->query('category');
        if (!array_key_exists((string) $activeKey, $sections)) {
            $activeKey = null;
        }

        // Awaiting first, then newest
        $activeTasks = $activeKey
            ? $buckets[$activeKey]->sortBy([['status', 'asc'], ['date', 'desc'], ['id', 'desc']])->values()
            : collect();

        $pendingByCycle = TaskLog::where('staff_id', $staff->id)
            ->where('status', 'awaiting')
            ->selectRaw('cycle_id, count(*) as total')
            ->groupBy('cycle_id')
            ->pluck('total', 'cycle_id');

        return view('supervisor.tasks_staff', compact(
            'user', 'staff', 'cycle', 'cycles', 'sections', 'activeKey', 'activeTasks',
            'pendingByCycle', 'type', 'typeCounts'
        ));
    }

    // -------------------------------------------------------
    // SUPERVISOR: submit feedback on a task (any cycle, any time)
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

        // Sync score to Appraisal section
        $this->syncGradeToAppraisal($taskLog);

        // Push the staff member; a push failure must never block grading
        try {
            $taskLog->load(['reviewer', 'staff']);
            $taskLog->staff?->notify(new \App\Notifications\TaskGraded($taskLog));
        } catch (\Throwable $e) {
            report($e);
        }

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
    // HR: view task logs across all staff for any cycle (defaults to the active one)
    // -------------------------------------------------------
    public function hrIndex(Request $request)
    {
        abort_unless(Auth::user()->isHR(), 403);
        [$cycle, $cycles] = $this->resolveCycle($request);

        $tasks = $cycle
            ? TaskLog::where('cycle_id', $cycle->id)->get()->groupBy('staff_id')
            : collect();

        // Everyone who logs tasks, with their supervisor, so supervisors with zero logs still appear
        $allStaff = User::with('supervisor')
            ->where(function ($q) {
                $q->where('role', 'staff')
                  ->orWhere(fn ($q) => $q->where('role', 'supervisor')->where('is_staff', true));
            })
            ->orderBy('name')
            ->get();

        return view('staff_performance.tasks', compact('cycle', 'cycles', 'tasks', 'allStaff'));
    }

    public function hrSupervisor(Request $request, string $supervisor)
    {
        abort_unless(Auth::user()->isHR(), 403);
        [$cycle, $cycles] = $this->resolveCycle($request);

        $supervisorUser = $supervisor === 'none' ? null : User::findOrFail((int) $supervisor);

        $team = User::query()
            ->when(
                $supervisorUser,
                fn ($q) => $q->where('supervisor_id', $supervisorUser->id),
                fn ($q) => $q->whereNull('supervisor_id')->where(function ($q) {
                    $q->where('role', 'staff')
                      ->orWhere(fn ($q) => $q->where('role', 'supervisor')->where('is_staff', true));
                })
            )
            ->orderBy('name')
            ->get();

        $tasks = $cycle
            ? TaskLog::with('reviewer')
                ->whereIn('staff_id', $team->pluck('id'))
                ->where('cycle_id', $cycle->id)
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->get()
                ->groupBy('staff_id')
            : collect();

        return view('staff_performance.tasks_supervisor', compact('cycle', 'cycles', 'supervisorUser', 'team', 'tasks'));
    }
}
