@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <!-- Header Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient-primary text-white border-0 py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1 fw-bold">
                                @if(is_pelanggan())
                                    <i class="fas fa-briefcase me-2"></i>Lowongan Kerja Tersedia
                                @else
                                    <i class="fas fa-users-cog me-2"></i>Daftar Lowongan Kerja
                                @endif
                            </h2>
                            <p class="mb-0 opacity-75">
                                @if(is_pelanggan())
                                    Temukan karir impian Anda bersama kami
                                @else
                                    Kelola dan pantau semua lowongan pekerjaan
                                @endif
                            </p>
                        </div>
                        @if(is_admin() || is_hrd())
                            <a href="{{ route('recruitments.create') }}" class="btn btn-light btn-lg px-4 py-2 shadow-sm">
                                <i class="fas fa-plus me-2"></i>Buat Lowongan
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <!-- Alert Messages -->
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle fa-lg me-3 text-success"></i>
                                <div>
                                    <strong>Berhasil!</strong><br>
                                    {{ session('success') }}
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle fa-lg me-3 text-danger"></i>
                                <div>
                                    <strong>Terjadi Kesalahan!</strong><br>
                                    {{ session('error') }}
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($recruitments->count() > 0)
                        <div class="row">
                            @foreach($recruitments as $recruitment)
                                @php
                                    // Definisikan variabel-variabel yang akan digunakan di seluruh card
                                    $recruitment_status = is_object($recruitment) ? $recruitment->status : (is_array($recruitment) ? ($recruitment['status'] ?? 'closed') : 'closed');
                                    
                                    // Get deadline and check if it's past
                                    $deadline = is_object($recruitment) && $recruitment->application_deadline 
                                        ? $recruitment->application_deadline 
                                        : (is_array($recruitment) && isset($recruitment['application_deadline']) 
                                            ? \Carbon\Carbon::parse($recruitment['application_deadline']) 
                                            : null);
                                    $is_past = $deadline ? $deadline->isPast() : true;
                                    $is_future = $deadline ? $deadline->isFuture() : false;
                                    
                                    // A recruitment is open only if status is 'open'/'aktif' AND deadline hasn't passed
                                    $is_recruitment_open = ($recruitment_status === 'open' || $recruitment_status === 'aktif') && $is_future;
                                    
                                    $position = is_object($recruitment) ? ($recruitment->position ?? 'Posisi tidak tersedia') : 
                                        (is_array($recruitment) ? ($recruitment['position'] ?? 'Posisi tidak tersedia') : 'Posisi tidak tersedia');
                                    $employment_type_display = is_object($recruitment) ? ($recruitment->employment_type_display ?? 'Tidak ditentukan') : 
                                        (is_array($recruitment) ? ($recruitment['employment_type_display'] ?? 'Tidak ditentukan') : 'Tidak ditentukan');
                                    $deadline_formatted = $deadline ? $deadline->format('d M Y') : 'Tidak tersedia';
                                    $slots = is_object($recruitment) ? ($recruitment->slots ?? 0) : 
                                        (is_array($recruitment) ? ($recruitment['slots'] ?? 0) : 0);
                                    $salary_range = is_object($recruitment) ? ($recruitment->salary_range ?? 'Tidak tersedia') : 
                                        (is_array($recruitment) ? ($recruitment['salary_range'] ?? 'Tidak tersedia') : 'Tidak tersedia');
                                    $description = is_object($recruitment) ? ($recruitment->description ?? 'Tidak ada deskripsi') : 
                                        (is_array($recruitment) ? ($recruitment['description'] ?? 'Tidak ada deskripsi') : 'Tidak ada deskripsi');
                                @endphp
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 border-0 shadow-sm hover-card">
                                        <div class="card-header bg-gradient-info text-white border-bottom-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    @if($is_recruitment_open)
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check-circle"></i> Dibuka
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times-circle"></i> Ditutup
                                                        </span>
                                                    @endif
                                                    
                                                    <span class="badge bg-light text-dark">{{ $employment_type_display }}</span>
                                                </div>
                                                @if(is_admin() || is_hrd())
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-v text-dark"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="{{ route('recruitments.edit', $recruitment->id) }}">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form action="{{ route('recruitments.destroy', $recruitment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus lowongan ini?')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item text-danger">
                                                                        <i class="fas fa-trash"></i> Hapus
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="mt-2">
                                                <i class="fas fa-briefcase fa-2x mb-2"></i>
                                                <h5 class="card-title mb-0">{{ $position }}</h5>
                                            </div>
                                        </div>
                                        
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar"></i> Deadline
                                                    </small>
                                                    <span class="badge {{ $is_past ? 'bg-danger' : 'bg-warning text-dark' }}">
                                                        {{ $deadline_formatted }}
                                                    </span>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-users"></i> Posisi Tersedia
                                                    </small>
                                                    <span class="fw-bold text-primary">{{ $slots }} orang</span>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="fas fa-money-bill-wave"></i> Gaji
                                                    </small>
                                                    <span class="fw-bold text-success">{{ $salary_range }}</span>
                                                </div>
                                            </div>

                                            <p class="card-text text-muted">{{ Str::limit($description, 120) }}</p>
                                            
                                            @if($deadline && $deadline->isToday())
                                                <div class="alert alert-warning alert-sm p-2">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <small>Deadline hari ini!</small>
                                                </div>
                                            @elseif($deadline && $deadline->diffInDays() <= 3 && $deadline->isFuture())
                                                <div class="alert alert-info alert-sm p-2">
                                                    <i class="fas fa-clock"></i>
                                                    <small>{{ $deadline->diffForHumans() }}</small>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="card-footer bg-transparent border-top-0">
                                            @if(is_pelanggan())
                                                <div class="d-grid gap-2">
                                                    <a href="{{ route('recruitments.show', $recruitment->id) }}" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i> Lihat Detail
                                                    </a>
                                                    @if($is_recruitment_open)
                                                        <a href="{{ route('recruitments.apply.form', $recruitment->id) }}" class="btn btn-success w-100 btn-sm">
                                                            <i class="fas fa-paper-plane"></i> Lamar Sekarang
                                                        </a>
                                                    @else
                                                        <button class="btn btn-secondary w-100 btn-sm" disabled>
                                                            <i class="fas fa-times"></i> Lowongan Ditutup
                                                        </button>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="d-grid">
                                                    <a href="{{ route('recruitments.show', $recruitment->id) }}" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-eye"></i> Lihat Detail & Kelola
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $recruitments->links() }}
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <div class="empty-state-icon mb-4">
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                                    <i class="fas fa-briefcase fa-3x text-muted"></i>
                                </div>
                            </div>
                            <h4 class="text-muted mb-3">
                                @if(is_pelanggan())
                                    Belum Ada Lowongan Tersedia
                                @else
                                    Belum Ada Lowongan Kerja
                                @endif
                            </h4>
                            <p class="text-muted mb-4 lead">
                                @if(is_pelanggan())
                                    Saat ini belum ada lowongan kerja yang tersedia.<br>
                                    Silakan cek kembali nanti untuk peluang karir terbaru.
                                @else
                                    Belum ada lowongan kerja yang dibuat.<br>
                                    Mulai dengan membuat lowongan pertama.
                                @endif
                            </p>
                            @if(is_admin() || is_hrd())
                                <a href="{{ route('recruitments.create') }}" class="btn btn-primary btn-lg px-4 py-3 shadow-sm">
                                    <i class="fas fa-plus me-2"></i>Buat Lowongan Pertama
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
/* Header gradient */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-info {
    background: linear-gradient(45deg, #17a2b8, #117a8b);
}

/* Card improvements */
.hover-card {
    transition: all 0.3s ease-in-out;
    border-radius: 15px;
    overflow: hidden;
    border: none !important;
}

.hover-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

.card {
    border-radius: 15px;
    border: none;
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    padding: 1.5rem;
    border: none !important;
}

.card-body {
    padding: 1.5rem;
}

.card-footer {
    padding: 1rem 1.5rem 1.5rem;
    background: rgba(0,0,0,0.02) !important;
    border: none !important;
}

/* Alert improvements */
.alert {
    border-radius: 12px;
    border: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.07);
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
    color: #721c24;
}

.alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
}

