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
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Cari Pelatihan</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" name="search" value="{{ request('search') }}"
                                       placeholder="Judul pelatihan...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Jenis Pelatihan</label>
                            <select class="form-select" name="jenis_pelatihan">
                                <option value="">Semua Jenis</option>
                                <option value="Internal" {{ request('jenis_pelatihan') == 'Internal' ? 'selected' : '' }}>Internal</option>
                                <option value="Eksternal" {{ request('jenis_pelatihan') == 'Eksternal' ? 'selected' : '' }}>Eksternal</option>
                                <option value="video" {{ request('jenis_pelatihan') == 'video' ? 'selected' : '' }}>Video Online</option>
                                <option value="document" {{ request('jenis_pelatihan') == 'document' ? 'selected' : '' }}>Dokumen</option>
                                <option value="zoom" {{ request('jenis_pelatihan') == 'zoom' ? 'selected' : '' }}>
                                    <i class="fas fa-video-camera"></i> Zoom Meeting
                                </option>
                                <option value="video/meet" {{ request('jenis_pelatihan') == 'video/meet' ? 'selected' : '' }}>Video/Meeting Online</option>
                                <option value="video/online meet" {{ request('jenis_pelatihan') == 'video/online meet' ? 'selected' : '' }}>Video Online Meet</option>
                                <option value="offline" {{ request('jenis_pelatihan') == 'offline' ? 'selected' : '' }}>Offline/Tatap Muka</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Status Pelatihan</label>
                            <select class="form-select" name="status_filter">
                                <option value="">Semua Status</option>
                                <option value="upcoming" {{ request('status_filter') == 'upcoming' ? 'selected' : '' }}>
                                    <i class="fas fa-clock"></i> Akan Datang
                                </option>
                                <option value="past" {{ request('status_filter') == 'past' ? 'selected' : '' }}>
                                    <i class="fas fa-check"></i> Sudah Selesai
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
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
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(isset($trainingsData) && count($trainingsData) > 0)
            
            <!-- Filter Status Info -->
            @if(request('status_filter') || request('search') || request('jenis_pelatihan'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Filter Aktif:</strong> 
                @if(request('status_filter'))
                    Status: <span class="badge bg-primary">{{ request('status_filter') === 'upcoming' ? 'Akan Datang' : 'Sudah Selesai' }}</span>
                @endif
                @if(request('search'))
                    Pencarian: <span class="badge bg-secondary">"{{ request('search') }}"</span>
                @endif
                @if(request('jenis_pelatihan'))
                    Jenis: <span class="badge bg-info">{{ ucfirst(request('jenis_pelatihan')) }}</span>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif
            
            <div class="row">
                @foreach($trainingsData as $training)
                <div class="col-lg-4 col-md-6 mb-4">
                    @php
                        $is_zoom = isset($training['jenis_pelatihan']) && $training['jenis_pelatihan'] === 'zoom';
                        $card_class = $is_zoom ? 'card h-100 border-0 shadow-sm hover-card zoom-meeting-card position-relative' : 'card h-100 border-0 shadow-sm hover-card';
                        $header_class = $is_zoom ? 'card-header bg-gradient-zoom text-white border-bottom-0' : 'card-header bg-gradient-primary text-white border-bottom-0';
                        
                        // Check if it's an upcoming Zoom meeting
                        $is_upcoming = false;
                        $is_past = false;
                        $time_until_meeting = null;
                        $time_status = 'normal';
                        $jadwal_pelatihan = $training['jadwal_pelatihan'] ?? null;
                        
                        if ($jadwal_pelatihan) {
                            try {
                                $jadwal = \Carbon\Carbon::parse($jadwal_pelatihan);
                                $now = \Carbon\Carbon::now();
                                
                                if ($jadwal->isPast()) {
                                    $is_past = true;
                                    $time_status = 'past';
                                    $card_class .= ' training-past';
                                    $header_class = 'card-header bg-gradient-secondary text-white border-bottom-0';
                                } elseif ($is_zoom && $jadwal->isFuture() && $jadwal->diffInDays($now) <= 7) {
                                    $is_upcoming = true;
                                    $time_status = 'upcoming';
                                    
                                    if ($jadwal->isToday()) {
                                        $time_until_meeting = 'Hari ini, ' . $jadwal->format('H:i');
                                    } else if ($jadwal->isTomorrow()) {
                                        $time_until_meeting = 'Besok, ' . $jadwal->format('H:i');
                                    } else {
                                        $time_until_meeting = $jadwal->format('d M, H:i');
                                    }
                                }
                            } catch (Exception $e) {
                                // Do nothing if parsing fails
                            }
                        }
                    @endphp
                    
                    @if($is_upcoming)
                    <div class="upcoming-meeting-badge">
                        <i class="fas fa-calendar-alt me-1"></i> Akan Datang
                    </div>
                    @elseif($is_past)
                    <div class="past-meeting-badge">
                        <i class="fas fa-clock me-1"></i> Sudah Selesai
                    </div>
                    @endif
                    
                    <div class="{{ $card_class }}">
                        <div class="{{ $header_class }}">
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
                                    
                                    @if($is_zoom)
                                        <span class="badge bg-zoom-pulse ms-1">
                                            <i class="fas fa-video-camera fa-pulse"></i> 
                                            {{ $jenis_display }}
                                        </span>
                                    @else
                                        <span class="{{ $jenis_badge_class }} ms-1">
                                            {{ $jenis_display }}
                                        </span>
                                    @endif
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-ellipsis-v text-dark"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @php
                                            $training_id = $training['id'] ?? $training['id_pelatihan'] ?? null;
                                        @endphp
                                        @if($training_id)
                                            <li>
                                                <a class="dropdown-item" href="{{ route('trainings.show', $training_id) }}">
                                                    <i class="fas fa-eye me-2"></i>Lihat Detail
                                                </a>
                                            </li>
                                            @if(is_admin() || is_hrd())
                                            <li>
                                                <a class="dropdown-item" href="{{ route('trainings.edit', $training_id) }}">
                                                    <i class="fas fa-edit me-2"></i>Edit Pelatihan
                                                </a>
                                            </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('trainings.destroy', $training_id) }}" method="POST" class="d-inline w-100" onsubmit="return confirmDelete('{{ $training['judul'] ?? 'pelatihan ini' }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="fas fa-trash me-2"></i>Hapus Pelatihan
                                                    </button>
                                                </form>
                                            </li>
                                        @else
                                            <li><span class="dropdown-item-text text-muted">
                                                <i class="fas fa-exclamation-triangle me-2"></i>ID tidak tersedia
                                            </span></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                            <div class="mt-2">
                                @if($is_zoom)
                                <i class="fas fa-video-camera fa-2x mb-2 zoom-icon-glow"></i>
                                @else
                                <i class="fas fa-graduation-cap fa-2x mb-2"></i>
                                @endif
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
                                            $now = \Carbon\Carbon::now();
                                            
                                            if ($jadwal->isPast()) {
                                                $jadwal_display = 'Selesai pada ' . $jadwal->format('d M Y, H:i');
                                                $text_class = 'text-muted text-decoration-line-through';
                                            } elseif ($jadwal->isToday()) {
                                                $jadwal_display = 'Hari ini, ' . $jadwal->format('H:i');
                                                $text_class = 'text-danger fw-bold';
                                            } elseif ($jadwal->isTomorrow()) {
                                                $jadwal_display = 'Besok, ' . $jadwal->format('H:i');
                                                $text_class = 'text-warning fw-bold';
                                            } elseif ($jadwal->isFuture() && $jadwal->diffInDays($now) < 7) {
                                                $jadwal_display = $jadwal->format('l, d M H:i');
                                                $text_class = 'text-info fw-bold';
                                            } else {
                                                $jadwal_display = $jadwal->format('d M Y, H:i');
                                                $text_class = 'text-info';
                                            }
                                            
                                        } catch (Exception $e) {
                                            $jadwal_display = 'Format tanggal tidak valid';
                                            $text_class = 'text-secondary';
                                        }
                                    @endphp
                                    <span class="fw-bold {{ $text_class }}">{{ $jadwal_display }}</span>
                                </div>
                                @else
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> Jadwal
                                    </small>
                                    <span class="fw-bold text-secondary">Belum dijadwalkan</span>
                                </div>
                                @endif
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    @php
                                        $jenis_pelatihan = $training['jenis_pelatihan'] ?? 'offline';
                                        $location_info = $training['link_url'] ?? 'Lokasi tidak tersedia';
                                        
                                        // Tentukan icon berdasarkan jenis pelatihan
                                        $icon = 'map-marker-alt'; // default untuk offline
                                        if ($jenis_pelatihan === 'video') {
                                            $icon = 'video';
                                        } elseif ($jenis_pelatihan === 'document') {
                                            $icon = 'file-alt';
                                        } elseif ($jenis_pelatihan === 'zoom') {
                                            $icon = 'video-camera';
                                        }
                                        
                                        // Tentukan label berdasarkan jenis pelatihan
                                        $label = 'Lokasi';
                                        if (in_array($jenis_pelatihan, ['video', 'document', 'zoom'])) {
                                            $label = 'Akses';
                                        }
                                        
                                        // Tentukan text yang ditampilkan
                                        $display_text = $location_info;
                                        if ($jenis_pelatihan === 'video') {
                                            $display_text = 'Video Online';
                                        } elseif ($jenis_pelatihan === 'document') {
                                            $display_text = 'Dokumen Online';
                                        } elseif ($jenis_pelatihan === 'zoom') {
                                            $display_text = 'Zoom Meeting';
                                        } elseif ($jenis_pelatihan === 'offline') {
                                            $display_text = $location_info ? Str::limit($location_info, 25) : 'Lokasi belum ditentukan';
                                        }
                                        
                                        // Tentukan warna text
                                        $text_color = 'text-danger'; // default untuk offline
                                        if (in_array($jenis_pelatihan, ['video', 'document', 'zoom'])) {
                                            $text_color = 'text-success';
                                        }
                                    @endphp
                                    <small class="text-muted">
                                        <i class="fas fa-{{ $icon }}"></i>
                                         {{ $label }}
                                    </small>
                                    <span class="fw-bold {{ $text_color }}" title="{{ $jenis_pelatihan === 'offline' && $location_info ? $location_info : '' }}">
                                        {{ $display_text }}
                                    </span>
                                </div>
                                
                                @if(isset($training['link_url']) && $training['link_url'] && in_array($jenis_pelatihan, ['video', 'document', 'zoom']))
                                    @if($jenis_pelatihan === 'zoom')
                                    <div class="mt-3">
                                        @if($is_upcoming && $time_until_meeting)
                                        <div class="zoom-countdown mb-3">
                                            <div class="d-flex justify-content-between">
                                                <span>
                                                    <i class="fas fa-clock me-1"></i> <strong>Meeting Time</strong>
                                                </span>
                                                <span class="fw-bold">{{ $time_until_meeting }}</span>
                                            </div>
                                        </div>
                                        @endif
                                        <a href="{{ $training['link_url'] }}" target="_blank" class="btn btn-zoom-meeting btn-sm w-100">
                                            <i class="fas fa-video-camera me-2"></i> Join Zoom Meeting
                                        </a>
                                    </div>
                                    @else
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
                            <div class="d-grid gap-2">
                                @php
                                    $training_id = $training['id'] ?? $training['id_pelatihan'] ?? null;
                                @endphp
                                @if($training_id)
                                    <a href="{{ route('trainings.show', $training_id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i> Lihat Detail
                                    </a>
                                    @if($is_past)
                                    <div class="row g-2">
                                        @if(is_admin() || is_hrd())
                                        <div class="col-4">
                                            <a href="{{ route('trainings.edit', $training_id) }}" class="btn btn-warning btn-sm w-100">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </a>
                                        </div>
                                        @endif
                                        <div class="{{ (is_admin() || is_hrd()) ? 'col-8' : 'col-12' }}">
                                            <form action="{{ route('trainings.destroy', $training_id) }}" method="POST" class="d-inline w-100" onsubmit="return confirmDelete('{{ $training['judul'] ?? 'pelatihan ini' }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                                    <i class="fas fa-trash me-1"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    @else
                                    <div class="row g-2">
                                        @if(is_admin() || is_hrd())
                                        <div class="col-6">
                                            <a href="{{ route('trainings.edit', $training_id) }}" class="btn btn-warning btn-sm w-100">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </a>
                                        </div>
                                        <div class="col-6">
                                            <form action="{{ route('trainings.destroy', $training_id) }}" method="POST" class="d-inline w-100" onsubmit="return confirmDelete('{{ $training['judul'] ?? 'pelatihan ini' }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm w-100">
                                                    <i class="fas fa-trash me-1"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                        @else
                                        <form action="{{ route('trainings.destroy', $training_id) }}" method="POST" class="d-inline w-100" onsubmit="return confirmDelete('{{ $training['judul'] ?? 'pelatihan ini' }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                                <i class="fas fa-trash me-1"></i> Hapus Pelatihan
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                    @endif
                                @else
                                    <button class="btn btn-outline-secondary btn-sm" disabled>
                                        <i class="fas fa-eye me-1"></i> Detail tidak tersedia
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
                
                <div class="text-center text-muted mt-3">
                    <div class="pagination-info">
                        <i class="fas fa-info-circle me-1"></i>
                        Menampilkan {{ (($paginationInfo['current_page'] - 1) * $paginationInfo['per_page']) + 1 }} 
                        - {{ min($paginationInfo['current_page'] * $paginationInfo['per_page'], $paginationInfo['total']) }} 
                        dari {{ $paginationInfo['total'] }} data pelatihan
                        (Halaman {{ $paginationInfo['current_page'] }} dari {{ $paginationInfo['last_page'] }})
                    </div>
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

