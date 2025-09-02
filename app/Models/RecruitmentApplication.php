<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecruitmentApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'recruitment_id',
        'user_id',
        'full_name',
        'nik',
        'email',
        'phone',
        'address',
        'education',
        'cover_letter',
        'cv_file',
        'cv_path',
        'cover_letter_path',
        'additional_documents',
        'additional_documents_path',
        'document_status',
        'document_notes',
        'document_reviewed_at',
        'document_reviewed_by',
        'interview_status',
        'interview_date',
        'interview_scheduled_at',
        'interview_location',
        'interview_notes',
        'interview_score',
        'interview_completed_at',
        'interview_conducted_by',
        'interview_scheduled_by',
        'final_status',
        'final_notes',
        'final_decided_at',
        'final_decided_by',
        'start_date',
        'overall_status',
    ];

    protected $casts = [
        'additional_documents' => 'array',
        'document_reviewed_at' => 'datetime',
        'interview_date' => 'datetime',
        'interview_scheduled_at' => 'datetime',
        'interview_completed_at' => 'datetime',
        'final_decided_at' => 'datetime',
        'start_date' => 'date',
    ];

    // Relationships
    public function recruitment()
    {
        return $this->belongsTo(Recruitment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentReviewer()
    {
        return $this->belongsTo(User::class, 'document_reviewed_by');
    }

    public function interviewConductor()
    {
        return $this->belongsTo(User::class, 'interview_conducted_by');
    }

    public function finalDecider()
    {
        return $this->belongsTo(User::class, 'final_decided_by');
    }

    // Helper methods
    public function canAccessInterviewStage()
    {
        return $this->document_status === 'accepted';
    }

    public function canAccessFinalStage()
    {
        return $this->document_status === 'accepted' && $this->interview_status === 'accepted';
    }

    public function getCurrentStage()
    {
        if ($this->final_status !== 'pending') {
            return 'final';
        } elseif ($this->interview_status !== 'pending') {
            return 'interview';
        } else {
            return 'document';
        }
    }

    public function getStatusBadgeClass()
    {
        return match($this->overall_status) {
            'applied' => 'bg-secondary',
            'document_review' => 'bg-info',
            'interview_stage' => 'bg-warning',
            'final_review' => 'bg-primary',
            'accepted' => 'bg-success',
            'rejected' => 'bg-danger',
            default => 'bg-secondary'
        };
    }

    public function getStatusLabel()
    {
        return match($this->overall_status) {
            'applied' => 'Melamar',
            'document_review' => 'Seleksi Berkas',
            'interview_stage' => 'Tahap Wawancara',
            'final_review' => 'Tahap Akhir',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
            default => 'Unknown'
        };
    }

    public function getEducationDisplayAttribute()
    {
        return match($this->education) {
            'SD' => 'SD/Sederajat',
            'SMP' => 'SMP/Sederajat',
            'SMA' => 'SMA/SMK/Sederajat',
            'D1' => 'Diploma 1 (D1)',
            'D2' => 'Diploma 2 (D2)',
            'D3' => 'Diploma 3 (D3)',
            'D4' => 'Diploma 4 (D4)',
            'S1' => 'Sarjana (S1)',
            'S2' => 'Magister (S2)',
            'S3' => 'Doktor (S3)',
            default => ucfirst($this->education ?? '')
        };
    }
}
