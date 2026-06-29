<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AppraisalCycle extends Model
{
    protected $fillable = ['name','start_date','end_date','deadline','is_active'];
    protected $casts = ['start_date'=>'date','end_date'=>'date','deadline'=>'date','is_active'=>'boolean'];
    public static function active() { return static::where('is_active', true)->first(); }
}
