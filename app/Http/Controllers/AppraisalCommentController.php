<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use App\Models\AppraisalInnovation;
use App\Models\AppraisalKra;
use App\Models\AppraisalTask;
use App\Models\TaskLog;
use App\Notifications\TaskCommented;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AppraisalCommentController extends Controller
{
    private const MODELS = [
        'kra'        => AppraisalKra::class,
        'task'       => AppraisalTask::class,
        'innovation' => AppraisalInnovation::class,
    ];

    /**
     * Save (or clear) the supervisor's comment on one KRA / task / idea row.
     * Same rule as grading: only the assigned supervisor, while the appraisal is drafting or submitted.
     * A new or changed comment is copied to the staff member's task log and notifies them (bell + push).
     */
    public function store(Request $request, Appraisal $appraisal): JsonResponse
    {
        abort_unless(Auth::id() === $appraisal->supervisor_id, 403);
        abort_unless(
            in_array($appraisal->status, ['drafting', 'submitted'], true),
            403,
            'This appraisal has been forwarded and can no longer be commented on.'
        );

        $data = $request->validate([
            'type'    => ['required', Rule::in(array_keys(self::MODELS))],
            'id'      => ['required', 'integer'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $model = self::MODELS[$data['type']];

        // Scoped to this appraisal, so a row from someone else's appraisal can never be edited.
        $row = $model::where('appraisal_id', $appraisal->id)->findOrFail($data['id']);

        $previous = trim((string) ($row->supervisor_comment ?? ''));
        $comment  = trim((string) ($data['comment'] ?? ''));

        $row->forceFill(['supervisor_comment' => $comment !== '' ? $comment : null])->save();

        // Mirror the comment onto the staff member's own task log, so their Tasks page can show it.
        $log = $row->task_log_id
            ? TaskLog::where('staff_id', $appraisal->staff_id)->find($row->task_log_id)
            : null;

        if ($log) {
            $log->forceFill([
                'supervisor_note'    => $comment !== '' ? $comment : null,
                'supervisor_note_at' => $comment !== '' ? now() : null,
            ])->save();
        }

        // Notify only when there is text and it is new or changed. A push failure must never block saving.
        if ($comment !== '' && $comment !== $previous) {
            $title = match ($data['type']) {
                'kra'   => $row->kra,
                'task'  => $row->task,
                default => $row->idea,
            };

            try {
                $appraisal->staff?->notify(new TaskCommented(
                    (string) $title,
                    $comment,
                    Auth::user()->name,
                    $log?->id,
                    $appraisal->id,
                ));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'ok'       => true,
            'comment'  => $comment,
            'saved_at' => now()->format('d M Y, H:i'),
        ]);
    }
}
