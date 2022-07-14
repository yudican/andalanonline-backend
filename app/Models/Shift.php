<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    //use Uuid;
    use HasFactory;
    protected $table = 'shift';
    //public $incrementing = false;

    protected $fillable = ['kode_shift', 'nama_shift'];

    protected $dates = [];

    /**
     * Get all of the scheduleShift for the Shift
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function scheduleShift()
    {
        return $this->hasMany(ScheduleShift::class);
    }
}
