@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Stats Cards -->
        @if(is_admin() || is_hrd())
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Users
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers ?? 0 }}</div>
                                <small class="text-muted">{{ $totalActiveUsers ?? 0 }} aktif</small>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Today's Appointments
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayAppointments ?? 0 }}</div>
                                <small class="text-muted">{{ $pendingAppointments ?? 0 }} pending</small>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
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
                                    Total Treatments
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTreatments ?? 0 }}</div>
                                <small class="text-muted">{{ $completedAppointments ?? 0 }} completed</small>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-heart-pulse fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Job Applications
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalApplications ?? 0 }}</div>
                                <small class="text-muted">{{ $acceptedApplications ?? 0 }} accepted</small>
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
                                {{ ucfirst(str_replace('_', ' ', user_role())) }}
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
        @if(!is_pelanggan())
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(is_hrd() || is_admin())
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('trainings.create') }}" class="btn btn-info btn-block">
                                    <i class="bi bi-plus-circle"></i> Create Training
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('payroll.index') }}" class="btn btn-warning btn-block">
                                    <i class="bi bi-eye"></i> View Penggajian
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-block">
                                    <i class="bi bi-people"></i> Manage Users
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('users.create') }}" class="btn btn-dark btn-block">
                                    <i class="bi bi-person-plus"></i> Add User
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('pegawai.index') }}" class="btn btn-primary btn-block">
                                    <i class="bi bi-person-badge"></i> Kelola Pegawai
                                </a>
                            </div>
                        @endif

                        @if(!is_pelanggan())
                            <div class="col-md-6 mb-2">
                                @if(isset($hasCheckedIn) && $hasCheckedIn)
                                    @if(isset($hasCheckedOut) && $hasCheckedOut)
                                        <button class="btn btn-success btn-block" disabled>
                                            <i class="bi bi-check-circle"></i> Absensi Selesai
                                        </button>
                                    @else
                                        <button onclick="checkOut()" class="btn btn-outline-danger btn-block">
                                            <i class="bi bi-clock-history"></i> Check Out
                                        </button>
                                    @endif
                                @else
                                    <button onclick="checkIn()" class="btn btn-outline-success btn-block">
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

        @if(is_pelanggan())
        <!-- My Job Applications for Pelanggan -->
        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">My Job Applications</h6>
                    <a href="{{ route('recruitments.index') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> Apply Job
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($myApplications) && is_array($myApplications) && count($myApplications) > 0)
                        @foreach($myApplications as $application)
                        @php
                            // Handle both array and object format from API
                            $isArray = is_array($application);
                            $lowonganData = $isArray ? ($application['lowongan_pekerjaan'] ?? null) : ($application->lowonganPekerjaan ?? null);
                            $posisiData = null;
                            
                            if ($lowonganData) {
                                $posisiData = $isArray ? ($lowonganData['posisi'] ?? null) : ($lowonganData->posisi ?? null);
                            }
                            
                            $applicationId = $isArray ? ($application['id_lamaran_pekerjaan'] ?? null) : ($application->id ?? null);
                            $namaUser = $isArray ? ($application['nama_pelamar'] ?? 'N/A') : ($application->nama_pelamar ?? 'N/A');
                            $statusLamaran = $isArray ? ($application['status_lamaran'] ?? 'pending') : ($application->status_lamaran ?? 'pending');
                            $createdAt = $isArray ? ($application['created_at'] ?? null) : ($application->created_at ?? null);
                            
                            $judulPekerjaan = 'N/A';
                            $namaPosisi = 'Position not available';
                            
                            if ($lowonganData) {
                                $judulPekerjaan = $isArray ? ($lowonganData['judul_pekerjaan'] ?? 'N/A') : ($lowonganData->judul_pekerjaan ?? 'N/A');
                            }
                            
                            if ($posisiData) {
                                $namaPosisi = $isArray ? ($posisiData['nama_posisi'] ?? 'Position not available') : ($posisiData->nama_posisi ?? 'Position not available');
                            }
                        @endphp
                        
                        <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                            <div class="flex-grow-1">
                                <strong>{{ $judulPekerjaan }}</strong><br>
                                <small class="text-muted">
                                    {{ $namaPosisi }} - 
                                    Applied: {{ $createdAt ? date('d M Y', strtotime($createdAt)) : 'N/A' }}
                                </small>
                                @if(isset($application['interview_date']) || isset($application->interview_date))
                                @php
                                    $interviewDate = $isArray ? ($application['interview_date'] ?? null) : ($application->interview_date ?? null);
                                @endphp
                                @if($interviewDate)
                                <br><small class="text-info">
                                    <i class="bi bi-calendar"></i> Interview: {{ date('d M Y, H:i', strtotime($interviewDate)) }}
                                </small>
                                @endif
                                @endif
                            </div>
                            <div class="text-right">
                                @php
                                    $badgeClass = match($statusLamaran) {
                                        'pending' => 'bg-warning',
                                        'diterima' => 'bg-success', 
                                        'ditolak' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    
                                    $statusLabel = match($statusLamaran) {
                                        'pending' => 'Pending',
                                        'diterima' => 'Accepted',
                                        'ditolak' => 'Rejected',
                                        default => ucfirst($statusLamaran)
                                    };
                                @endphp
                                
                                <span class="badge {{ $badgeClass }} mb-2">
                                    {{ $statusLabel }}
                                </span>
                                @if($statusLamaran == 'diterima')
                                <br><small class="text-success">
                                    <i class="bi bi-check-circle"></i> Congratulations!
                                </small>
                                @endif
                                <br>
                                <div class="btn-group mt-2" role="group">
                                    @if($lowonganData)
                                    @php
                                        $lowonganId = $isArray ? ($lowonganData['id_lowongan_pekerjaan'] ?? null) : ($lowonganData->id_lowongan_pekerjaan ?? null);
                                    @endphp
                                    <a href="{{ route('recruitments.show', $lowonganId) }}" 
                                       class="btn btn-sm btn-outline-primary" title="View Job Details">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    @endif
                                    <button class="btn btn-sm btn-outline-info" title="Application ID: {{ $applicationId }}">
                                        <i class="bi bi-info-circle"></i> Status
                                    </button>
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
                            <a href="{{ route('recruitments.index') }}" class="btn btn-primary mt-3">
                                <i class="bi bi-briefcase"></i> Browse Jobs
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    @if(is_hrd() || is_admin())
    <div class="row">
        <!-- Recent Trainings -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Trainings</h6>
                </div>
                <div class="card-body">
                    @if(isset($upcomingTrainings) && is_array($upcomingTrainings) && count($upcomingTrainings) > 0)
                        @foreach($upcomingTrainings as $training)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>{{ $training->judul }}</strong><br>
                                <small class="text-muted">
                                    {{ $training->jenis_display }} - {{ $training->durasi_display }}
                                </small>
                            </div>
                            <span class="{{ $training->status_badge_class }}">{{ $training->status_display }}</span>
                        </div>
                        @endforeach
                        <div class="text-center mt-3">
                            <a href="{{ route('trainings.index') }}" class="btn btn-primary btn-sm">View All Trainings</a>
                        </div>
                    @else
                        <p class="text-muted">No trainings available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Religious Studies -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upcoming Penggajian</h6>
                </div>
                <div class="card-body">
                    @if(isset($upcomingReligiousStudies) && is_array($upcomingReligiousStudies) && count($upcomingReligiousStudies) > 0)
                        @foreach($upcomingReligiousStudies as $study)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>{{ $study->title }}</strong><br>
                                <small class="text-muted">
                                    {{ isset($study->scheduled_date) ? (is_object($study->scheduled_date) ? $study->scheduled_date->format('d M Y, H:i') : date('d M Y, H:i', strtotime($study->scheduled_date))) : 'N/A' }}
                                </small>
                            </div>
                            <span class="badge bg-warning">{{ $study->status }}</span>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted">No upcoming penggajian</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function checkIn() {
        // Redirect ke halaman check-in
        window.location.href = '{{ route("absensi.create") }}';
    }
    
    function checkOut() {
        // Redirect ke halaman absensi dan trigger check-out modal
        window.location.href = '{{ route("absensi.index") }}#checkout';
    }
</script>
@endsection
