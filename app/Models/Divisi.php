<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    protected $table = 'divisi';
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['kode_divisi', 'nama_divisi'];

    protected $dates = [];
}
