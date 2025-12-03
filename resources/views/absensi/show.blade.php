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
                                                    if(isset($absensi['tanggal_absensi'])) {
                                                        // Parse sebagai date saja (tanpa timezone conversion)
                                                        $tanggal = \Carbon\Carbon::parse($absensi['tanggal_absensi'])->timezone('Asia/Makassar')->locale('id')->translatedFormat('d F Y');
                                                    } else {
                                                        $tanggal = 'Tidak tersedia';
                                                    }
                                                } else {
                                                    if(isset($absensi->tanggal_absensi) && $absensi->tanggal_absensi) {
                                                        // Jika sudah Carbon object, pastikan timezone correct
                                                        if($absensi->tanggal_absensi instanceof \Carbon\Carbon) {
                                                            $tanggal = $absensi->tanggal_absensi->timezone('Asia/Makassar')->locale('id')->translatedFormat('d F Y');
                                                        } else {
                                                            $tanggal = \Carbon\Carbon::parse($absensi->tanggal_absensi)->timezone('Asia/Makassar')->locale('id')->translatedFormat('d F Y');
                                                        }
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
                                                    if(isset($absensi['tanggal_absensi'])) {
                                                        $date = \Carbon\Carbon::parse($absensi['tanggal_absensi'])->timezone('Asia/Makassar');
                                                        $hari = $date->locale('id')->translatedFormat('l');
                                                    } else {
                                                        $hari = 'Tidak tersedia';
                                                    }
                                                } else {
                                                    if(isset($absensi->tanggal_absensi) && $absensi->tanggal_absensi) {
                                                        if($absensi->tanggal_absensi instanceof \Carbon\Carbon) {
                                                            $hari = $absensi->tanggal_absensi->timezone('Asia/Makassar')->locale('id')->translatedFormat('l');
                                                        } else {
                                                            $hari = \Carbon\Carbon::parse($absensi->tanggal_absensi)->timezone('Asia/Makassar')->locale('id')->translatedFormat('l');
                                                        }
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
                                            // Ambil status dari database
                                            if(is_array($absensi)) {
                                                $status = $absensi['status'] ?? 'Hadir';
                                            } else {
                                                $status = $absensi->status ?? 'Hadir';
                                            }
                                            
                                            // Tentukan badge class berdasarkan status
                                            $badgeClass = match($status) {
                                                'Hadir' => 'bg-success',
                                                'Terlambat' => 'bg-warning',
                                                'Cuti' => 'bg-info',
                                                'Alpa' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">
                                            {{ $status }}
                                        </span>
                                        
                                        @if($status === 'Cuti')
                                            @php
                                                $approvalStatus = is_array($absensi) ? ($absensi['approval_status'] ?? null) : ($absensi->approval_status ?? null);
                                                $cutiReason = is_array($absensi) ? ($absensi['cuti_reason'] ?? null) : ($absensi->cuti_reason ?? null);
                                            @endphp
                                            @if($approvalStatus)
                                                <span class="badge {{ $approvalStatus === 'approved' ? 'bg-success' : ($approvalStatus === 'rejected' ? 'bg-danger' : 'bg-warning') }} ms-1">
                                                    {{ ucfirst($approvalStatus) }}
                                                </span>
                                            @endif
                                        @endif
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
                                            <h6 class="card-title text-success">Masuk</h6>
                                            @php
                                                // Ambil jam_masuk dari API (langsung dari field)
                                                $jamMasuk = '';
                                                $tanggalMasuk = '';
                                                
                                                try {
                                                    if(is_array($absensi)) {
                                                        // Gunakan jam_masuk langsung dari API
                                                        $jamMasuk = $absensi['jam_masuk'] ?? '';
                                                        
                                                        // Format tanggal dari tanggal_absensi dengan timezone correct
                                                        if(isset($absensi['tanggal_absensi'])) {
                                                            $tanggalMasuk = \Carbon\Carbon::parse($absensi['tanggal_absensi'])->timezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y');
                                                        }
                                                        
                                                        // Jika jam_masuk format datetime lengkap, ambil waktu saja
                                                        if($jamMasuk && strlen($jamMasuk) > 8) {
                                                            $jamMasuk = \Carbon\Carbon::parse($jamMasuk)->timezone('Asia/Makassar')->format('H:i:s');
                                                        }
                                                    } else {
                                                        // Gunakan jam_masuk langsung dari API
                                                        $jamMasuk = $absensi->jam_masuk ?? '';
                                                        
                                                        // Format jam_masuk jika object Carbon
                                                        if($jamMasuk instanceof \Carbon\Carbon) {
                                                            $jamMasuk = $jamMasuk->timezone('Asia/Makassar')->format('H:i:s');
                                                        } elseif(is_string($jamMasuk) && strlen($jamMasuk) > 8) {
                                                            $jamMasuk = \Carbon\Carbon::parse($jamMasuk)->timezone('Asia/Makassar')->format('H:i:s');
                                                        }
                                                        
                                                        // Format tanggal dari tanggal_absensi dengan timezone correct
                                                        if(isset($absensi->tanggal_absensi)) {
                                                            if($absensi->tanggal_absensi instanceof \Carbon\Carbon) {
                                                                $tanggalMasuk = $absensi->tanggal_absensi->timezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y');
                                                            } else {
                                                                $tanggalMasuk = \Carbon\Carbon::parse($absensi->tanggal_absensi)->timezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y');
                                                            }
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
                                
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title text-danger">Keluar</h6>
                                            @php
                                                // Ambil jam_keluar dari API (langsung dari field)
                                                $jamKeluar = '';
                                                $tanggalKeluar = '';
                                                
                                                try {
                                                    if(is_array($absensi)) {
                                                        // Gunakan jam_keluar langsung dari API
                                                        $jamKeluar = $absensi['jam_keluar'] ?? '';
                                                        
                                                        // Format tanggal dari tanggal_absensi dengan timezone correct
                                                        if(isset($absensi['tanggal_absensi'])) {
                                                            $tanggalKeluar = \Carbon\Carbon::parse($absensi['tanggal_absensi'])->timezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y');
                                                        }
                                                        
                                                        // Jika jam_keluar format datetime lengkap, ambil waktu saja
                                                        if($jamKeluar && strlen($jamKeluar) > 8) {
                                                            $jamKeluar = \Carbon\Carbon::parse($jamKeluar)->timezone('Asia/Makassar')->format('H:i:s');
                                                        }
                                                    } else {
                                                        // Gunakan jam_keluar langsung dari API
                                                        $jamKeluar = $absensi->jam_keluar ?? '';
                                                        
                                                        // Format jam_keluar jika object Carbon
                                                        if($jamKeluar instanceof \Carbon\Carbon) {
                                                            $jamKeluar = $jamKeluar->timezone('Asia/Makassar')->format('H:i:s');
                                                        } elseif(is_string($jamKeluar) && strlen($jamKeluar) > 8) {
                                                            $jamKeluar = \Carbon\Carbon::parse($jamKeluar)->timezone('Asia/Makassar')->format('H:i:s');
                                                        }
                                                        
                                                        // Format tanggal dari tanggal_absensi dengan timezone correct
                                                        if(isset($absensi->tanggal_absensi)) {
                                                            if($absensi->tanggal_absensi instanceof \Carbon\Carbon) {
                                                                $tanggalKeluar = $absensi->tanggal_absensi->timezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y');
                                                            } else {
                                                                $tanggalKeluar = \Carbon\Carbon::parse($absensi->tanggal_absensi)->timezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y');
                                                            }
                                                        }
                                                    }
                                                } catch(\Exception $e) {
                                                    $jamKeluar = 'Error';
                                                    $tanggalKeluar = 'Error';
                                                }
                                            @endphp
                                            <h4 class="text-danger">
                                                {{ $jamKeluar ?: '-' }}
                                            </h4>
                                            @if($jamKeluar)
                                                <small class="text-muted">
                                                    {{ $tanggalKeluar }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
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
                        $status = is_array($absensi) ? ($absensi['status'] ?? null) : ($absensi->status ?? null);
                        $cutiReason = is_array($absensi) ? ($absensi['cuti_reason'] ?? null) : ($absensi->cuti_reason ?? null);
                        $approvalStatus = is_array($absensi) ? ($absensi['approval_status'] ?? null) : ($absensi->approval_status ?? null);
                        $approvedBy = is_array($absensi) ? ($absensi['approved_by'] ?? null) : ($absensi->approved_by ?? null);
                        $approvedAt = is_array($absensi) ? ($absensi['approved_at'] ?? null) : ($absensi->approved_at ?? null);
                        $rejectionReason = is_array($absensi) ? ($absensi['rejection_reason'] ?? null) : ($absensi->rejection_reason ?? null);
                    @endphp
                    
                    @if($status === 'Cuti' && $cutiReason)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-muted mb-3">
                                    <i class="fas fa-calendar-day"></i> Informasi Cuti
                                </h5>
                                <div class="card border-info">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <strong>Alasan Cuti:</strong>
                                            <p class="mb-0 mt-2">{{ $cutiReason }}</p>
                                        </div>
                                        
                                        @if($approvalStatus)
                                            <div class="mb-3">
                                                <strong>Status Persetujuan:</strong>
                                                <span class="badge {{ $approvalStatus === 'approved' ? 'bg-success' : ($approvalStatus === 'rejected' ? 'bg-danger' : 'bg-warning') }} ms-2">
                                                    {{ $approvalStatus === 'approved' ? 'Disetujui' : ($approvalStatus === 'rejected' ? 'Ditolak' : 'Menunggu Persetujuan') }}
                                                </span>
                                            </div>
                                        @endif
                                        
                                        @if($approvalStatus === 'approved' && $approvedBy)
                                            <div class="mb-3">
                                                <strong>Disetujui Oleh:</strong>
                                                <span class="ms-2">
                                                    @if(is_array($approvedBy))
                                                        {{ $approvedBy['nama_lengkap'] ?? 'Admin' }}
                                                    @else
                                                        {{ $approvedBy->nama_lengkap ?? 'Admin' }}
                                                    @endif
                                                </span>
                                            </div>
                                            
                                            @if($approvedAt)
                                                <div class="mb-0">
                                                    <strong>Tanggal Disetujui:</strong>
                                                    <span class="ms-2">{{ \Carbon\Carbon::parse($approvedAt)->format('d F Y H:i') }}</span>
                                                </div>
                                            @endif
                                        @endif
                                        
                                        @if($approvalStatus === 'rejected')
                                            @if($rejectionReason)
                                                <div class="alert alert-danger mb-0 mt-2">
                                                    <strong>Alasan Penolakan:</strong>
                                                    <p class="mb-0 mt-2">{{ $rejectionReason }}</p>
                                                </div>
                                            @endif
                                            
                                            @if($approvedBy)
                                                <div class="mt-2">
                                                    <strong>Ditolak Oleh:</strong>
                                                    <span class="ms-2">
                                                        @if(is_array($approvedBy))
                                                            {{ $approvedBy['nama_lengkap'] ?? 'Admin' }}
                                                        @else
                                                            {{ $approvedBy->nama_lengkap ?? 'Admin' }}
                                                        @endif
                                                    </span>
                                                </div>
                                            @endif
                                            
                                            @if($approvedAt)
                                                <div class="mt-2">
                                                    <strong>Tanggal Ditolak:</strong>
                                                    <span class="ms-2">{{ \Carbon\Carbon::parse($approvedAt)->format('d F Y H:i') }}</span>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
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
