<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class HrNotificationController extends Controller
{
    /**
     * Notification feed for the HR bell: two kinds of event per appraisal, newest first.
     *   - submitted: the staff member submitted their appraisal (submitted_at)
     *   - graded:    the supervisor finished grading and forwarded it (forwarded_at)
     * An appraisal that has been both submitted and graded appears twice, once per event.
     */
    public static function feed(): Collection
    {
        $appraisals = Appraisal::with(['staff', 'cycle', 'supervisor'])
            ->where(function ($q) {
                $q->whereNotNull('submitted_at')
                  ->orWhereNotNull('forwarded_at')
                  ->orWhereIn('status', ['submitted', 'with_staff_performance', 'approved']);
            })
            ->orderByRaw('COALESCE(forwarded_at, submitted_at, updated_at) DESC')
            ->limit(50)
            ->get();

        $events = collect();

        foreach ($appraisals as $a) {
            $st   = $a->staff;
            $dept = $st->department ?? '';
            $desg = $st->designation ?? '';

            $base = [
                'id'    => $a->id,
                'name'  => $st->name ?? 'Unknown staff',
                'meta'  => trim($dept . ($dept && $desg ? ' · ' : '') . $desg),
                'cycle' => $a->cycle->name ?? '',
                'by'    => $a->supervisor->name ?? '',
                'url'   => route('hr.appraisal.show', $a),
            ];

            // Submitted by staff
            if ($a->submitted_at || $a->status !== 'drafting') {
                $when = $a->submitted_at ?? $a->updated_at;
                $events->push($base + [
                    'key'  => 'submitted-' . $a->id,
                    'type' => 'submitted',
                    'ago'  => $when ? $when->diffForHumans() : '',
                    'ts'   => $when ? $when->timestamp : 0,
                ]);
            }

            // Graded by the supervisor (forwarded to Staff Performance)
            $graded = $a->forwarded_at !== null
                || in_array($a->status, ['with_staff_performance', 'approved'], true);

            if ($graded) {
                $when = $a->forwarded_at ?? $a->updated_at;
                $events->push($base + [
                    'key'  => 'graded-' . $a->id,
                    'type' => 'graded',
                    'ago'  => $when ? $when->diffForHumans() : '',
                    'ts'   => $when ? $when->timestamp : 0,
                ]);
            }
        }

        return $events->sortByDesc('ts')->take(40)->values();
    }

    public function appraisals(): JsonResponse
    {
        abort_unless(Auth::user()->isHR(), 403);

        return response()->json(['items' => self::feed()]);
    }
}
