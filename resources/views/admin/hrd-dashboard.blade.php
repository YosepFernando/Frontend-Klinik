@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard Admin HRD (Admin Only)</h2>
                    <p class="text-muted mb-0">Kelola lowongan kerja, pelatihan, dan penggajian - Akses Admin</p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="row mb-4">
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-gradient-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="fw-bold mb-0">{{ $totalRecruitments ?? 0 }}</h4>
                                    <p class="mb-0">Total Lowongan</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-briefcase fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-gradient-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="fw-bold mb-0">{{ $totalApplications ?? 0 }}</h4>
                                    <p class="mb-0">Total Aplikasi</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-gradient-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="fw-bold mb-0">{{ $totalTrainings ?? 0 }}</h4>
                                    <p class="mb-0">Total Pelatihan</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-graduation-cap fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card bg-gradient-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="fw-bold mb-0">{{ $totalReligiousStudies ?? 0 }}</h4>
                                    <p class="mb-0">Total Penggajian</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-cash-register fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Recruitment Actions -->
                                <div class="col-lg-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6 class="text-primary mb-3">
                                            <i class="fas fa-briefcase me-2"></i>Manajemen Lowongan
                                        </h6>
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('recruitments.create') }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus me-2"></i>Buat Lowongan Baru
                                            </a>
                                            <a href="{{ route('recruitments.index') }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-list me-2"></i>Lihat Semua Lowongan
                                            </a>
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            Kelola lowongan dengan sistem seleksi 3 tahap
                                        </small>
                                    </div>
                                </div>

                                <!-- Training Actions -->
                                <div class="col-lg-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6 class="text-warning mb-3">
                                            <i class="fas fa-graduation-cap me-2"></i>Manajemen Pelatihan
                                        </h6>
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('trainings.create') }}" class="btn btn-warning btn-sm">
                                                <i class="fas fa-plus me-2"></i>Buat Pelatihan Baru
                                            </a>
                                            <a href="{{ route('trainings.index') }}" class="btn btn-outline-warning btn-sm">
                                                <i class="fas fa-list me-2"></i>Lihat Semua Pelatihan
                                            </a>
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            Kelola pelatihan untuk karyawan
                                        </small>
                                    </div>
                                </div>

                                <!-- Religious Study Actions -->
                                <div class="col-lg-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <h6 class="text-info mb-3">
                                            <i class="fas fa-cash-register me-2"></i>Manajemen Penggajian
                                        </h6>
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('payroll.index') }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-list me-2"></i>Lihat Semua Penggajian
                                            </a>
                                            <button class="btn btn-outline-info btn-sm" disabled>
                                                <i class="fas fa-plus me-2"></i>Tidak Ada Fitur Tambah
                                            </button>
                                        </div>
                                        <small class="text-muted mt-2 d-block">
                                            Hanya dapat melihat dan mengedit penggajian
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-clock me-2"></i>Lowongan Terbaru
                            </h6>
                            <a href="{{ route('recruitments.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                        </div>
                        <div class="card-body">
                            @if(isset($recentRecruitments) && $recentRecruitments->count() > 0)
                                @foreach($recentRecruitments->take(5) as $recruitment)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <h6 class="mb-1">{{ $recruitment->position }}</h6>
                                        <small class="text-muted">{{ $recruitment->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-{{ $recruitment->status === 'open' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($recruitment->status) }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-briefcase fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Belum ada lowongan</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-graduation-cap me-2"></i>Pelatihan Terbaru
                            </h6>
                            <a href="{{ route('trainings.index') }}" class="btn btn-sm btn-outline-warning">Lihat Semua</a>
                        </div>
                        <div class="card-body">
                            @if(isset($recentTrainings) && $recentTrainings->count() > 0)
                                @foreach($recentTrainings->take(5) as $training)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <h6 class="mb-1">{{ $training->title }}</h6>
                                        <small class="text-muted">{{ $training->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-{{ $training->status === 'scheduled' ? 'info' : ($training->status === 'ongoing' ? 'warning' : ($training->status === 'completed' ? 'success' : 'danger')) }}">
                                            {{ ucfirst($training->status) }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="text-center py-3">
                                    <i class="fas fa-graduation-cap fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Belum ada pelatihan</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature Summary -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Ringkasan Fitur</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 mb-3">
                                    <div class="border-start border-primary border-4 ps-3">
                                        <h6 class="text-primary">Fitur Lowongan Kerja</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-check text-success me-2"></i>Buat & kelola lowongan</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Sistem seleksi 3 tahap</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Upload & review dokumen</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Manajemen wawancara</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Keputusan akhir</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <div class="border-start border-warning border-4 ps-3">
                                        <h6 class="text-warning">Fitur Pelatihan</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-check text-success me-2"></i>Buat & kelola pelatihan</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Jadwal pelatihan</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Manajemen peserta</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Materi pelatihan</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Status tracking</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-lg-4 mb-3">
                                    <div class="border-start border-info border-4 ps-3">
                                        <h6 class="text-info">Fitur Penggajian</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-eye text-primary me-2"></i>Lihat data penggajian</li>
                                            <li><i class="fas fa-edit text-warning me-2"></i>Edit penggajian</li>
                                            <li><i class="fas fa-users text-info me-2"></i>Manajemen peserta</li>
                                            <li><i class="fas fa-times text-danger me-2"></i>Tidak ada fitur tambah</li>
                                            <li><i class="fas fa-times text-danger me-2"></i>Tidak ada fitur hapus</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.bg-gradient-success {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}
.bg-gradient-warning {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}
.bg-gradient-info {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}
.hover-card {
    transition: transform 0.2s;
}
.hover-card:hover {
    transform: translateY(-2px);
}
</style>
@endpush
