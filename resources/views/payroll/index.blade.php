@extends('layouts.app')

@section('content')
<style>
.glass-card {
    background: rgba(255, 255, 255, 0.25);
    border-radius: 16px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.glass-header {
    background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(80, 200, 120, 0.1));
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px 16px 0 0;
}

.section-card {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
    transition: all 0.3s ease;
}

.section-card:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}

.payroll-card {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 12px;
    border: 1px solid rgba(74, 144, 226, 0.2);
    transition: all 0.3s ease;
    overflow: hidden;
}

.payroll-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: #4a90e2;
}

.btn-modern {
    padding: 0.5rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.btn-primary.btn-modern {
    background: linear-gradient(135deg, #4a90e2, #50c878);
    color: white;
}

.btn-primary.btn-modern:hover {
    background: linear-gradient(135deg, #357abd, #45b369);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
}

.btn-secondary.btn-modern {
    background: rgba(108, 117, 125, 0.8);
    color: white;
}

.btn-secondary.btn-modern:hover {
    background: rgba(108, 117, 125, 1);
    transform: translateY(-1px);
}

.btn-success.btn-modern {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.btn-success.btn-modern:hover {
    background: linear-gradient(135deg, #218838, #1abc9c);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-warning.btn-modern {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.btn-warning.btn-modern:hover {
    background: linear-gradient(135deg, #e0a800, #dc6705);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
}

.btn-outline-info {
    border: 2px solid #17a2b8;
    color: #17a2b8;
    transition: all 0.3s ease;
}

.btn-outline-info:hover {
    background: #17a2b8;
    color: white;
    transform: translateY(-1px);
}

.badge-status {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.75rem;
}

.badge-paid {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.badge-pending {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.badge-cancelled {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.attendance-badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-weight: 600;
}

.attendance-excellent {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.attendance-good {
    background: linear-gradient(135deg, #17a2b8, #6610f2);
    color: white;
}

.attendance-average {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.attendance-poor {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.form-control, .form-select {
    border: 2px solid rgba(74, 144, 226, 0.2);
    border-radius: 8px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.8);
}

.form-control:focus, .form-select:focus {
    border-color: #4a90e2;
    box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
    background: rgba(255, 255, 255, 0.95);
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: #6c757d;
}

.empty-state-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.success-message {
    background: linear-gradient(135deg, rgba(80, 200, 120, 0.1), rgba(74, 144, 226, 0.1));
    border: 1px solid rgba(80, 200, 120, 0.3);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    color: #155724;
}

.error-message {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(255, 193, 7, 0.1));
    border: 1px solid rgba(220, 53, 69, 0.3);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    color: #721c24;
}

.table-modern {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.table-modern thead {
    background: linear-gradient(135deg, #4a90e2, #50c878);
    color: white;
}

.table-modern tbody tr {
    transition: all 0.3s ease;
}

.table-modern tbody tr:hover {
    background-color: rgba(74, 144, 226, 0.05);
    transform: scale(1.001);
}

.view-toggle {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 8px;
    padding: 0.25rem;
    border: 1px solid rgba(74, 144, 226, 0.2);
}

.view-toggle .btn {
    border: none;
    border-radius: 6px;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.view-toggle .btn.active {
    background: linear-gradient(135deg, #4a90e2, #50c878);
    color: white;
}

.pagination {
    justify-content: center;
}

.page-link {
    border: 2px solid rgba(74, 144, 226, 0.2);
    color: #4a90e2;
    border-radius: 8px;
    margin: 0 2px;
    transition: all 0.3s ease;
}

.page-link:hover {
    background: #4a90e2;
    color: white;
    border-color: #4a90e2;
    transform: translateY(-1px);
}

.page-item.active .page-link {
    background: linear-gradient(135deg, #4a90e2, #50c878);
    border-color: #4a90e2;
}

.custom-pagination {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 12px;
    padding: 1rem;
    margin-top: 2rem;
    border: 1px solid rgba(74, 144, 226, 0.2);
}

.dropdown-menu {
    border: none;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-radius: 12px;
}

.dropdown-item {
    padding: 0.75rem 1.25rem;
    transition: all 0.3s ease;
    border-radius: 8px;
    margin: 0.125rem 0.5rem;
}

.dropdown-item:hover {
    background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(80, 200, 120, 0.1));
    color: #4a90e2;
    transform: translateX(2px);
}

.dropdown-item i {
    width: 20px;
}

.btn-modern.dropdown-toggle::after {
    margin-left: 0.5rem;
}

.dropdown-toggle:focus {
    box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
}

/* Fix untuk dropdown Bootstrap - pastikan dropdown muncul dan tidak tertutup oleh elemen lain */
.dropdown {
    position: relative;
    z-index: 1050;
}

.dropdown-menu {
    z-index: 1051 !important;
    min-width: 250px;
    margin-top: 0.5rem !important;
}

.dropdown-menu.show {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.dropdown-item {
    font-size: 0.9rem;
    padding: 0.75rem 1rem;
}

.dropdown-item:hover {
    color: #ffffff !important;
}

/* Override Bootstrap defaults untuk memastikan dropdown terlihat */
.btn-group .dropdown-menu,
.dropdown .dropdown-menu {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
}

/* Perbaikan untuk responsif */
@media (max-width: 768px) {
    .dropdown-menu {
        min-width: 200px;
        font-size: 0.85rem;
    }
}
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="glass-card fade-in">
                <div class="glass-header p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1 text-primary">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                @if(is_admin_or_hrd())
                                    Manajemen Penggajian
                                @else
                                    Slip Gaji Saya
                                @endif
                            </h3>
                            <p class="text-muted mb-0">
                                @if(is_admin_or_hrd())
                                    Kelola data gaji dan payroll karyawan
                                @else
                                    Lihat informasi gaji dan slip pembayaran Anda
                                @endif
                            </p>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <!-- View Toggle -->
                            <div class="view-toggle me-3">
                                <button type="button" class="btn btn-sm active" id="cardViewBtn">
                                    <i class="fas fa-th-large me-1"></i> Kartu
                                </button>
                                <button type="button" class="btn btn-sm" id="tableViewBtn">
                                    <i class="fas fa-table me-1"></i> Tabel
                                </button>
                            </div>
                            @if(is_admin_or_hrd())
                                <div class="dropdown me-2">
                                    <button class="btn btn-primary btn-modern dropdown-toggle" type="button" 
                                            id="masterGajiDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-cogs me-1"></i> Master Data Gaji
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="masterGajiDropdown">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="showMasterGajiPegawaiModal()">
                                                <i class="fas fa-user-tie me-2"></i> 
                                                <span>Gaji Pokok Pegawai Individual</span>
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0)" onclick="showMasterGajiPosisiModal()">
                                                <i class="fas fa-users me-2"></i> 
                                                <span>Master Gaji Per Posisi</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="success-message fade-in">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="error-message fade-in">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            @if(strpos(session('error'), 'Sesi') !== false || strpos(session('error'), 'login') !== false)
                                <div class="mt-2">
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-sign-in-alt me-1"></i> Login Kembali
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if(isset($error))
                        <div class="error-message fade-in">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ $error }}
                            @if(strpos($error, 'Sesi') !== false || strpos($error, 'login') !== false)
                                <div class="mt-2">
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-sign-in-alt me-1"></i> Login Kembali
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Filter Section -->
                    <div class="section-card p-4 mb-4">
                        <h5 class="mb-3">
                            <i class="fas fa-filter text-primary me-2"></i>Filter Data Gaji
                        </h5>
                        <form method="GET" action="{{ route('payroll.index') }}" class="row g-3">
                            @if(is_admin_or_hrd())
                                <div class="col-md-3">
                                    <label for="search" class="form-label">Cari Pegawai</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" placeholder="Nama pegawai...">
                                </div>
                            @endif
                            <div class="col-md-2">
                                <label for="periode_bulan" class="form-label">Bulan</label>
                                <select class="form-select" id="periode_bulan" name="periode_bulan">
                                    <option value="">Semua</option>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ request('periode_bulan') == $i ? 'selected' : '' }}>
                                            {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="periode_tahun" class="form-label">Tahun</label>
                                <select class="form-select" id="periode_tahun" name="periode_tahun">
                                    <option value="">Semua</option>
                                    @for($year = date('Y'); $year >= 2020; $year--)
                                        <option value="{{ $year }}" {{ request('periode_tahun') == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            @if(is_admin_or_hrd())
                                <div class="col-md-3">
                                    <label for="pegawai_id" class="form-label">Pegawai</label>
                                    <select class="form-select" id="pegawai_id" name="pegawai_id">
                                        <option value="">Semua Pegawai</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee['id_pegawai'] ?? $employee['id'] }}" 
                                                    {{ request('pegawai_id') == ($employee['id_pegawai'] ?? $employee['id']) ? 'selected' : '' }}>
                                                {{ $employee['nama_lengkap'] ?? $employee['nama'] ?? 'Nama tidak tersedia' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status Pembayaran</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Semua</option>
                                    <option value="Belum Terbayar" {{ request('status') == 'Belum Terbayar' ? 'selected' : '' }}>Belum Terbayar</option>
                                    <option value="Terbayar" {{ request('status') == 'Terbayar' ? 'selected' : '' }}>Terbayar</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-modern me-2">
                                    <i class="fas fa-search me-1"></i> Filter
                                </button>
                                <a href="{{ route('payroll.index') }}" class="btn btn-secondary btn-modern me-2">
                                    <i class="fas fa-times me-1"></i> Reset
                                </a>
                                @if(is_admin_or_hrd())
                                    <button type="button" class="btn btn-success btn-modern" onclick="exportPayrollToPdf()">
                                        <i class="fas fa-file-pdf me-1"></i> Download Laporan PDF
                                    </button>
                                @else
                                    <button type="button" class="btn btn-success btn-modern" onclick="downloadSlipGajiSaya()">
                                        <i class="fas fa-download me-1"></i> Download Slip Gaji Saya
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Payroll Data -->
                    @if($payrolls->count() > 0)
                        <!-- Card View -->
                        <div id="cardView" class="view-content">
                            <div class="row">
                                @foreach($payrolls as $payroll)
                                    <div class="col-lg-6 col-xl-4 mb-4">
                                        <div class="payroll-card">
                                            <div class="card-header p-3 bg-gradient-primary text-white">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-0 fw-bold">
                                                            {{ $payroll['pegawai']['nama_lengkap'] ?? 'Nama tidak tersedia' }}
                                                        </h6>
                                                        <small class="opacity-75">
                                                            {{ $payroll['pegawai']['posisi']['nama_posisi'] ?? 'Posisi tidak tersedia' }} | {{ $payroll['pegawai']['NIP'] ?? 'N/A' }}
                                                        </small>
                                                    </div>
                                                    <span class="badge-status {{ ($payroll['status'] ?? '') == 'Terbayar' ? 'badge-paid' : 'badge-pending' }}">
                                                        @if(($payroll['status'] ?? '') == 'Terbayar')
                                                            <i class="fas fa-check-circle"></i> Terbayar
                                                        @else
                                                            <i class="fas fa-clock"></i> Belum Terbayar
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="mt-2">
                                                    <small>Periode: {{ DateTime::createFromFormat('!m', $payroll['periode_bulan'] ?? 1)->format('F') }} {{ $payroll['periode_tahun'] ?? date('Y') }}</small>
                                                </div>
                                            </div>
                                            
                                            <div class="card-body p-4">
                                                <div class="row text-center mb-3">
                                                    <div class="col-6">
                                                        <div class="border-end">
                                                            <h5 class="text-success mb-0">
                                                                Rp {{ number_format($payroll['gaji_total'] ?? 0, 0, ',', '.') }}
                                                            </h5>
                                                            <small class="text-muted">Total Gaji</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <h6 class="text-primary mb-0">
                                                            Rp {{ number_format($payroll['gaji_pokok'] ?? 0, 0, ',', '.') }}
                                                        </h6>
                                                        <small class="text-muted">Gaji Pokok</small>
                                                    </div>
                                                </div>
                                                
                                                <div class="row g-2 mb-3">
                                                    @if(($payroll['gaji_kehadiran'] ?? 0) > 0)
                                                        <div class="col-6">
                                                            <small class="text-muted">Gaji Kehadiran</small>
                                                            <div class="fw-bold text-success">
                                                                +Rp {{ number_format($payroll['gaji_kehadiran'] ?? 0, 0, ',', '.') }}
                                                            </div>
                                                        </div>
                                                    @endif
                                                    
                                                    @if(($payroll['gaji_bonus'] ?? 0) > 0)
                                                        <div class="col-6">
                                                            <small class="text-muted">Bonus</small>
                                                            <div class="fw-bold text-success">
                                                                +Rp {{ number_format($payroll['gaji_bonus'] ?? 0, 0, ',', '.') }}
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Attendance Info -->
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="small text-muted">Kehadiran:</span>
                                                        <span class="attendance-badge 
                                                            @php
                                                                $persentase = $payroll['persentase_kehadiran'] ?? 0;
                                                            @endphp
                                                            @if($persentase >= 95) attendance-excellent
                                                            @elseif($persentase >= 85) attendance-good
                                                            @elseif($persentase >= 75) attendance-average
                                                            @else attendance-poor
                                                            @endif">
                                                            {{ $payroll['persentase_kehadiran'] ?? 0 }}%
                                                        </span>
                                                    </div>
                                                    <div class="small text-muted">
                                                        {{ $payroll['jumlah_absensi'] ?? 0 }}/{{ $payroll['total_hari_kerja'] ?? 0 }} hari kerja
                                                    </div>
                                                </div>
                                            </div>
                                                             <div class="card-footer bg-light p-2">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('payroll.show', $payroll['id_gaji']) }}" 
                                       class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </a>
                                    @if(is_admin_or_hrd() || (isset($payroll['pegawai']['user']['id_user']) && $payroll['pegawai']['user']['id_user'] == session('user_id')))
                                        <button type="button" class="btn btn-success btn-sm" 
                                                onclick="downloadSlipGaji('{{ $payroll['id_gaji'] }}', '{{ $payroll['pegawai']['nama_lengkap'] ?? 'N/A' }}')">
                                            <i class="fas fa-download me-1"></i> Slip
                                        </button>
                                    @endif
                                    @if(is_admin_or_hrd())
                                        <a href="{{ route('payroll.edit', $payroll['id_gaji']) }}" 
                                           class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('payroll.destroy', $payroll['id_gaji']) }}" 
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Yakin ingin menghapus data gaji ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Table View -->
                        <div id="tableView" class="view-content" style="display: none;">
                            <div class="table-modern">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Pegawai</th>
                                            <th>Posisi</th>
                                            <th>Periode</th>
                                            <th>Gaji Pokok</th>
                                            <th>Bonus</th>
                                            <th>Kehadiran</th>
                                            <th>Total Gaji</th>
                                            <th>Absensi</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payrolls as $payroll)
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong>{{ $payroll['pegawai']['nama_lengkap'] ?? 'N/A' }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $payroll['pegawai']['NIP'] ?? 'N/A' }}</small>
                                                    </div>
                                                </td>
                                                <td>{{ $payroll['pegawai']['posisi']['nama_posisi'] ?? 'N/A' }}</td>
                                                <td>
                                                    {{ DateTime::createFromFormat('!m', $payroll['periode_bulan'] ?? 1)->format('M') }} 
                                                    {{ $payroll['periode_tahun'] ?? date('Y') }}
                                                </td>
                                                <td>Rp {{ number_format($payroll['gaji_pokok'] ?? 0, 0, ',', '.') }}</td>
                                                <td>Rp {{ number_format($payroll['gaji_bonus'] ?? 0, 0, ',', '.') }}</td>
                                                <td>Rp {{ number_format($payroll['gaji_kehadiran'] ?? 0, 0, ',', '.') }}</td>
                                                <td>
                                                    <strong class="text-success">
                                                        Rp {{ number_format($payroll['gaji_total'] ?? 0, 0, ',', '.') }}
                                                    </strong>
                                                </td>
                                                <td>
                                                    <span class="attendance-badge 
                                                        @php
                                                            $persentase = $payroll['persentase_kehadiran'] ?? 0;
                                                        @endphp
                                                        @if($persentase >= 95) attendance-excellent
                                                        @elseif($persentase >= 85) attendance-good
                                                        @elseif($persentase >= 75) attendance-average
                                                        @else attendance-poor
                                                        @endif">
                                                        {{ $payroll['persentase_kehadiran'] ?? 0 }}%
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">{{ $payroll['jumlah_absensi'] ?? 0 }}/{{ $payroll['total_hari_kerja'] ?? 0 }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge-status {{ ($payroll['status'] ?? '') == 'Terbayar' ? 'badge-paid' : 'badge-pending' }}">
                                                        {{ $payroll['status'] ?? 'Belum Terbayar' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-1">
                                                        <a href="{{ route('payroll.show', $payroll['id_gaji']) }}" 
                                                           class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        @if(is_admin_or_hrd() || (isset($payroll['pegawai']['user']['id_user']) && $payroll['pegawai']['user']['id_user'] == session('user_id')))
                                                            <button type="button" class="btn btn-success btn-sm" 
                                                                    onclick="downloadSlipGaji('{{ $payroll['id_gaji'] }}', '{{ $payroll['pegawai']['nama_lengkap'] ?? 'N/A' }}')"
                                                                    title="Download Slip Gaji">
                                                                <i class="fas fa-download"></i>
                                                            </button>
                                                        @endif
                                                        @if(is_admin_or_hrd())
                                                            <a href="{{ route('payroll.edit', $payroll['id_gaji']) }}" 
                                                               class="btn btn-warning btn-sm">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <form action="{{ route('payroll.destroy', $payroll['id_gaji']) }}" 
                                                                  method="POST" class="d-inline"
                                                                  onsubmit="return confirm('Yakin ingin menghapus data gaji ini?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pagination -->
                        @if(isset($payrolls->paginationData) && $payrolls->paginationData['has_pages'])
                            <div class="custom-pagination">
                                <nav aria-label="Pagination">
                                    <ul class="pagination mb-0">
                                        {{-- Previous Page Link --}}
                                        @if ($payrolls->paginationData['on_first_page'])
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    <i class="fas fa-chevron-left"></i> Sebelumnya
                                                </span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $payrolls->paginationData['current_page'] - 1]) }}">
                                                    <i class="fas fa-chevron-left"></i> Sebelumnya
                                                </a>
                                            </li>
                                        @endif

                                        {{-- Pagination Elements --}}
                                        @for ($page = 1; $page <= $payrolls->paginationData['last_page']; $page++)
                                            @if ($page == $payrolls->paginationData['current_page'])
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $page }}</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $page]) }}">{{ $page }}</a>
                                                </li>
                                            @endif
                                        @endfor

                                        {{-- Next Page Link --}}
                                        @if ($payrolls->paginationData['has_more_pages'])
                                            <li class="page-item">
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $payrolls->paginationData['current_page'] + 1]) }}">
                                                    Selanjutnya <i class="fas fa-chevron-right"></i>
                                                </a>
                                            </li>
                                        @else
                                            <li class="page-item disabled">
                                                <span class="page-link">
                                                    Selanjutnya <i class="fas fa-chevron-right"></i>
                                                </span>
                                            </li>
                                        @endif
                                    </ul>
                                </nav>

                                <!-- Pagination Info -->
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="text-muted">
                                        Menampilkan {{ $payrolls->paginationData['from'] }} sampai {{ $payrolls->paginationData['to'] }} 
                                        dari {{ $payrolls->paginationData['total'] }} data
                                    </div>
                                    <div class="text-muted">
                                        Halaman {{ $payrolls->paginationData['current_page'] }} dari {{ $payrolls->paginationData['last_page'] }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <h4>
                                @if(is_admin_or_hrd())
                                    Belum Ada Data Gaji
                                @else
                                    Belum Ada Data Gaji Anda
                                @endif
                            </h4>
                            <p class="lead">
                                @if(request()->hasAny(['search', 'bulan', 'tahun', 'pegawai_id', 'status']))
                                    Tidak ada data gaji yang sesuai dengan filter yang diterapkan.
                                @elseif(is_admin_or_hrd())
                                    Belum ada data gaji yang tersedia.
                                @else
                                    Belum ada data gaji untuk Anda. Silakan hubungi HRD untuk informasi lebih lanjut.
                                @endif
                            </p>
                            @if(is_admin_or_hrd())
                                <button class="btn btn-primary btn-modern" onclick="generateSalary()">
                                    <i class="fas fa-calculator me-2"></i>Generate Gaji Pertama
                                </button>
                            @else
                                <div class="mt-3">
                                    <small class="text-muted">
                                        Jika Anda merasa ini adalah kesalahan, silakan:
                                        <br>1. Logout dan login kembali
                                        <br>2. Hubungi administrator sistem
                                    </small>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Salary Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-calculator me-2"></i>Generate Gaji Massal
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="generateForm">
                    <div class="mb-3">
                        <label for="generate_bulan" class="form-label">Bulan</label>
                        <select class="form-select" id="generate_bulan" name="periode_bulan" required>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}" {{ $i == date('n') ? 'selected' : '' }}>
                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="generate_tahun" class="form-label">Tahun</label>
                        <select class="form-select" id="generate_tahun" name="periode_tahun" required>
                            @for($year = date('Y'); $year >= 2020; $year--)
                                <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Fitur ini akan menghitung gaji otomatis berdasarkan:
                        <ul class="mb-0 mt-2">
                            <li>Gaji pokok dari posisi pegawai</li>
                            <li>Bonus berdasarkan treatment yang ditangani</li>
                            <li>Gaji kehadiran: Rp 100.000 × jumlah hari hadir</li>
                        </ul>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="submitGenerate()">
                    <i class="fas fa-calculator me-1"></i> Generate Gaji
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// View Toggle
document.getElementById('cardViewBtn').addEventListener('click', function() {
    document.getElementById('cardView').style.display = 'block';
    document.getElementById('tableView').style.display = 'none';
    
    this.classList.add('active');
    document.getElementById('tableViewBtn').classList.remove('active');
    
    localStorage.setItem('payrollView', 'card');
});

