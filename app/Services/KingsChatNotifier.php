<?php

namespace App\Services;

use App\Models\Appraisal;
use App\Models\User;

/**
 * Who gets told what, and when. Messages go out after the HTTP response,
 * so nobody waits on KingsChat. People who have never signed in with KingsChat
 * (no kc_user_id) are skipped by KingsChatMessenger.
 */
class KingsChatNotifier
{
    /** Staff submitted -> notify their supervisor */
    public static function submitted(Appraisal $appraisal): void
    {
        $appraisal->loadMissing(['staff', 'cycle']);

        self::notify(User::find($appraisal->supervisor_id), sprintf(
            "iPerform: %s has submitted their %s appraisal for your review.\n%s",
            $appraisal->staff->name ?? 'A staff member',
            $appraisal->cycle->name ?? '',
            route('supervisor.appraisal.show', $appraisal)
        ));
    }

    /** Supervisor graded and forwarded -> notify the staff and Staff Performance */
    public static function graded(Appraisal $appraisal): void
    {
        $appraisal->loadMissing(['staff', 'cycle']);
        $supervisor = User::find($appraisal->supervisor_id);

        self::notify($appraisal->staff, sprintf(
            "iPerform: Your supervisor%s has graded your %s appraisal. It is now with Staff Performance for final review.\n%s",
            $supervisor ? ' (' . $supervisor->name . ')' : '',
            $appraisal->cycle->name ?? '',
            route('staff.appraisal.view', $appraisal)
        ));

        $hrMessage = sprintf(
            "iPerform: %s's %s appraisal has been graded by the supervisor and is ready for Staff Performance review.\n%s",
            $appraisal->staff->name ?? 'A staff member',
            $appraisal->cycle->name ?? '',
            route('hr.appraisal.show', $appraisal)
        );
        foreach (User::where('role', 'staff_performance')->get() as $hr) {
            self::notify($hr, $hrMessage);
        }
    }

    /** Staff Performance approved -> notify the staff and the supervisor */
    public static function approved(Appraisal $appraisal): void
    {
        $appraisal->loadMissing(['staff', 'cycle']);
        $result = ($appraisal->staff_performance_overall !== null)
            ? sprintf(' Score: %s/100, Grade: %s.', $appraisal->staff_performance_overall, $appraisal->staff_performance_grade ?? '-')
            : '';

        self::notify($appraisal->staff, sprintf(
            "iPerform: Staff Performance has finalised your %s appraisal.%s\n%s",
            $appraisal->cycle->name ?? '', $result, route('staff.appraisal.view', $appraisal)
        ));

        self::notify(User::find($appraisal->supervisor_id), sprintf(
            "iPerform: Staff Performance has finalised %s's %s appraisal.%s\n%s",
            $appraisal->staff->name ?? 'a staff member', $appraisal->cycle->name ?? '', $result,
            route('supervisor.appraisal.show', $appraisal)
        ));
    }

    /** Send after the response; skip anyone without a KingsChat ID. */
    private static function notify(?User $user, string $message): void
    {
        if (!$user || !$user->kc_user_id) {
            return;
        }
        app()->terminating(fn () => app(KingsChatMessenger::class)->send($user, $message));
    }
}
