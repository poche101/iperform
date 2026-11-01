<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class HrNotificationController extends Controller
{
    /**
     * Every appraisal a staff member has submitted (anything that is no longer a draft),
     * newest first. Uses submitted_at when it exists, otherwise updated_at.
     */
    public static function feed(): Collection
    {
        return Appraisal::with(['staff', 'cycle'])
            ->where(function ($q) {
                $q->whereNotNull('submitted_at')
                  ->orWhere(function ($q) {
                      $q->whereNotNull('status')
                        ->whereRaw("LOWER(status) != 'drafting'");
                  });
            })
            ->orderByRaw('COALESCE(submitted_at, updated_at) DESC')
            ->limit(30)
            ->get()
            ->map(function ($a) {
                $st   = $a->staff;
                $dept = $st->department ?? '';
                $desg = $st->designation ?? '';
                $when = $a->submitted_at ?? $a->updated_at;

                return [
                    'id'    => $a->id,
                    'name'  => $st->name ?? 'Unknown staff',
                    'meta'  => trim($dept . ($dept && $desg ? ' · ' : '') . $desg),
                    'cycle' => $a->cycle->name ?? '',
                    'ago'   => $when ? $when->diffForHumans() : '',
                    'ts'    => $when ? $when->timestamp : 0,
                    'url'   => route('hr.appraisal.show', $a),
                ];
            })
            ->values();
    }

    public function appraisals(): JsonResponse
    {
        abort_unless(Auth::user()->isHR(), 403);

        return response()->json(['items' => self::feed()]);
    }
}
