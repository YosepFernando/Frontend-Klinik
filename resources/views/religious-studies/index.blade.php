@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-cash-register me-2"></i>Data Penggajian</h2>
                <div class="text-muted">
                    <small>Hanya dapat melihat dan mengedit data penggajian</small>
                </div>
            </div>

            <!-- Filter & Search -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('religious-studies.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Cari Penggajian</label>
                            <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                                   placeholder="Judul penggajian...">
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
                                <option value="{{ is_array($leader) ? ($leader['id'] ?? '') : ($leader->id ?? '') }}" {{ request('leader_id') == (is_array($leader) ? ($leader['id'] ?? '') : ($leader->id ?? '')) ? 'selected' : '' }}>
                                    {{ is_array($leader) ? ($leader['name'] ?? 'Tidak ada nama') : ($leader->name ?? 'Tidak ada nama') }}
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
                                <span class="badge bg-{{ is_array($study) ? ($study['status'] == 'scheduled' ? 'info' : ($study['status'] == 'ongoing' ? 'warning' : ($study['status'] == 'completed' ? 'success' : 'danger'))) : ($study->status == 'scheduled' ? 'info' : ($study->status == 'ongoing' ? 'warning' : ($study->status == 'completed' ? 'success' : 'danger'))) }}">
                                    {{ is_array($study) ? ucfirst($study['status']) : ucfirst($study->status) }}
                                </span>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i>
                                {{ is_array($study) ? date('d M Y', strtotime($study['created_at'])) : $study->created_at->format('d M Y') }}
                            </small>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ is_array($study) ? $study['title'] : $study->title }}</h5>
                            <p class="card-text text-muted">{{ Str::limit(is_array($study) ? $study['description'] : $study->description, 100) }}</p>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Pemateri</small>
                                    <strong>
                                        @if(is_array($study))
                                            {{ is_array($study['leader'] ?? []) ? ($study['leader']['name'] ?? 'N/A') : 'N/A' }}
                                        @else
                                            {{ $study->leader->name ?? 'N/A' }}
                                        @endif
                                    </strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Lokasi</small>
                                    <strong>{{ is_array($study) ? $study['location'] : $study->location }}</strong>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Tanggal</small>
                                    <strong>{{ is_array($study) ? date('d M Y', strtotime($study['study_date'])) : $study->study_date->format('d M Y') }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Waktu</small>
                                    <strong>{{ is_array($study) ? $study['start_time'] : $study->start_time }} - {{ is_array($study) ? $study['end_time'] : $study->end_time }}</strong>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <small class="text-muted d-block">Peserta</small>
                                    <strong>
                                        @php
                                            $participantCount = is_array($study) 
                                                ? (is_array($study['participants'] ?? null) ? count($study['participants']) : 0) 
                                                : ($study->participants->count() ?? 0);
                                            $maxParticipants = is_array($study) ? ($study['max_participants'] ?? 0) : ($study->max_participants ?? 0);
                                        @endphp
                                        {{ $participantCount }}/{{ $maxParticipants }}
                                    </strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Kapasitas</small>
                                    <div class="progress" style="height: 8px;">
                                        @php
                                            $percentage = $maxParticipants > 0 ? ($participantCount / $maxParticipants) * 100 : 0;
                                        @endphp
                                        <div class="progress-bar bg-{{ $percentage >= 100 ? 'danger' : ($percentage >= 80 ? 'warning' : 'success') }}" 
                                             style="width: {{ min($percentage, 100) }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="btn-group w-100" role="group">
                                <a href="{{ route('religious-studies.show', is_array($study) ? $study['id'] : $study) }}" class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye me-1"></i>Detail
                                </a>
                                @if(is_admin() || is_hrd())
                                <a href="{{ route('religious-studies.edit', is_array($study) ? $study['id'] : $study) }}" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-edit me-1"></i>Edit
                                </a>
                                @endif
                                @php
                                    $userId = get_session_user_id();
                                    $isUserParticipant = is_array($study) 
                                        ? (is_array($study['participants'] ?? null) ? collect($study['participants'])->where('user_id', $userId)->count() > 0 : false) 
                                        : ($study->participants->contains('user_id', $userId));
                                @endphp
                                @if((is_array($study) ? $study['status'] : $study->status) == 'scheduled' && !$isUserParticipant)
                                <form action="{{ route('religious-studies.join', is_array($study) ? $study['id'] : $study) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-sm" 
                                            {{ $participantCount >= $maxParticipants ? 'disabled' : '' }}>
                                        <i class="fas fa-plus me-1"></i>Daftar
                                    </button>
                                </form>
                                @elseif($isUserParticipant)
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
                    <i class="fas fa-cash-register fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Belum ada data penggajian</h5>
                    <p class="text-muted">Data penggajian akan muncul di sini setelah ada yang menambahkan.</p>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        Fitur penggajian hanya dapat dilihat dan diedit, tidak dapat ditambah atau dihapus.
                    </small>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
