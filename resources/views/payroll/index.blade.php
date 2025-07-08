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
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="glass-card fade-in">
                <div class="glass-header p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1 text-primary">
                                <i class="fas fa-money-bill-wave me-2"></i>Manajemen Penggajian
                            </h3>
                            <p class="text-muted mb-0">Kelola data gaji dan payroll karyawan</p>
                        </div>
                        <!-- <div class="d-flex gap-2">
                            @if(is_admin() || is_hrd())
                                <a href="{{ route('payroll.generate.form') }}" class="btn btn-warning btn-modern">
                                    <i class="fas fa-calculator me-1"></i> Generate Payroll
                                </a>
                                <a href="{{ route('payroll.create') }}" class="btn btn-primary btn-modern">
                                    <i class="fas fa-plus me-1"></i> Tambah Gaji
                                </a>
                            @endif
                        </div> -->
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
                        </div>
                    @endif

                    <!-- Filter Section -->
                    <div class="section-card p-4 mb-4">
                        <h5 class="mb-3">
                            <i class="fas fa-filter text-primary me-2"></i>Filter Data
                        </h5>
                        <form method="GET" action="{{ route('payroll.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Cari</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" placeholder="Nama pegawai...">
                            </div>
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
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
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
                                <a href="{{ route('payroll.index') }}" class="btn btn-secondary btn-modern">
                                    <i class="fas fa-times me-1"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Payroll Data -->
                    @if($payrolls->count() > 0)
                        <div class="row">
                            @foreach($payrolls as $payroll)
                                <div class="col-lg-6 col-xl-4 mb-4">
                                    <div class="payroll-card">
                                        <div class="card-header p-2 bg-gradient-primary text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0 fw-bold">
                                                        {{ $payroll['pegawai']['nama_lengkap'] ?? 'Nama tidak tersedia' }}
                                                    </h6>
                                                    <small class="opacity-75">
                                                        {{ $payroll['periode_bulan'] ?? 'N/A' }}/{{ $payroll['periode_tahun'] ?? 'N/A' }}
                                                    </small>
                                                </div>
                                                <span class="badge-status {{ $payroll['status'] == 'Terbayar' ? 'badge-paid' : 'badge-pending' }}">
                                                    @if($payroll['status'] == 'Terbayar')
                                                        <i class="fas fa-check-circle"></i> Terbayar
                                                    @else
                                                        <i class="fas fa-clock"></i> Belum Terbayar
                                                    @endif
                                                </span>
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
                                                            +Rp {{ number_format($payroll['gaji_kehadiran'], 0, ',', '.') }}
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                @if(($payroll['gaji_bonus'] ?? 0) > 0)
                                                    <div class="col-6">
                                                        <small class="text-muted">Bonus</small>
                                                        <div class="fw-bold text-success">
                                                            +Rp {{ number_format($payroll['gaji_bonus'], 0, ',', '.') }}
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            @if($payroll['keterangan'] ?? false)
                                                <div class="mb-3">
                                                    <small class="text-muted">Keterangan:</small>
                                                    <p class="mb-0 small">{{ $payroll['keterangan'] }}</p>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="card-footer bg-light p-2">                        <div class="d-flex gap-2">
                            <a href="{{ route('payroll.show', $payroll['id_gaji']) }}" 
                               class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="fas fa-eye me-1"></i> Detail
                            </a>
                            @if(is_admin() || is_hrd())
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
                    @else
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <h4>Belum Ada Data Gaji</h4>
                            <p class="lead">
                                @if(request()->hasAny(['search', 'bulan', 'tahun', 'pegawai_id', 'status']))
                                    Tidak ada data gaji yang sesuai dengan filter yang diterapkan.
                                @else
                                    Belum ada data gaji yang tersedia.
                                @endif
                            </p>
                            @if(is_admin() || is_hrd())
                                <a href="{{ route('payroll.create') }}" class="btn btn-primary btn-modern">
                                    <i class="fas fa-plus me-2"></i>Tambah Data Gaji Pertama
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

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
@endsection
