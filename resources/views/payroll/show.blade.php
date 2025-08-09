@extends('layouts.app')

@section('content')
<style>
/* Glass Morphism Cards */
.glass-card {
    background: rgba(255, 255, 255, 0.25);
    border-radius: 20px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    overflow: hidden;
}

.glass-header {
    background: linear-gradient(135deg, rgba(74, 144, 226, 0.15), rgba(80, 200, 120, 0.15));
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px 20px 0 0;
    position: relative;
}

.glass-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    animation: shimmer 3s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.section-card {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.section-card:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
}

.info-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 16px;
    border: 1px solid rgba(74, 144, 226, 0.2);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: hidden;
    position: relative;
}

.info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(74, 144, 226, 0.2);
    border-color: #4a90e2;
}

.info-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(74, 144, 226, 0.1), transparent);
    transition: left 0.5s;
}

.info-card:hover::before {
    left: 100%;
}

/* Enhanced Info Items */
.info-item {
    background: rgba(255, 255, 255, 0.98);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    border-left: 4px solid #4a90e2;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.info-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(45deg, #4a90e2, #50c878);
    transition: width 0.3s ease;
}

.info-item:hover {
    background: rgba(255, 255, 255, 1);
    transform: translateX(8px) scale(1.02);
    box-shadow: 0 8px 25px rgba(74, 144, 226, 0.15);
}

.info-item:hover::before {
    width: 100%;
    opacity: 0.05;
}

.info-item.success {
    border-left-color: #28a745;
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.03), rgba(255, 255, 255, 0.98));
}

.info-item.success::before {
    background: linear-gradient(45deg, #28a745, #20c997);
}

.info-item.warning {
    border-left-color: #ffc107;
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.03), rgba(255, 255, 255, 0.98));
}

.info-item.warning::before {
    background: linear-gradient(45deg, #ffc107, #fd7e14);
}

.info-item.primary {
    border-left-color: #4a90e2;
    background: linear-gradient(135deg, rgba(74, 144, 226, 0.03), rgba(255, 255, 255, 0.98));
}

.info-item.info {
    border-left-color: #17a2b8;
    background: linear-gradient(135deg, rgba(23, 162, 184, 0.03), rgba(255, 255, 255, 0.98));
}

.info-item.info::before {
    background: linear-gradient(45deg, #17a2b8, #6610f2);
}

/* Enhanced Salary Highlight */
.salary-highlight {
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(32, 201, 151, 0.1));
    border: 2px solid rgba(40, 167, 69, 0.3);
    border-radius: 20px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.salary-highlight::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(40, 167, 69, 0.1) 0%, transparent 70%);
    animation: pulse 4s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 0.7; }
    50% { transform: scale(1.1); opacity: 1; }
}

.salary-amount {
    font-size: 2.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, #28a745, #20c997);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    position: relative;
    z-index: 1;
}

/* Improved Salary Breakdown */
.salary-breakdown {
    background: rgba(255, 255, 255, 0.98);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 0.75rem;
    border-left: 4px solid #28a745;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.salary-breakdown::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(45deg, #28a745, #20c997);
    transition: width 0.3s ease;
}

.salary-breakdown:hover {
    transform: translateX(6px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.15);
}

.salary-breakdown:hover::before {
    width: 100%;
    opacity: 0.05;
}

.attendance-info {
    background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(80, 200, 120, 0.1));
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 1rem;
}

/* Enhanced Modern Buttons */
.btn-modern {
    padding: 0.75rem 2rem;
    border-radius: 12px;
    font-weight: 600;
    border: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.875rem;
}

.btn-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-modern:hover::before {
    left: 100%;
}

