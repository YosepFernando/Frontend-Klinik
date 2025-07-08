@extends('layouts.app')

@section('content')
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
                                        @else
                                            {{ $absensi->pegawai->user->name ?? 'Tidak tersedia' }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Role</strong></td>
                                    <td>: 
                                        @if(is_array($absensi))
                                            {{ ucfirst($absensi['pegawai']['user']['role'] ?? 'Tidak tersedia') }}
                                        @else
                                            {{ ucfirst($absensi->pegawai->user->role ?? 'Tidak tersedia') }}
                                        @endif
                                    </td>
                                </tr>
                                @php
                                    $hasPosition = false;
                                    $positionName = '';
                                    
                                    if(is_array($absensi)) {
                                        $hasPosition = isset($absensi['pegawai']['posisi']) && !empty($absensi['pegawai']['posisi']);
                                        $positionName = $absensi['pegawai']['posisi']['nama_posisi'] ?? '';
                                    } else {
                                        $hasPosition = isset($absensi->pegawai->posisi) && !empty($absensi->pegawai->posisi);
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
                                        @else
                                            {{ $absensi->pegawai->user->email ?? 'Tidak tersedia' }}
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
                                            if(is_array($absensi)) {
                                                $tanggal = isset($absensi['tanggal']) ? \Carbon\Carbon::parse($absensi['tanggal'])->format('d F Y') : 'Tidak tersedia';
                                            } else {
                                                $tanggal = $absensi->tanggal ? $absensi->tanggal->format('d F Y') : 'Tidak tersedia';
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
                                            if(is_array($absensi)) {
                                                $hari = isset($absensi['tanggal']) ? \Carbon\Carbon::parse($absensi['tanggal'])->format('l') : 'Tidak tersedia';
                                            } else {
                                                $hari = $absensi->tanggal ? $absensi->tanggal->format('l') : 'Tidak tersedia';
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
                                            <h6 class="card-title text-success">Check In</h6>
                                            @php
                                                $jamMasuk = '';
                                                $tanggalMasuk = '';
                                                
                                                if(is_array($absensi)) {
                                                    if(isset($absensi['created_at'])) {
                                                        $checkIn = \Carbon\Carbon::parse($absensi['created_at']);
                                                        $jamMasuk = $checkIn->format('H:i:s');
                                                        $tanggalMasuk = $checkIn->format('d M Y');
                                                    }
                                                } else {
                                                    if($absensi->created_at) {
                                                        $jamMasuk = $absensi->created_at->format('H:i:s');
                                                        $tanggalMasuk = $absensi->created_at->format('d M Y');
                                                    }
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
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title text-danger">Check Out</h6>
                                            @php
                                                $jamKeluar = '';
                                                $tanggalKeluar = '';
                                                $hasCheckOut = false;
                                                
                                                if(is_array($absensi)) {
                                                    if(isset($absensi['updated_at']) && isset($absensi['created_at']) && 
                                                       $absensi['updated_at'] !== $absensi['created_at']) {
                                                        $checkOut = \Carbon\Carbon::parse($absensi['updated_at']);
                                                        $jamKeluar = $checkOut->format('H:i:s');
                                                        $tanggalKeluar = $checkOut->format('d M Y');
                                                        $hasCheckOut = true;
                                                    }
                                                } else {
                                                    if($absensi->updated_at && $absensi->created_at && 
                                                       $absensi->updated_at->format('Y-m-d H:i:s') !== $absensi->created_at->format('Y-m-d H:i:s')) {
                                                        $jamKeluar = $absensi->updated_at->format('H:i:s');
                                                        $tanggalKeluar = $absensi->updated_at->format('d M Y');
                                                        $hasCheckOut = true;
                                                    }
                                                }
                                            @endphp
                                            <h4 class="text-danger">
                                                {{ $jamKeluar ?: '-' }}
                                            </h4>
                                            @if($hasCheckOut)
                                                <small class="text-muted">
                                                    {{ $tanggalKeluar }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @php
                                $durasiKerja = '';
                                if($jamMasuk && $hasCheckOut) {
                                    if(is_array($absensi)) {
                                        $start = \Carbon\Carbon::parse($absensi['created_at']);
                                        $end = \Carbon\Carbon::parse($absensi['updated_at']);
                                    } else {
                                        $start = $absensi->created_at;
                                        $end = $absensi->updated_at;
                                    }
                                    
                                    if($start && $end) {
                                        $diff = $start->diff($end);
                                        $durasiKerja = $diff->format('%h jam %i menit');
                                    }
                                }
                            @endphp
                            
                            @if($durasiKerja)
                                <div class="text-center mt-3">
                                    <div class="alert alert-info">
                                        <i class="fas fa-hourglass-half"></i>
                                        <strong>Durasi Kerja: {{ $durasiKerja }}</strong>
                                    </div>
                                </div>
                            @endif
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
                                        <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> Lokasi Check In</h6>
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
                                        <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> Lokasi Check Out</h6>
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
                        
                        @if(auth()->user()->isAdmin() || auth()->user()->isHRD())
                            @php
                                $absensiId = is_array($absensi) ? ($absensi['id'] ?? null) : ($absensi->id ?? null);
                            @endphp
                            
                            @if($absensiId)
                                <a href="{{ route('absensi.edit', $absensiId) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                
                                @if(auth()->user()->isAdmin())
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
@endsection
