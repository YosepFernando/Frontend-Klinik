@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header bg-gradient-dark text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard Absensi (Admin Only)
                            </h4>
                            <small class="opacity-75">Overview sistem absensi dan kehadiran - Akses Admin</small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('absensi.index') }}" class="btn btn-outline-light">
                                <i class="fas fa-list me-1"></i>Data Absensi
                            </a>
                            <a href="{{ route('absensi.report') }}" class="btn btn-primary">
                                <i class="fas fa-chart-bar me-1"></i>Laporan Detail
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Quick Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Kehadiran Hari Ini</h6>
                                            <h3 class="mb-0">{{ $todayStats['hadir'] }}</h3>
                                            <small>dari {{ $totalActiveEmployees }} karyawan</small>
                                        </div>
                                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Terlambat Hari Ini</h6>
                                            <h3 class="mb-0">{{ $todayStats['terlambat'] }}</h3>
                                            <small>{{ $todayStats['terlambat'] > 0 ? 'perlu perhatian' : 'sangat baik' }}</small>
                                        </div>
                                        <i class="fas fa-clock fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Izin/Sakit</h6>
                                            <h3 class="mb-0">{{ $todayStats['izin_sakit'] }}</h3>
                                            <small>hari ini</small>
                                        </div>
                                        <i class="fas fa-user-md fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Belum Absen</h6>
                                            <h3 class="mb-0">{{ $todayStats['belum_absen'] }}</h3>
                                            <small>perlu follow up</small>
                                        </div>
                                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Statistik Kehadiran 7 Hari Terakhir</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="attendanceChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Distribusi Status Bulan Ini</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="statusChart" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Absensi Terbaru</h6>
                                </div>
                                <div class="card-body">
                                    @if($recentAttendances->count() > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($recentAttendances as $attendance)
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle me-3 bg-{{ $attendance->status === 'Hadir' ? 'success' : ($attendance->status === 'Terlambat' ? 'warning' : 'secondary') }} text-white">
                                                            {{ substr($attendance->pegawai->user->name, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <strong>{{ $attendance->pegawai->user->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                {{ $attendance->tanggal->format('d/m/Y H:i') }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <span class="badge bg-{{ $attendance->status === 'Hadir' ? 'success' : ($attendance->status === 'Terlambat' ? 'warning' : 'secondary') }}">
                                                        {{ $attendance->status }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted text-center">Belum ada data absensi hari ini</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Karyawan Dengan Kehadiran Terbaik</h6>
                                </div>
                                <div class="card-body">
                                    @if($topEmployees->count() > 0)
                                        <div class="list-group list-group-flush">
                                            @foreach($topEmployees as $employee)
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-circle me-3 bg-success text-white">
                                                            {{ substr($employee->name, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <strong>{{ $employee->name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ ucfirst($employee->role) }}</small>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <div class="progress" style="width: 80px; height: 20px;">
                                                            <div class="progress-bar bg-success" style="width: {{ $employee->attendance_percentage }}%">
                                                                {{ number_format($employee->attendance_percentage, 0) }}%
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted text-center">Belum ada data untuk ditampilkan</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Attendance Chart (7 days)
const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
const attendanceChart = new Chart(attendanceCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($weeklyStats['labels']) !!},
        datasets: [{
            label: 'Hadir',
            data: {!! json_encode($weeklyStats['hadir']) !!},
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4
        }, {
            label: 'Terlambat',
            data: {!! json_encode($weeklyStats['terlambat']) !!},
            borderColor: '#ffc107',
            backgroundColor: 'rgba(255, 193, 7, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Status Chart (Monthly)
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Tidak Hadir'],
        datasets: [{
            data: {!! json_encode($monthlyStats) !!},
            backgroundColor: [
                '#28a745',
                '#ffc107', 
                '#17a2b8',
                '#6c757d',
                '#dc3545'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<style>
.bg-gradient-dark {
    background: linear-gradient(135deg, #343a40 0%, #495057 100%);
}

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

.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
}

.list-group-item {
    border: none;
    border-bottom: 1px solid rgba(0,0,0,0.125);
}

.list-group-item:last-child {
    border-bottom: none;
}
</style>
@endsection