.btn-primary.btn-modern {
    background: linear-gradient(135deg, #4a90e2, #50c878);
    color: white;
    box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
}

.btn-primary.btn-modern:hover {
    background: linear-gradient(135deg, #357abd, #45b369);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(74, 144, 226, 0.4);
}

.btn-secondary.btn-modern {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
}

.btn-secondary.btn-modern:hover {
    background: linear-gradient(135deg, #5a6268, #495057);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
}

.btn-success.btn-modern {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-success.btn-modern:hover {
    background: linear-gradient(135deg, #218838, #1abc9c);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
}

.btn-warning.btn-modern {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
}

.btn-warning.btn-modern:hover {
    background: linear-gradient(135deg, #e0a800, #dc6705);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 193, 7, 0.4);
}

.btn-danger.btn-modern {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
}

.btn-danger.btn-modern:hover {
    background: linear-gradient(135deg, #c82333, #bd2130);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
}

.badge-status {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
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
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
    border-radius: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
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

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Enhanced Icon Circles */
.icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-right: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.icon-circle::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 50%;
    background: linear-gradient(45deg, rgba(255, 255, 255, 0.3), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.icon-circle:hover::before {
    opacity: 1;
}

.icon-circle:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.icon-circle.primary {
    background: linear-gradient(135deg, #4a90e2, #50c878);
    color: white;
}

.icon-circle.success {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.icon-circle.warning {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.icon-circle.info {
    background: linear-gradient(135deg, #17a2b8, #6610f2);
    color: white;
}

.stat-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    border: 1px solid rgba(74, 144, 226, 0.2);
    transition: all 0.3s ease;
    text-align: center;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.text-gradient {
    background: linear-gradient(135deg, #4a90e2, #50c878);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.breadcrumb-modern {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 0.5rem 1rem;
    margin-bottom: 1rem;
}

.breadcrumb-modern .breadcrumb {
    margin-bottom: 0;
    background: none;
}

.breadcrumb-modern .breadcrumb-item + .breadcrumb-item::before {
    content: "›";
    color: #6c757d;
}

/* Enhanced Print Styles */
@media print {
    body * {
        visibility: hidden;
    }
    
    .glass-card, .glass-card * {
        visibility: visible;
    }
    
    .glass-card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        background: white !important;
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        backdrop-filter: none !important;
    }
    
    .info-card, .section-card {
        background: white !important;
        box-shadow: none !important;
        backdrop-filter: none !important;
        border: 1px solid #ddd !important;
    }
    
    .btn-modern, .breadcrumb-modern {
        display: none !important;
    }
    
    .salary-highlight {
        border: 2px solid #28a745 !important;
        background: #f0f8f0 !important;
        backdrop-filter: none !important;
    }
    
    .glass-header {
        background: #f8f9fa !important;
        border-bottom: 2px solid #28a745 !important;
        backdrop-filter: none !important;
    }
    
    .info-item {
        border: 1px solid #eee !important;
        background: #f9f9f9 !important;
    }
    
    .salary-breakdown {
        border: 1px solid #ddd !important;
        background: white !important;
    }
    
    body {
        font-size: 12px !important;
        color: black !important;
    }
    
    .container {
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .text-gradient {
        color: #28a745 !important;
        -webkit-text-fill-color: #28a745 !important;
    }
    
    .icon-circle {
        border: 2px solid #28a745 !important;
        background: white !important;
        color: #28a745 !important;
    }
}

/* Enhanced Mobile Responsive */
@media (max-width: 768px) {
    .container {
        padding: 0.5rem;
    }
    
    .glass-card {
        border-radius: 16px;
        margin: 0.5rem 0;
    }
    
    .glass-header {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
        padding: 1.5rem !important;
    }
    
    .salary-highlight {
        text-align: center;
        padding: 1.5rem;
    }
    
    .salary-amount {
        font-size: 2rem;
    }
    
    .salary-breakdown {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .stat-card {
        margin-bottom: 0.5rem;
    }
    
    .d-flex.gap-3.flex-wrap {
        flex-direction: column;
        gap: 0.5rem !important;
    }
    
    .btn-modern {
        width: 100%;
        margin-bottom: 0.5rem;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
    
    .icon-circle {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    .breadcrumb-modern {
        font-size: 0.875rem;
    }
    
    .info-item {
        padding: 1rem;
    }
    
    .section-card {
        margin-bottom: 1rem;
    }
}

/* Animation untuk loading */
.fade-in {
    animation: fadeInUp 0.6s ease-out;
}

.fade-in:nth-child(1) { animation-delay: 0.1s; }
.fade-in:nth-child(2) { animation-delay: 0.2s; }
.fade-in:nth-child(3) { animation-delay: 0.3s; }
.fade-in:nth-child(4) { animation-delay: 0.4s; }

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Hover effects untuk interaktivity */
.info-item:hover .fas {
    transform: scale(1.1);
    transition: transform 0.3s ease;
}

.stat-card:hover .fas {
    transform: rotate(5deg) scale(1.1);
    transition: transform 0.3s ease;
}

/* Loading skeleton untuk data yang belum load */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, transparent 37%, #f0f0f0 63%);
    background-size: 400% 100%;
    animation: skeleton-loading 1.5s ease-in-out infinite;
}

@keyframes skeleton-loading {
    0% { background-position: 100% 50%; }
    100% { background-position: -100% 50%; }
}
</style>
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <!-- Breadcrumb -->
            <div class="breadcrumb-modern fade-in">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('payroll.index') }}" class="text-decoration-none">
                                <i class="fas fa-money-bill-wave me-1"></i>Gaji
                            </a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Detail Gaji</li>
                    </ol>
                </nav>
            </div>

            <div class="glass-card fade-in">
                <div class="glass-header p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle primary me-3">
                                <i class="fas fa-receipt"></i>
                            </div>
                            <div>
                                <h3 class="mb-1 text-gradient fw-bold">
                                    Detail Slip Gaji
                                </h3>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-user me-1"></i>
                                    {{ $payroll['pegawai']['nama_lengkap'] ?? 'Nama tidak tersedia' }}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ DateTime::createFromFormat('!m', $payroll['periode_bulan'] ?? 1)->format('F') }} {{ $payroll['periode_tahun'] ?? date('Y') }}
                                </p>
                            </div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge-status {{ ($payroll['status'] ?? '') == 'Terbayar' ? 'badge-paid' : 'badge-pending' }}">
                                @if(($payroll['status'] ?? '') == 'Terbayar')
                                    <i class="fas fa-check-circle"></i> Terbayar
                                @else
                                    <i class="fas fa-clock"></i> Belum Terbayar
                                @endif
                            </span>
                            <a href="{{ route('payroll.index') }}" class="btn btn-secondary btn-modern">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>

                <div class="p-4">
                    <!-- Salary Highlight Section -->
                    <div class="salary-highlight fade-in">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-wallet me-3 fs-1 text-success"></i>
                                    <div>
                                        <h1 class="salary-amount mb-0">
                                            Rp {{ number_format($payroll['gaji_total'] ?? 0, 0, ',', '.') }}
                                        </h1>
                                        <p class="text-muted mb-0 fs-5">Total Gaji Periode Ini</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-md-end">
                                @if($payroll['tanggal_pembayaran'])
                                    <div class="text-success d-flex align-items-center justify-content-md-end">
                                        <i class="fas fa-calendar-check me-2 fs-4"></i>
                                        <div>
                                            <strong>Dibayar</strong><br>
                                            <small>{{ \Carbon\Carbon::parse($payroll['tanggal_pembayaran'])->format('d M Y') }}</small>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-warning d-flex align-items-center justify-content-md-end">
                                        <i class="fas fa-hourglass-half me-2 fs-4"></i>
                                        <div>
                                            <strong>Menunggu</strong><br>
                                            <small>Pembayaran</small>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Employee Information -->
                        <div class="col-lg-6">
                            <div class="info-card p-4 h-100 fade-in">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="icon-circle primary">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold">Informasi Pegawai</h5>
                                </div>
                                
                                <div class="info-item primary">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <label class="text-muted small mb-1">Nama Lengkap</label>
                                            <div class="fw-bold fs-6">{{ $payroll['pegawai']['nama_lengkap'] ?? 'Nama tidak tersedia' }}</div>
                                        </div>
                                        <i class="fas fa-id-card text-primary fs-4"></i>
                                    </div>
                                </div>
                                
                                @if(isset($payroll['pegawai']['NIP']))
                                <div class="info-item info">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <label class="text-muted small mb-1">NIP</label>
                                            <div class="fw-bold fs-6">{{ $payroll['pegawai']['NIP'] }}</div>
                                        </div>
                                        <i class="fas fa-id-badge text-info fs-4"></i>
                                    </div>
                                </div>
                                @endif
                                
                                @if(isset($payroll['pegawai']['posisi']))
                                <div class="info-item warning">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <label class="text-muted small mb-1">Posisi</label>
                                            <div class="fw-bold fs-6">{{ $payroll['pegawai']['posisi']['nama_posisi'] ?? 'Tidak tersedia' }}</div>
                                        </div>
                                        <i class="fas fa-briefcase text-warning fs-4"></i>
                                    </div>
                                </div>
                                @endif
                                
                                <div class="info-item success">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <label class="text-muted small mb-1">Periode Gaji</label>
                                            <div class="fw-bold fs-6">
                                                {{ DateTime::createFromFormat('!m', $payroll['periode_bulan'] ?? 1)->format('F') }} {{ $payroll['periode_tahun'] ?? date('Y') }}
                                            </div>
                                        </div>
                                        <i class="fas fa-calendar text-success fs-4"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Salary Breakdown -->
                        <div class="col-lg-6">
                            <div class="info-card p-4 h-100 fade-in">
                                <div class="d-flex align-items-center mb-4">
                                    <div class="icon-circle success">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                    <h5 class="mb-0 fw-bold">Rincian Gaji</h5>
                                </div>
                                
                                <div class="salary-breakdown">
                                    <div>
                                        <label class="text-muted small mb-1 d-block">Gaji Pokok</label>
                                        <div class="fw-bold h6 text-primary mb-0">
                                            Rp {{ number_format($payroll['gaji_pokok'] ?? 0, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <i class="fas fa-money-bill-alt text-primary fs-3"></i>
                                </div>
                                
                                @if(($payroll['gaji_kehadiran'] ?? 0) > 0)
                                <div class="salary-breakdown">
                                    <div>
                                        <label class="text-muted small mb-1 d-block">Gaji Kehadiran</label>
                                        <div class="fw-bold text-success fs-6">
                                            +Rp {{ number_format($payroll['gaji_kehadiran'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <i class="fas fa-user-check text-success fs-3"></i>
                                </div>
                                @endif
                                
                                @if(($payroll['gaji_bonus'] ?? 0) > 0)
                                <div class="salary-breakdown">
                                    <div>
                                        <label class="text-muted small mb-1 d-block">Bonus</label>
                                        <div class="fw-bold text-success fs-6">
                                            +Rp {{ number_format($payroll['gaji_bonus'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <i class="fas fa-gift text-success fs-3"></i>
                                </div>
                                @endif
                                
                                <hr class="my-3 border-success">
                                
                                <div class="salary-breakdown" style="background: linear-gradient(135deg, rgba(40, 167, 69, 0.1), rgba(32, 201, 151, 0.1)); border-left-color: #28a745; box-shadow: 0 4px 15px rgba(40, 167, 69, 0.2);">
                                    <div>
                                        <label class="text-success small mb-1 d-block fw-bold">💰 TOTAL GAJI</label>
                                        <div class="fw-bold h3 text-success mb-0">
                                            Rp {{ number_format($payroll['gaji_total'] ?? 0, 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <i class="fas fa-coins text-success fs-2"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Information -->
                    @if(isset($payroll['jumlah_absensi']) || isset($payroll['total_hari_kerja']) || isset($payroll['persentase_kehadiran']))
                    <div class="attendance-info fade-in">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle info">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h5 class="mb-0 fw-bold">Informasi Kehadiran</h5>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="text-primary mb-2">
                                        <i class="fas fa-calendar-check fs-2"></i>
                                    </div>
                                    <h4 class="fw-bold text-primary">{{ $payroll['jumlah_absensi'] ?? 0 }}</h4>
                                    <small class="text-muted">Hari Hadir</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="text-info mb-2">
                                        <i class="fas fa-calendar fs-2"></i>
                                    </div>
                                    <h4 class="fw-bold text-info">{{ $payroll['total_hari_kerja'] ?? 0 }}</h4>
                                    <small class="text-muted">Total Hari Kerja</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    @php
                                        $persentase = $payroll['persentase_kehadiran'] ?? 0;
                                        $attendanceClass = 'attendance-poor';
                                        $attendanceIcon = 'fa-times-circle';
                                        $attendanceColor = 'text-danger';
                                        
                                        if ($persentase >= 95) {
                                            $attendanceClass = 'attendance-excellent';
                                            $attendanceIcon = 'fa-star';
                                            $attendanceColor = 'text-success';
                                        } elseif ($persentase >= 85) {
                                            $attendanceClass = 'attendance-good';
                                            $attendanceIcon = 'fa-thumbs-up';
                                            $attendanceColor = 'text-info';
                                        } elseif ($persentase >= 75) {
                                            $attendanceClass = 'attendance-average';
                                            $attendanceIcon = 'fa-minus-circle';
                                            $attendanceColor = 'text-warning';
                                        }
                                    @endphp
                                    <div class="{{ $attendanceColor }} mb-2">
                                        <i class="fas {{ $attendanceIcon }} fs-2"></i>
                                    </div>
                                    <h4 class="fw-bold {{ $attendanceColor }}">{{ $persentase }}%</h4>
                                    <span class="attendance-badge {{ $attendanceClass }}">
                                        @if($persentase >= 95)
                                            <i class="fas fa-star"></i> Excellent
                                        @elseif($persentase >= 85)
                                            <i class="fas fa-thumbs-up"></i> Good
                                        @elseif($persentase >= 75)
                                            <i class="fas fa-minus-circle"></i> Average
                                        @else
                                            <i class="fas fa-times-circle"></i> Poor
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($payroll['keterangan'] ?? false)
                    <div class="section-card p-4 mt-4 fade-in">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle warning">
                                <i class="fas fa-sticky-note"></i>
                            </div>
                            <h5 class="mb-0 fw-bold">Keterangan</h5>
                        </div>
                        <div class="info-item warning">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ $payroll['keterangan'] }}
                        </div>
                    </div>
                    @endif
                    
                    @if(isset($payroll['tanggal_dibuat']) || isset($payroll['created_at']))
                    <div class="section-card p-4 mt-4 fade-in">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-circle info">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h5 class="mb-0 fw-bold">Informasi Sistem</h5>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item info">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <label class="text-muted small mb-1">Tanggal Dibuat</label>
                                            <div class="fw-bold">
                                                {{ isset($payroll['created_at']) ? \Carbon\Carbon::parse($payroll['created_at'])->format('d M Y H:i') : 
                                                   (isset($payroll['tanggal_dibuat']) ? \Carbon\Carbon::parse($payroll['tanggal_dibuat'])->format('d M Y H:i') : 'Tidak tersedia') }}
                                            </div>
                                        </div>
                                        <i class="fas fa-plus-circle text-info"></i>
                                    </div>
                                </div>
                            </div>
                            
                            @if(isset($payroll['tanggal_diupdate']) || isset($payroll['updated_at']))
                            <div class="col-md-6">
                                <div class="info-item info">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <label class="text-muted small mb-1">Terakhir Diupdate</label>
                                            <div class="fw-bold">
                                                {{ isset($payroll['updated_at']) ? \Carbon\Carbon::parse($payroll['updated_at'])->format('d M Y H:i') : 
                                                   (isset($payroll['tanggal_diupdate']) ? \Carbon\Carbon::parse($payroll['tanggal_diupdate'])->format('d M Y H:i') : 'Tidak tersedia') }}
                                            </div>
                                        </div>
                                        <i class="fas fa-edit text-info"></i>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <!-- Action Buttons -->
                    @if(is_admin_or_hrd())
                    <div class="section-card p-4 mt-4 fade-in">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-3 flex-wrap">
                                <a href="{{ route('payroll.edit', $payroll['id_gaji'] ?? $payroll['id']) }}" 
                                   class="btn btn-warning btn-modern">
                                    <i class="fas fa-edit me-2"></i>Edit Gaji
                                </a>
                                
                                @if(($payroll['status'] ?? '') === 'Belum Terbayar')
                                <form action="{{ route('payroll.payment-status', $payroll['id_gaji'] ?? $payroll['id']) }}" 
                                      method="POST" class="d-inline payment-form"
                                      onsubmit="return confirmPayment('{{ $payroll['pegawai']['nama_lengkap'] ?? 'pegawai' }}', '{{ number_format($payroll['gaji_total'] ?? 0, 0, ',', '.') }}')">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="status" value="Terbayar">
                                    <input type="hidden" name="tanggal_pembayaran" value="{{ date('Y-m-d') }}">
                                    <!-- Debug info -->
                                    @if(config('app.debug'))
                                    <input type="hidden" name="debug_id" value="{{ $payroll['id_gaji'] ?? $payroll['id'] }}">
                                    <input type="hidden" name="debug_user" value="{{ session('user_id') }}">
                                    <input type="hidden" name="debug_role" value="{{ session('user_role') }}">
                                    <input type="hidden" name="debug_timestamp" value="{{ time() }}">
                                    @endif
                                    <button type="submit" class="btn btn-success btn-modern"
                                            id="confirmPaymentBtn">
                                        <i class="fas fa-check-circle me-2"></i>Konfirmasi Pembayaran
                                    </button>
                                </form>
                                @else
                                <div class="alert alert-success d-inline-flex align-items-center">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <div>
                                        <strong>Sudah Terbayar</strong>
                                        @if($payroll['tanggal_pembayaran'] ?? false)
                                            <br><small>{{ \Carbon\Carbon::parse($payroll['tanggal_pembayaran'])->format('d M Y') }}</small>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                
                                <button class="btn btn-primary btn-modern" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i>Cetak Slip
                                </button>
                                
                                <!-- @if(config('app.debug'))
                                <button class="btn btn-info btn-modern" onclick="debugSessionToken()">
                                    <i class="fas fa-bug me-2"></i>Debug Session
                                </button>
                                @endif -->
                            </div>
                            
                            <form action="{{ route('payroll.destroy', $payroll['id_gaji']) }}" 
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Yakin ingin menghapus data gaji ini? Tindakan ini tidak dapat dibatalkan!')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-modern">
                                    <i class="fas fa-trash me-2"></i>Hapus Data
                                </button>
                            </form>
                        </div>
                    </div>
                    @else
                    <!-- Action untuk user biasa -->
                    <div class="section-card p-4 mt-4 fade-in">
                        <div class="d-flex justify-content-center gap-3">
                            <button class="btn btn-primary btn-modern" onclick="window.print()">
                                <i class="fas fa-print me-2"></i>Cetak Slip Gaji
                            </button>
                            <a href="{{ route('payroll.index') }}" class="btn btn-secondary btn-modern">
                                <i class="fas fa-list me-2"></i>Lihat Semua Gaji
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmPayment(namaPegawai, totalGaji) {
    const today = new Date().toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    return confirm(`Konfirmasi pembayaran gaji untuk ${namaPegawai}?\n\nTotal: Rp ${totalGaji}\nTanggal Pembayaran: ${today}\n\nTindakan ini akan menandai gaji sebagai "Terbayar" dan mencatat tanggal pembayaran hari ini.\n\nApakah Anda yakin?`);
}

// Debug function to check session and token
function debugSessionToken() {
    fetch('/debug-session-token', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('Session and Token Debug:', data);
        
        let debugInfo = 'SESSION & TOKEN DEBUG:\n\n';
        debugInfo += `Session ID: ${data.session_id}\n`;
        debugInfo += `Authenticated: ${data.authenticated}\n`;
        debugInfo += `API Token: ${data.api_token || 'Missing'}\n`;
        debugInfo += `Token Length: ${data.api_token_length}\n`;
        debugInfo += `User ID: ${data.user_id || 'Missing'}\n`;
        debugInfo += `User Role: ${data.user_role || 'Missing'}\n`;
        debugInfo += `User Name: ${data.user_name || 'Missing'}\n`;
        debugInfo += `Token Valid: ${data.token_test?.valid || 'Unknown'}\n`;
        
        if (data.token_test?.error) {
            debugInfo += `Token Error: ${data.token_test.error}\n`;
        }
        
        alert(debugInfo);
    })
    .catch(error => {
        console.error('Debug failed:', error);
        alert('Debug gagal: ' + error.message);
    });
}

// Function to check session status before form submission
function checkSessionStatus() {
    // Check if CSRF token exists
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken || !csrfToken.getAttribute('content')) {
        console.error('CSRF token not found');
        alert('Sesi keamanan tidak valid. Halaman akan dimuat ulang.');
        window.location.reload();
        return false;
    }
    
    // Validate session via AJAX call before form submission
    return fetch('/check-session', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.status === 401 || response.status === 419) {
            console.error('Session expired or invalid');
            alert('Sesi Anda telah berakhir. Silakan login kembali.');
            window.location.href = '{{ route("login") }}';
            return false;
        }
        return response.json();
    })
    .then(data => {
        if (!data || !data.authenticated) {
            console.error('Session not authenticated');
            alert('Sesi Anda tidak valid. Silakan login kembali.');
            window.location.href = '{{ route("login") }}';
            return false;
        }
        return true;
    })
    .catch(error => {
        console.error('Session check failed:', error);
        // Don't block the form if session check fails due to network issues
        return true;
    });
}

// Auto-refresh status jika ada perubahan
document.addEventListener('DOMContentLoaded', function() {
    // Check for success/error messages and show them
    @if(session('success'))
        setTimeout(() => {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }, 100);
    @endif
    
    @if(session('error'))
        setTimeout(() => {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            // Auto remove after 8 seconds for errors
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 8000);
        }, 100);
    @endif
    
    // Enhanced session checking every 5 minutes
    setInterval(checkSessionStatus, 300000); // 5 minutes
});

// Enhanced form submission with loading state and session check
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.payment-form');
    if (form) {
        // Add debug logging
        console.log('Payment form found:', form.action);
        
        form.addEventListener('submit', function(e) {
            console.log('Form submission started');
            e.preventDefault(); // Prevent default submission first
            
            // Debug information
            const debugData = {
                action: form.action,
                method: form.method,
                csrf_token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                form_token: form.querySelector('input[name="_token"]')?.value,
                status: form.querySelector('input[name="status"]')?.value,
                debug_id: form.querySelector('input[name="debug_id"]')?.value,
                debug_user: form.querySelector('input[name="debug_user"]')?.value,
                debug_role: form.querySelector('input[name="debug_role"]')?.value
            };
            
            console.log('Form debug data:', debugData);
            
            // Validate CSRF token first
            if (!debugData.csrf_token || !debugData.form_token) {
                alert('Token keamanan tidak valid. Halaman akan dimuat ulang.');
                window.location.reload();
                return false;
            }
            
            if (debugData.csrf_token !== debugData.form_token) {
                alert('Token keamanan tidak cocok. Halaman akan dimuat ulang.');
                window.location.reload();
                return false;
            }
            
            const btn = document.getElementById('confirmPaymentBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses Pembayaran...';
                btn.classList.add('disabled');
                
                // Update form appearance
                form.style.opacity = '0.7';
                form.style.pointerEvents = 'none';
            }
            
            // Check session status via AJAX first
            checkSessionStatus().then(sessionValid => {
                if (sessionValid) {
                    console.log('Session valid, checking API token...');
                    
                    // Additional check: verify API token is valid before submitting
                    fetch('/debug-session-token', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(tokenData => {
                        console.log('Token validation result:', tokenData);
                        
                        if (tokenData.token_test && tokenData.token_test.valid === true) {
                            console.log('API token is valid, submitting form...');
                            form.submit();
                        } else {
                            console.error('API token is invalid:', tokenData.token_test);
                            
                            // Re-enable button
                            if (btn) {
                                btn.disabled = false;
                                btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Konfirmasi Pembayaran';
                                btn.classList.remove('disabled');
                                form.style.opacity = '1';
                                form.style.pointerEvents = 'auto';
                            }
                            
                            alert('Token API tidak valid atau telah kedaluwarsa. Silakan refresh halaman dan coba lagi, atau login ulang jika masalah berlanjut.');
                        }
                    })
                    .catch(tokenError => {
                        console.error('Token validation error:', tokenError);
                        // If token check fails, still try to submit (maybe network issue)
                        console.log('Token check failed, submitting form anyway...');
                        form.submit();
                    });
                } else {
                    console.error('Session invalid, not submitting form');
                    // Re-enable button if session is invalid
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Konfirmasi Pembayaran';
                        btn.classList.remove('disabled');
                        form.style.opacity = '1';
                        form.style.pointerEvents = 'auto';
                    }
                }
            }).catch(error => {
                console.error('Session check error:', error);
                // If session check fails, still try to submit (maybe network issue)
                console.log('Session check failed, submitting anyway...');
                form.submit();
            });
            
            // Fallback re-enable after 15 seconds
            setTimeout(() => {
                if (btn && btn.disabled) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Konfirmasi Pembayaran';
                    btn.classList.remove('disabled');
                    form.style.opacity = '1';
                    form.style.pointerEvents = 'auto';
                }
            }, 15000);
        });
    } else {
        console.log('Payment form not found');
    }
    
    // Add CSRF token refresh functionality
    const refreshCSRF = () => {
        fetch('/csrf-token', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.csrf_token) {
                // Update CSRF token in meta tag
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                if (csrfMeta) {
                    csrfMeta.setAttribute('content', data.csrf_token);
                }
                
                // Update CSRF token in forms
                const csrfInputs = document.querySelectorAll('input[name="_token"]');
                csrfInputs.forEach(input => {
                    input.value = data.csrf_token;
                });
                
                console.log('CSRF token refreshed successfully');
            }
        })
        .catch(error => {
            console.warn('CSRF token refresh failed:', error);
        });
    };
    
    // Refresh CSRF token every 10 minutes
    setInterval(refreshCSRF, 600000); // 10 minutes
    
    // Session heartbeat to keep session alive
    const sessionHeartbeat = () => {
        fetch('/check-session', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.status === 401) {
                console.warn('Session expired during heartbeat');
                alert('Sesi Anda telah berakhir. Halaman akan dimuat ulang untuk login kembali.');
                window.location.href = '{{ route("login") }}';
            }
            return response.json();
        })
        .then(data => {
            if (!data.authenticated) {
                console.warn('Session not authenticated during heartbeat');
                alert('Sesi Anda tidak valid. Halaman akan dimuat ulang untuk login kembali.');
                window.location.href = '{{ route("login") }}';
            } else {
                console.log('Session heartbeat successful');
            }
        })
        .catch(error => {
            console.warn('Session heartbeat failed:', error);
            // Don't redirect on network errors during heartbeat
        });
    };
    
    // Run session heartbeat every 2 minutes
    setInterval(sessionHeartbeat, 120000); // 2 minutes
});
</script>
@endsection
