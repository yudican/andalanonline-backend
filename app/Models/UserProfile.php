<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['tanggal_lahir', 'tanggal_masuk_kerja', 'jenis_kelamin', 'alamat', 'foto_ktp', 'foto_wajah', 'cabang_id', 'divisi_id', 'user_id'];

    protected $dates = [];

    /**
     * Get the user that owns the UserProfile
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }
}
