@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-graduation-cap me-2"></i>Data Pelatihan</h2>
                @if(is_admin() || is_hrd())
                <a href="{{ route('trainings.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Pelatihan
                </a>
                @endif
            </div>
            
            <!-- Filter & Search -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-gradient-light border-0">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filter & Pencarian
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('trainings.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Cari Pelatihan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                       placeholder="Judul pelatihan...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" name="is_active">
                                <option value="">Semua Status</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Jenis Pelatihan</label>
                            <select class="form-select" name="jenis_pelatihan">
                                <option value="">Semua Jenis</option>
                                <option value="Internal" {{ request('jenis_pelatihan') == 'Internal' ? 'selected' : '' }}>Internal</option>
                                <option value="Eksternal" {{ request('jenis_pelatihan') == 'Eksternal' ? 'selected' : '' }}>Eksternal</option>
                                <option value="video" {{ request('jenis_pelatihan') == 'video' ? 'selected' : '' }}>Video Online</option>
                                <option value="document" {{ request('jenis_pelatihan') == 'document' ? 'selected' : '' }}>Dokumen</option>
                                <option value="offline" {{ request('jenis_pelatihan') == 'offline' ? 'selected' : '' }}>Offline/Tatap Muka</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <div class="d-grid gap-2 w-100">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                                <a href="{{ route('trainings.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            @if(isset($error))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> {{ $error }}
                </div>
            @endif
            
            @if(isset($trainingsData) && count($trainingsData) > 0)
            <div class="row">
                @foreach($trainingsData as $training)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-header bg-gradient-primary text-white border-bottom-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    @php
                                        // Data sudah ditransformasi di controller
                                        $status_badge_class = $training['status_badge_class'] ?? 'badge bg-secondary';
                                        $status = $training['status'] ?? 'inactive';
                                        $status_display = $training['status_display'] ?? 'Tidak Aktif';
                                        
                                        $jenis_badge_class = $training['jenis_badge_class'] ?? 'badge bg-secondary';
                                        $jenis_display = $training['jenis_display'] ?? 'Tidak ditentukan';
                                        
                                        $training_id = $training['id'] ?? $training['id_pelatihan'] ?? null;
                                    @endphp
                                    <span class="{{ $status_badge_class }}">
                                        <i class="fas fa-{{ $status === 'active' ? 'check-circle' : 'times-circle' }}"></i>
                                        {{ $status_display }}
                                    </span>
                                    <span class="{{ $jenis_badge_class }} ms-1">
                                        {{ $jenis_display }}
                                    </span>
                                </div>
                                @if(is_admin() || is_hrd())
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v text-dark"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if($training_id)
                                            <li><a class="dropdown-item" href="{{ route('trainings.edit', $training_id) }}">
                                                <i class="fas fa-edit"></i> Edit
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('trainings.destroy', $training_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pelatihan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                </form>
                                            </li>
                                        @else
                                            <li><span class="dropdown-item-text text-muted">ID tidak tersedia</span></li>
                                        @endif
                                    </ul>
                                </div>
                                @endif
                            </div>
                            <div class="mt-2">
                                <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                                @php
                                    $judul = $training['judul'] ?? 'Judul tidak tersedia';
                                @endphp
                                <h5 class="card-title mb-0">{{ $judul }}</h5>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> Durasi
                                    </small>
                                    @php
                                        $durasi_display = $training['durasi_display'] ?? 'Tidak ditentukan';
                                    @endphp
                                    <span class="fw-bold text-primary">{{ $durasi_display }}</span>
                                </div>
                                
                                @if(isset($training['jadwal_pelatihan']) && $training['jadwal_pelatihan'])
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> Jadwal
                                    </small>
                                    @php
                                        try {
                                            $jadwal = \Carbon\Carbon::parse($training['jadwal_pelatihan']);
                                            $jadwal_display = $jadwal->format('d M Y, H:i');
                                        } catch (Exception $e) {
                                            $jadwal_display = 'Tidak dijadwalkan';
                                        }
                                    @endphp
                                    <span class="fw-bold text-info">{{ $jadwal_display }}</span>
                                </div>
                                @endif
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    @php
                                        $jenis_pelatihan = $training['jenis_pelatihan'] ?? 'offline';
                                        $location_info = $training['location_info'] ?? 'Lokasi tidak tersedia';
                                    @endphp
                                    <small class="text-muted">
                                        <i class="fas fa-{{ $jenis_pelatihan === 'video' ? 'video' : ($jenis_pelatihan === 'document' ? 'file-alt' : 'map-marker-alt') }}"></i>
                                         {{ $jenis_pelatihan === 'offline' ? 'Lokasi' : 'Jenis' }}
                                    </small>
                                    <span class="fw-bold {{ $jenis_pelatihan === 'offline' ? 'text-danger' : 'text-success' }}">
                                        {{ $jenis_pelatihan === 'offline' ? $location_info : ($jenis_pelatihan === 'video' ? 'Video Online' : $jenis_display) }}
                                    </span>
                                </div>
                                
                                @if(isset($training['link_url']) && $training['link_url'] && $jenis_pelatihan !== 'offline')
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-link"></i> Link
                                    </small>
                                    <a href="{{ $training['link_url'] }}" target="_blank" class="text-decoration-none">
                                        <small class="text-primary">
                                            <i class="fas fa-external-link-alt"></i> Buka Link
                                        </small>
                                    </a>
                                </div>
                                @endif
                            </div>
                            @php
                                $deskripsi = $training['deskripsi'] ?? 'Tidak ada deskripsi';
                            @endphp
                            <p class="card-text text-muted">{{ Str::limit($deskripsi, 120) }}</p>
                            
                            @php
                                $created_at = null;
                                if (isset($training['created_at'])) {
                                    try {
                                        $created_at = \Carbon\Carbon::parse($training['created_at']);
                                    } catch (Exception $e) {
                                        $created_at = null;
                                    }
                                }
                            @endphp
                            @if($created_at && $created_at->isToday())
                                <div class="alert alert-success alert-sm p-2">
                                    <i class="fas fa-certificate"></i>
                                    <small>Baru ditambahkan hari ini!</small>
                                </div>
                            @endif
                        </div>
                        
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-grid">
                                @if($training_id)
                                    <a href="{{ route('trainings.show', $training_id) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-eye"></i> Lihat Detail
                                    </a>
                                @else
                                    <button class="btn btn-outline-secondary" disabled>
                                        <i class="fas fa-eye"></i> Detail tidak tersedia
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <!-- Pagination -->
            @if(isset($paginationInfo) && $paginationInfo['has_pages'])
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        @if($paginationInfo['current_page'] > 1)
                            <li class="page-item">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $paginationInfo['current_page'] - 1]) }}">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        @endif
                        
                        @for($i = 1; $i <= $paginationInfo['last_page']; $i++)
                            <li class="page-item {{ $i == $paginationInfo['current_page'] ? 'active' : '' }}">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                            </li>
                        @endfor
                        
                        @if($paginationInfo['current_page'] < $paginationInfo['last_page'])
                            <li class="page-item">
                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $paginationInfo['current_page'] + 1]) }}">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        @endif
                    </ul>
                </nav>
                
                <div class="text-center text-muted mt-2">
                    <small>
                        Menampilkan {{ (($paginationInfo['current_page'] - 1) * $paginationInfo['per_page']) + 1 }} 
                        - {{ min($paginationInfo['current_page'] * $paginationInfo['per_page'], $paginationInfo['total']) }} 
                        dari {{ $paginationInfo['total'] }} data pelatihan
                    </small>
                </div>
            @endif
            @else
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="empty-state mb-4">
                        <i class="fas fa-graduation-cap fa-4x text-muted opacity-50"></i>
                    </div>
                    <h4 class="text-muted mb-3">Belum ada data pelatihan</h4>
                    <p class="text-muted lead mb-4">Data pelatihan akan muncul di sini setelah ditambahkan.</p>
                    @if(is_admin() || is_hrd())
                    <a href="{{ route('trainings.create') }}" class="btn btn-primary btn-lg px-4 py-2">
                        <i class="fas fa-plus me-2"></i>Tambah Pelatihan Pertama
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.hover-card {
    transition: all 0.3s ease;
    overflow: hidden;
    border-radius: 12px;
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
}
.bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
}
.bg-gradient-light {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}
.alert-sm {
    font-size: 0.85rem;
    padding: 0.5rem;
    margin-bottom: 0;
}
.card-title {
    font-weight: 600;
}
.card-header .badge {
    font-size: 0.75rem;
    padding: 0.5em 0.75em;
}
.dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}
.dropdown-item i {
    width: 20px;
}
</style>
@endpush