document.getElementById('tableViewBtn').addEventListener('click', function() {
    document.getElementById('cardView').style.display = 'none';
    document.getElementById('tableView').style.display = 'block';
    
    this.classList.add('active');
    document.getElementById('cardViewBtn').classList.remove('active');
    
    localStorage.setItem('payrollView', 'table');
});

// Generate Salary Functions
function showGenerateModal() {
    const modal = new bootstrap.Modal(document.getElementById('generateModal'));
    modal.show();
}

function generateSalary() {
    // Generate for current month
    const currentDate = new Date();
    submitGenerateAPI(currentDate.getMonth() + 1, currentDate.getFullYear());
}

// Initialize Bootstrap components and debug dropdown
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded - initializing components...');
    
    // Check if Bootstrap is loaded
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap JavaScript tidak dimuat!');
    } else {
        console.log('Bootstrap JavaScript berhasil dimuat');
    }
    
    // Initialize dropdown manually if needed
    const dropdownElements = document.querySelectorAll('.dropdown-toggle');
    dropdownElements.forEach(function(element) {
        try {
            new bootstrap.Dropdown(element);
            console.log('Dropdown initialized for:', element);
        } catch (error) {
            console.error('Error initializing dropdown:', error);
        }
    });
    
    // Add click event listeners as fallback
    const masterGajiButton = document.getElementById('masterGajiDropdown');
    if (masterGajiButton) {
        console.log('Master Gaji button found');
        
        // Manual toggle for debugging
        masterGajiButton.addEventListener('click', function(e) {
            console.log('Master Gaji button clicked');
            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                console.log('Dropdown menu found, toggling...');
                dropdownMenu.classList.toggle('show');
            }
        });
    } else {
        console.log('Master Gaji button not found');
    }
    
    // Load saved view preference
    const savedView = localStorage.getItem('payrollView');
    if (savedView === 'table') {
        document.getElementById('tableViewBtn').click();
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('.dropdown-menu.show');
        dropdowns.forEach(function(dropdown) {
            const toggle = dropdown.previousElementSibling;
            if (!toggle.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
    });
});

