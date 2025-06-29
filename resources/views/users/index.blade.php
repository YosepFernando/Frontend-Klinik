@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="text-primary mb-1">
                        <i class="fas fa-users-cog me-2"></i>Manajemen User
                    </h2>
                    <p class="text-muted mb-0">Kelola akun pengguna sistem dengan mudah dan aman</p>
                </div>
                <div>
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-lg shadow-sm">
                        <i class="fas fa-user-plus me-2"></i>Tambah User
                    </a>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>Filter & Pencarian
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Pencarian</label>
                            <input type="text" class="form-control" name="search"
                                   placeholder="Cari nama atau email..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Role</label>
                            <select class="form-select" name="role">
                                <option value="">Semua Role</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="hrd" {{ request('role') == 'hrd' ? 'selected' : '' }}>HRD</option>
                                <option value="front_office" {{ request('role') == 'front_office' ? 'selected' : '' }}>Front Office</option>
                                <option value="kasir" {{ request('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                                <option value="dokter" {{ request('role') == 'dokter' ? 'selected' : '' }}>Dokter</option>
                                <option value="beautician" {{ request('role') == 'beautician' ? 'selected' : '' }}>Beautician</option>
                                <option value="pelanggan" {{ request('role') == 'pelanggan' ? 'selected' : '' }}>Pelanggan</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Status</label>
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-semibold">Gender</label>
                            <select class="form-select" name="gender">
                                <option value="">Semua Gender</option>
                                <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid gap-2 d-md-flex">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Filter
                                </button>
                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Main Data Table -->
            @if($users->count() > 0)
                <div class="card shadow border-0">
                    <div class="card-header bg-gradient-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 text-dark">
                                <i class="fas fa-table me-2"></i>Data User ({{ $users->total() }} pengguna)
                            </h5>
                            <small class="text-muted">Menampilkan {{ $users->firstItem() }}-{{ $users->lastItem() }} dari {{ $users->total() }} data</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th class="text-center" width="5%">#</th>
                                        <th width="20%">
                                            <i class="fas fa-user me-1"></i>Pengguna
                                        </th>
                                        <th width="15%">
                                            <i class="fas fa-envelope me-1"></i>Email
                                        </th>
                                        <th width="12%" class="text-center">
                                            <i class="fas fa-user-tag me-1"></i>Role
                                        </th>
                                        <th width="10%" class="text-center">
                                            <i class="fas fa-venus-mars me-1"></i>Gender
                                        </th>
                                        <th width="12%" class="text-center">
                                            <i class="fas fa-toggle-on me-1"></i>Status
                                        </th>
                                        <th width="13%" class="text-center">
                                            <i class="fas fa-calendar me-1"></i>Bergabung
                                        </th>
                                        <th width="13%" class="text-center">
                                            <i class="fas fa-cogs me-1"></i>Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $index => $user)
                                        <tr class="user-row">
                                            <td class="text-center">
                                                <span class="badge bg-primary rounded-pill">{{ $users->firstItem() + $index }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle me-3 bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'hrd' ? 'warning' : ($user->role == 'pelanggan' ? 'info' : 'success')) }}">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-bold text-dark">{{ $user->name }}</div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-clock me-1"></i>{{ $user->created_at->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-envelope text-primary me-2"></i>
                                                    <span class="text-break">{{ $user->email }}</span>
                                                </div>
                                                @if($user->email_verified_at)
                                                    <small class="text-success">
                                                        <i class="fas fa-check-circle me-1"></i>Terverifikasi
                                                    </small>
                                                @else
                                                    <small class="text-warning">
                                                        <i class="fas fa-exclamation-circle me-1"></i>Belum verifikasi
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'hrd' ? 'warning' : ($user->role == 'pelanggan' ? 'info' : 'success')) }} px-3 py-2">
                                                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($user->gender)
                                                    <span class="badge {{ $user->gender == 'male' ? 'bg-info' : 'bg-pink' }} px-3">
                                                        {{ $user->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                                                    {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="fw-semibold">{{ $user->created_at->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $user->created_at->format('H:i') }}</small>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group modern-btn-group" role="group">
                                                    <a href="{{ route('users.show', $user) }}" class="btn btn-outline-info btn-sm modern-btn" title="Lihat Detail">
                                                        <i class="fas fa-eye me-1"></i>
                                                        <span class="d-none d-md-inline">Lihat</span>
                                                    </a>
                                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-warning btn-sm modern-btn" title="Edit">
                                                        <i class="fas fa-edit me-1"></i>
                                                        <span class="d-none d-md-inline">Edit</span>
                                                    </a>
                                                    @if($user->id != auth()->id())
                                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm modern-btn" title="Hapus">
                                                                <i class="fas fa-trash me-1"></i>
                                                                <span class="d-none d-md-inline">Hapus</span>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <button class="btn btn-outline-secondary btn-sm modern-btn" disabled title="Tidak bisa hapus diri sendiri">
                                                            <i class="fas fa-lock me-1"></i>
                                                            <span class="d-none d-md-inline">Terkunci</span>
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
                    
                    <!-- Pagination -->
                    @if($users->hasPages())
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-center">
                                {{ $users->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Statistics Cards -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-gradient-primary text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-user-shield fa-2x mb-2"></i>
                                <h4 class="mb-0">{{ $users->where('role', 'admin')->count() }}</h4>
                                <small>Admin</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-gradient-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-user-tie fa-2x mb-2"></i>
                                <h4 class="mb-0">{{ $users->where('role', 'hrd')->count() }}</h4>
                                <small>HRD</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-gradient-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h4 class="mb-0">{{ $users->whereIn('role', ['front_office', 'kasir', 'dokter', 'beautician'])->count() }}</h4>
                                <small>Staff</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm bg-gradient-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-user-friends fa-2x mb-2"></i>
                                <h4 class="mb-0">{{ $users->where('role', 'pelanggan')->count() }}</h4>
                                <small>Pelanggan</small>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="card shadow border-0">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-users fa-4x text-muted"></i>
                        </div>
                        <h5 class="text-muted mb-3">Belum Ada Data User</h5>
                        <p class="text-muted mb-4">Silakan tambahkan user pertama untuk memulai.</p>
                        <a href="{{ route('users.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Tambah User Pertama
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-light {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
}

.bg-pink {
    background-color: #e91e63 !important;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.user-row:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

/* Modern Button Group Styling */
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

.table th {
    border-top: none;
    font-weight: 600;
    vertical-align: middle;
}

.table td {
    vertical-align: middle;
    border-color: rgba(0,0,0,0.05);
}

.btn-group .btn {
    border-radius: 0.375rem;
    margin: 0 1px;
}

.card {
    border-radius: 12px;
    overflow: hidden;
}

.badge {
    font-size: 0.75rem;
    font-weight: 500;
}

@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin: 1px 0;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>
@endpush