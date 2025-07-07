@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $recruitment->position ?? 'N/A' }}</h4>
                    <div>
                        @if(is_admin() || is_hrd())
                            <a href="{{ route('recruitments.edit', $recruitment->id ?? 0) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                        <a href="{{ route('recruitments.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex flex-wrap gap-2">
                                @if(method_exists($recruitment, 'isOpen') && $recruitment->isOpen())
                                    <span class="badge bg-success fs-6">
                                        <i class="fas fa-check-circle"></i> Lowongan Terbuka
                                    </span>
                                @else
                                    <span class="badge bg-danger fs-6">
                                        <i class="fas fa-times-circle"></i> Lowongan Ditutup
                                    </span>
                                @endif
                                <span class="badge bg-info fs-6">{{ $recruitment->employment_type_display ?? 'N/A' }}</span>
                                <span class="badge bg-primary fs-6">{{ $recruitment->slots ?? 0 }} Posisi</span>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-1"><strong>Deadline Lamaran:</strong></p>
                            <p class="text-{{ isset($recruitment->application_deadline) && method_exists($recruitment->application_deadline, 'isPast') && $recruitment->application_deadline->isPast() ? 'danger' : 'success' }}">
                                <i class="fas fa-calendar"></i> {{ isset($recruitment->application_deadline) ? $recruitment->application_deadline->format('d F Y') : 'N/A' }}
                                @if(isset($recruitment->application_deadline) && method_exists($recruitment->application_deadline, 'isPast') && $recruitment->application_deadline->isPast())
                                    (Sudah Lewat)
                                @else
                                    @if(isset($recruitment->application_deadline) && method_exists($recruitment->application_deadline, 'diffForHumans'))
                                        ({{ $recruitment->application_deadline->diffForHumans() }})
                                    @endif
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="border-bottom pb-2 mb-3">Deskripsi Pekerjaan</h5>
                            <div class="mb-4">
                                {!! nl2br(e($recruitment->description ?? '')) !!}
                            </div>

                            <h5 class="border-bottom pb-2 mb-3">Persyaratan</h5>
                            <div class="mb-4">
                                {!! nl2br(e($recruitment->requirements ?? '')) !!}
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Informasi Lowongan</h6>
                                    
                                    <table class="table table-borderless table-sm">
                                        <tr>
                                            <td class="fw-bold">Posisi:</td>
                                            <td>{{ $recruitment->position ?? 'N/A' }}</td>
                                        </tr>
                                        @if(isset($recruitment->posisi) && $recruitment->posisi)
                                        <tr>
                                            <td class="fw-bold">Posisi Master:</td>
                                            <td>{{ $recruitment->posisi->nama_posisi ?? 'N/A' }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td class="fw-bold">Tipe:</td>
                                            <td>{{ $recruitment->employment_type_display ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Slots:</td>
                                            <td>{{ $recruitment->slots ?? 0 }} orang</td>
                                        </tr>
                                        @if(isset($recruitment->age_min) || isset($recruitment->age_max))
                                        <tr>
                                            <td class="fw-bold">Rentang Usia:</td>
                                            <td>{{ $recruitment->age_range ?? 'N/A' }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td class="fw-bold">Gaji:</td>
                                            <td>{{ $recruitment->salary_range ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Status:</td>
                                            <td>
                                                @if(isset($recruitment->status) && $recruitment->status === 'open')
                                                    <span class="badge bg-success">Buka</span>
                                                @else
                                                    <span class="badge bg-danger">Tutup</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Deadline:</td>
                                            <td>{{ isset($recruitment->application_deadline) ? $recruitment->application_deadline->format('d/m/Y') : 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            @if(method_exists($recruitment, 'isOpen') && $recruitment->isOpen() && is_pelanggan())
                                @if(method_exists($recruitment, 'getUserApplication') && ($userApplication = $recruitment->getUserApplication(auth()->id())))
                                    <div class="card mt-3">
                                        <div class="card-body text-center bg-gradient-info text-white">
                                            <h6>Anda sudah melamar untuk posisi ini</h6>
                                            <p class="mb-3">Pantau status lamaran Anda di halaman berikut</p>
                                            <a href="{{ route('recruitments.application-status', $recruitment->id ?? 0) }}" class="btn btn-light btn-lg">
                                                <i class="fas fa-chart-line text-info"></i> Lihat Status Lamaran
                                            </a>
                                            <small class="d-block mt-2 opacity-75">Tanggal melamar: {{ isset($userApplication->created_at) ? $userApplication->created_at->format('d M Y H:i') : 'N/A' }}</small>
                                        </div>
                                    </div>
                                @else
                                    <div class="card mt-3">
                                        <div class="card-body text-center bg-gradient-success text-white">
                                            <h6>Tertarik dengan posisi ini?</h6>
                                            <p class="text-black mb-3">Kirim lamaran Anda sekarang dan bergabung dengan tim kami!</p>
                                            <a href="{{ route('recruitments.apply.form', $recruitment->id ?? 0) }}" class="btn btn-light btn-lg">
                                                <i class="fas fa-paper-plane text-success"></i> Lamar Sekarang
                                            </a>
                                            <small class="d-block mt-2 opacity-75">Tim HRD akan menghubungi Anda segera</small>
                                        </div>
                                    </div>
                                @endif
                            @elseif(method_exists($recruitment, 'isOpen') && !$recruitment->isOpen() && is_pelanggan())
                                <div class="card mt-3">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Lowongan Tidak Tersedia</h6>
                                        <p class="text-muted small">Lowongan ini sudah ditutup atau melewati deadline.</p>
                                        <button class="btn btn-secondary" disabled>
                                            <i class="fas fa-times"></i> Tidak Dapat Melamar
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @if(is_admin() || is_hrd())
                                <div class="card mt-3">
                                    <div class="card-body">
                                        <h6>Manajemen Aplikasi</h6>
                                        <p class="text-muted small">Kelola dan review aplikasi untuk lowongan ini</p>
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('recruitments.manage-applications', $recruitment->id ?? 0) }}" class="btn btn-primary">
                                                <i class="fas fa-users"></i> Kelola Aplikasi
                                                @if(isset($recruitment->applications) && $recruitment->applications->count() > 0)
                                                    <span class="badge bg-light text-primary">{{ $recruitment->applications->count() }}</span>
                                                @endif
                                            </a>
                                        </div>
                                        
                                        @if(isset($recruitment->applications) && $recruitment->applications->count() > 0)
                                            <div class="row mt-3 text-center">
                                                <div class="col-3">
                                                    <div class="border rounded p-2">
                                                        <div class="fw-bold text-primary">{{ isset($recruitment->applications) ? $recruitment->applications->count() : 0 }}</div>
                                                        <small>Total</small>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="border rounded p-2">
                                                        <div class="fw-bold text-warning">{{ isset($recruitment->applications) ? $recruitment->applications->where('document_status', 'pending')->count() : 0 }}</div>
                                                        <small>Review</small>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="border rounded p-2">
                                                        <div class="fw-bold text-info">{{ isset($recruitment->applications) ? $recruitment->applications->where('interview_status', 'scheduled')->count() : 0 }}</div>
                                                        <small>Interview</small>
                                                    </div>
                                                </div>
                                                <div class="col-3">
                                                    <div class="border rounded p-2">
                                                        <div class="fw-bold text-success">{{ isset($recruitment->applications) ? $recruitment->applications->where('final_status', 'accepted')->count() : 0 }}</div>
                                                        <small>Diterima</small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Riwayat</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-bold" style="width: 20%;">Dibuat:</td>
                                    <td>{{ isset($recruitment->created_at) ? $recruitment->created_at->format('d F Y H:i') : 'N/A' }} WIB</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Terakhir Diupdate:</td>
                                    <td>{{ isset($recruitment->updated_at) ? $recruitment->updated_at->format('d F Y H:i') : 'N/A' }} WIB</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if(is_admin() || is_hrd())
                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('recruitments.edit', $recruitment->id ?? 0) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Lowongan
                            </a>
                            <form action="{{ route('recruitments.destroy', $recruitment->id ?? 0) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus lowongan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Hapus
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
