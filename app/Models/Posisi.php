<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Posisi extends Model
{
    protected $table = 'tb_posisi';
    protected $primaryKey = 'id_posisi';
    
    protected $fillable = [
        'nama_posisi',
        'gaji_pokok',
        'persen_bonus'
    ];
    
    protected $casts = [
        'gaji_pokok' => 'decimal:2',
        'persen_bonus' => 'decimal:2'
    ];
    
    // Relasi dengan pegawai
    public function pegawai()
    {
        return $this->hasMany(Pegawai::class, 'id_posisi', 'id_posisi');
    }
    
    // Relasi dengan lowongan pekerjaan
    public function lowonganPekerjaan()
    {
        return $this->hasMany(LowonganPekerjaan::class, 'id_posisi', 'id_posisi');
    }
}
