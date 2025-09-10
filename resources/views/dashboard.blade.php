@extends('layouts.app')

@section('page-title', 'Dashboard')

@push('styles')
<style>
/* Custom styles for job application status */
.status-container {
    min-width: 200px;
}

.left-side {
    font-family: poppins, sans-serif;
}

/* Progress bars untuk posisi pegawai */
.position-progress-item {
    margin-bottom: 8px;
}

.position-progress-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 3px;
    font-size: 10px;
    color: #495057;
}

.position-progress-bar {
    height: 6px;
    background-color: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
}

.position-progress-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.8s ease-out;
}

.position-color-1 { background-color: #17a2b8; }
.position-color-2 { background-color: #fd7e14; }
.position-color-3 { background-color: #6f42c1; }
.position-color-4 { background-color: #dc3545; }
.position-color-5 { background-color: #28a745; }
.position-color-6 { background-color: #ffc107; }

/* Gender legend styling */
.gender-legend-item {
    display: flex;
    align-items: center;
    margin-bottom: 4px;
    font-size: 11px;
}

.gender-legend-color {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-right: 8px;
}

.progress-steps {
    background: rgba(0,0,0,0.02);
    border-radius: 8px;
    padding: 0.5rem;
    margin-top: 0.5rem;
}

.progress-steps .flex-fill {
    position: relative;
}

.progress-steps .flex-fill:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 0.5rem;
    right: -50%;
    width: 100%;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.progress-steps .text-success.flex-fill:not(:last-child)::after {
    background: #28a745;
}

.progress-steps .text-warning.flex-fill:not(:last-child)::after {
    background: #ffc107;
}

.progress-steps .text-info.flex-fill:not(:last-child)::after {
    background: #17a2b8;
}

.application-card {
    transition: all 0.3s ease;
    border-radius: 12px;
}

.application-card:hover {
    background-color: rgba(0,123,255,0.02);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.interview-info {
    background: linear-gradient(135deg, rgba(0,123,255,0.1), rgba(0,123,255,0.05));
    border-left: 3px solid #007bff;
}

.status-badge-large {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
}

.job-title {
    color: #2c3e50;
    font-weight: 600;
}

.job-position {
    color: #6c757d;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .status-container {
        min-width: auto;
        margin-top: 1rem;
    }

    .progress-steps {
        font-size: 0.65rem !important;
    }

    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .btn-group .btn {
        margin: 0;
        border-radius: 0.375rem !important;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-12 col-md-12 mb-4 mt-4">
            <div class="card shadow h-100">
                <div class="card-body d-flex justify-content-between">
                    <div class="left-side w-100">
                        <div class="m-3">
                            <h2><b>Hallo</b>, {{ session('user_name') }}!</h2>
                            <h4 class="text-muted">{{ now()->format('d F Y') }}</h4>        
                        </div>
                        @if(!is_pelanggan())
                            <div class="col-lg-6 mb-4 w-75">
                                <div class="">
                                    <div class="card-body">
                                        <div class="row">
                                            @if(is_hrd() || is_admin())
                                                <div class="col-md-4 mb-2">
                                                    <a href="{{ route('trainings.create') }}" class="btn btn-info btn-block w-100 mr-1">
                                                        <i class="bi bi-plus-circle"></i> Buat Pelatihan
                                                    </a>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <a href="{{ route('payroll.index') }}" class="btn btn-warning btn-block w-100 mr-1">
                                                        <i class="bi bi-eye"></i> Lihat Penggajian
                                                    </a>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <a href="{{ route('users.index') }}" class="btn btn-secondary btn-block w-100 mr-1">
                                                        <i class="bi bi-people"></i> Kelola User
                                                    </a>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <a href="{{ route('users.create') }}" class="btn btn-dark btn-block w-100 mr-1">
                                                        <i class="bi bi-person-plus"></i> Tambah User
                                                    </a>
                                                </div>
                                                <div class="col-md-4 mb-2">
                                                    <a href="{{ route('pegawai.index') }}" class="btn btn-primary btn-block w-100 mr-1">
                                                        <i class="bi bi-person-badge"></i> Kelola Pegawai
                                                    </a>
                                                </div>
                                            @endif

                                            @if(!is_pelanggan())
                                                <div class="col-md-4 mb-2">
                                                    @if(isset($hasCheckedIn) && $hasCheckedIn)
                                                        @if(isset($hasCheckedOut) && $hasCheckedOut)
                                                            <button class="btn btn-success btn-block w-100 mr-1" disabled>
                                                                <i class="bi bi-check-circle"></i> Absensi Selesai
                                                            </button>
                                                        @else
                                                            <button onclick="checkOut()" class="btn btn-outline-danger btn-block w-100 mr-1">
                                                                <i class="bi bi-clock-history"></i> Check Out
                                                            </button>
                                                        @endif
                                                    @else
                                                        <button onclick="checkIn()" class="btn btn-outline-success btn-block w-100 mr-1">
                                                            <i class="bi bi-clock-history"></i> Check In
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @if(!is_pelanggan() && !is_admin() && !is_hrd())
                            <!-- Today's Attendance Status for Staff -->
                            <div class="col-lg-6 mb-4">
                                <div class="card shadow">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Status Absensi Hari Ini</h6>
                                    </div>
                                    <div class="card-body">
                                        @if(isset($hasCheckedIn) && $hasCheckedIn)
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>Status:
                                                        @if(isset($hasCheckedOut) && $hasCheckedOut)
                                                            <span class="text-success">Selesai</span>
                                                        @else
                                                            <span class="text-warning">Sedang Bekerja</span>
                                                        @endif
                                                    </strong><br>
                                                    @if(isset($attendanceRecord))
                                                        <small class="text-muted">
                                                            Check-in: {{ isset($attendanceRecord['jam_masuk']) ? date('H:i', strtotime($attendanceRecord['jam_masuk'])) : 'N/A' }}
                                                            @if(isset($attendanceRecord['jam_keluar']) && $attendanceRecord['jam_keluar'])
                                                                | Check-out: {{ date('H:i', strtotime($attendanceRecord['jam_keluar'])) }}
                                                            @endif
                                                        </small>
                                                    @endif
                                                </div>
                                                <div>
                                                    @if(isset($hasCheckedOut) && $hasCheckedOut)
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-check-circle"></i> Selesai
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning">
                                                            <i class="bi bi-clock"></i> Aktif
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            @if(!$hasCheckedOut)
                                                <div class="mt-3">
                                                    <button onclick="checkOut()" class="btn btn-danger btn-sm">
                                                        <i class="bi bi-box-arrow-right"></i> Check Out Sekarang
                                                    </button>
                                                    <a href="{{ route('absensi.index') }}" class="btn btn-outline-info btn-sm">
                                                        <i class="bi bi-eye"></i> Lihat Detail
                                                    </a>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-center py-3">
                                                <i class="bi bi-clock-history text-muted" style="font-size: 2rem;"></i>
                                                <h6 class="mt-2 text-muted">Belum Check-in Hari Ini</h6>
                                                <p class="text-muted small">Silakan lakukan check-in untuk memulai hari kerja Anda</p>
                                                <button onclick="checkIn()" class="btn btn-success btn-sm">
                                                    <i class="bi bi-clock"></i> Check In Sekarang
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif

                    </div>
                    <div class="right-side d-flex justify-content-end w-25">
                        <img style="width: 100%; object-fit: contain;" src="https://media-public.canva.com/dhfbk/MAGVigdhfbk/1/s.png" alt="">
                    </div>
                </div>
            </div>
        </div>  
        <!-- Stats Cards -->
        @if(is_admin() || is_hrd())
            @php
                // normalisasi beberapa kemungkinan key dari $genderStats
                $laki = data_get($genderStats, 'male', data_get($genderStats, 'L', data_get($genderStats, 'l', 0)));
                $perempuan = data_get($genderStats, 'female', data_get($genderStats, 'P', data_get($genderStats, 'p', 0)));
            @endphp
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Jumlah Pegawai
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ isset($pegawaiCount) ? number_format($pegawaiCount) : '-' }}</div>
                                
                                <!-- Grafik Pegawai berdasarkan Posisi -->
                                <div class="mt-3">
                                    <div class="text-xs font-weight-bold text-muted text-uppercase mb-2">
                                        Job Level
                                    </div>
                                    <div id="positionProgressBars" style="min-height:120px;">
                                        <!-- Progress bars akan di-generate oleh JavaScript -->
                                    </div>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Gender Diversity
                                </div>
                                <!-- Chart container -->
                                <div style="height:150px; max-width:200px; position: relative;">
                                    <canvas id="genderChart"></canvas>
                                    <!-- Total di tengah donut -->
                                    <div id="genderTotal" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none;">
                                        <div style="font-size: 20px; font-weight: bold; color: #495057;">{{ $pegawaiCount ?? 0 }}</div>
                                    </div>
                                </div>
                                <!-- Numeric labels dengan persentase -->
                                <div class="mt-2" id="genderLegend">
                                    <!-- Legend akan di-generate oleh JavaScript -->
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-heart-pulse fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(is_pelanggan())
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Lamaran Saya
                                </div>
                                {{-- <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $myApplicationsCount }}</div> --}}
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-briefcase fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Your Role
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ ucfirst(str_replace('_', ' ', user_role() === 'pelanggan' ? 'pelamar' : user_role())) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-badge fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Your Role
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ ucfirst(str_replace('_', ' ', user_role() === 'pelanggan' ? 'pelamar' : user_role())) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-badge fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions -->

        @if(is_pelanggan())
        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Lamaran Saya</h6>
                    <a href="{{ route('recruitments.index') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> Lamar Pekerjaan
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($myApplications) && count($myApplications) > 0)
                        @foreach($myApplications as $application)
                            @php
                                // Handle both array and object format from API
                                $isArray = is_array($application);
                                $lowonganData = $isArray ? ($application['lowongan_pekerjaan'] ?? null) : ($application->lowonganPekerjaan ?? null);
                                $posisiData = null;

                                if ($lowonganData) {
                                    $posisiData = $isArray ? ($lowonganData['posisi'] ?? null) : ($lowonganData->posisi ?? null);
                                }

                                $applicationId = $isArray ? ($application['id_lamaran_pekerjaan'] ?? $application['id'] ?? null) : ($application->id ?? null);
                                $namaUser = $isArray ? ($application['nama_pelamar'] ?? 'N/A') : ($application->nama_pelamar ?? 'N/A');
                                $createdAt = $isArray ? ($application['created_at'] ?? null) : ($application->created_at ?? null);

                                $judulPekerjaan = 'N/A';
                                $namaPosisi = 'Position not available';

                                if ($lowonganData) {
                                    $judulPekerjaan = $isArray ? ($lowonganData['judul_pekerjaan'] ?? 'N/A') : ($lowonganData->judul_pekerjaan ?? 'N/A');
                                }

                                if ($posisiData) {
                                    $namaPosisi = $isArray ? ($posisiData['nama_posisi'] ?? 'Position not available') : ($posisiData->nama_posisi ?? 'Position not available');
                                }

                                // Status dari ketiga tahapan
                                $statusSeleksiBerkas = $isArray ? ($application['status_seleksi_berkas'] ?? 'pending') : ($application->status_seleksi_berkas ?? 'pending');
                                $statusWawancara = $isArray ? ($application['status_wawancara'] ?? null) : ($application->status_wawancara ?? null);
                                $statusSeleksiAkhir = $isArray ? ($application['status_seleksi_akhir'] ?? null) : ($application->status_seleksi_akhir ?? null);

                                // Data wawancara
                                $interviewDate = $isArray ? ($application['interview_date'] ?? null) : ($application->interview_date ?? null);
                                $interviewTime = $isArray ? ($application['interview_time'] ?? null) : ($application->interview_time ?? null);
                                $interviewLocation = $isArray ? ($application['interview_location'] ?? null) : ($application->interview_location ?? null);
                                $interviewZoomLink = $isArray ? ($application['interview_zoom_link'] ?? null) : ($application->interview_zoom_link ?? null);
                                $interviewNotes = $isArray ? ($application['interview_notes'] ?? null) : ($application->interview_notes ?? null);

                                // Tentukan status keseluruhan untuk badge
                                $overallStatus = '';
                                $badgeClass = 'bg-secondary';
                                $statusIcon = 'bi-hourglass-split';
                                $statusMessage = '';

                                // Logika status berdasarkan tahapan
                                if ($statusSeleksiAkhir && str_contains(strtolower($statusSeleksiAkhir), 'diterima')) {
                                    $overallStatus = 'Diterima Bekerja';
                                    $badgeClass = 'bg-success';
                                    $statusIcon = 'bi-check-circle';
                                    $statusMessage = 'Selamat! Anda diterima bekerja.';
                                } elseif ($statusSeleksiAkhir && (str_contains(strtolower($statusSeleksiAkhir), 'ditolak') || str_contains(strtolower($statusSeleksiAkhir), 'tidak diterima'))) {
                                    $overallStatus = 'Tidak Lolos Seleksi Akhir';
                                    $badgeClass = 'bg-danger';
                                    $statusIcon = 'bi-x-circle';
                                    $statusMessage = 'Tidak berhasil dalam tahap seleksi akhir.';
                                } elseif ($statusSeleksiAkhir && (str_contains(strtolower($statusSeleksiAkhir), 'menunggu') || str_contains(strtolower($statusSeleksiAkhir), 'pending'))) {
                                    $overallStatus = 'Menunggu Keputusan Final';
                                    $badgeClass = 'bg-info';
                                    $statusIcon = 'bi-clock-history';
                                    $statusMessage = 'Wawancara telah selesai, menunggu keputusan final.';
                                } elseif ($statusWawancara && (str_contains(strtolower($statusWawancara), 'diterima') || str_contains(strtolower($statusWawancara), 'lulus'))) {
                                    $overallStatus = 'Lolos Wawancara';
                                    $badgeClass = 'bg-primary';
                                    $statusIcon = 'bi-chat-dots';
                                    $statusMessage = 'Selamat! Anda lolos tahap wawancara.';
                                } elseif ($statusWawancara && (str_contains(strtolower($statusWawancara), 'tidak lulus') || str_contains(strtolower($statusWawancara), 'ditolak') || str_contains(strtolower($statusWawancara), 'tidak_lulus'))) {
                                    $overallStatus = 'Tidak Lolos Wawancara';
                                    $badgeClass = 'bg-danger';
                                    $statusIcon = 'bi-x-circle';
                                    $statusMessage = 'Tidak berhasil dalam tahap wawancara.';
                                } elseif ($statusWawancara && str_contains(strtolower($statusWawancara), 'dijadwalkan')) {
                                    $overallStatus = 'Terjadwal Wawancara';
                                    $badgeClass = 'bg-info';
                                    $statusIcon = 'bi-calendar-check';
                                    $statusMessage = 'Anda lolos seleksi berkas. Silakan ikuti wawancara.';
                                } elseif ($statusSeleksiBerkas && str_contains(strtolower($statusSeleksiBerkas), 'diterima')) {
                                    $overallStatus = 'Lolos Seleksi Berkas';
                                    $badgeClass = 'bg-info';
                                    $statusIcon = 'bi-file-earmark-check';
                                    $statusMessage = 'Menunggu jadwal wawancara dari HRD.';
                                } elseif ($statusSeleksiBerkas && str_contains(strtolower($statusSeleksiBerkas), 'ditolak')) {
                                    $overallStatus = 'Tidak Lolos Seleksi Berkas';
                                    $badgeClass = 'bg-danger';
                                    $statusIcon = 'bi-x-circle';
                                    $statusMessage = 'Berkas Anda tidak memenuhi syarat.';
                                } else {
                                    $overallStatus = 'Dalam Review Berkas';
                                    $badgeClass = 'bg-warning';
                                    $statusIcon = 'bi-file-earmark-text';
                                    $statusMessage = 'Berkas sedang dalam proses review oleh HRD.';
                                }

                                // Debug mode
                                $debugMode = config('app.debug', false);
                            @endphp

                            <div class="application-card d-flex justify-content-between align-items-start border-bottom py-3 px-2">
                                <div class="flex-grow-1 me-3">
                                    <div class="job-title mb-1">{{ $judulPekerjaan }}</div>
                                    <div class="job-position mb-2">
                                        <i class="bi bi-briefcase me-1"></i>{{ $namaPosisi }}
                                        <span class="mx-2">•</span>
                                        <i class="bi bi-calendar3 me-1"></i>Applied: {{ $createdAt ? date('d M Y', strtotime($createdAt)) : 'N/A' }}
                                    </div>

                                    {{-- Informasi Wawancara jika status scheduled atau lulus --}}
                                    @if($statusWawancara && (str_contains(strtolower($statusWawancara), 'dijadwalkan') || str_contains(strtolower($statusWawancara), 'lulus') || str_contains(strtolower($statusWawancara), 'diterima')))
                                    <div class="interview-info mt-2 p-2 rounded border border-info border-opacity-25" style="font-size: 0.85rem;">
                                        <div class="d-flex align-items-center text-primary mb-1">
                                            <i class="bi bi-calendar-event me-2"></i>
                                            <strong>Informasi Wawancara</strong>
                                        </div>
                                        <div class="ms-4">
                                            @if($interviewDate)
                                            <div class="mb-1">
                                                <i class="bi bi-clock me-1 text-muted"></i>
                                                <strong>{{ date('l, d M Y', strtotime($interviewDate)) }}</strong>
                                                @if($interviewTime)
                                                    <span class="text-primary fw-bold ms-2">{{ date('H:i', strtotime($interviewTime)) }} WIB</span>
                                                @endif
                                            </div>
                                            @endif

                                            @if($interviewLocation)
                                            <div class="mb-1">
                                                <i class="bi bi-geo-alt me-1 text-muted"></i>
                                                <span class="text-dark">{{ $interviewLocation }}</span>
                                            </div>
                                            @endif

                                            @if($interviewZoomLink)
                                            <div class="mb-1">
                                                <i class="bi bi-camera-video me-1 text-muted"></i>
                                                <a href="{{ $interviewZoomLink }}" target="_blank" class="text-primary text-decoration-none">
                                                    Join Zoom Meeting
                                                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                                                </a>
                                            </div>
                                            @endif

                                            @if($interviewNotes)
                                            <div class="mt-1 p-1 bg-light rounded">
                                                <i class="bi bi-info-circle me-1 text-info"></i>
                                                <small class="text-muted">{{ $interviewNotes }}</small>
                                            </div>
                                            @endif

                                            @if($statusWawancara && (str_contains(strtolower($statusWawancara), 'lulus') || str_contains(strtolower($statusWawancara), 'diterima')))
                                            <div class="mt-2 p-1 bg-success bg-opacity-10 rounded">
                                                <i class="bi bi-check-circle me-1 text-success"></i>
                                                <small class="text-success fw-bold">Wawancara Berhasil</small>
                                            </div>
                                            @elseif($statusWawancara && str_contains(strtolower($statusWawancara), 'dijadwalkan'))
                                            <div class="mt-2 p-1 bg-warning bg-opacity-10 rounded">
                                                <i class="bi bi-exclamation-triangle me-1 text-warning"></i>
                                                <small class="text-warning fw-bold">Jangan sampai terlambat!</small>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Informasi Hasil Seleksi Final --}}
                                    @php
                                        $hasilSeleksiData = $isArray ? ($application['hasil_seleksi'] ?? null) : ($application->hasil_seleksi ?? null);
                                    @endphp

                                    @if($hasilSeleksiData && $statusSeleksiAkhir === 'lulus')
                                    <div class="final-result-info mt-2 p-2 rounded border border-success border-opacity-25" style="font-size: 0.85rem;">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="bi bi-award me-2 text-success"></i>
                                            <strong class="text-success">Hasil Seleksi Final</strong>
                                        </div>
                                        <div class="ms-4">
                                            <div class="text-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                <strong>Selamat! Anda diterima bekerja</strong>
                                            </div>
                                            @php
                                                $tanggalMulaiKerja = is_array($hasilSeleksiData) ?
                                                    ($hasilSeleksiData['tanggal_mulai_kerja'] ?? null) :
                                                    ($hasilSeleksiData->tanggal_mulai_kerja ?? null);
                                                $catatanFinal = is_array($hasilSeleksiData) ?
                                                    ($hasilSeleksiData['catatan'] ?? null) :
                                                    ($hasilSeleksiData->catatan ?? null);
                                            @endphp
                                            @if($tanggalMulaiKerja)
                                            <div class="mt-1">
                                                <i class="bi bi-calendar-plus me-1 text-muted"></i>
                                                <small>Mulai kerja: <strong>{{ date('d M Y', strtotime($tanggalMulaiKerja)) }}</strong></small>
                                            </div>
                                            @endif
                                            @if($catatanFinal)
                                            <div class="mt-1 p-1 bg-light rounded">
                                                <i class="bi bi-info-circle me-1 text-info"></i>
                                                <small class="text-muted">{{ $catatanFinal }}</small>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <div class="status-container text-end">


                                    <div class="status-badge mb-2">
                                        <span class="badge status-badge-large {{ $badgeClass }}">
                                            <i class="{{ $statusIcon }}"></i> {{ $overallStatus }}
                                        </span>

                                        @if($statusMessage)
                                        <div class="status-message mt-1">
                                            <small class="text-muted d-block" style="font-size: 0.75rem; line-height: 1.2;">
                                                {{ $statusMessage }}
                                            </small>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Progress Indicator -->
                                    <div class="progress-steps" style="font-size: 0.7rem;">
                                        @php
                                            $steps = [
                                                ['key' => 'berkas', 'label' => 'Berkas', 'status' => $statusSeleksiBerkas],
                                                ['key' => 'wawancara', 'label' => 'Interview', 'status' => $statusWawancara],
                                                ['key' => 'final', 'label' => 'Final', 'status' => $statusSeleksiAkhir]
                                            ];
                                        @endphp

                                        <div class="d-flex justify-content-between text-center">
                                            @foreach($steps as $step)
                                                @php
                                                    $stepClass = 'text-muted';
                                                    $stepIcon = 'bi-circle';

                                                    if ($step['status'] && (str_contains(strtolower($step['status']), 'diterima') || str_contains(strtolower($step['status']), 'lulus'))) {
                                                        $stepClass = 'text-success';
                                                        $stepIcon = 'bi-check-circle-fill';
                                                    } elseif ($step['status'] && (str_contains(strtolower($step['status']), 'ditolak') || str_contains(strtolower($step['status']), 'tidak lulus') || str_contains(strtolower($step['status']), 'tidak_lulus'))) {
                                                        $stepClass = 'text-danger';
                                                        $stepIcon = 'bi-x-circle-fill';
                                                    } elseif ($step['status'] && (str_contains(strtolower($step['status']), 'menunggu') || str_contains(strtolower($step['status']), 'pending'))) {
                                                        $stepClass = 'text-warning';
                                                        $stepIcon = 'bi-hourglass-split';
                                                    } elseif ($step['status'] && str_contains(strtolower($step['status']), 'dijadwalkan')) {
                                                        $stepClass = 'text-info';
                                                        $stepIcon = 'bi-calendar-check';
                                                    }
                                                @endphp

                                                <div class="flex-fill {{ $stepClass }}">
                                                    <i class="{{ $stepIcon }}"></i><br>
                                                    <small>{{ $step['label'] }}</small>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="action-buttons mt-2">
                                        <div class="btn-group d-flex" role="group">
                                            @if($lowonganData)
                                            @php
                                                $lowonganId = $isArray ? ($lowonganData['id_lowongan_pekerjaan'] ?? null) : ($lowonganData->id_lowongan_pekerjaan ?? null);
                                            @endphp
                                            <a href="{{ route('recruitments.show', $lowonganId) }}"
                                            class="btn btn-sm btn-outline-primary flex-fill" title="View Job Details">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                            @endif

                                            @if($statusSeleksiAkhir && str_contains(strtolower($statusSeleksiAkhir), 'diterima'))
                                            <button class="btn btn-sm btn-success flex-fill" disabled title="Congratulations! You are hired">
                                                <i class="bi bi-trophy"></i> Hired
                                            </button>
                                            @elseif(($statusSeleksiAkhir && str_contains(strtolower($statusSeleksiAkhir), 'ditolak')) || ($statusSeleksiBerkas && str_contains(strtolower($statusSeleksiBerkas), 'ditolak')) || ($statusWawancara && (str_contains(strtolower($statusWawancara), 'tidak lulus') || str_contains(strtolower($statusWawancara), 'tidak_lulus') || (str_contains(strtolower($statusWawancara), 'ditolak') && !str_contains(strtolower($statusWawancara), 'diterima')))))
                                            <button class="btn btn-sm btn-outline-secondary flex-fill" disabled title="Application was not successful">
                                                <i class="bi bi-info-circle"></i> Closed
                                            </button>
                                            @else
                                                @if($statusWawancara && str_contains(strtolower($statusWawancara), 'dijadwalkan') && ($interviewDate || $interviewZoomLink))
                                                @if($interviewZoomLink)
                                                <a href="{{ $interviewZoomLink }}" target="_blank" class="btn btn-sm btn-info flex-fill" title="Join interview">
                                                    <i class="bi bi-camera-video"></i> Join
                                                </a>
                                                @else
                                                <button class="btn btn-sm btn-info flex-fill" title="Prepare for your interview">
                                                    <i class="bi bi-calendar-check"></i> Interview
                                                </button>
                                                @endif
                                                @elseif($statusWawancara && (str_contains(strtolower($statusWawancara), 'lulus') || str_contains(strtolower($statusWawancara), 'diterima')))
                                                <button class="btn btn-sm btn-primary flex-fill" title="Waiting for final decision">
                                                    <i class="bi bi-clock-history"></i> Final
                                                </button>
                                                @elseif($statusSeleksiBerkas && str_contains(strtolower($statusSeleksiBerkas), 'diterima'))
                                                <button class="btn btn-sm btn-info flex-fill" title="Waiting for interview schedule">
                                                    <i class="bi bi-calendar-plus"></i> Schedule
                                                </button>
                                                @elseif($statusSeleksiBerkas && str_contains(strtolower($statusSeleksiBerkas), 'menunggu'))
                                                <button class="btn btn-sm btn-warning flex-fill" title="Documents under review">
                                                    <i class="bi bi-hourglass-split"></i> Review
                                                </button>
                                                @else
                                                <button class="btn btn-sm btn-outline-info flex-fill" title="Application ID: {{ $applicationId }}">
                                                    <i class="bi bi-clock-history"></i> Progress
                                                </button>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @if(count($myApplications) >= 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('recruitments.index') }}" class="btn btn-outline-primary">
                                    View All Jobs
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-briefcase text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">No Job Applications Yet</h5>
                            <p class="text-muted">You haven't applied to any jobs yet. Start exploring available positions!</p>
                            <a href="{{ route('recruitments.index') }}" class="btn btn-primary mt-3"><i class="bi bi-briefcase"></i> Browse Jobs</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function checkIn() {
        // Redirect ke halaman check-in
        window.location.href = '{{ route("absensi.create") }}';
    }

    function checkOut() {
        // Redirect ke halaman absensi dan trigger check-out modal
        window.location.href = '{{ route("absensi.index") }}#checkout';
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if(is_admin() || is_hrd())
            // Gender Chart (Donut dengan total di tengah)
            const maleCount = {{ json_encode(intval($laki ?? 0)) }};
            const femaleCount = {{ json_encode(intval($perempuan ?? 0)) }};
            const totalGender = maleCount + femaleCount;
            const ctx = document.getElementById('genderChart');
            
            if (ctx && totalGender > 0) {
                const malePercentage = ((maleCount / totalGender) * 100).toFixed(1);
                const femalePercentage = ((femaleCount / totalGender) * 100).toFixed(1);
                
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Male', 'Female'],
                        datasets: [{
                            data: [maleCount, femaleCount],
                            backgroundColor: ['#36A2EB', '#17a2b8'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            cutout: '70%'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: false
                            }
                        }
                    }
                });

                // Generate custom legend dengan persentase
                const legendContainer = document.getElementById('genderLegend');
                legendContainer.innerHTML = `
                    <div class="gender-legend-item">
                        <div class="gender-legend-color" style="background-color: #36A2EB;"></div>
                        <span>Male</span>
                        <span style="margin-left: auto; font-weight: bold;">${maleCount} ${malePercentage}%</span>
                    </div>
                    <div class="gender-legend-item">
                        <div class="gender-legend-color" style="background-color: #17a2b8;"></div>
                        <span>Female</span>
                        <span style="margin-left: auto; font-weight: bold;">${femaleCount} ${femalePercentage}%</span>
                    </div>
                `;
            } else if (ctx) {
                // Placeholder jika tidak ada data
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['No Data'],
                        datasets: [{ 
                            data: [1], 
                            backgroundColor: ['#e9ecef'],
                            cutout: '70%'
                        }]
                    },
                    options: { 
                        plugins: { legend: { display: false } }, 
                        maintainAspectRatio: false 
                    }
                });
            }

            // Position Progress Bars
            const positionStats = @json($positionStats ?? []);
            const positionContainer = document.getElementById('positionProgressBars');
            
            if (positionContainer && Object.keys(positionStats).length > 0) {
                const totalPositions = Object.values(positionStats).reduce((sum, count) => sum + count, 0);
                const colors = ['position-color-1', 'position-color-2', 'position-color-3', 'position-color-4', 'position-color-5', 'position-color-6'];
                
                let progressHTML = `
                    <div style="margin-bottom: 6px; font-size: 9px; color: #6c757d;">
                        <span>0%</span>
                        <span style="float: right;">100%</span>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <span style="font-size: 10px; font-weight: bold;">Total</span>
                        <span style="float: right; font-size: 10px; font-weight: bold;">${totalPositions}</span>
                    </div>
                `;
                
                Object.entries(positionStats).forEach(([position, count], index) => {
                    const percentage = totalPositions > 0 ? ((count / totalPositions) * 100).toFixed(1) : 0;
                    const colorClass = colors[index % colors.length];
                    
                    progressHTML += `
                        <div class="position-progress-item">
                            <div class="position-progress-label">
                                <span>${position}</span>
                                <span>${count} ${percentage}%</span>
                            </div>
                            <div class="position-progress-bar">
                                <div class="position-progress-fill ${colorClass}" style="width: 0%;" data-width="${percentage}%"></div>
                            </div>
                        </div>
                    `;
                });
                
                positionContainer.innerHTML = progressHTML;
                
                // Animasi progress bars
                setTimeout(() => {
                    const progressFills = positionContainer.querySelectorAll('.position-progress-fill');
                    progressFills.forEach(fill => {
                        fill.style.width = fill.dataset.width;
                    });
                }, 100);
            } else if (positionContainer) {
                positionContainer.innerHTML = `
                    <div style="text-align: center; padding: 15px; color: #6c757d; font-size: 10px;">
                        Belum ada data posisi pegawai
                    </div>
                `;
            }
        @endif
    });
</script>
@endsection
