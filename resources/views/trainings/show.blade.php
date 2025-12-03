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
                    <a href="{{ route('trainings.verify-proof', $training->id_pelatihan) }}" class="btn btn-info me-2">
                        <i class="fas fa-check-circle me-1"></i>Verifikasi Bukti
                    </a>
                    @else
                        {{-- Check if current user is participant - passed from controller --}}
                        @if(isset($isParticipant) && $isParticipant)
                        <a href="{{ route('trainings.upload-proof', $training->id_pelatihan) }}" class="btn btn-success me-2">
                            <i class="fas fa-upload me-1"></i>Upload Bukti
                        </a>
                        @endif
                    @endif
                    <a href="{{ route('trainings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>

            <div class="row justify-content-center">
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
                                            <td><strong>
                                                @if($training->jenis_pelatihan == 'video')
                                                    Link Video:
                                                @elseif($training->jenis_pelatihan == 'online')
                                                    Link Meeting:
                                                @else
                                                    Link Dokumen:
                                                @endif
                                            </strong></td>
                                            <td>
                                                @if($training->link_url)
                                                <div class="d-flex align-items-center">
                                                    @php
                                                        $icon = 'file-alt';
                                                        $label = 'Dokumen';
                                                        if($training->jenis_pelatihan == 'video') {
                                                            $icon = 'video';
                                                            $label = 'Video';
                                                        } elseif($training->jenis_pelatihan == 'online') {
                                                            $icon = 'video-camera';
                                                            $label = 'Meeting';
                                                        }
                                                    @endphp
                                                    <i class="fas fa-{{ $icon }} text-primary me-2"></i>
                                                    <a href="{{ $training->link_url }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-external-link-alt me-1"></i>
                                                        Akses {{ $label }}
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
                                        @if($training->jadwal_pelatihan)
                                        <tr>
                                            <td><strong>Jadwal Pelatihan:</strong></td>
                                            <td>
                                                <i class="fas fa-calendar-alt text-primary me-1"></i>
                                                <span class="fw-bold">{{ $training->jadwal_formatted }}</span>
                                                @php
                                                    $jadwal = \Carbon\Carbon::parse($training->jadwal_pelatihan);
                                                    $now = \Carbon\Carbon::now();
                                                @endphp
                                                @if($jadwal->isPast())
                                                    <span class="badge bg-secondary ms-2">Selesai</span>
                                                @elseif($jadwal->isToday())
                                                    <span class="badge bg-danger ms-2">Hari Ini</span>
                                                @elseif($jadwal->isTomorrow())
                                                    <span class="badge bg-warning ms-2">Besok</span>
                                                @elseif($jadwal->diffInDays($now) <= 7)
                                                    <span class="badge bg-info ms-2">Minggu Ini</span>
                                                @endif
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
                                        @elseif($training->jenis_pelatihan === 'online')
                                            @if($training->link_url)
                                            <p class="mb-2">
                                                <i class="fas fa-video-camera me-2"></i>
                                                <strong>Meeting Online:</strong> Klik tombol akses di bawah untuk bergabung ke meeting.
                                            </p>
                                            <div class="text-center">
                                                <a href="{{ $training->link_url }}" target="_blank" class="btn btn-primary">
                                                    <i class="fas fa-video-camera me-2"></i>Join Meeting
                                                </a>
                                            </div>
                                            @else
                                            <p class="mb-0 text-muted">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                Link meeting belum tersedia.
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
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                        Pastikan Anda datang tepat waktu dan membawa perlengkapan yang diperlukan.
                                    </small>
                                </div>
                            </div>
                            @endif
                            
                            {{-- Upload Bukti Button untuk Pegawai --}}
                            @if(!is_admin() && !is_hrd() && isset($isParticipant) && $isParticipant)
                            <div class="mb-4">
                                <h6 class="text-muted">Bukti Kehadiran</h6>
                                
                                @if(isset($userProof) && $userProof)
                                    {{-- User sudah upload bukti --}}
                                    @php
                                        $statusVerifikasi = $userProof['status_verifikasi'] ?? 'menunggu';
                                        $badgeClass = 'bg-warning';
                                        $iconClass = 'fa-clock';
                                        $statusText = 'Menunggu Verifikasi';
                                        $alertClass = 'alert-warning';
                                        
                                        if ($statusVerifikasi === 'disetujui') {
                                            $badgeClass = 'bg-success';
                                            $iconClass = 'fa-check-circle';
                                            $statusText = 'Disetujui';
                                            $alertClass = 'alert-success';
                                        } elseif ($statusVerifikasi === 'ditolak') {
                                            $badgeClass = 'bg-danger';
                                            $iconClass = 'fa-times-circle';
                                            $statusText = 'Ditolak';
                                            $alertClass = 'alert-danger';
                                        }
                                    @endphp
                                    
                                    <div class="alert {{ $alertClass }}">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas {{ $iconClass }} fa-2x me-3"></i>
                                            <div>
                                                <h6 class="mb-1">Status Bukti: <span class="badge {{ $badgeClass }}">{{ $statusText }}</span></h6>
                                                <small>
                                                    @if($statusVerifikasi === 'menunggu')
                                                        Bukti kehadiran Anda sedang dalam proses verifikasi oleh admin/HRD.
                                                    @elseif($statusVerifikasi === 'disetujui')
                                                        Selamat! Bukti kehadiran Anda telah diverifikasi dan disetujui.
                                                    @else
                                                        Bukti kehadiran Anda ditolak. Silakan upload ulang bukti yang valid.
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                        
                                        @if(isset($userProof['bukti_path']) && $userProof['bukti_path'])
                                        <div class="mt-2">
                                            <strong>File:</strong> 
                                            <a href="{{ asset('storage/' . $userProof['bukti_path']) }}" target="_blank" class="text-decoration-underline">
                                                <i class="fas fa-file-alt me-1"></i>Lihat Bukti
                                            </a>
                                        </div>
                                        @endif
                                        
                                        @if(isset($userProof['keterangan']) && $userProof['keterangan'])
                                        <div class="mt-2">
                                            <strong>Keterangan:</strong> {{ $userProof['keterangan'] }}
                                        </div>
                                        @endif
                                        
                                        @if($statusVerifikasi === 'ditolak')
                                            @if(isset($userProof['catatan_verifikasi']) && $userProof['catatan_verifikasi'])
                                            <div class="mt-2">
                                                <strong>Alasan Penolakan:</strong> 
                                                <span class="text-danger">{{ $userProof['catatan_verifikasi'] }}</span>
                                            </div>
                                            @endif
                                            <div class="text-center mt-3">
                                                <a href="{{ route('trainings.upload-proof', $training->id_pelatihan) }}" class="btn btn-danger">
                                                    <i class="fas fa-redo me-2"></i>Upload Ulang Bukti
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    {{-- User belum upload bukti --}}
                                    <div class="alert alert-info">
                                        <p class="mb-2">
                                            <i class="fas fa-upload me-2"></i>
                                            Anda terdaftar sebagai peserta pelatihan ini. Silakan upload bukti kehadiran Anda setelah mengikuti pelatihan.
                                        </p>
                                        <div class="text-center mt-3">
                                            <a href="{{ route('trainings.upload-proof', $training->id_pelatihan) }}" class="btn btn-success">
                                                <i class="fas fa-upload me-2"></i>Upload Bukti Kehadiran
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Daftar Peserta untuk Admin/HRD --}}
                    @if(is_admin() || is_hrd())
                    <div class="card mt-4">
                        <div class="card-header bg-gradient-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-users me-2"></i>Daftar Peserta Pelatihan
                            </h5>
                        </div>
                        <div class="card-body">
                            @if(count($participants ?? []) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="25%">Nama Pegawai</th>
                                            <th width="20%">Posisi</th>
                                            <th width="15%">Status Kehadiran</th>
                                            <th width="20%">Bukti Pelatihan</th>
                                            <th width="15%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($participants as $index => $participant)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $participant['pegawai']['nama_lengkap'] ?? 'N/A' }}</strong>
                                            </td>
                                            <td>{{ $participant['pegawai']['posisi']['nama_posisi'] ?? 'N/A' }}</td>
                                            <td>
                                                @php
                                                    $status = $participant['status_kehadiran'] ?? 'terdaftar';
                                                    $badgeClass = 'badge bg-secondary';
                                                    if ($status === 'hadir') {
                                                        $badgeClass = 'badge bg-success';
                                                    } elseif ($status === 'tidak_hadir') {
                                                        $badgeClass = 'badge bg-danger';
                                                    } elseif ($status === 'terdaftar') {
                                                        $badgeClass = 'badge bg-info';
                                                    }
                                                @endphp
                                                <span class="{{ $badgeClass }}">
                                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if(isset($participant['bukti_pelatihan']) && count($participant['bukti_pelatihan']) > 0)
                                                    @php
                                                        $bukti = $participant['bukti_pelatihan'][0];
                                                        $statusBukti = $bukti['status_verifikasi'] ?? 'menunggu';
                                                        $badgeBuktiClass = 'badge bg-warning';
                                                        if ($statusBukti === 'disetujui') {
                                                            $badgeBuktiClass = 'badge bg-success';
                                                        } elseif ($statusBukti === 'ditolak') {
                                                            $badgeBuktiClass = 'badge bg-danger';
                                                        }
                                                    @endphp
                                                    <span class="{{ $badgeBuktiClass }}">
                                                        <i class="fas fa-file-alt me-1"></i>
                                                        {{ ucfirst($statusBukti) }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-minus-circle me-1"></i>
                                                        Belum Upload
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($participant['bukti_pelatihan']) && count($participant['bukti_pelatihan']) > 0)
                                                    <a href="{{ route('trainings.verify-proof', $training->id_pelatihan) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye me-1"></i>Lihat Bukti
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-info-circle fa-2x mb-2"></i>
                                <p class="mb-0">Belum ada peserta terdaftar untuk pelatihan ini.</p>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
}

.table td {
    vertical-align: middle;
}

.alert-info .btn-success {
    box-shadow: 0 4px 6px rgba(40, 167, 69, 0.3);
    transition: all 0.3s ease;
}

.alert-info .btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(40, 167, 69, 0.4);
}

.alert-success {
    border-left: 4px solid #28a745;
}

.alert-warning {
    border-left: 4px solid #ffc107;
}

.alert-danger {
    border-left: 4px solid #dc3545;
}

.alert h6 {
    margin-bottom: 0.5rem;
}

.alert .badge {
    font-size: 0.9rem;
    padding: 0.4em 0.8em;
}

.btn-danger {
    box-shadow: 0 4px 6px rgba(220, 53, 69, 0.3);
    transition: all 0.3s ease;
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 8px rgba(220, 53, 69, 0.4);
}
</style>
@endpush

@endsection