function generateSalary() {
    // Generate for current month
    const currentDate = new Date();
    submitGenerateAPI(currentDate.getMonth() + 1, currentDate.getFullYear());
}

function submitGenerate() {
    const bulan = document.getElementById('generate_bulan').value;
    const tahun = document.getElementById('generate_tahun').value;
    
    if (bulan && tahun) {
        submitGenerateAPI(bulan, tahun);
        bootstrap.Modal.getInstance(document.getElementById('generateModal')).hide();
    }
}

function submitGenerateAPI(bulan, tahun) {
    // Show loading
    Swal.fire({
        title: 'Generating...',
        text: 'Sedang memproses generate gaji massal',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch('/api/gaji/generate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + '{{ session("api_token") ?? auth()->user()->createToken("payroll")->plainTextToken }}',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            periode_bulan: parseInt(bulan),
            periode_tahun: parseInt(tahun)
        })
    })
    .then(response => response.json())
    .then(data => {
        Swal.close();
        
        if (data.status === 'sukses') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: data.pesan,
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.pesan || 'Terjadi kesalahan saat generate gaji',
                confirmButtonText: 'OK'
            });
        }
    })
    .catch(error => {
        Swal.close();
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan koneksi. Periksa format request Anda.',
            confirmButtonText: 'OK'
        });
    });
}
</script>

