@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-graduation-cap me-2"></i>Data Pelatihan</h2>
                @if(Auth::user()->isAdmin() || Auth::user()->isHRD())
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
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Jenis Pelatihan</label>
                            <select class="form-select" name="jenis_pelatihan">
                                <option value="">Semua Jenis</option>
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
            
            @if($trainings->count() > 0)
            <div class="row">
                @foreach($trainings as $training)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 border-0 shadow-sm hover-card">
                        <div class="card-header bg-gradient-primary text-white border-bottom-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="{{ $training->status_badge_class }}">
                                        <i class="fas fa-{{ $training->status === 'active' ? 'check-circle' : 'times-circle' }}"></i>
                                        {{ $training->status_display }}
                                    </span>
                                    <span class="{{ $training->jenis_badge_class }} ms-1">
                                        {{ $training->jenis_display }}
                                    </span>
                                </div>
                                @if(Auth::user()->isAdmin() || Auth::user()->isHRD())
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v text-dark"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('trainings.edit', $training) }}">
                                            <i class="fas fa-edit"></i> Edit
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('trainings.destroy', $training) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pelatihan ini?')">
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
                                <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                                <h5 class="card-title mb-0">{{ $training->judul }}</h5>
                            </div>
                        </div>
                        
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> Durasi
                                    </small>
                                    <span class="fw-bold text-primary">{{ $training->durasi_display }}</span>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-{{ $training->jenis_pelatihan === 'video' ? 'video' : ($training->jenis_pelatihan === 'document' ? 'file-alt' : 'map-marker-alt') }}"></i>
                                         {{ $training->jenis_pelatihan === 'offline' ? 'Lokasi' : 'Jenis' }}
                                    </small>
                                    <span class="fw-bold {{ $training->jenis_pelatihan === 'offline' ? 'text-danger' : 'text-success' }}">
                                        {{ $training->jenis_pelatihan === 'offline' ? $training->location_info : ($training->jenis_pelatihan === 'video' ? 'Video Online' : 'Dokumen Online') }}
                                    </span>
                                </div>
                            </div>
                            <p class="card-text text-muted">{{ Str::limit($training->deskripsi, 120) }}</p>
                            
                            @if($training->created_at->isToday())
                                <div class="alert alert-success alert-sm p-2">
                                    <i class="fas fa-certificate"></i>
                                    <small>Baru ditambahkan hari ini!</small>
                                </div>
                            @endif
                        </div>
                        
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-grid">
                                <a href="{{ route('trainings.show', $training) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <!-- Pagination -->
            {{ $trainings->withQueryString()->links() }}
            @else
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="empty-state mb-4">
                        <i class="fas fa-graduation-cap fa-4x text-muted opacity-50"></i>
                    </div>
                    <h4 class="text-muted mb-3">Belum ada data pelatihan</h4>
                    <p class="text-muted lead mb-4">Data pelatihan akan muncul di sini setelah ditambahkan.</p>
                    @if(Auth::user()->isAdmin() || Auth::user()->isHRD())
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