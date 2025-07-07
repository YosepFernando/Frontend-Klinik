<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'no_telp',
        'password',
        'role',
        'phone',
        'address',
        'birth_date',
        'tanggal_lahir',
        'gender',
        'is_active',
        'username',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
            'tanggal_lahir' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Role helper methods
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isFrontOffice()
    {
        return $this->role === 'front_office';
    }

    public function isPelanggan()
    {
        return $this->role === 'pelanggan';
    }

    public function isKasir()
    {
        return $this->role === 'kasir';
    }

    public function isDokter()
    {
        return $this->role === 'dokter';
    }

    public function isBeautician()
    {
        return $this->role === 'beautician';
    }

    public function isHRD()
    {
        return $this->role === 'hrd';
    }

    // Relationships
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function staffAppointments()
    {
        return $this->hasMany(Appointment::class, 'staff_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function trainingsAsTrainer()
    {
        return $this->hasMany(Training::class, 'trainer_id');
    }

    public function trainingParticipations()
    {
        return $this->hasMany(TrainingParticipant::class);
    }

    public function religiousStudiesAsSpeaker()
    {
        return $this->hasMany(ReligiousStudy::class, 'speaker_id');
    }

    public function religiousStudyParticipations()
    {
        return $this->hasMany(ReligiousStudyParticipant::class);
    }

    // Relationship to Pegawai (Employee)
    public function pegawai()
    {
        return $this->hasOne(Pegawai::class, 'id_user');
    }

    // Relationship to new Absensi table through Pegawai
    // Disabled - using API only
    /*
    public function absensi()
    {
        return $this->hasManyThrough(
            // Absensi::class,
            'App\Models\Absensi', // Disabled
            Pegawai::class,
            'id_user', // Foreign key on pegawai table
            'id_pegawai', // Foreign key on absensi table
            'id', // Local key on user table
            'id_pegawai' // Local key on pegawai table
        );
    }
    */

    // Recruitment application relationship
    public function recruitmentApplications()
    {
        return $this->hasMany(RecruitmentApplication::class);
    }
}
