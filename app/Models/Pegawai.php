<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pegawai extends Model
{
    protected $table = 'tb_pegawai';
    protected $primaryKey = 'id_pegawai';
    
    protected $fillable = [
        'id_user',
        'nama_lengkap',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'telepon',
        'email',
        'NIK',
        'id_posisi',
        'agama',
        'tanggal_masuk',
        'tanggal_keluar'
    ];
    
    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
        'tanggal_keluar' => 'date'
    ];
    
    // Relasi dengan user
    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id');
    }
    
    // Relasi dengan posisi
    public function posisi()
    {
        return $this->belongsTo(Posisi::class, 'id_posisi', 'id_posisi');
    }
    
    // Relasi dengan absensi
    // Disabled - using API only
    /*
    public function absensi()
    {
        return $this->hasMany('App\Models\Absensi', 'id_pegawai', 'id_pegawai'); // Disabled
    }
    */
    
    // Relasi dengan gaji
    public function gaji()
    {
        return $this->hasMany(Gaji::class, 'id_pegawai', 'id_pegawai');
    }
    
    // Helper methods
    public function getUmurAttribute()
    {
        if ($this->tanggal_lahir) {
            return Carbon::parse($this->tanggal_lahir)->age;
        }
        return null;
    }
    
    public function getMasaKerjaAttribute()
    {
        if ($this->tanggal_masuk) {
            $endDate = $this->tanggal_keluar ?: Carbon::now();
            return Carbon::parse($this->tanggal_masuk)->diffInMonths($endDate);
        }
        return 0;
    }
    
    public function isActive()
    {
        return is_null($this->tanggal_keluar);
    }
}
