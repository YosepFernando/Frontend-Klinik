@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        @if(auth()->user()->isPelanggan())
                            Lowongan Kerja Tersedia
                        @else
                            Daftar Lowongan Kerja
                        @endif
                    </h4>
                    @if(auth()->user()->isAdmin() || auth()->user()->isHRD())
                        <a href="{{ route('recruitments.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Buat Lowongan
                        </a>
                    @endif
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($recruitments->count() > 0)
                        <div class="row">
                            @foreach($recruitments as $recruitment)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card h-100 border-0 shadow-sm hover-card">
                                        <div class="card-header bg-gradient-info text-white border-bottom-0">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    @if($recruitment->status === 'open' && $recruitment->application_deadline->isFuture())
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check-circle"></i> Dibuka
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-times-circle"></i> Ditutup
                                                        </span>
                                                    @endif
                                                    <span class="badge bg-light text-dark">{{ $recruitment->employment_type_display }}</span>
                                                </div>
                                                @if(auth()->user()->isAdmin() || auth()->user()->isHRD())
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-light btn-sm" type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-v text-dark"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="{{ route('recruitments.edit', $recruitment) }}">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form action="{{ route('recruitments.destroy', $recruitment) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus lowongan ini?')">
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
                                                <i class="fas fa-briefcase fa-2x mb-2"></i>
                                                <h5 class="card-title mb-0">{{ $recruitment->position }}</h5>
                                            </div>
                                        </div>
                                        
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar"></i> Deadline
                                                    </small>
                                                    <span class="badge {{ $recruitment->application_deadline->isPast() ? 'bg-danger' : 'bg-warning text-dark' }}">
                                                        {{ $recruitment->application_deadline->format('d M Y') }}
                                                    </span>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-users"></i> Posisi Tersedia
                                                    </small>
                                                    <span class="fw-bold text-primary">{{ $recruitment->slots }} orang</span>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="fas fa-money-bill-wave"></i> Gaji
                                                    </small>
                                                    <span class="fw-bold text-success">{{ $recruitment->salary_range }}</span>
                                                </div>
                                            </div>

                                            <p class="card-text text-muted">{{ Str::limit($recruitment->description, 120) }}</p>
                                            
                                            @if($recruitment->application_deadline->isToday())
                                                <div class="alert alert-warning alert-sm p-2">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <small>Deadline hari ini!</small>
                                                </div>
                                            @elseif($recruitment->application_deadline->diffInDays() <= 3 && $recruitment->application_deadline->isFuture())
                                                <div class="alert alert-info alert-sm p-2">
                                                    <i class="fas fa-clock"></i>
                                                    <small>{{ $recruitment->application_deadline->diffForHumans() }}</small>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="card-footer bg-transparent border-top-0">
                                            @if(auth()->user()->isPelanggan())
                                                <div class="d-grid gap-2">
                                                    <a href="{{ route('recruitments.show', $recruitment) }}" class="btn btn-outline-primary">
                                                        <i class="fas fa-eye"></i> Lihat Detail
                                                    </a>
                                                    @if($recruitment->isOpen())
                                                        <form action="{{ route('recruitments.apply', $recruitment) }}" method="POST" onsubmit="return confirm('Yakin ingin melamar posisi ini?')">
                                                            @csrf
                                                            <!-- <button type="submit" class="btn btn-success w-100">
                                                                <i class="fas fa-paper-plane"></i> Lamar Sekarang
                                                            </button> -->
                                                        </form>
                                                    @else
                                                        <button class="btn btn-secondary w-100" disabled>
                                                            <i class="fas fa-times"></i> Lowongan Ditutup
                                                        </button>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="d-grid">
                                                    <a href="{{ route('recruitments.show', $recruitment) }}" class="btn btn-outline-primary">
                                                        <i class="fas fa-eye"></i> Lihat Detail & Kelola
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-center">
                            {{ $recruitments->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-briefcase fa-4x text-muted"></i>
                            </div>
                            <h5>
                                @if(auth()->user()->isPelanggan())
                                    Belum ada lowongan kerja tersedia
                                @else
                                    Belum ada lowongan kerja
                                @endif
                            </h5>
                            <p class="text-muted">
                                @if(auth()->user()->isPelanggan())
                                    Saat ini belum ada lowongan kerja yang tersedia. Silakan cek kembali nanti.
                                @else
                                    Saat ini belum ada lowongan kerja yang tersedia.
                                @endif
                            </p>
                            @if(auth()->user()->isAdmin() || auth()->user()->isHRD())
                                <a href="{{ route('recruitments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Buat Lowongan Pertama
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-info {
    background: linear-gradient(45deg, #17a2b8, #117a8b);
}

.hover-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.hover-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.alert-sm {
    font-size: 0.875rem;
}
</style>
@endsection
