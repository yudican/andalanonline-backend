<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleShift extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['schedule_time', 'schedule_title', 'schedule_type', 'shift_id', 'parent_id'];

    protected $dates = [];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get all of the attendance for the ScheduleShift
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendance()
    {
        return $this->hasOne(Attendance::class)->orderBy('created_at', 'desc');
    }
}
