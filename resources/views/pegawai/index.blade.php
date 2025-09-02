@extends('layouts.app')

@section('content')
<!-- Hero Section with Gradient Background -->
<div class="hero-section mb-2 rounded">
    <div class="container-fluid">
        <div class="row align-items-center py-5">
            <div class="col-lg-8">
                <div class="hero-content text-white">
                    <h1 class="text-black display-4 fw-bold mb-3">
                        <i class="fas fa-users-cog me-3"></i>Kelola Pegawai
                    </h1>
                    <p class="text-black lead mb-4">Kelola data karyawan dengan mudah, efisien, dan modern. Pantau informasi lengkap setiap pegawai dalam satu dashboard terintegrasi.</p>
                    <div class="d-flex gap-3">
                        <a href="{{ route('pegawai.create') }}" class="btn btn-light btn-lg px-4 py-3 shadow-sm">
                            <i class="fas fa-user-plus me-2"></i>Tambah Pegawai Baru
                        </a>
                        <a href="{{ route('absensi.index') }}" class="btn btn-light btn-lg px-4 py-3 shadow-sm">
                            <i class="text-black fas fa-chart-line me-2"></i>Lihat Absensi
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-center">
                <div class="hero-illustration">
                    <i class="fas fa-users fa-8x text-white opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid mt-n4">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card bg-gradient-primary text-white border-0 shadow-lg">
                <div class="card-body text-black">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-0">
                                @php
                                    $totalPegawai = 0;
                                    if (is_object($pegawai) && method_exists($pegawai, 'total')) {
                                        $totalPegawai = $pegawai->total();
                                    } elseif (is_array($pegawai) && isset($pegawai['total'])) {
                                        $totalPegawai = $pegawai['total'];
                                    } elseif (is_array($pegawai) && isset($pegawai['data'])) {
                                        $totalPegawai = count($pegawai['data']);
                                    } elseif (is_array($pegawai)) {
                                        $totalPegawai = count($pegawai);
                                    }
                                @endphp
                                {{ $totalPegawai }}
                            </h4>
                            <small class="opacity-75">Total Pegawai</small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card bg-gradient-success text-white border-0 shadow-lg">
                <div class="card-body text-black">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-0">
                                @php
                                    $totalLaki = 0;
                                    if (is_object($pegawai) && method_exists($pegawai, 'where')) {
                                        $totalLaki = $pegawai->where('jenis_kelamin', 'L')->count();
                                    } elseif (is_array($pegawai) && isset($pegawai['data'])) {
                                        $totalLaki = collect($pegawai['data'])->where('jenis_kelamin', 'L')->count();
                                    } elseif (is_array($pegawai)) {
                                        $totalLaki = collect($pegawai)->where('jenis_kelamin', 'L')->count();
                                    }
                                @endphp
                                {{ $totalLaki }}
                            </h4>
                            <small class="opacity-75">Pegawai Laki-laki</small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-male fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card bg-gradient-pink text-white border-0 shadow-lg">
                <div class="card-body text-black">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-0">
                                @php
                                    $totalPerempuan = 0;
                                    if (is_object($pegawai) && method_exists($pegawai, 'where')) {
                                        $totalPerempuan = $pegawai->where('jenis_kelamin', 'P')->count();
                                    } elseif (is_array($pegawai) && isset($pegawai['data'])) {
                                        $totalPerempuan = collect($pegawai['data'])->where('jenis_kelamin', 'P')->count();
                                    } elseif (is_array($pegawai)) {
                                        $totalPerempuan = collect($pegawai)->where('jenis_kelamin', 'P')->count();
                                    }
                                @endphp
                                {{ $totalPerempuan }}
                            </h4>
                            <small class="opacity-75">Pegawai Perempuan</small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-female fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card stats-card bg-gradient-warning text-white border-0 shadow-lg">
                <div class="card-body text-black">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="fw-bold mb-0">
                                @php
                                    $totalPosisi = 0;
                                    if (is_object($posisi) && method_exists($posisi, 'count')) {
                                        $totalPosisi = $posisi->count();
                                    } elseif (is_array($posisi) && isset($posisi['data'])) {
                                        $totalPosisi = count($posisi['data']);
                                    } elseif (is_array($posisi)) {
                                        $totalPosisi = count($posisi);
                                    }
                                @endphp
                                {{ $totalPosisi }}
                            </h4>
                            <small class="opacity-75">Total Posisi</small>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-briefcase fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filter Section -->
    <div class="card modern-filter-card border-0 shadow-lg mb-4">
        <div class="card-header bg-white border-0 py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold text-dark">
                        <i class="fas fa-search-plus me-2 text-primary"></i>Filter & Pencarian Lanjutan
                    </h5>
                    <small class="text-muted">Gunakan filter untuk menemukan pegawai dengan kriteria tertentu</small>
                </div>
                <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                    <i class="fas fa-filter me-2"></i>Toggle Filter
                </button>
            </div>
        </div>
        <div class="collapse show" id="filterCollapse">
            <div class="card-body text-black bg-light">
                <form method="GET" action="{{ route('pegawai.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">
                            <i class="fas fa-briefcase me-1"></i>Posisi
                        </label>
                        <select name="posisi_id" class="form-select form-select-lg">
                            <option value="">🔍 Semua Posisi</option>
                            @foreach($posisi as $p)
                                <option value="{{ is_array($p) ? ($p['id_posisi'] ?? '') : ($p->id_posisi ?? '') }}" {{ request('posisi_id') == (is_array($p) ? ($p['id_posisi'] ?? '') : ($p->id_posisi ?? '')) ? 'selected' : '' }}>
                                    {{ is_array($p) ? ($p['nama_posisi'] ?? 'Tidak ada nama') : ($p->nama_posisi ?? 'Tidak ada nama') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">
                            <i class="fas fa-venus-mars me-1"></i>Jenis Kelamin
                        </label>
                        <select name="jenis_kelamin" class="form-select form-select-lg">
                            <option value="">👥 Semua Gender</option>
                            <option value="L" {{ request('jenis_kelamin') == 'L' ? 'selected' : '' }}>👨 Laki-laki</option>
                            <option value="P" {{ request('jenis_kelamin') == 'P' ? 'selected' : '' }}>👩 Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">
                            <i class="fas fa-search me-1"></i>Pencarian
                        </label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Cari nama, email, atau NIK...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                            <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-undo me-2"></i>Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm modern-alert" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle fa-2x me-3"></i>
                <div>
                    <h6 class="mb-0">Berhasil!</h6>
                    <small>{{ session('success') }}</small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm modern-alert" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fa-2x me-3"></i>
                <div>
                    <h6 class="mb-0">Terjadi Kesalahan!</h6>
                    <small>{{ session('error') }}</small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Main Content Area -->
    @if(is_array($pegawai) ? count($pegawai) > 0 : $pegawai->count() > 0)
        <!-- Data Overview Card -->
        <div class="card modern-table-card border-0 shadow-lg">
            <div class="card-header bg-gradient-light border-0 py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1 fw-bold text-dark">
                            <i class="fas fa-table me-2 text-primary"></i>Data Pegawai
                        </h5>
                        @php
                            $firstItem = 1;
                            $lastItem = is_array($pegawai) ? count($pegawai) : (method_exists($pegawai, 'count') ? $pegawai->count() : 0);
                            $totalItems = is_array($pegawai) ? (isset($pegawai['total']) ? $pegawai['total'] : count($pegawai)) : 
                                (method_exists($pegawai, 'total') ? $pegawai->total() : (isset($pegawai->total) ? $pegawai->total : 0));
                            
                            if (is_object($pegawai) && method_exists($pegawai, 'firstItem')) {
                                $firstItem = $pegawai->firstItem();
                            }
                            
                            if (is_object($pegawai) && method_exists($pegawai, 'lastItem')) {
                                $lastItem = $pegawai->lastItem();
                            }
                        @endphp
                        <small class="text-muted">Menampilkan {{ $firstItem }}-{{ $lastItem }} dari {{ $totalItems }} pegawai</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="exportPegawaiToPdf()">
                            <i class="fas fa-file-pdf me-1"></i>Download PDF
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="printData()">
                            <i class="fas fa-print me-1"></i>Print
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 modern-table">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center py-3" width="5%">
                                    <i class="fas fa-hashtag"></i>
                                </th>
                                <th class="py-3" width="20%">
                                    <i class="fas fa-user me-2"></i>Pegawai
                                </th>
                                <th class="py-3" width="15%">
                                    <i class="fas fa-briefcase me-2"></i>Posisi
                                </th>
                                <th class="py-3" width="15%">
                                    <i class="fas fa-envelope me-2"></i>Kontak
                                </th>
                                <th class="text-center py-3" width="10%">
                                    <i class="fas fa-venus-mars me-2"></i>Gender
                                </th>
                                <th class="text-center py-3" width="12%">
                                    <i class="fas fa-calendar me-2"></i>Bergabung
                                </th>
                                <th class="text-center py-3" width="10%">
                                    <i class="fas fa-user-shield me-2"></i>Role
                                </th>
                                <th class="text-center py-3" width="13%">
                                    <i class="fas fa-cogs me-2"></i>Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pegawai as $index => $p)
                                @php
                                    // Pastikan $p adalah object/array dan bukan integer
                                    if (is_int($p) || is_string($p)) {
                                        continue; // Skip jika bukan object/array
                                    }
                                    
                                    // Convert array to object for consistency
                                    if (is_array($p)) {
                                        $p = (object) $p;
                                    }
                                @endphp
                                <tr class="employee-row">
                                    <td class="text-center py-3">
                                        @php
                                            $firstItemValue = 0;
                                            if (is_object($pegawai) && method_exists($pegawai, 'firstItem')) {
                                                $firstItemValue = $pegawai->firstItem();
                                            } elseif (isset($pegawai->firstItem)) {
                                                $firstItemValue = $pegawai->firstItem;
                                            }
                                            $firstItemValue = intval($firstItemValue);
                                            $indexValue = intval($index);
                                        @endphp
                                        <span class="badge bg-primary rounded-pill px-3 py-2 fs-6">
                                            {{ $firstItemValue + $indexValue }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-modern me-3 {{ ($p->jenis_kelamin ?? '') == 'L' ? 'bg-gradient-info' : 'bg-gradient-pink' }}">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark fs-6">{{ $p->nama_lengkap ?? 'Nama tidak tersedia' }}</div>
                                                <small class="text-muted">
                                                    <i class="fas fa-id-card me-1"></i>{{ $p->NIK ?? 'NIK tidak tersedia' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-gradient-secondary text-dark px-3 py-2 fs-6">
                                            @if(is_object($p) && isset($p->posisi) && is_object($p->posisi))
                                                {{ $p->posisi->nama_posisi ?? 'Belum ditentukan' }}
                                            @elseif(is_object($p) && isset($p->posisi) && is_array($p->posisi))
                                                {{ $p->posisi['nama_posisi'] ?? 'Belum ditentukan' }}
                                            @else
                                                Belum ditentukan
                                            @endif
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        @if(isset($p->email) && $p->email)
                                            <div class="mb-1">
                                                <i class="fas fa-envelope text-primary me-2"></i>
                                                <small class="text-break">{{ $p->email }}</small>
                                            </div>
                                        @endif
                                        @if(isset($p->telepon) && $p->telepon)
                                            <div>
                                                <i class="fas fa-phone text-success me-2"></i>
                                                <small>{{ $p->telepon }}</small>
                                            </div>
                                        @endif
                                        @if((!isset($p->email) || !$p->email) && (!isset($p->telepon) || !$p->telepon))
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center py-3">
                                        <span class="badge {{ ($p->jenis_kelamin ?? '') == 'L' ? 'bg-gradient-info' : 'bg-gradient-pink' }} px-3 py-2 fs-6 text-black">
                                            {{ ($p->jenis_kelamin ?? '') == 'L' ? '👨 Laki-laki' : '👩 Perempuan' }}
                                        </span>
                                    </td>
                                    <td class="text-center py-3">
                                        @if(isset($p->tanggal_masuk) && $p->tanggal_masuk)
                                            @php
                                                $tanggalMasuk = null;
                                                try {
                                                    if (is_string($p->tanggal_masuk)) {
                                                        $tanggalMasuk = \Carbon\Carbon::parse($p->tanggal_masuk);
                                                    } elseif (is_object($p->tanggal_masuk) && method_exists($p->tanggal_masuk, 'format')) {
                                                        $tanggalMasuk = $p->tanggal_masuk;
                                                    }
                                                } catch (\Exception $e) {
                                                    $tanggalMasuk = null;
                                                }
                                            @endphp
                                            @if($tanggalMasuk)
                                                <div class="fw-semibold">{{ $tanggalMasuk->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $tanggalMasuk->diffForHumans() }}</small>
                                            @else
                                                <span class="text-muted">Tanggal tidak valid</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center py-3">
                                        @if(isset($p->user) && $p->user)
                                            @php
                                                $userRole = 'unknown';
                                                if (is_object($p->user) && isset($p->user->role)) {
                                                    $userRole = $p->user->role;
                                                } elseif (is_array($p->user) && isset($p->user['role'])) {
                                                    $userRole = $p->user['role'];
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $userRole == 'admin' ? 'danger' : ($userRole == 'hrd' ? 'warning' : 'success') }} px-3 py-2 fs-6">
                                                {{ ucfirst($userRole) }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary px-3 py-2 fs-6">No User</span>
                                        @endif
                                    </td>
                                    <td class="text-center py-3">
                                        <div class="btn-group modern-btn-group" role="group">
                                            @if(isset($p->id_pegawai) || isset($p->id))
                                                <a href="{{ route('pegawai.show', $p->id_pegawai ?? $p->id ?? 0) }}" class="btn btn-outline-info btn-sm modern-btn" title="Lihat Detail">
                                                    <i class="fas fa-eye me-1"></i>
                                                    <span class="d-none d-md-inline">Lihat</span>
                                                </a>
                                            @endif
                                            @if(isset($p->id_pegawai) || isset($p->id))
                                                <a href="{{ route('pegawai.edit', $p->id_pegawai ?? $p->id ?? 0) }}" class="btn btn-outline-warning btn-sm modern-btn" title="Edit">
                                                    <i class="fas fa-edit me-1"></i>
                                                    <span class="d-none d-md-inline">Edit</span>
                                                </a>
                                            @endif
                                            @if(isset($p->id_pegawai) || isset($p->id))
                                                <button type="button" class="btn btn-outline-danger btn-sm modern-btn" title="Hapus" onclick="confirmDelete('{{ $p->id_pegawai ?? $p->id ?? 0 }}', '{{ $p->nama_lengkap ?? 'Pegawai' }}')">
                                                    <i class="fas fa-trash me-1"></i>
                                                    <span class="d-none d-md-inline">Hapus</span>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Enhanced Pagination -->
            @if(is_object($pegawai) && method_exists($pegawai, 'hasPages') && $pegawai->hasPages())
                <div class="card-footer bg-light border-0 py-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <small class="text-muted">
                                Menampilkan {{ $pegawai->firstItem() ?? 1 }} - {{ $pegawai->lastItem() ?? count($pegawai) }} dari {{ $pegawai->total() ?? count($pegawai) }} hasil
                            </small>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                {{ $pegawai->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else
        <!-- Beautiful Empty State -->
        <div class="card empty-state-card border-0 shadow-lg">
            <div class="card-body text-center py-5">
                <div class="empty-state-illustration mb-4">
                    <i class="fas fa-users fa-6x text-muted opacity-50"></i>
                </div>
                <h4 class="text-muted mb-3">Belum Ada Data Pegawai</h4>
                <p class="text-muted mb-4 lead">Mulai dengan menambahkan pegawai pertama untuk membangun tim yang solid!</p>
                <div class="d-flex justify-content-center gap-3">
                    <a href="{{ route('pegawai.create') }}" class="btn btn-primary btn-lg px-5 py-3">
                        <i class="fas fa-user-plus me-2"></i>Tambah Pegawai Pertama
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-lg px-5 py-3">
                        <i class="fas fa-home me-2"></i>Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4">
                <div class="text-center">
                    <i class="fas fa-user-times fa-3x text-danger mb-3"></i>
                    <h6>Apakah Anda yakin ingin menghapus pegawai:</h6>
                    <strong id="employeeName" class="text-danger"></strong>
                    <p class="text-muted mt-2">Data yang dihapus tidak dapat dikembalikan.</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Batal
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Hero Section */
.hero-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
    overflow: hidden;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 100" fill="white" opacity="0.1"><polygon points="0,100 1000,0 1000,100"/></svg>') no-repeat bottom;
    background-size: cover;
}

.hero-content {
    position: relative;
    z-index: 2;
}

/* Statistics Cards */
.stats-card {
    border-radius: 15px;
    transform: translateY(0);
    transition: all 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.bg-gradient-pink {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.bg-gradient-light {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.bg-gradient-secondary {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}

/* Modern Cards */
.modern-filter-card,
.modern-table-card,
.empty-state-card {
    border-radius: 20px;
    overflow: hidden;
}

.modern-table-card .card-header {
    border-radius: 20px 20px 0 0;
}

/* Table Styling */
.modern-table {
    font-size: 0.95rem;
}

.modern-table thead th {
    border: none;
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 0.85rem;
}

.modern-table tbody td {
    border-color: rgba(0,0,0,0.05);
    vertical-align: middle;
}

.employee-row {
    transition: all 0.3s ease;
}

.employee-row:hover {
    background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    transform: scale(1.01);
}

/* Avatar Modern */
.avatar-modern {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    position: relative;
    overflow: hidden;
}

.avatar-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.2);
    border-radius: inherit;
}

/* Modern Buttons */
.modern-btn-group .modern-btn {
    border-radius: 8px;
    margin: 0 2px;
    padding: 8px 12px;
    transition: all 0.3s ease;
    border-width: 2px;
    min-width: 45px;
}

.modern-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* FontAwesome Icon Styling */
.modern-btn i {
    font-size: 14px;
    line-height: 1;
    display: inline-block;
}

.modern-btn i.fas,
.modern-btn i.fa {
    font-family: "Font Awesome 6 Free" !important;
    font-weight: 900 !important;
}

/* Modern Alert */
.modern-alert {
    border-radius: 15px;
    border-left: 5px solid;
    padding: 20px;
}

.alert-success {
    border-left-color: #28a745;
    background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(40, 167, 69, 0.05) 100%);
}

.alert-danger {
    border-left-color: #dc3545;
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
}

/* Badge Modern */
.badge {
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Empty State */
.empty-state-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.empty-state-illustration {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-10px); }
}

/* Form Controls */
.form-select-lg,
.form-control {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-select-lg:focus,
.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-section .display-4 {
        font-size: 2rem;
    }
    
    .hero-section .lead {
        font-size: 1rem;
    }
    
    .stats-card {
        margin-bottom: 1rem;
    }
    
    .modern-btn-group {
        flex-direction: column;
    }
    
    .modern-btn-group .modern-btn {
        margin: 2px 0;
        width: 100%;
    }
    
    .table-responsive {
        font-size: 0.85rem;
    }
}

/* Animation */
.card {
    animation: fadeInUp 0.6s ease-out;
}


@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Pagination Styling */
.pagination .page-link {
    border-radius: 8px;
    margin: 0 2px;
    border: none;
    color: #667eea;
}

.pagination .page-link:hover {
    background-color: #667eea;
    color: white;
}

.pagination .page-item.active .page-link {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(id, name) {
    document.getElementById('employeeName').textContent = name;
    document.getElementById('deleteForm').action = `/pegawai/${id}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// PDF Export function for Pegawai
function exportPegawaiToPdf() {
    // Get current filters
    const urlParams = new URLSearchParams(window.location.search);
    const filters = {
        posisi_id: urlParams.get('posisi_id') || '',
        jenis_kelamin: urlParams.get('jenis_kelamin') || '',
        search: urlParams.get('search') || ''
    };
    
    // Build export URL with current filters
    const exportUrl = new URL('{{ route("pegawai.export-pdf") }}', window.location.origin);
    Object.keys(filters).forEach(key => {
        if (filters[key]) {
            exportUrl.searchParams.append(key, filters[key]);
        }
    });
    
    // Open in new window to download
    window.open(exportUrl.toString(), '_blank');
}

function printData() {
    window.print();
}

// Add loading animation
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
    
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert) {
                new bootstrap.Alert(alert).close();
            }
        });
    }, 5000);
});
</script>
@endpush
