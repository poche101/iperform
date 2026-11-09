<?php

namespace App\Console\Commands;

use App\Models\Appraisal;
use App\Models\AppraisalCycle;
use App\Models\AppraisalInnovation;
use App\Models\AppraisalKra;
use App\Models\AppraisalTask;
use App\Models\TaskLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Repairs staff whose task logs lost their link to an appraisal (appraisal_id is NULL),
 * which leaves the appraisal empty ("No tasks recorded") for the supervisor.
 *
 * Dry run by default; nothing is written unless --apply is passed.
 */
class RelinkTaskLogs extends Command
{
    protected $signature = 'iperform:relink-tasks
                            {--cycle= : Cycle id (default: the active cycle)}
                            {--staff= : Only this staff user id}
                            {--apply : Write the changes (without this it is a dry run)}';

    protected $description = 'Re-link task logs that lost their appraisal and rebuild the KRA/Task/Idea rows from them';

    private const IDEAS_CATEGORY = 'Ideas, Innovation & Outstanding Contribution';

    public function handle(): int
    {
        $cycleId = $this->option('cycle') ?: AppraisalCycle::where('is_active', true)->value('id');

        if (! $cycleId) {
            $this->error('No cycle given and no active cycle found.');
            return self::FAILURE;
        }

        $apply = (bool) $this->option('apply');

        $logs = TaskLog::where('cycle_id', $cycleId)
            ->whereNull('appraisal_id')
            ->when($this->option('staff'), fn ($q, $id) => $q->where('staff_id', (int) $id))
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->groupBy('staff_id');

        if ($logs->isEmpty()) {
            $this->info("Cycle {$cycleId}: no unlinked task logs. Nothing to do.");
            return self::SUCCESS;
        }

        $this->line(($apply ? 'APPLYING' : 'DRY RUN') . " for cycle {$cycleId}: {$logs->count()} staff with unlinked task logs");
        $this->newLine();

        $fixed = 0;
        $skipped = 0;

        foreach ($logs as $staffId => $staffLogs) {
            $user = User::find($staffId);

            if (! $user) {
                $this->warn("Staff #{$staffId}: user not found, skipped ({$staffLogs->count()} logs).");
                $skipped++;
                continue;
            }

            $name      = trim(preg_replace('/\s+/', ' ', (string) $user->name));
            $appraisal = Appraisal::where('staff_id', $staffId)->where('cycle_id', $cycleId)->first();

            if ($appraisal && $appraisal->status === 'approved') {
                $this->warn("#{$staffId} {$name}: appraisal #{$appraisal->id} is already approved, skipped ({$staffLogs->count()} logs).");
                $skipped++;
                continue;
            }

            $state = $appraisal
                ? "appraisal #{$appraisal->id} ({$appraisal->status})"
                : 'no appraisal yet (one will be created as a draft)';

            $kra   = $staffLogs->where('category', 'KRA')->count();
            $ideas = $staffLogs->where('category', self::IDEAS_CATEGORY)->count();
            $tasks = $staffLogs->count() - $kra - $ideas;

            $this->line("#{$staffId} {$name}: {$state}; {$staffLogs->count()} logs -> KRA {$kra}, Tasks {$tasks}, Ideas {$ideas}");

            if (! $apply) {
                continue;
            }

            DB::transaction(function () use ($user, $staffId, $cycleId, $staffLogs, &$appraisal) {
                // Same defaults TaskLogController::staffStore uses
                $appraisal = $appraisal ?: Appraisal::firstOrCreate(
                    ['staff_id' => $staffId, 'cycle_id' => $cycleId],
                    ['supervisor_id' => $user->supervisor_id, 'status' => 'drafting']
                );

                foreach ($staffLogs as $log) {
                    $this->syncLog($log, $appraisal);
                    $log->update(['appraisal_id' => $appraisal->id]);
                }
            });

            $fixed++;
        }

        $this->newLine();

        if ($apply) {
            $this->info("Done. Repaired {$fixed} staff, skipped {$skipped}.");
        } else {
            $this->comment('Dry run only, nothing was written. Re-run with --apply to make these changes.');
        }

        return self::SUCCESS;
    }

    /** Mirrors TaskLogController::syncToAppraisal (+ the supervisor score for already-graded logs). */
    private function syncLog(TaskLog $log, Appraisal $appraisal): void
    {
        $model = match ($log->category) {
            'KRA'                => AppraisalKra::class,
            self::IDEAS_CATEGORY => AppraisalInnovation::class,
            default              => AppraisalTask::class, // Routine
        };

        $fields = [
            'completion_percentage' => $log->completion_percentage,
            'staff_score'           => $log->self_score,
        ];

        if ($model === AppraisalKra::class) {
            $fields += ['kra' => $log->title, 'target' => $log->target, 'achievement' => $log->details];
        } elseif ($model === AppraisalInnovation::class) {
            $fields += ['idea' => $log->title, 'impact' => $log->details];
        } else {
            $fields += ['task' => $log->title, 'performance' => $log->details];
        }

        if ($log->status === 'graded' && $log->supervisor_score !== null) {
            $fields['supervisor_score'] = $log->supervisor_score;
        }

        $row = $model::where('appraisal_id', $appraisal->id)
            ->where('task_log_id', $log->id)
            ->first();

        if ($row) {
            $row->update($fields);
            return;
        }

        $sn = ($model::where('appraisal_id', $appraisal->id)->max('sn') ?? 0) + 1;

        $model::create($fields + [
            'appraisal_id' => $appraisal->id,
            'task_log_id'  => $log->id,
            'sn'           => $sn,
        ]);
    }
}
