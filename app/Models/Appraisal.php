<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appraisal extends Model
{
    protected $fillable = [
        'cycle_id','staff_id','supervisor_id','status',
        'section5','section6','section7_items',
        'overall_contribution','key_strengths','areas_for_improvement',
        'salary_percent','supervisor_comments','supervisor_confirmed',
        'staff_performance_s1_weighted','staff_performance_s2_weighted','staff_performance_s3_weighted','staff_performance_s4_weighted',
        'staff_performance_overall','staff_performance_grade','staff_performance_comments',
        'submitted_at','forwarded_at','approved_at',
    ];

    protected $casts = [
        'section5'             => 'array',
        'section6'             => 'array',
        'section7_items'       => 'array',
        'supervisor_confirmed' => 'boolean',
        'submitted_at'         => 'datetime',
        'forwarded_at'         => 'datetime',
        'approved_at'          => 'datetime',
    ];

    // -------------------------------------------------------
    // Accessors — safely decode JSON strings (SQLite fix)
    // -------------------------------------------------------
    public function getSection5Attribute($value): array
    {
        if (is_array($value)) return $value;
        if (empty($value)) return [];
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function getSection6Attribute($value): array
    {
        if (is_array($value)) return $value;
        if (empty($value)) return [];
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    public function getSection7ItemsAttribute($value): array
    {
        if (is_array($value)) return $value;
        if (empty($value)) return $this->getDefaultSection7();
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : $this->getDefaultSection7();
    }

    // -------------------------------------------------------
    // Mutators — always store as JSON string
    // -------------------------------------------------------
    public function setSection5Attribute($value): void
    {
        $this->attributes['section5'] = is_array($value) ? json_encode($value) : ($value ?? '[]');
    }

    public function setSection6Attribute($value): void
    {
        $this->attributes['section6'] = is_array($value) ? json_encode($value) : ($value ?? '[]');
    }

    public function setSection7ItemsAttribute($value): void
    {
        $this->attributes['section7_items'] = is_array($value) ? json_encode($value) : ($value ?? '[]');
    }

    // -------------------------------------------------------
    // Relationships
    // -------------------------------------------------------
    public function cycle()        { return $this->belongsTo(AppraisalCycle::class); }
    public function staff()        { return $this->belongsTo(User::class, 'staff_id'); }
    public function supervisor()   { return $this->belongsTo(User::class, 'supervisor_id'); }

    public function kras()         { return $this->hasMany(AppraisalKra::class)->orderBy('sn'); }
    public function tasks()        { return $this->hasMany(AppraisalTask::class)->orderBy('sn'); }
    public function innovations()  { return $this->hasMany(AppraisalInnovation::class)->orderBy('sn'); }
    public function competencies() { return $this->hasMany(AppraisalCompetency::class)->orderBy('sn'); }

    // NEW: Link to daily logged tasks
    public function taskLogs()
    {
        return $this->hasMany(TaskLog::class);
    }

    // Optional: For easier access to only Routine tasks, etc.
    public function routineTasks()
    {
        return $this->taskLogs()->where('category', 'Routine');
    }

    // -------------------------------------------------------
    // Business logic
    // -------------------------------------------------------
    public function calcWeighted($items, $weight, $key = 'supervisor_score')
    {
        $valid = collect($items)->filter(fn($i) => isset($i[$key]) && $i[$key] !== null);
        if ($valid->isEmpty()) return null;
        return round(($valid->avg($key) / 10) * $weight, 2);
    }

    public function autoCalculate()
    {
        $s1      = $this->calcWeighted($this->kras->toArray(), 35);
        $s2      = $this->calcWeighted($this->tasks->toArray(), 25);
        $s3      = $this->calcWeighted($this->innovations->toArray(), 20);
        $s7total = collect($this->section7_items)->sum('score');
        $s4      = round(($s7total / 60) * 20, 2);
        $overall = round(($s1 ?? 0) + ($s2 ?? 0) + ($s3 ?? 0) + $s4, 2);
        $grade   = $this->getGrade($overall);

        $this->update([
            'staff_performance_s1_weighted' => $s1,
            'staff_performance_s2_weighted' => $s2,
            'staff_performance_s3_weighted' => $s3,
            'staff_performance_s4_weighted' => $s4,
            'staff_performance_overall'     => $overall,
            'staff_performance_grade'       => $grade,
        ]);

        return $this;
    }

    public function getGrade(float $score): string
    {
        return match(true) {
            $score >= 100 => 'A+',
            $score >= 90  => 'A',
            $score >= 80  => 'B+',
            $score >= 70  => 'B',
            $score >= 60  => 'C+',
            $score >= 50  => 'C',
            $score >= 40  => 'D',
            $score >= 30  => 'E',
            default       => 'F',
        };
    }

    public function getDefaultSection7(): array
    {
        return [
            ['sn'=>1,'policy'=>'Attendance at work','score'=>null,'comment'=>''],
            ['sn'=>2,'policy'=>'Chapel Attendance','score'=>null,'comment'=>''],
            ['sn'=>3,'policy'=>'Punctuality to work','score'=>null,'comment'=>''],
            ['sn'=>4,'policy'=>'Prompt submission of weekly/monthly reports','score'=>null,'comment'=>''],
            ['sn'=>5,'policy'=>'Participation in meetings & prayer','score'=>null,'comment'=>''],
            ['sn'=>6,'policy'=>'Official warnings/disciplinary actions','score'=>null,'comment'=>''],
        ];
    }
}
