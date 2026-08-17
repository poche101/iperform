<?php
// app/Models/User.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    use Notifiable;
    use HasPushSubscriptions;

    protected $fillable = ['name','username','email','password','role','department','designation','title','supervisor_id'];
    protected $hidden = ['password','remember_token'];

    public function isHR() { return $this->role === 'staff_performance'; }
    public function supervisor() { return $this->belongsTo(User::class, 'supervisor_id'); }
    public function subordinates() { return $this->hasMany(User::class, 'supervisor_id'); }
    public function appraisals() { return $this->hasMany(Appraisal::class, 'staff_id'); }
    public function isStaffPerformance() { return $this->role === 'staff_performance'; }
    public function isSupervisor() { return $this->role === 'supervisor'; }
    public function isStaff() { return $this->role === 'staff'; }

    public function currentAppraisal()
    {
        $cycle = AppraisalCycle::where('is_active', true)->first();
        if (!$cycle) return null;
        return $this->appraisals()->where('cycle_id', $cycle->id)->first();
    }
}
