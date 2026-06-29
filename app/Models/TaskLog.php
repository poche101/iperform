<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    protected $fillable = [
        'staff_id',
        'cycle_id',
        'appraisal_id',
        'title',
        'target',
        'date',
        'details',
        'challenge_identified', // Add this
    'challenge_impact',     // Add this
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
        'reviewed_at' => 'datetime',
    ];

    public function staff()    { return $this->belongsTo(User::class, 'staff_id'); }
    public function cycle()    { return $this->belongsTo(AppraisalCycle::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function getCategoryColorClass(): string
    {
        return match($this->category) {
            'KRA'        => 'bg-[#eeedfe] text-[#3C3489]',
            'Innovation' => 'bg-amber-100 text-amber-700',
            default      => 'bg-green-100 text-green-700',
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

    public function appraisal()
    {
        return $this->belongsTo(Appraisal::class);
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
