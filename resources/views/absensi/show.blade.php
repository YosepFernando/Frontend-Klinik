@extends('layouts.app')

@section('content')
@if(!$absensi)
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="alert alert-warning">
                <h4>Data Tidak Ditemukan</h4>
                <p>Data absensi yang Anda cari tidak ditemukan atau telah dihapus.</p>
                <a href="{{ route('absensi.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar Absensi
                </a>
            </div>
        </div>
    </div>
</div>
@else
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-eye"></i> Detail Absensi
                    </h4>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Employee Information -->
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Informasi Karyawan</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Nama</strong></td>
                                    <td>: 
                                        @if(is_array($absensi))
                                            {{ $absensi['pegawai']['user']['nama_user'] ?? $absensi['pegawai']['user']['name'] ?? 'Tidak tersedia' }}
                                        @elseif(isset($absensi->pegawai) && isset($absensi->pegawai->user))
                                            {{ $absensi->pegawai->user->nama_user ?? $absensi->pegawai->user->name ?? 'Tidak tersedia' }}
                                        @else
                                            Tidak tersedia
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Peran</strong></td>
                                    <td>: 
                                        @if(is_array($absensi))
                                            {{ ucfirst($absensi['pegawai']['user']['role'] ?? 'Tidak tersedia') }}
                                        @elseif(isset($absensi->pegawai) && isset($absensi->pegawai->user))
                                            {{ ucfirst($absensi->pegawai->user->role ?? 'Tidak tersedia') }}
                                        @else
                                            Tidak tersedia
                                        @endif
                                    </td>
                                </tr>
                                @php
                                    $hasPosition = false;
                                    $positionName = '';
                                    
                                    if(is_array($absensi)) {
                                        $hasPosition = isset($absensi['pegawai']['posisi']) && !empty($absensi['pegawai']['posisi']);
                                        $positionName = $absensi['pegawai']['posisi']['nama_posisi'] ?? '';
                                    } elseif(isset($absensi->pegawai) && isset($absensi->pegawai->posisi)) {
                                        $hasPosition = !empty($absensi->pegawai->posisi);
                                        $positionName = $absensi->pegawai->posisi->nama_posisi ?? '';
                                    }
                                @endphp
                                @if($hasPosition)
                                <tr>
                                    <td><strong>Posisi</strong></td>
                                    <td>: {{ $positionName }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Email</strong></td>
                                    <td>: 
                                        @if(is_array($absensi))
                                            {{ $absensi['pegawai']['user']['email'] ?? 'Tidak tersedia' }}
                                        @elseif(isset($absensi->pegawai) && isset($absensi->pegawai->user))
                                            {{ $absensi->pegawai->user->email ?? 'Tidak tersedia' }}
                                        @else
                                            Tidak tersedia
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Attendance Information -->
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Detail Absensi</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Tanggal</strong></td>
                                    <td>: 
                                        @php
                                            $tanggal = '';
                                            try {
                                                if(is_array($absensi)) {
                                                    if(isset($absensi['tanggal'])) {
                                                        $tanggal = \Carbon\Carbon::parse($absensi['tanggal'])->locale('id')->format('d F Y');
                                                    } else {
                                                        $tanggal = 'Tidak tersedia';
                                                    }
                                                } else {
                                                    if(isset($absensi->tanggal) && $absensi->tanggal) {
                                                        $tanggal = $absensi->tanggal->locale('id')->format('d F Y');
                                                    } else {
                                                        $tanggal = 'Tidak tersedia';
                                                    }
                                                }
                                            } catch(\Exception $e) {
                                                $tanggal = 'Format tanggal tidak valid';
                                            }
                                        @endphp
                                        {{ $tanggal }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Hari</strong></td>
                                    <td>: 
                                        @php
                                            $hari = '';
                                            try {
                                                if(is_array($absensi)) {
                                                    if(isset($absensi['tanggal'])) {
                                                        $date = \Carbon\Carbon::parse($absensi['tanggal']);
                                                        $hari = $date->locale('id')->format('l');
                                                    } else {
                                                        $hari = 'Tidak tersedia';
                                                    }
                                                } else {
                                                    if(isset($absensi->tanggal) && $absensi->tanggal) {
                                                        $hari = $absensi->tanggal->locale('id')->format('l');
                                                    } else {
                                                        $hari = 'Tidak tersedia';
                                                    }
                                                }
                                            } catch(\Exception $e) {
                                                $hari = 'Format tanggal tidak valid';
                                            }
                                        @endphp
                                        {{ $hari }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td>: 
                                        @php
                                            $status = 'Hadir'; // Default status
                                            $createdAt = null;
                                            
                                            if(is_array($absensi)) {
                                                $createdAt = isset($absensi['created_at']) ? \Carbon\Carbon::parse($absensi['created_at']) : null;
                                            } else {
                                                $createdAt = $absensi->created_at ?? null;
                                            }
                                            
                                            // Hitung status berdasarkan waktu check-in
                                            if ($createdAt && $createdAt->hour > 8) {
                                                $status = 'Terlambat';
                                            }
                                            
                                            $badgeClass = $status === 'Hadir' ? 'bg-success' : 'bg-warning';
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- Time Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Waktu Kehadiran</h5>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title text-success">Masuk</h6>                            @php
                                $jamMasuk = '';
                                $tanggalMasuk = '';
                                
                                try {
                                    if(is_array($absensi)) {
                                        if(isset($absensi['created_at'])) {
                                            $checkIn = \Carbon\Carbon::parse($absensi['created_at']);
                                            $jamMasuk = $checkIn->format('H:i:s');
                                            $tanggalMasuk = $checkIn->locale('id')->format('d M Y');
                                        }
                                    } else {
                                        if(isset($absensi->created_at) && $absensi->created_at) {
                                            $jamMasuk = $absensi->created_at->format('H:i:s');
                                            $tanggalMasuk = $absensi->created_at->locale('id')->format('d M Y');
                                        }
                                    }
                                } catch(\Exception $e) {
                                    $jamMasuk = 'Error';
                                    $tanggalMasuk = 'Error';
                                }
                            @endphp
                                            <h4 class="text-success">
                                                {{ $jamMasuk ?: '-' }}
                                            </h4>
                                            @if($jamMasuk)
                                                <small class="text-muted">
                                                    {{ $tanggalMasuk }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                        <!-- Location Information -->
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Informasi Lokasi</h5>
                            
                            @php
                                $alamatMasuk = is_array($absensi) ? ($absensi['alamat_masuk'] ?? null) : ($absensi->alamat_masuk ?? null);
                                $latitudeMasuk = is_array($absensi) ? ($absensi['latitude_masuk'] ?? null) : ($absensi->latitude_masuk ?? null);
                                $longitudeMasuk = is_array($absensi) ? ($absensi['longitude_masuk'] ?? null) : ($absensi->longitude_masuk ?? null);
                                $alamatKeluar = is_array($absensi) ? ($absensi['alamat_keluar'] ?? null) : ($absensi->alamat_keluar ?? null);
                                $latitudeKeluar = is_array($absensi) ? ($absensi['latitude_keluar'] ?? null) : ($absensi->latitude_keluar ?? null);
                                $longitudeKeluar = is_array($absensi) ? ($absensi['longitude_keluar'] ?? null) : ($absensi->longitude_keluar ?? null);
                            @endphp
                            
                            @if($alamatMasuk)
                                <div class="card mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> Lokasi Masuk</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1">{{ $alamatMasuk }}</p>
                                        @if($latitudeMasuk && $longitudeMasuk)
                                            <small class="text-muted">
                                                Koordinat: {{ $latitudeMasuk }}, {{ $longitudeMasuk }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if($alamatKeluar)
                                <div class="card">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> Lokasi Keluar</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1">{{ $alamatKeluar }}</p>
                                        @if($latitudeKeluar && $longitudeKeluar)
                                            <small class="text-muted">
                                                Koordinat: {{ $latitudeKeluar }}, {{ $longitudeKeluar }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @php
                        $catatan = is_array($absensi) ? ($absensi['catatan'] ?? null) : ($absensi->catatan ?? null);
                    @endphp
                    
                    @if($catatan)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-muted mb-3">Catatan</h5>
                                <div class="alert alert-secondary">
                                    <i class="fas fa-comment"></i>
                                    {{ $catatan }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="text-center mt-4">
                        <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        
                        @if(auth()->check() && auth()->user() && (auth()->user()->isAdmin() || auth()->user()->isHRD()))
                    @php
                        $absensiId = is_array($absensi) ? ($absensi['id_absensi'] ?? $absensi['id'] ?? null) : ($absensi->id_absensi ?? $absensi->id ?? null);
                    @endphp
                            
                            @if($absensiId)
                                <a href="{{ route('absensi.edit', $absensiId) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                
                                @if(auth()->check() && auth()->user() && auth()->user()->isAdmin())
                                    <form action="{{ route('absensi.destroy', $absensiId) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" 
                                                onclick="return confirm('Yakin ingin menghapus data absensi ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                @endif
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
