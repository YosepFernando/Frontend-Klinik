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

.info-item {
    background: rgba(255, 255, 255, 0.8);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 0.5rem;
    border-left: 4px solid #4a90e2;
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

.badge-status {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.875rem;
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

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="glass-card fade-in">
                <div class="glass-header p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1 text-primary">
                                <i class="fas fa-receipt me-2"></i>Detail Gaji
                            </h3>
                            <p class="text-muted mb-0">
                                {{ $payroll['pegawai']['nama_lengkap'] ?? 'Nama tidak tersedia' }} - 
                                {{ $payroll['periode_bulan'] ?? 'N/A' }}/{{ $payroll['periode_tahun'] ?? 'N/A' }}
                            </p>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge-status {{ $payroll['status'] == 'Terbayar' ? 'badge-paid' : 'badge-pending' }}">
                                @if($payroll['status'] == 'Terbayar')
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
                    <div class="row">
                        <!-- Employee Information -->
                        <div class="col-md-6">
                            <div class="section-card p-4 h-100">
                                <h5 class="mb-3">
                                    <i class="fas fa-user text-primary me-2"></i>Informasi Pegawai
                                </h5>
                                
                                <div class="info-item">
                                    <label class="text-muted small">Nama Lengkap</label>
                                    <div class="fw-bold">{{ $payroll['pegawai']['nama_lengkap'] ?? 'Nama tidak tersedia' }}</div>
                                </div>
                                
                                @if(isset($payroll['pegawai']['posisi']))
                                <div class="info-item">
                                    <label class="text-muted small">Posisi</label>
                                    <div class="fw-bold">{{ $payroll['pegawai']['posisi']['nama_posisi'] ?? 'Tidak tersedia' }}</div>
                                </div>
                                @endif
                                
                                <div class="info-item">
                                    <label class="text-muted small">Periode Gaji</label>
                                    <div class="fw-bold">
                                        {{ DateTime::createFromFormat('!m', $payroll['periode_bulan'] ?? 1)->format('F') }} {{ $payroll['periode_tahun'] ?? date('Y') }}
                                    </div>
                                </div>
                                
                                @if($payroll['tanggal_pembayaran'])
                                <div class="info-item">
                                    <label class="text-muted small">Tanggal Pembayaran</label>
                                    <div class="fw-bold text-success">
                                        {{ \Carbon\Carbon::parse($payroll['tanggal_pembayaran'])->format('d M Y H:i') }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Salary Breakdown -->
                        <div class="col-md-6">
                            <div class="section-card p-4 h-100">
                                <h5 class="mb-3">
                                    <i class="fas fa-calculator text-success me-2"></i>Rincian Gaji
                                </h5>
                                
                                <div class="info-item border-start border-primary border-3">
                                    <label class="text-muted small">Gaji Pokok</label>
                                    <div class="fw-bold h5 text-primary mb-0">
                                        Rp {{ number_format($payroll['gaji_pokok'] ?? 0, 0, ',', '.') }}
                                    </div>
                                </div>
                                
                                @if(($payroll['gaji_kehadiran'] ?? 0) > 0)
                                <div class="info-item border-start border-success border-3">
                                    <label class="text-muted small">Gaji Kehadiran</label>
                                    <div class="fw-bold text-success">
                                        +Rp {{ number_format($payroll['gaji_kehadiran'], 0, ',', '.') }}
                                    </div>
                                </div>
                                @endif
                                
                                @if(($payroll['gaji_bonus'] ?? 0) > 0)
                                <div class="info-item border-start border-success border-3">
                                    <label class="text-muted small">Bonus</label>
                                    <div class="fw-bold text-success">
                                        +Rp {{ number_format($payroll['gaji_bonus'], 0, ',', '.') }}
                                    </div>
                                </div>
                                @endif
                                
                                <div class="info-item border-start border-warning border-3 mt-3" style="background: rgba(255, 193, 7, 0.1);">
                                    <label class="text-muted small">Total Gaji</label>
                                    <div class="fw-bold h4 text-warning mb-0">
                                        Rp {{ number_format($payroll['gaji_total'] ?? 0, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($payroll['keterangan'] ?? false)
                    <div class="section-card p-4 mt-4">
                        <h5 class="mb-3">
                            <i class="fas fa-sticky-note text-warning me-2"></i>Keterangan
                        </h5>
                        <div class="info-item">
                            {{ $payroll['keterangan'] }}
                        </div>
                    </div>
                    @endif
                    
                    @if(isset($payroll['tanggal_dibuat']) || isset($payroll['created_at']))
                    <div class="section-card p-4 mt-4">
                        <h5 class="mb-3">
                            <i class="fas fa-clock text-info me-2"></i>Informasi Tambahan
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small">Tanggal Dibuat</label>
                                    <div class="fw-bold">
                                        {{ isset($payroll['created_at']) ? \Carbon\Carbon::parse($payroll['created_at'])->format('d M Y H:i') : 
                                           (isset($payroll['tanggal_dibuat']) ? \Carbon\Carbon::parse($payroll['tanggal_dibuat'])->format('d M Y H:i') : 'Tidak tersedia') }}
                                    </div>
                                </div>
                            </div>
                            
                            @if(isset($payroll['tanggal_diupdate']) || isset($payroll['updated_at']))
                            <div class="col-md-6">
                                <div class="info-item">
                                    <label class="text-muted small">Terakhir Diupdate</label>
                                    <div class="fw-bold">
                                        {{ isset($payroll['updated_at']) ? \Carbon\Carbon::parse($payroll['updated_at'])->format('d M Y H:i') : 
                                           (isset($payroll['tanggal_diupdate']) ? \Carbon\Carbon::parse($payroll['tanggal_diupdate'])->format('d M Y H:i') : 'Tidak tersedia') }}
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    
                    <!-- Actions -->
                    @if(is_admin() || is_hrd())
                    <div class="d-flex justify-content-between align-items-center pt-4 mt-4 border-top">
                        <div class="d-flex gap-2">
                            <a href="{{ route('payroll.edit', $payroll['id_gaji'] ?? $payroll['id']) }}" 
                               class="btn btn-warning btn-modern">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                            
                            @if($payroll['status'] === 'Belum Terbayar')
                            <form action="{{ route('payroll.payment-status', $payroll['id_gaji']) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="Terbayar">
                                <button type="submit" class="btn btn-success btn-modern"
                                        onclick="return confirm('Konfirmasi pembayaran gaji ini?')">
                                    <i class="fas fa-check me-1"></i>Konfirmasi Pembayaran
                                </button>
                            </form>
                            @endif
                        </div>
                        
                        <form action="{{ route('payroll.destroy', $payroll['id_gaji']) }}" 
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Yakin ingin menghapus data gaji ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-modern">
                                <i class="fas fa-trash me-1"></i>Hapus
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