<!-- SweetAlert2 for better notifications -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #4a90e2, #50c878);
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
    border: none !important;
}

.card-footer {
    border-radius: 0 0 12px 12px !important;
    border: none !important;
}
</style>

<script>
// PDF Export function for Payroll
function exportPayrollToPdf() {
    // Get current filters
    const urlParams = new URLSearchParams(window.location.search);
    const filters = {
        periode_bulan: urlParams.get('periode_bulan') || '',
        periode_tahun: urlParams.get('periode_tahun') || '',
        pegawai_id: urlParams.get('pegawai_id') || '',
        status: urlParams.get('status') || '',
        search: urlParams.get('search') || ''
    };
    
    // Build export URL with current filters
    const exportUrl = new URL('{{ route("payroll.export-pdf") }}', window.location.origin);
    Object.keys(filters).forEach(key => {
        if (filters[key]) {
            exportUrl.searchParams.append(key, filters[key]);
        }
    });
    
    // Show loading and then download
    Swal.fire({
        title: 'Menyiapkan Laporan...',
        text: 'Sedang memproses laporan payroll',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Open in new window to download
    window.open(exportUrl.toString(), '_blank');
    
    // Close loading after a short delay
    setTimeout(() => {
        Swal.close();
    }, 2000);
}

// Download slip gaji individual - Updated to use real API data
function downloadSlipGaji(id_gaji, nama_pegawai) {
    // Check if user is authenticated
    @if(!session('api_token') || !session('authenticated'))
        Swal.fire({
            icon: 'error',
            title: 'Sesi Berakhir',
            text: 'Sesi Anda telah berakhir. Silakan login kembali.',
            confirmButtonText: 'Login Kembali'
        }).then(() => {
            window.location.href = '{{ route("login") }}';
        });
        return;
    @endif
    
    // Show loading indicator
    Swal.fire({
        title: 'Menyiapkan Slip Gaji...',
        text: `Sedang memproses slip gaji untuk ${nama_pegawai}`,
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Build URL for individual slip download
    const slipUrl = `{{ route('payroll.export-slip', ':id') }}`.replace(':id', id_gaji);
    
    // Use window.open for PDF download - this works better with Laravel auth
    const downloadWindow = window.open(slipUrl, '_blank');
    
    // Check if window was blocked
    if (!downloadWindow || downloadWindow.closed || typeof downloadWindow.closed == 'undefined') {
        Swal.close();
        Swal.fire({
            icon: 'warning',
            title: 'Pop-up Diblokir',
            text: 'Browser memblokir pop-up. Silakan allow pop-up untuk domain ini atau coba lagi.',
            confirmButtonText: 'OK'
        });
        return;
    }
    
    // Success handler - close loading after delay
    setTimeout(() => {
        Swal.close();
        Swal.fire({
            icon: 'success',
            title: 'Download Dimulai',
            text: `Slip gaji ${nama_pegawai} sedang didownload`,
            timer: 2000,
            showConfirmButton: false
        });
    }, 2000);
    
    // Optional: Close the download window after a delay
    setTimeout(() => {
        if (downloadWindow && !downloadWindow.closed) {
            downloadWindow.close();
        }
    }, 5000);
}

// Download slip gaji untuk pegawai sendiri (khusus non-admin/hrd)
function downloadSlipGajiSaya() {
    @if(!is_admin_or_hrd())
        // Ambil data gaji terbaru dari list yang ada
        const payrollCards = document.querySelectorAll('[onclick*="downloadSlipGaji"]');
        
        if (payrollCards.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Tidak Ada Data',
                text: 'Belum ada data gaji yang tersedia untuk Anda.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Jika hanya ada satu slip gaji, download langsung
        if (payrollCards.length === 1) {
            const onclickAttr = payrollCards[0].getAttribute('onclick');
            const matches = onclickAttr.match(/downloadSlipGaji\('([^']+)',\s*'([^']+)'/);
            if (matches) {
                downloadSlipGaji(matches[1], matches[2]);
            }
            return;
        }
        
        // Jika ada beberapa slip gaji, tampilkan pilihan
        let options = '';
        payrollCards.forEach((card, index) => {
            const onclickAttr = card.getAttribute('onclick');
            const matches = onclickAttr.match(/downloadSlipGaji\('([^']+)',\s*'([^']+)'/);
            if (matches) {
                // Cari periode dari card parent
                const cardElement = card.closest('.payroll-card');
                let periode = 'Periode tidak diketahui';
                if (cardElement) {
                    const periodeText = cardElement.querySelector('.card-header small');
                    if (periodeText) {
                        periode = periodeText.textContent.trim();
                    }
                }
                options += `<option value="${matches[1]}" data-nama="${matches[2]}">${periode}</option>`;
            }
        });
        
        if (options) {
            Swal.fire({
                title: 'Pilih Periode Gaji',
                html: `
                    <select id="selectPeriode" class="form-select">
                        <option value="">Pilih periode gaji...</option>
                        ${options}
                    </select>
                `,
                showCancelButton: true,
                confirmButtonText: 'Download',
                cancelButtonText: 'Batal',
                preConfirm: () => {
                    const selectElement = document.getElementById('selectPeriode');
                    const selectedValue = selectElement.value;
                    const selectedOption = selectElement.options[selectElement.selectedIndex];
                    
                    if (!selectedValue) {
                        Swal.showValidationMessage('Silakan pilih periode gaji');
                        return false;
                    }
                    
                    return {
                        id: selectedValue,
                        nama: selectedOption.getAttribute('data-nama')
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    downloadSlipGaji(result.value.id, result.value.nama);
                }
            });
        }
    @else
        // Untuk admin/hrd, download laporan lengkap
        exportPayrollToPdf();
    @endif
}

// Function untuk menampilkan modal update gaji pokok pegawai individual
function showMasterGajiPegawaiModal() {
    // Check authentication first
    @if(!session('api_token') || !session('authenticated'))
        Swal.fire({
            icon: 'error',
            title: 'Sesi Berakhir',
            text: 'Sesi Anda telah berakhir. Silakan login kembali untuk mengakses data pegawai.',
            confirmButtonText: 'Login Kembali'
        }).then(() => {
            window.location.href = '{{ route("login") }}';
        });
        return;
    @endif
    
    // Show loading indicator
    Swal.fire({
        title: 'Memuat Data Pegawai...',
        text: 'Sedang mengambil daftar pegawai dari server',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Ambil daftar pegawai dari API klinik (port 8002) dengan authentication
    fetch('http://localhost:8002/api/master-gaji', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer {{ session("api_token") }}',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        Swal.close(); // Close loading
        
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('Token tidak valid. Silakan login kembali.');
            } else if (response.status === 403) {
                throw new Error('Anda tidak memiliki akses untuk melihat data pegawai.');
            } else if (response.status === 404) {
                throw new Error('Endpoint API master gaji tidak ditemukan.');
            } else {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
        }
        return response.json();
    })
    .then(data => {
        console.log('Master Gaji API Response:', data); // Debug log
        
        let pegawaiOptions = '<option value="">Pilih Pegawai...</option>';
        
        // Handle different possible response structures
        let pegawaiList = [];
        if (data.status === 'success' || data.success) {
            // Try different data paths
            pegawaiList = data.data?.data || data.data || data.pegawai || [];
        } else if (Array.isArray(data)) {
            // Direct array response
            pegawaiList = data;
        } else if (data.pegawai && Array.isArray(data.pegawai)) {
            pegawaiList = data.pegawai;
        }
        
        console.log('Pegawai List:', pegawaiList); // Debug log
        
        if (pegawaiList.length > 0) {
            pegawaiList.forEach(pegawai => {
                const nama = pegawai.nama_lengkap || pegawai.nama || 'Nama Tidak Tersedia';
                const nip = pegawai.NIP || pegawai.nip || 'Tanpa NIP';
                const posisi = (pegawai.posisi && pegawai.posisi.nama_posisi) || 'Tanpa Posisi';
                const gajiPokok = pegawai.gaji_pokok_tambahan > 0 ? ` (Custom: Rp ${parseInt(pegawai.gaji_pokok_tambahan).toLocaleString('id-ID')})` : '';
                pegawaiOptions += `<option value="${pegawai.id_pegawai || pegawai.id}" data-current-gaji="${pegawai.gaji_pokok_tambahan || 0}">${nama} - ${posisi} (${nip})${gajiPokok}</option>`;
            });
        } else {
            pegawaiOptions += '<option value="" disabled>Tidak ada data pegawai tersedia</option>';
        }
        
        Swal.fire({
            title: 'Update Gaji Pokok Pegawai Individual',
            html: `
                <form id="masterGajiPegawaiForm" class="text-start">
                    <div class="mb-3">
                        <label for="id_pegawai_individual" class="form-label">Pegawai <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_pegawai_individual" name="id_pegawai" required onchange="showCurrentGajiPegawai(this)">
                            ${pegawaiOptions}
                        </select>
                    </div>
                    <div id="currentGajiInfo" class="alert alert-info" style="display: none;">
                        <strong>Gaji Pokok Saat Ini:</strong> <span id="currentGajiAmount">-</span>
                    </div>
                    <div class="mb-3">
                        <label for="gaji_pokok_tambahan_individual" class="form-label">Gaji Pokok Custom <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="gaji_pokok_tambahan_individual" name="gaji_pokok_tambahan" 
                               placeholder="Masukkan gaji pokok khusus untuk pegawai ini" min="0" step="10000" required>
                        <small class="form-text text-muted">
                            Gaji ini akan menggantikan gaji default dari posisi untuk pegawai yang dipilih saja.
                            <br>Masukkan 0 untuk menggunakan gaji default posisi.
                        </small>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Catatan Penting:</strong> 
                        <ul class="mb-0 mt-2">
                            <li>Gaji ini hanya berlaku untuk pegawai yang dipilih</li>
                            <li>Jika diisi 0, akan menggunakan gaji default dari posisi</li>
                            <li>Tidak mempengaruhi pegawai lain di posisi yang sama</li>
                        </ul>
                    </div>
                </form>
            `,
            width: '600px',
            showCancelButton: true,
            confirmButtonText: 'Update Gaji Pegawai',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745',
            preConfirm: () => {
                const form = document.getElementById('masterGajiPegawaiForm');
                const formData = new FormData(form);
                
                // Validasi
                if (!formData.get('id_pegawai')) {
                    Swal.showValidationMessage('Mohon pilih pegawai');
                    return false;
                }
                
                return {
                    id_pegawai: parseInt(formData.get('id_pegawai')),
                    gaji_pokok_tambahan: parseFloat(formData.get('gaji_pokok_tambahan')) || 0
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                submitMasterGajiPegawai(result.value);
            }
        });
    })
    .catch(error => {
        Swal.close(); // Close any loading dialogs
        console.error('Error fetching master gaji data:', error);
        
        let errorMessage = 'Gagal mengambil data master gaji pegawai.';
        
        if (error.message.includes('Token tidak valid')) {
            errorMessage = 'Sesi Anda telah berakhir. Silakan login kembali.';
            Swal.fire({
                title: 'Sesi Berakhir',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Login Kembali'
            }).then(() => {
                window.location.href = '{{ route("login") }}';
            });
        } else if (error.message.includes('tidak memiliki akses')) {
            errorMessage = 'Anda tidak memiliki akses untuk melihat data pegawai. Hubungi administrator.';
            Swal.fire({
                title: 'Akses Ditolak',
                text: errorMessage,
                icon: 'warning',
                confirmButtonColor: '#ffc107'
            });
        } else if (error.message.includes('tidak ditemukan')) {
            errorMessage = 'API master gaji tidak ditemukan. Pastikan server API klinik (port 8002) sudah berjalan.';
            Swal.fire({
                title: 'API Tidak Ditemukan',
                html: `${errorMessage}<br><br><small class="text-muted">Endpoint: <code>http://localhost:8002/api/master-gaji</code></small>`,
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        } else {
            errorMessage = `Terjadi kesalahan: ${error.message}`;
            Swal.fire({
                title: 'Error',
                html: `${errorMessage}<br><br><small class="text-muted">Pastikan:<br>1. Server API (port 8002) berjalan<br>2. Token masih valid<br>3. Koneksi internet stabil</small>`,
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        }
    });
}

// Function untuk menampilkan gaji saat ini ketika pegawai dipilih
function showCurrentGajiPegawai(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const currentGaji = selectedOption.getAttribute('data-current-gaji') || 0;
    
    const currentGajiInfo = document.getElementById('currentGajiInfo');
    const currentGajiAmount = document.getElementById('currentGajiAmount');
    const inputGaji = document.getElementById('gaji_pokok_tambahan_individual');
    
    if (selectElement.value) {
        currentGajiAmount.textContent = currentGaji > 0 ? `Rp ${parseInt(currentGaji).toLocaleString('id-ID')} (Custom)` : 'Menggunakan gaji default posisi';
        currentGajiInfo.style.display = 'block';
        inputGaji.value = currentGaji;
    } else {
        currentGajiInfo.style.display = 'none';
        inputGaji.value = '';
    }
}

// Function untuk submit update gaji pegawai individual
function submitMasterGajiPegawai(data) {
    const pegawaiId = data.id_pegawai;
    
    Swal.fire({
        title: 'Mengupdate Gaji Pegawai...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Use port 8002 untuk API klinik - endpoint khusus gaji pegawai individual dengan authentication
    fetch(`http://localhost:8002/api/master-gaji/${pegawaiId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer {{ session("api_token") }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            gaji_pokok_tambahan: data.gaji_pokok_tambahan
        })
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('Token tidak valid. Silakan login kembali.');
            } else if (response.status === 403) {
                throw new Error('Anda tidak memiliki akses untuk mengupdate data pegawai.');
            } else if (response.status === 404) {
                throw new Error('Endpoint API atau pegawai tidak ditemukan.');
            } else {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
        }
        return response.json();
    })
    .then(result => {
        if (result.status === 'success' || result.success) {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Gaji pokok pegawai berhasil diupdate',
                icon: 'success',
                confirmButtonColor: '#28a745'
            }).then(() => {
                // Reload halaman untuk menampilkan data terbaru
                window.location.reload();
            });
        } else {
            throw new Error(result.message || result.pesan || 'Gagal mengupdate gaji pegawai');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        let errorMessage = error.message || 'Terjadi kesalahan saat mengupdate gaji pegawai';
        
        if (error.message.includes('Token tidak valid')) {
            Swal.fire({
                title: 'Sesi Berakhir',
                text: 'Sesi Anda telah berakhir. Silakan login kembali.',
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Login Kembali'
            }).then(() => {
                window.location.href = '{{ route("login") }}';
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        }
    });
}

// Function untuk menampilkan modal master gaji per posisi  
function showMasterGajiPosisiModal() {
    // Check authentication first
    @if(!session('api_token') || !session('authenticated'))
        Swal.fire({
            icon: 'error',
            title: 'Sesi Berakhir',
            text: 'Sesi Anda telah berakhir. Silakan login kembali untuk mengakses data posisi.',
            confirmButtonText: 'Login Kembali'
        }).then(() => {
            window.location.href = '{{ route("login") }}';
        });
        return;
    @endif
    
    // Show loading indicator
    Swal.fire({
        title: 'Memuat Data Posisi...',
        text: 'Sedang mengambil daftar posisi dari server',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Ambil daftar posisi dari API klinik (port 8002) dengan authentication
    fetch('http://localhost:8002/api/posisi', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer {{ session("api_token") }}',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        Swal.close(); // Close loading
        
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('Token tidak valid. Silakan login kembali.');
            } else if (response.status === 403) {
                throw new Error('Anda tidak memiliki akses untuk melihat data posisi.');
            } else if (response.status === 404) {
                throw new Error('Endpoint API posisi tidak ditemukan.');
            } else {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
        }
        return response.json();
    })
    .then(data => {
        console.log('API Response:', data); // Debug log
        
        let posisiOptions = '<option value="">Pilih Posisi...</option>';
        
        // Handle different possible response structures
        let posisiList = [];
        if (data.status === 'success' || data.success) {
            // Try different data paths
            posisiList = data.data?.data || data.data || data.posisi || [];
        } else if (Array.isArray(data)) {
            // Direct array response
            posisiList = data;
        } else if (data.posisi && Array.isArray(data.posisi)) {
            posisiList = data.posisi;
        }
        
        console.log('Posisi List:', posisiList); // Debug log
        
        if (posisiList.length > 0) {
            posisiList.forEach(posisi => {
                const nama = posisi.nama_posisi || posisi.posisi || 'Posisi Tidak Tersedia';
                const gajiPokok = posisi.gaji_pokok ? `(Gaji: Rp ${parseInt(posisi.gaji_pokok).toLocaleString('id-ID')})` : '(Belum ada gaji)';
                const bonus = posisi.persen_bonus ? `${posisi.persen_bonus}%` : '0%';
                const absensi = posisi.gaji_absensi ? `Rp ${parseInt(posisi.gaji_absensi).toLocaleString('id-ID')}` : 'Rp 0';
                
                posisiOptions += `<option value="${posisi.id_posisi || posisi.id}" 
                    data-gaji-pokok="${posisi.gaji_pokok || 0}" 
                    data-persen-bonus="${posisi.persen_bonus || 0}" 
                    data-gaji-absensi="${posisi.gaji_absensi || 0}">
                    ${nama} ${gajiPokok} | Bonus: ${bonus} | Absensi: ${absensi}
                </option>`;
            });
        } else {
            posisiOptions += '<option value="" disabled>Tidak ada data posisi tersedia</option>';
        }
        
        Swal.fire({
            title: 'Update Master Gaji Per Posisi',
            html: `
                <form id="masterGajiPosisiForm" class="text-start">
                    <div class="mb-3">
                        <label for="id_posisi" class="form-label">Posisi <span class="text-danger">*</span></label>
                        <select class="form-select" id="id_posisi" name="id_posisi" required onchange="showCurrentGajiPosisi(this)">
                            ${posisiOptions}
                        </select>
                    </div>
                    
                    <div id="currentPosisiInfo" class="alert alert-info" style="display: none;">
                        <strong>Master Gaji Saat Ini:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Gaji Pokok: <span id="currentGajiPokokPosisi">-</span></li>
                            <li>Persentase Bonus: <span id="currentPersenBonusPosisi">-</span></li>
                            <li>Gaji Kehadiran: <span id="currentGajiAbsensiPosisi">-</span></li>
                        </ul>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label for="gaji_pokok_posisi" class="form-label">Gaji Pokok Default Posisi <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="gaji_pokok_posisi" name="gaji_pokok" 
                                       placeholder="0" min="0" step="10000" required>
                                <small class="form-text text-muted">Gaji dasar untuk semua pegawai di posisi ini</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="persen_bonus_posisi" class="form-label">Persentase Bonus (%)</label>
                                <input type="number" class="form-control" id="persen_bonus_posisi" name="persen_bonus" 
                                       placeholder="0" min="0" max="100" step="0.1" value="0">
                                <small class="form-text text-muted">Contoh: 5.5 untuk 5.5%</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="gaji_absensi_posisi" class="form-label">Gaji per Kehadiran</label>
                                <input type="number" class="form-control" id="gaji_absensi_posisi" name="gaji_absensi" 
                                       placeholder="0" min="0" step="1000" value="0">
                                <small class="form-text text-muted">Gaji yang diterima per hari hadir</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-success">
                        <i class="fas fa-info-circle"></i>
                        <strong>Master Gaji Per Posisi:</strong> 
                        <ul class="mb-0 mt-2">
                            <li><strong>Gaji Pokok:</strong> Berlaku untuk semua pegawai di posisi ini (kecuali yang punya custom)</li>
                            <li><strong>Persentase Bonus & Gaji Kehadiran:</strong> Berlaku untuk semua pegawai di posisi ini</li>
                        </ul>
                    </div>
                </form>
            `,
            width: '700px',
            showCancelButton: true,
            confirmButtonText: 'Update Master Posisi',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#17a2b8',
            preConfirm: () => {
                const form = document.getElementById('masterGajiPosisiForm');
                const formData = new FormData(form);
                
                // Validasi
                if (!formData.get('id_posisi')) {
                    Swal.showValidationMessage('Mohon pilih posisi');
                    return false;
                }
                
                if (!formData.get('gaji_pokok') || parseFloat(formData.get('gaji_pokok')) <= 0) {
                    Swal.showValidationMessage('Gaji pokok harus diisi dan lebih dari 0');
                    return false;
                }
                
                return {
                    id_posisi: parseInt(formData.get('id_posisi')),
                    gaji_pokok: parseFloat(formData.get('gaji_pokok')),
                    persen_bonus: parseFloat(formData.get('persen_bonus')) || 0,
                    gaji_absensi: parseFloat(formData.get('gaji_absensi')) || 0
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                submitMasterGajiPosisi(result.value);
            }
        });
    })
    .catch(error => {
        Swal.close(); // Close any loading dialogs
        console.error('Error fetching posisi data:', error);
        
        let errorMessage = 'Gagal mengambil data posisi.';
        
        if (error.message.includes('Token tidak valid')) {
            errorMessage = 'Sesi Anda telah berakhir. Silakan login kembali.';
            Swal.fire({
                title: 'Sesi Berakhir',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Login Kembali'
            }).then(() => {
                window.location.href = '{{ route("login") }}';
            });
        } else if (error.message.includes('tidak memiliki akses')) {
            errorMessage = 'Anda tidak memiliki akses untuk melihat data posisi. Hubungi administrator.';
            Swal.fire({
                title: 'Akses Ditolak',
                text: errorMessage,
                icon: 'warning',
                confirmButtonColor: '#ffc107'
            });
        } else if (error.message.includes('tidak ditemukan')) {
            errorMessage = 'API posisi tidak ditemukan. Pastikan server API klinik (port 8002) sudah berjalan.';
            Swal.fire({
                title: 'API Tidak Ditemukan',
                html: `${errorMessage}<br><br><small class="text-muted">Endpoint: <code>http://localhost:8002/api/posisi</code></small>`,
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        } else {
            errorMessage = `Terjadi kesalahan: ${error.message}`;
            Swal.fire({
                title: 'Error',
                html: `${errorMessage}<br><br><small class="text-muted">Pastikan:<br>1. Server API (port 8002) berjalan<br>2. Token masih valid<br>3. Koneksi internet stabil</small>`,
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        }
    });
}

// Function untuk menampilkan gaji saat ini ketika posisi dipilih
function showCurrentGajiPosisi(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const currentGajiPokok = selectedOption.getAttribute('data-gaji-pokok') || 0;
    const currentPersenBonus = selectedOption.getAttribute('data-persen-bonus') || 0;
    const currentGajiAbsensi = selectedOption.getAttribute('data-gaji-absensi') || 0;
    
    const currentPosisiInfo = document.getElementById('currentPosisiInfo');
    const currentGajiPokokPosisi = document.getElementById('currentGajiPokokPosisi');
    const currentPersenBonusPosisi = document.getElementById('currentPersenBonusPosisi');
    const currentGajiAbsensiPosisi = document.getElementById('currentGajiAbsensiPosisi');
    
    const inputGajiPokok = document.getElementById('gaji_pokok_posisi');
    const inputPersenBonus = document.getElementById('persen_bonus_posisi');
    const inputGajiAbsensi = document.getElementById('gaji_absensi_posisi');
    
    if (selectElement.value) {
        currentGajiPokokPosisi.textContent = currentGajiPokok > 0 ? `Rp ${parseInt(currentGajiPokok).toLocaleString('id-ID')}` : 'Belum diset';
        currentPersenBonusPosisi.textContent = currentPersenBonus > 0 ? `${currentPersenBonus}%` : '0%';
        currentGajiAbsensiPosisi.textContent = currentGajiAbsensi > 0 ? `Rp ${parseInt(currentGajiAbsensi).toLocaleString('id-ID')}` : 'Rp 0';
        currentPosisiInfo.style.display = 'block';
        
        // Set current values to input fields
        inputGajiPokok.value = currentGajiPokok;
        inputPersenBonus.value = currentPersenBonus;
        inputGajiAbsensi.value = currentGajiAbsensi;
    } else {
        currentPosisiInfo.style.display = 'none';
        inputGajiPokok.value = '';
        inputPersenBonus.value = '0';
        inputGajiAbsensi.value = '0';
    }
}

// Function untuk submit update master gaji per posisi
function submitMasterGajiPosisi(data) {
    const posisiId = data.id_posisi;
    
    Swal.fire({
        title: 'Mengupdate Master Gaji Posisi...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Use port 8002 untuk API klinik - endpoint master gaji per posisi dengan authentication
    fetch(`http://localhost:8002/api/posisi/${posisiId}/master-gaji`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer {{ session("api_token") }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            gaji_pokok: data.gaji_pokok,
            persen_bonus: data.persen_bonus,
            gaji_absensi: data.gaji_absensi
        })
    })
    .then(response => {
        if (!response.ok) {
            if (response.status === 401) {
                throw new Error('Token tidak valid. Silakan login kembali.');
            } else if (response.status === 403) {
                throw new Error('Anda tidak memiliki akses untuk mengupdate data posisi.');
            } else if (response.status === 404) {
                throw new Error('Endpoint API atau posisi tidak ditemukan.');
            } else {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
        }
        return response.json();
    })
    .then(result => {
        if (result.status === 'success' || result.success) {
            Swal.fire({
                title: 'Berhasil!',
                text: 'Master gaji per posisi berhasil diupdate',
                icon: 'success',
                confirmButtonColor: '#28a745'
            }).then(() => {
                // Reload halaman untuk menampilkan data terbaru
                window.location.reload();
            });
        } else {
            throw new Error(result.message || result.pesan || 'Gagal mengupdate master gaji posisi');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        let errorMessage = error.message || 'Terjadi kesalahan saat mengupdate master gaji posisi';
        
        if (error.message.includes('Token tidak valid')) {
            Swal.fire({
                title: 'Sesi Berakhir',
                text: 'Sesi Anda telah berakhir. Silakan login kembali.',
                icon: 'error',
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Login Kembali'
            }).then(() => {
                window.location.href = '{{ route("login") }}';
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        }
    });
}
</script>

<!-- 
    Modal Update Master Gaji Pegawai 
    Modal ini menggunakan SweetAlert2 yang dikelola oleh JavaScript function showTambahGajiModal()
    yang mengakses endpoint PUT /api/master-gaji/{id} pada port 8002
-->

@endsection
