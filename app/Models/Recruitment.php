<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recruitment extends Model
{
    protected $fillable = [
        'position',
        'id_posisi',
        'description',
        'requirements',
        'status',
        'application_deadline',
        'slots',
        'salary_min',
        'salary_max',
        'employment_type',
        'age_min',
        'age_max',
    ];

    protected $casts = [
        'application_deadline' => 'date',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
    ];

    public function isOpen()
    {
        return $this->status === 'open' && $this->application_deadline->isFuture();
    }

    public function getEmploymentTypeDisplayAttribute()
    {
        return match($this->employment_type) {
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Contract',
            default => ucfirst($this->employment_type)
        };
    }

    public function getSalaryRangeAttribute()
    {
        if ($this->salary_min && $this->salary_max) {
            return 'Rp ' . number_format($this->salary_min, 0, ',', '.') . ' - Rp ' . number_format($this->salary_max, 0, ',', '.');
        } elseif ($this->salary_min) {
            return 'Minimal Rp ' . number_format($this->salary_min, 0, ',', '.');
        } elseif ($this->salary_max) {
            return 'Maksimal Rp ' . number_format($this->salary_max, 0, ',', '.');
        }
        return 'Negosiasi';
    }

    public function getAgeRangeAttribute()
    {
        if ($this->age_min && $this->age_max) {
            return $this->age_min . ' - ' . $this->age_max . ' tahun';
        } elseif ($this->age_min) {
            return 'Minimal ' . $this->age_min . ' tahun';
        } elseif ($this->age_max) {
            return 'Maksimal ' . $this->age_max . ' tahun';
        }
        return 'Tidak ada batasan usia';
    }

    // Relationships
    public function posisi()
    {
        return $this->belongsTo(Posisi::class, 'id_posisi', 'id_posisi');
    }

    public function applications()
    {
        return $this->hasMany(RecruitmentApplication::class);
    }

    public function acceptedApplications()
    {
        return $this->hasMany(RecruitmentApplication::class)->where('final_status', 'accepted');
    }

    // Helper methods
    public function getAvailableSlots()
    {
        return $this->slots - $this->acceptedApplications()->count();
    }

    public function hasUserApplied($userId)
    {
        return $this->applications()->where('user_id', $userId)->exists();
    }

    public function getUserApplication($userId)
    {
        return $this->applications()->where('user_id', $userId)->first();
    }
}
