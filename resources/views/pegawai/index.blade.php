@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Manajemen Pegawai</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('pegawai.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Pegawai
                        </a>
                        <a href="{{ route('absensi.index') }}" class="btn btn-info">
                            <i class="fas fa-chart-bar"></i> Lihat Absensi
                        </a>
                    </div>
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

                    <!-- Filters -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('pegawai.index') }}" class="row g-3">
                                <div class="col-md-3">
                                    <select name="posisi_id" class="form-select">
                                        <option value="">Semua Posisi</option>
                                        @foreach($posisi as $p)
                                            <option value="{{ $p->id_posisi }}" {{ request('posisi_id') == $p->id_posisi ? 'selected' : '' }}>
                                                {{ $p->nama_posisi }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="jenis_kelamin" class="form-select">
                                        <option value="">Semua Jenis Kelamin</option>
                                        <option value="L" {{ request('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ request('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari nama/email...">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    @if($pegawai->count() > 0)
                        <!-- Pegawai Cards Grid -->
                        <div class="row">
                            @foreach($pegawai as $p)
                                <div class="col-lg-6 col-xl-4 mb-4">
                                    <div class="card h-100 shadow-sm pegawai-card">
                                        <!-- Card Header with Name and Position -->
                                        <div class="card-header bg-primary text-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-0">{{ $p->nama_lengkap }}</h6>
                                                    <small class="opacity-75">{{ $p->posisi->nama_posisi ?? 'Belum ditentukan' }}</small>
                                                </div>
                                                <span class="badge bg-light text-dark">
                                                    {{ $p->jenis_kelamin == 'L' ? 'L' : 'P' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="card-body">
                                            <!-- Employee Info -->
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="avatar-circle me-3">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $p->user->name ?? 'Tidak ada user' }}</strong>
                                                        <small class="text-muted d-block">
                                                            {{ $p->user ? ucfirst($p->user->role) : 'N/A' }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Contact Info -->
                                            <div class="mb-3">
                                                @if($p->email)
                                                    <div class="mb-1">
                                                        <small class="text-muted">
                                                            <i class="fas fa-envelope"></i> {{ $p->email }}
                                                        </small>
                                                    </div>
                                                @endif
                                                @if($p->telepon)
                                                    <div class="mb-1">
                                                        <small class="text-muted">
                                                            <i class="fas fa-phone"></i> {{ $p->telepon }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Employment Info -->
                                            <div class="mb-3">
                                                <div class="row text-center">
                                                    <div class="col-6">
                                                        <div class="border-end">
                                                            <div class="text-muted small">NIK</div>
                                                            <div class="fw-bold">{{ $p->NIK ?? '-' }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="text-muted small">Mulai Kerja</div>
                                                        <div class="fw-bold">
                                                            {{ $p->tanggal_masuk ? $p->tanggal_masuk->format('d/m/Y') : '-' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Additional Info -->
                                            @if($p->agama)
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-pray"></i> {{ $p->agama }}
                                                    </small>
                                                </div>
                                            @endif

                                            @if($p->alamat)
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        {{ Str::limit($p->alamat, 40) }}
                                                    </small>
                                                </div>
                                            @endif

                                            <!-- Actions -->
                                            <div class="d-flex gap-1 mt-3">
                                                <a href="{{ route('pegawai.show', $p) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                <a href="{{ route('pegawai.edit', $p) }}" class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if(auth()->user()->isAdmin())
                                                    <form action="{{ route('pegawai.destroy', $p) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                onclick="return confirm('Yakin ingin menghapus data pegawai ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $pegawai->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada data pegawai</h5>
                            <p class="text-muted">Silakan tambah pegawai baru.</p>
                            <a href="{{ route('pegawai.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Tambah Pegawai Pertama
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pegawai-card {
    transition: all 0.3s ease;
}

.pegawai-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}
</style>
@endsection
