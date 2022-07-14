<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['time'];
    /**
     * Get the user that owns the Attendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the schedule that owns the Attendance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function schedule()
    {
        return $this->belongsTo(ScheduleShift::class, 'schedule_shift_id');
    }

    public function getTimeAttribute()
    {
        if ($this->attributes['attendance_date']) {
            return date('H:i:s', strtotime($this->attributes['attendance_date']));
        }

        return $this->schedule->schedule_time;
    }
}
