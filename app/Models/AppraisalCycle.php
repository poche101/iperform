<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppraisalCycle extends Model
{
    protected $fillable = ['name','start_date','end_date','deadline','is_active'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','deadline'=>'date','is_active'=>'boolean'];

    public static function active() { return static::where('is_active', true)->first(); }

    /** Staff can log and edit until the end of the deadline day. */
    public function isOpenForStaff(): bool
    {
        return now()->lte($this->deadline->copy()->endOfDay());
    }

    /**
     * Is this cycle open for the given user to submit / edit their own appraisal and tasks?
     * Supervisors are never locked out by the deadline; everyone else follows isOpenForStaff().
     */
    public function isOpenFor(?User $user): bool
    {
        if ($user && $user->isSupervisor()) {
            return true;
        }
        return $this->isOpenForStaff();
    }

    public function appraisals()
    {
        return $this->hasMany(Appraisal::class, 'cycle_id');
    }
}
