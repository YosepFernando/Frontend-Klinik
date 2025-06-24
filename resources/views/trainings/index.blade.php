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
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('trainings.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Cari Pelatihan</label>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                                   placeholder="Judul pelatihan...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Jenis Pelatihan</label>
                            <select class="form-select" name="jenis_pelatihan">
                                <option value="">Semua Jenis</option>
                                <option value="video" {{ request('jenis_pelatihan') == 'video' ? 'selected' : '' }}>Video Online</option>
                                <option value="document" {{ request('jenis_pelatihan') == 'document' ? 'selected' : '' }}>Dokumen</option>
                                <option value="offline" {{ request('jenis_pelatihan') == 'offline' ? 'selected' : '' }}>Offline/Tatap Muka</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('trainings.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if($trainings->count() > 0)
            <div class="row">
                @foreach($trainings as $training)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <span class="{{ $training->status_badge_class }}">
                                    {{ $training->status_display }}
                                </span>
                                <span class="{{ $training->jenis_badge_class }} ms-1">
                                    {{ $training->jenis_display }}
                                </span>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $training->created_at->format('d M Y') }}
                            </small>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $training->judul }}</h5>
                            <p class="card-text text-muted">{{ Str::limit($training->deskripsi, 100) }}</p>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Jenis</small>
                                    <strong>{{ $training->jenis_display }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Durasi</small>
                                    <strong>{{ $training->durasi_display }}</strong>
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted d-block">Akses</small>
                                @if($training->jenis_pelatihan === 'offline')
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                        <strong>{{ $training->location_info }}</strong>
                                    </div>
                                @else
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-{{ $training->jenis_pelatihan === 'video' ? 'video' : 'file-alt' }} text-primary me-2"></i>
                                        <strong>{{ $training->jenis_pelatihan === 'video' ? 'Video Online' : 'Dokumen Online' }}</strong>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('trainings.show', $training) }}" class="btn btn-primary">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </a>
                                @if(Auth::user()->isAdmin() || Auth::user()->isHRD())
                                <a href="{{ route('trainings.edit', $training) }}" class="btn btn-outline-warning">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            {{ $trainings->withQueryString()->links() }}
            @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada data pelatihan</h5>
                    <p class="text-muted">Data pelatihan akan muncul di sini setelah ditambahkan.</p>
                    @if(Auth::user()->isAdmin() || Auth::user()->isHRD())
                    <a href="{{ route('trainings.create') }}" class="btn btn-primary">
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
