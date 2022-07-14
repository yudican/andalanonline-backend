<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;
    protected $table = 'attendance_request';

    protected $guarded = [];
    protected $appends = ['status'];

    /**
     * Get the user that owns the AttendanceRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusAttribute()
    {
        return statusPengajuan($this->attributes['request_status']);
    }

    /**
     * Get the shift that owns the AttendanceRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