/* Dropdown menu improvements */
.dropdown-menu {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    transform: translateX(2px);
    transition: all 0.2s ease;
}

.dropdown-item.text-danger:hover {
    background-color: #f8d7da;
    color: #721c24 !important;
}

/* Badge for zoom meeting */
.badge.bg-info {
    background: linear-gradient(45deg, #17a2b8, #138496) !important;
    color: white;
}

/* Special styling for zoom links */
.text-primary:hover {
    color: #0056b3 !important;
    text-decoration: underline !important;
}

/* Icon colors for different training types */
.fa-video-camera {
    color: #007bff;
}

.fa-video {
    color: #28a745;
}

.fa-file-alt {
    color: #ffc107;
}

.fa-map-marker-alt {
    color: #dc3545;
}

/* Enhanced Zoom Meeting Card Styling */
.zoom-meeting-card {
    border: 2px solid #2D8CFF !important;
    box-shadow: 0 8px 30px rgba(45, 140, 255, 0.2) !important;
}

.bg-gradient-zoom {
    background: linear-gradient(135deg, #2D8CFF 0%, #0E71EB 100%) !important;
}

.btn-zoom-meeting {
    background-color: #2D8CFF;
    color: white;
    border-radius: 50px;
    font-weight: bold;
    transition: all 0.3s ease;
    border: none;
    padding: 8px 16px;
    box-shadow: 0 4px 15px rgba(45, 140, 255, 0.3);
}

.btn-zoom-meeting:hover {
    background-color: #0E71EB;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(45, 140, 255, 0.4);
}

.badge.bg-zoom-pulse {
    background-color: #2D8CFF !important;
    color: white;
    animation: pulse-blue 2s infinite;
}

@keyframes pulse-blue {
    0% {
        box-shadow: 0 0 0 0 rgba(45, 140, 255, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(45, 140, 255, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(45, 140, 255, 0);
    }
}

.zoom-icon-glow {
    color: #2D8CFF;
    text-shadow: 0 0 10px rgba(45, 140, 255, 0.7);
    animation: zoom-icon-glow 2s infinite alternate;
}

@keyframes zoom-icon-glow {
    from {
        text-shadow: 0 0 5px rgba(45, 140, 255, 0.7);
    }
    to {
        text-shadow: 0 0 15px rgba(45, 140, 255, 0.9), 0 0 20px rgba(45, 140, 255, 0.5);
    }
}

/* Past meeting badge */
.past-meeting-badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background: linear-gradient(135deg, #6C757D 0%, #ADB5BD 100%);
    color: white;
    border-radius: 50px;
    padding: 5px 15px;
    font-size: 0.75rem;
    font-weight: bold;
    box-shadow: 0 4px 10px rgba(108, 117, 125, 0.3);
    z-index: 10;
}

/* Training past styling */
.training-past {
    opacity: 0.8;
    filter: grayscale(20%);
}

.training-past .card-header {
    background: linear-gradient(135deg, #6C757D 0%, #ADB5BD 100%) !important;
}

.bg-gradient-secondary {
    background: linear-gradient(135deg, #6C757D 0%, #ADB5BD 100%);
}
.upcoming-meeting-badge {
    position: absolute;
    top: -10px;
    right: -10px;
    background: linear-gradient(135deg, #FF5722 0%, #FF9800 100%);
    color: white;
    border-radius: 50px;
    padding: 5px 15px;
    font-size: 0.75rem;
    font-weight: bold;
    box-shadow: 0 4px 10px rgba(255, 87, 34, 0.3);
    z-index: 10;
    animation: pulse-orange 2s infinite;
}

@keyframes pulse-orange {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 87, 34, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(255, 87, 34, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 87, 34, 0);
    }
}

.zoom-countdown {
    background: rgba(45, 140, 255, 0.1);
    border-left: 3px solid #2D8CFF;
    padding: 8px 12px;
    margin-top: 10px;
    border-radius: 4px;
}

.zoom-countdown i {
    color: #2D8CFF;
}

/* Make zoom meeting cards slightly larger */
@media (min-width: 992px) {
    .zoom-meeting-card {
        transform: scale(1.02);
    }
    .zoom-meeting-card:hover {
        transform: translateY(-5px) scale(1.03);
    }
}

/* Pagination improvements */
.pagination {
    border-radius: 10px;
    margin-bottom: 0;
}

.page-link {
    border-radius: 8px;
    margin: 0 2px;
    border: none;
    color: #495057;
    padding: 0.5rem 0.75rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.page-link:hover {
    background-color: #e9ecef;
    color: #007bff;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.page-item.active .page-link {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border-color: #007bff;
    color: white;
    box-shadow: 0 3px 6px rgba(0, 123, 255, 0.3);
}

.page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
    opacity: 0.5;
}

/* Pagination navigation improvements */
.page-link[aria-label="Previous"],
.page-link[aria-label="Next"] {
    font-weight: 600;
}

.page-link i {
    font-size: 0.875rem;
}

/* Pagination info styling */
.pagination-info {
    font-size: 0.875rem;
    color: #6c757d;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 8px;
    border-left: 4px solid #007bff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    display: inline-block;
}

.pagination-info i {
    color: #007bff;
}

/* Responsive pagination */
@media (max-width: 576px) {
    .pagination {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .page-link {
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
        margin: 1px;
    }
    
    .pagination-info {
        text-align: center;
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
        margin: 0 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(trainingTitle) {
    const result = confirm(`Apakah Anda yakin ingin menghapus pelatihan "${trainingTitle}"?\n\nTindakan ini tidak dapat dibatalkan.`);
    
    if (result) {
        // Add loading state to prevent multiple submissions
        const submitButton = event.target.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menghapus...';
        }
    }
    
    return result;
}

// Add animation for cards
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.hover-card');
    
    cards.forEach((card, index) => {
        // Add staggered animation
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('animate-fade-in');
    });
    
    // Add pulse animation for past training cards
    const pastCards = document.querySelectorAll('.training-past');
    pastCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.opacity = '1';
            this.style.filter = 'grayscale(0%)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.opacity = '0.8';
            this.style.filter = 'grayscale(20%)';
        });
    });
});
</script>

<style>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out forwards;
    opacity: 0;
}

/* Smooth transitions for past training cards */
.training-past {
    transition: opacity 0.3s ease, filter 0.3s ease;
}
</style>
@endpush