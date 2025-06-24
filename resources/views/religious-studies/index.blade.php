@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-mosque me-2"></i>Data Pengajian</h2>
                <div class="text-muted">
                    <small>Hanya dapat melihat dan mengedit data pengajian</small>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('religious-studies.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Cari Pengajian</label>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                                   placeholder="Judul pengajian...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                                <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Berlangsung</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pemateri</label>
                            <select class="form-select" name="leader_id">
                                <option value="">Semua Pemateri</option>
                                @foreach($leaders ?? [] as $leader)
                                <option value="{{ $leader->id }}" {{ request('leader_id') == $leader->id ? 'selected' : '' }}>
                                    {{ $leader->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('religious-studies.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if($religiousStudies->count() > 0)
            <div class="row">
                @foreach($religiousStudies as $study)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-{{ $study->status == 'scheduled' ? 'info' : ($study->status == 'ongoing' ? 'warning' : ($study->status == 'completed' ? 'success' : 'danger')) }}">
                                    {{ ucfirst($study->status) }}
                                </span>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                {{ $study->created_at->format('d M Y') }}
                            </small>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $study->title }}</h5>
                            <p class="card-text text-muted">{{ Str::limit($study->description, 100) }}</p>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Pemateri</small>
                                    <strong>{{ $study->leader->name ?? 'N/A' }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Lokasi</small>
                                    <strong>{{ $study->location }}</strong>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Tanggal</small>
                                    <strong>{{ $study->study_date->format('d M Y') }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Waktu</small>
                                    <strong>{{ $study->start_time }} - {{ $study->end_time }}</strong>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Peserta</small>
                                    <strong>{{ $study->participants->count() }}/{{ $study->max_participants }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Kapasitas</small>
                                    <div class="progress" style="height: 8px;">
                                        @php
                                            $percentage = $study->max_participants > 0 ? ($study->participants->count() / $study->max_participants) * 100 : 0;
                                        @endphp
                                        <div class="progress-bar bg-{{ $percentage >= 100 ? 'danger' : ($percentage >= 80 ? 'warning' : 'success') }}" 
                                             style="width: {{ min($percentage, 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('religious-studies.show', $study) }}" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </a>
                                @if(Auth::user()->isAdmin() || Auth::user()->isHRD())
                                <a href="{{ route('religious-studies.edit', $study) }}" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                @endif
                                @if($study->status == 'scheduled' && !$study->participants->contains('user_id', auth()->id()))
                                <form action="{{ route('religious-studies.join', $study) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-sm" 
                                            {{ $study->participants->count() >= $study->max_participants ? 'disabled' : '' }}>
                                        <i class="fas fa-plus me-1"></i>Daftar
                                    </button>
                                </form>
                                @elseif($study->participants->contains('user_id', auth()->id()))
                                <button class="btn btn-success btn-sm" disabled>
                                    <i class="fas fa-check me-1"></i>Terdaftar
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            {{ $religiousStudies->withQueryString()->links() }}
            @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-mosque fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada data pengajian</h5>
                    <p class="text-muted">Data pengajian akan muncul di sini setelah ada yang menambahkan.</p>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Fitur pengajian hanya dapat dilihat dan diedit, tidak dapat ditambah atau dihapus.
                    </small>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
