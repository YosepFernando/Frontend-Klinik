@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-graduation-cap me-2"></i>Detail Pelatihan</h2>
                <div>
                    @if(auth()->check() && (is_admin() || is_hrd()))
                    <a href="{{ route('trainings.edit', $training) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    @endif
                    <a href="{{ route('trainings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Training Info -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $training->judul }}</h5>
                            <span class="{{ $training->status_badge_class }} fs-6">
                                {{ $training->status_display }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Informasi Pelatihan</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Jenis Pelatihan:</strong></td>
                                            <td>
                                                <span class="{{ $training->jenis_badge_class }}">
                                                    {{ $training->jenis_display }}
                                                </span>
                                            </td>
                                        </tr>
                                        @if($training->jenis_pelatihan == 'offline')
                                        <tr>
                                            <td><strong>Lokasi:</strong></td>
                                            <td>
                                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                                {{ $training->link_url }}
                                            </td>
                                        </tr>
                                        @else
                                        <tr>
                                            <td><strong>{{ $training->jenis_pelatihan == 'video' ? 'Link Video:' : 'Link Dokumen:' }}</strong></td>
                                            <td>
                                                @if($training->link_url)
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-{{ $training->jenis_pelatihan == 'video' ? 'video' : 'file-alt' }} text-primary me-2"></i>
                                                    <a href="{{ $training->link_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-external-link-alt me-1"></i>
                                                        Akses {{ $training->jenis_pelatihan == 'video' ? 'Video' : 'Dokumen' }}
                                                    </a>
                                                </div>
                                                <small class="text-muted d-block mt-1">{{ $training->link_url }}</small>
                                                @else
                                                <span class="text-muted">Belum tersedia</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endif
                                        @if($training->durasi)
                                        <tr>
                                            <td><strong>Durasi:</strong></td>
                                            <td>
                                                <i class="fas fa-clock text-info me-1"></i>
                                                {{ $training->durasi_display }}
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="{{ $training->status_badge_class }}">
                                                    {{ $training->status_display }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Dibuat:</strong></td>
                                            <td>{{ $training->created_at->format('d M Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Akses Pelatihan</h6>
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle me-2"></i>Cara Mengakses:</h6>
                                        @if($training->jenis_pelatihan === 'offline')
                                            <p class="mb-2">
                                                <i class="fas fa-map-marker-alt me-2"></i>
                                                <strong>Lokasi:</strong> {{ $training->link_url }}
                                            </p>
                                            <p class="mb-0">
                                                <i class="fas fa-users me-2"></i>
                                                Pelatihan ini dilakukan secara tatap muka di lokasi yang telah ditentukan.
                                            </p>
                                        @elseif($training->jenis_pelatihan === 'video')
                                            @if($training->link_url)
                                            <p class="mb-2">
                                                <i class="fas fa-video me-2"></i>
                                                <strong>Video Online:</strong> Klik tombol akses di bawah untuk menonton video pelatihan.
                                            </p>
                                            <div class="text-center">
                                                <a href="{{ $training->link_url }}" target="_blank" class="btn btn-primary">
                                                    <i class="fas fa-play me-2"></i>Tonton Video
                                                </a>
                                            </div>
                                            @else
                                            <p class="mb-0 text-muted">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                Link video belum tersedia.
                                            </p>
                                            @endif
                                        @else
                                            @if($training->link_url)
                                            <p class="mb-2">
                                                <i class="fas fa-file-alt me-2"></i>
                                                <strong>Dokumen Online:</strong> Klik tombol akses di bawah untuk membaca materi pelatihan.
                                            </p>
                                            <div class="text-center">
                                                <a href="{{ $training->link_url }}" target="_blank" class="btn btn-primary">
                                                    <i class="fas fa-download me-2"></i>Akses Dokumen
                                                </a>
                                            </div>
                                            @else
                                            <p class="mb-0 text-muted">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                Link dokumen belum tersedia.
                                            </p>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-muted">Deskripsi Pelatihan</h6>
                                <div class="bg-light p-3 rounded">
                                    <p class="mb-0">{{ $training->deskripsi }}</p>
                                </div>
                            </div>

                            @if($training->jenis_pelatihan === 'offline' && $training->location_info)
                            <div class="mb-4">
                                <h6 class="text-muted">Informasi Lokasi</h6>
                                <div class="alert alert-warning">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <strong>Lokasi Pelatihan:</strong><br>
                                    {{ $training->location_info }}
                                    <br><br>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Pastikan Anda datang tepat waktu dan membawa perlengkapan yang diperlukan.
                                    </small>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Informasi Tambahan
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Jenis Pelatihan:</strong><br>
                                <span class="{{ $training->jenis_badge_class }}">{{ $training->jenis_display }}</span>
                            </div>
                            
                            @if($training->durasi)
                            <div class="mb-3">
                                <strong>Estimasi Durasi:</strong><br>
                                <i class="fas fa-clock text-info me-1"></i>{{ $training->durasi_display }}
                            </div>
                            @endif

                            <div class="mb-3">
                                <strong>Status:</strong><br>
                                <span class="{{ $training->status_badge_class }}">{{ $training->status_display }}</span>
                            </div>

                            <div class="mb-3">
                                <strong>Dibuat pada:</strong><br>
                                <small class="text-muted">{{ $training->created_at->format('d M Y H:i') }}</small>
                            </div>

                            @if($training->updated_at != $training->created_at)
                            <div class="mb-3">
                                <strong>Terakhir diupdate:</strong><br>
                                <small class="text-muted">{{ $training->updated_at->format('d M Y H:i') }}</small>
                            </div>
                            @endif
                        </div>
                    </div>

                    @if(($training->jenis_pelatihan === 'video' || $training->jenis_pelatihan === 'document') && $training->link_url)
                    <div class="card mt-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-external-link-alt me-2"></i>Akses Cepat
                            </h6>
                        </div>
                        <div class="card-body text-center">
                            <p class="text-muted mb-3">
                                Akses langsung ke materi pelatihan {{ $training->jenis_pelatihan === 'video' ? 'video' : 'dokumen' }}
                            </p>
                            <a href="{{ $training->link_url }}" target="_blank" class="btn btn-primary btn-lg">
                                <i class="fas fa-{{ $training->jenis_pelatihan === 'video' ? 'play' : 'download' }} me-2"></i>
                                {{ $training->jenis_pelatihan === 'video' ? 'Tonton Sekarang' : 'Buka Dokumen' }}
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