.alert-info {
    background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    color: #0c5460;
}

.alert-sm {
    font-size: 0.875rem;
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
}

/* Badge improvements */
.badge {
    border-radius: 25px;
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.5rem 0.9rem;
    letter-spacing: 0.5px;
}

.badge.bg-success {
    background: linear-gradient(45deg, #28a745, #20c997) !important;
    color: white;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
}

.badge.bg-danger {
    background: linear-gradient(45deg, #dc3545, #e74c3c) !important;
    color: white;
    box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
}

.badge.bg-warning {
    background: linear-gradient(45deg, #ffc107, #f39c12) !important;
    color: #212529;
    box-shadow: 0 2px 4px rgba(255, 193, 7, 0.3);
}

.badge.bg-light {
    background: rgba(255,255,255,0.9) !important;
    color: #495057;
    border: 1px solid rgba(255,255,255,0.3);
}

/* Button improvements */
.btn {
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border-radius: 8px;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    border-radius: 12px;
}

.btn-success {
    background: linear-gradient(45deg, #28a745, #20c997);
    box-shadow: 0 3px 6px rgba(40, 167, 69, 0.3);
}

.btn-success:hover {
    background: linear-gradient(45deg, #218838, #1abc9c);
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(40, 167, 69, 0.4);
}

.btn-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    box-shadow: 0 3px 6px rgba(0, 123, 255, 0.3);
}

.btn-primary:hover {
    background: linear-gradient(45deg, #0056b3, #004085);
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 123, 255, 0.4);
}

.btn-outline-primary {
    border: 2px solid #007bff;
    color: #007bff;
    font-weight: 500;
    background: transparent;
}

.btn-outline-primary:hover {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border-color: #007bff;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}

.btn-secondary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: #6c757d;
}

.btn-light {
    background: rgba(255,255,255,0.95);
    color: #495057;
    border: 1px solid rgba(255,255,255,0.3);
}

.btn-light:hover {
    background: white;
    color: #212529;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Typography improvements */
.card-title {
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1.3;
    color: #2c3e50;
}

.card-text {
    line-height: 1.6;
    font-size: 0.9rem;
    color: #6c757d;
}

.text-primary {
    color: #2c5aa0 !important;
    font-weight: 600;
}

.text-success {
    color: #27ae60 !important;
    font-weight: 600;
}

.fw-bold {
    font-weight: 600 !important;
}

/* Spacing improvements */
.d-grid.gap-2 {
    gap: 0.75rem !important;
}

/* Empty state improvements */
.empty-state-icon {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* Responsive improvements */
@media (max-width: 768px) {
    .col-lg-4 {
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        padding: 1.25rem;
    }
    
    .card-body {
        padding: 1.25rem;
    }
    
    .card-footer {
        padding: 1rem 1.25rem 1.25rem;
    }
    
    .btn-lg {
        padding: 0.625rem 1.25rem;
        font-size: 0.9rem;
    }
}

@media (max-width: 576px) {
    .hover-card:hover {
        transform: translateY(-4px);
    }
    
    .card-header h2 {
        font-size: 1.5rem;
    }
    
    .badge {
        font-size: 0.7rem;
        padding: 0.4rem 0.7rem;
    }
}

/* Loading animation */
.card-loading {
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
    100% {
        opacity: 1;
    }
}

/* Shadow improvements */
.shadow-sm {
    box-shadow: 0 2px 4px rgba(0,0,0,0.08) !important;
}

.shadow {
    box-shadow: 0 4px 6px rgba(0,0,0,0.07) !important;
}

/* Icon improvements */
.fas {
    font-weight: 900;
}

/* Pagination improvements */
.pagination {
    border-radius: 10px;
}

.page-link {
    border-radius: 8px;
    margin: 0 2px;
    border: none;
    color: #495057;
}

.page-link:hover {
    background-color: #e9ecef;
    color: #007bff;
}

.page-item.active .page-link {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border-color: #007bff;
}
</style>
@endsection
