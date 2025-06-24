<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
        'clock_in_address',
        'clock_out_address',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime:H:i:s',
        'clock_out' => 'datetime:H:i:s',
        'clock_in_latitude' => 'decimal:8',
        'clock_in_longitude' => 'decimal:8',
        'clock_out_latitude' => 'decimal:8',
        'clock_out_longitude' => 'decimal:8',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'present' => 'Hadir',
            'absent' => 'Tidak Hadir',
            'late' => 'Terlambat',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            default => ucfirst($this->status)
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'present' => 'badge bg-success',
            'absent' => 'badge bg-danger',
            'late' => 'badge bg-warning',
            'sick' => 'badge bg-info',
            'permission' => 'badge bg-secondary',
            default => 'badge bg-secondary'
        };
    }

    public function getWorkDurationAttribute()
    {
        if ($this->clock_in && $this->clock_out) {
            $checkIn = Carbon::parse($this->clock_in);
            $checkOut = Carbon::parse($this->clock_out);
            
            $minutes = $checkIn->diffInMinutes($checkOut);
            $hours = floor($minutes / 60);
            $remainingMinutes = $minutes % 60;
            
            return sprintf('%d jam %d menit', $hours, $remainingMinutes);
        }
        
        return '-';
    }

    public function isLate()
    {
        if (!$this->clock_in) return false;
        
        $workStartTime = Carbon::createFromTime(8, 0, 0);
        $checkInTime = Carbon::parse($this->clock_in);
        
        return $checkInTime->format('H:i') > $workStartTime->format('H:i');
    }

    public function canCheckOut()
    {
        return $this->clock_in && !$this->clock_out && in_array($this->status, ['present', 'late']);
    }
}
