<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $table = 'tb_pelatihan';
    protected $primaryKey = 'id_pelatihan';

    protected $fillable = [
        'judul',
        'deskripsi',
        'jenis_pelatihan',
        'konten',
        'link_url',
        'durasi',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getJenisDisplayAttribute()
    {
        return match($this->jenis_pelatihan) {
            'video' => 'Video Online',
            'document' => 'Dokumen',
            'offline' => 'Offline/Tatap Muka',
            default => ucfirst($this->jenis_pelatihan ?? 'Tidak Diketahui')
        };
    }

    public function getStatusDisplayAttribute()
    {
        return $this->is_active ? 'Aktif' : 'Tidak Aktif';
    }

    public function getStatusBadgeClassAttribute()
    {
        return $this->is_active ? 'badge bg-success' : 'badge bg-secondary';
    }

    public function getJenisBadgeClassAttribute()
    {
        return match($this->jenis_pelatihan) {
            'video' => 'badge bg-info',
            'document' => 'badge bg-warning',
            'offline' => 'badge bg-success',
            default => 'badge bg-secondary'
        };
    }

    public function getAccessInfoAttribute()
    {
        if ($this->jenis_pelatihan === 'offline') {
            return 'Lokasi: ' . ($this->konten ?? 'Belum ditentukan');
        } else {
            return 'URL: ' . ($this->link_url ?? 'Belum tersedia');
        }
    }

    public function getAccessLinkAttribute()
    {
        if ($this->jenis_pelatihan !== 'offline' && $this->link_url) {
            return $this->link_url;
        }
        return null;
    }

    public function getLocationInfoAttribute()
    {
        if ($this->jenis_pelatihan === 'offline') {
            return $this->konten ?? 'Belum ditentukan';
        }
        return null;
    }

    public function getDurasiDisplayAttribute()
    {
        if ($this->durasi) {
            return $this->durasi . ' jam';
        }
        return '-';
    }
}
