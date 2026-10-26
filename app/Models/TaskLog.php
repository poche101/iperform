<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    protected $fillable = [
        'staff_id',
        'cycle_id',
        'appraisal_id',
        'log_type',             // daily | weekly
        'title',
        'target',
        'date',                 // daily: the day worked; weekly: same as week_start
        'week_start',           // weekly only: "From" date picked by the staff member
        'week_end',             // weekly only: "To" date picked by the staff member
        'details',
        'challenge_identified',
        'challenge_impact',
        'category',
        'self_score',
        'completion_percentage',
        'status',
        'reviewed_by',
        'supervisor_score',
        'supervisor_comment',
        'reviewed_at',
    ];

    protected $casts = [
        'date'        => 'date',
        'week_start'  => 'date',
        'week_end'    => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function staff()    { return $this->belongsTo(User::class, 'staff_id'); }
    public function cycle()    { return $this->belongsTo(AppraisalCycle::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function appraisal()
    {
        return $this->belongsTo(Appraisal::class);
    }

    // -------------------------------------------------------
    // Daily / weekly helpers
    // -------------------------------------------------------

    public function scopeDaily(Builder $query): Builder
    {
        return $query->where('log_type', 'daily');
    }

    public function scopeWeekly(Builder $query): Builder
    {
        return $query->where('log_type', 'weekly');
    }

    public function isWeekly(): bool
    {
        return $this->log_type === 'weekly';
    }

    public function getLogTypeLabel(): string
    {
        return $this->isWeekly() ? 'Weekly' : 'Daily';
    }

    public function getLogTypeColorClass(): string
    {
        return $this->isWeekly()
            ? 'bg-sky-100 text-sky-700'
            : 'bg-gray-100 text-gray-600';
    }

    /**
     * e.g. "22 Oct 2026" for daily,
     *      "19 Oct – 25 Oct 2026" for a weekly From/To range,
     *      "19 Dec 2026 – 02 Jan 2027" when the range crosses a year.
     */
    public function getPeriodLabel(): string
    {
        if ($this->isWeekly() && $this->week_start && $this->week_end) {
            if ($this->week_start->isSameDay($this->week_end)) {
                return $this->week_start->format('d M Y');
            }

            $sameYear = $this->week_start->year === $this->week_end->year;

            return $this->week_start->format($sameYear ? 'd M' : 'd M Y')
                . ' – '
                . $this->week_end->format('d M Y');
        }

        return optional($this->date)->format('d M Y') ?? '';
    }

    // -------------------------------------------------------
    // Existing helpers
    // -------------------------------------------------------

    public function getCategoryColorClass(): string
    {
        return match($this->category) {
            'KRA'                                          => 'bg-[#eeedfe] text-[#3C3489]',
            'Innovation',
            'Ideas, Innovation & Outstanding Contribution' => 'bg-amber-100 text-amber-700',
            default                                        => 'bg-green-100 text-green-700',
        };
    }

    public function getStatusColorClass(): string
    {
        return match($this->status) {
            'graded'   => 'bg-green-100 text-green-700',
            'awaiting' => 'bg-amber-100 text-amber-700',
            default    => 'bg-gray-100 text-gray-500',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'graded'   => 'Graded',
            'awaiting' => 'Awaiting review',
            default    => 'Draft',
        };
    }
}
