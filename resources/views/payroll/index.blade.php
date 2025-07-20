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
                            <!-- @if(is_admin() || is_hrd())
                                <button class="btn btn-warning btn-modern" onclick="generateSalary()">
                                    <i class="fas fa-calculator me-1"></i> Generate Gaji
                                </button>
                                <button class="btn btn-outline-info btn-modern" onclick="showGenerateModal()">
                                    <i class="fas fa-cog me-1"></i> Generate Custom
                                </button>
                            @endif -->
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

// Load saved view preference
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('payrollView');
    if (savedView === 'table') {
        document.getElementById('tableViewBtn').click();
    }
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

// Download slip gaji individual
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
    
    // Download file
    fetch(slipUrl, {
        method: 'GET',
        headers: {
            'Accept': 'application/pdf',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        Swal.close();
        
        if (response.ok) {
            // Create download link
            return response.blob().then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = `slip_gaji_${nama_pegawai.replace(/\s+/g, '_')}_${new Date().getTime()}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: `Slip gaji ${nama_pegawai} berhasil didownload`,
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        } else if (response.status === 401) {
            Swal.fire({
                icon: 'error',
                title: 'Sesi Berakhir',
                text: 'Sesi Anda telah berakhir. Silakan login kembali.',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = '{{ route("login") }}';
            });
        } else {
            throw new Error('Gagal mengunduh slip gaji');
        }
    })
    .catch(error => {
        Swal.close();
        console.error('Error downloading slip:', error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal Download',
            text: `Gagal mengunduh slip gaji ${nama_pegawai}. Silakan coba lagi.`,
            confirmButtonText: 'OK'
        });
    });
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
</script>
@endsection
