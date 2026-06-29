<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppraisalTask extends Model
{
    protected $fillable = [
        'appraisal_id',
        'task_log_id',           // ← NEW: Link to original logged task
        'sn',
        'task',
        'performance',
        'completion_percentage',
        'staff_score',
        'supervisor_score',
    ];

    protected $casts = [
        'completion_percentage' => 'integer',
        'staff_score'           => 'integer',
        'supervisor_score'      => 'integer',
    ];

    // Relationships
    public function appraisal()
    {
        return $this->belongsTo(Appraisal::class);
    }

    public function taskLog()
    {
        return $this->belongsTo(TaskLog::class);
    }
}
