<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    protected $table = 'cabang';
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['kode_cabang', 'lokasi_cabang', 'nama_cabang'];

    protected $dates = [];
}
