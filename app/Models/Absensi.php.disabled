<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Absensi extends Model
{
    protected $table = 'tb_absensi';
    protected $primaryKey = 'id_absensi';
    
    protected $fillable = [
        'id_pegawai',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_keluar',
        'longitude_keluar',
        'alamat_masuk',
        'alamat_keluar',
        'catatan'
    ];
    
    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime',
        'jam_keluar' => 'datetime',
        'latitude_masuk' => 'decimal:8',
        'longitude_masuk' => 'decimal:8',
        'latitude_keluar' => 'decimal:8',
        'longitude_keluar' => 'decimal:8'
    ];
    
    // Relasi dengan pegawai
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'id_pegawai', 'id_pegawai');
    }
    
    // Helper methods
    public function getDurasiKerjaAttribute()
    {
        if ($this->jam_masuk && $this->jam_keluar) {
            $masuk = Carbon::parse($this->jam_masuk);
            $keluar = Carbon::parse($this->jam_keluar);
            
            $minutes = $masuk->diffInMinutes($keluar);
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;
            
            return sprintf('%d jam %d menit', $hours, $remainingMinutes);
        }
        
        return '-';
    }
    
    public function isLate()
    {
        if (!$this->jam_masuk) return false;
        
        $workStartTime = Carbon::createFromTime(8, 0, 0);
        $checkInTime = Carbon::parse($this->jam_masuk);
        
        return $checkInTime->format('H:i') > $workStartTime->format('H:i');
    }
    
    public function canCheckOut()
    {
        return $this->jam_masuk && !$this->jam_keluar;
    }
    
    public function getStatusAttribute()
    {
        if (!$this->jam_masuk) {
            return 'Tidak Hadir';
        }
        
        if ($this->isLate()) {
            return 'Terlambat';
        }
        
        return 'Hadir';
    }
}
