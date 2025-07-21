@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Absensi Saya
                    </h4>
                    <div>
                        <a href="{{ route('absensi.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Info Card -->
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3 bg-primary text-white">
                                @php
                                    $userName = session('user_name', 'Pengguna');
                                    if (auth()->check() && auth()->user()) {
                                        $userName = auth()->user()->name ?? $userName;
                                    }
                                @endphp
                                {{ substr($userName, 0, 1) }}
                            </div>
                            <div>
                                <strong>{{ $userName }}</strong>
                                <small class="d-block text-muted">
                                    @php
                                        $userRole = session('user_role', 'pegawai');
                                        if (auth()->check() && auth()->user()) {
                                            $userRole = auth()->user()->role ?? $userRole;
                                        }
                                    @endphp
                                    {{ ucfirst($userRole) }}
                                </small>
                                <small class="d-block">
                                    <i class="fas fa-calendar me-1"></i>
                                    @php
                                        $tanggalFormatted = 'Tidak diketahui';
                                        if (is_object($absensi) && isset($absensi->tanggal)) {
                                            if (is_string($absensi->tanggal)) {
                                                $tanggalFormatted = \Carbon\Carbon::parse($absensi->tanggal)->format('d F Y');
                                            } elseif (method_exists($absensi->tanggal, 'format')) {
                                                $tanggalFormatted = $absensi->tanggal->format('d F Y');
                                            }
                                        } elseif (is_array($absensi) && isset($absensi['tanggal'])) {
                                            $tanggalFormatted = \Carbon\Carbon::parse($absensi['tanggal'])->format('d F Y');
                                        }
                                    @endphp
                                    {{ $tanggalFormatted }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('absensi.update', $absensi) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Show current status (read-only) -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-info-circle me-1"></i>Status Saat Ini
                            </label>
                            @php
                                $currentStatus = 'Tidak diketahui';
                                if (is_object($absensi) && isset($absensi->status)) {
                                    $currentStatus = $absensi->status;
                                } elseif (is_array($absensi) && isset($absensi['status'])) {
                                    $currentStatus = $absensi['status'];
                                }
                                
                                $statusClass = 'secondary';
                                switch($currentStatus) {
                                    case 'Hadir':
                                        $statusClass = 'success';
                                        break;
                                    case 'Terlambat':
                                        $statusClass = 'warning';
                                        break;
                                    case 'Sakit':
                                        $statusClass = 'info';
                                        break;
                                    case 'Izin':
                                        $statusClass = 'primary';
                                        break;
                                    case 'Alpa':
                                    case 'Tidak Hadir':
                                        $statusClass = 'danger';
                                        break;
                                }
                            @endphp
                            <div class="form-control-plaintext bg-light p-2 rounded">
                                <span class="badge bg-{{ $statusClass }}">
                                    {{ $currentStatus }}
                                </span>
                            </div>
                        </div>

                        <!-- Show time info (read-only) -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-clock me-1"></i>Jam Masuk
                                    </label>
                                    <div class="form-control-plaintext bg-light p-2 rounded">
                                        @php
                                            $jamMasukFormatted = '-';
                                            if (is_object($absensi) && isset($absensi->jam_masuk)) {
                                                if (is_string($absensi->jam_masuk)) {
                                                    $jamMasukFormatted = \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i');
                                                } elseif (method_exists($absensi->jam_masuk, 'format')) {
                                                    $jamMasukFormatted = $absensi->jam_masuk->format('H:i');
                                                }
                                            } elseif (is_array($absensi) && isset($absensi['jam_masuk']) && !empty($absensi['jam_masuk'])) {
                                                $jamMasukFormatted = \Carbon\Carbon::parse($absensi['jam_masuk'])->format('H:i');
                                            }
                                        @endphp
                                        {{ $jamMasukFormatted }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-sign-out-alt me-1"></i>Jam Keluar
                                    </label>
                                    <div class="form-control-plaintext bg-light p-2 rounded">
                                        @php
                                            $jamKeluarFormatted = 'Belum checkout';
                                            if (is_object($absensi) && isset($absensi->jam_keluar)) {
                                                if (is_string($absensi->jam_keluar)) {
                                                    $jamKeluarFormatted = \Carbon\Carbon::parse($absensi->jam_keluar)->format('H:i');
                                                } elseif (method_exists($absensi->jam_keluar, 'format')) {
                                                    $jamKeluarFormatted = $absensi->jam_keluar->format('H:i');
                                                }
                                            } elseif (is_array($absensi) && isset($absensi['jam_keluar']) && !empty($absensi['jam_keluar'])) {
                                                $jamKeluarFormatted = \Carbon\Carbon::parse($absensi['jam_keluar'])->format('H:i');
                                            }
                                        @endphp
                                        {{ $jamKeluarFormatted }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        @php
                            $alamatMasuk = '';
                            $durasiKerja = '';
                            
                            if (is_object($absensi)) {
                                $alamatMasuk = $absensi->alamat_masuk ?? '';
                                $durasiKerja = $absensi->durasi_kerja ?? '';
                            } elseif (is_array($absensi)) {
                                $alamatMasuk = $absensi['alamat_masuk'] ?? '';
                                $durasiKerja = $absensi['durasi_kerja'] ?? '';
                            }
                        @endphp
                        
                        @if(!empty($alamatMasuk))
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Lokasi Check-in
                            </label>
                            <div class="form-control-plaintext bg-light p-2 rounded">
                                {{ $alamatMasuk }}
                            </div>
                        </div>
                        @endif

                        @if(!empty($durasiKerja))
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-hourglass-half me-1"></i>Durasi Kerja
                            </label>
                            <div class="form-control-plaintext bg-light p-2 rounded">
                                {{ $durasiKerja }}
                            </div>
                        </div>
                        @endif

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <div>
                                @php
                                    $absensiId = null;
                                    if (is_object($absensi)) {
                                        $absensiId = $absensi->id_absensi ?? $absensi->id ?? null;
                                    } elseif (is_array($absensi)) {
                                        $absensiId = $absensi['id_absensi'] ?? $absensi['id'] ?? null;
                                    }
                                @endphp
                                
                                @if($absensiId)
                                <a href="{{ route('absensi.show', $absensiId) }}" class="btn btn-info me-2">
                                    <i class="fas fa-eye me-1"></i>Lihat Detail
                                </a>
                                @endif
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: bold;
}

.form-control-plaintext {
    border: 1px solid #dee2e6;
}

.card-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
    transform: translateY(-1px);
}
</style>
@endsection
