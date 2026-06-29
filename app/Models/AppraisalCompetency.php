<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppraisalCompetency extends Model
{
    protected $fillable = [
        'appraisal_id',
        'sn',
        'competency',
        'staff_score',
        'supervisor_score',
    ];
}
