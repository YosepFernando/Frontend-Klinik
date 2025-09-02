@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 fw-bold text-dark mb-1">
                        <i class="fas fa-users me-2 text-primary"></i>Manajemen User
                    </h2>
                    <p class="text-muted mb-0">Kelola akun pengguna sistem</p>
                </div>
                <div>
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Tambah User
                    </a>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-semibold text-dark">
                                <i class="fas fa-chart-bar me-2 text-primary"></i>Statistik Pengguna
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                @php
                                    $stats = [
                                        'admin' => ['count' => 0, 'label' => 'Admin', 'icon' => 'fa-user-shield', 'color' => 'danger', 'bg' => '#dc3545'],
                                        'hrd' => ['count' => 0, 'label' => 'HRD', 'icon' => 'fa-users-cog', 'color' => 'warning', 'bg' => '#ffc107'],
                                        'dokter' => ['count' => 0, 'label' => 'Dokter', 'icon' => 'fa-user-md', 'color' => 'info', 'bg' => '#0dcaf0'],
                                        'staff' => ['count' => 0, 'label' => 'Staff', 'icon' => 'fa-user-tie', 'color' => 'success', 'bg' => '#198754'],
                                        'pelanggan' => ['count' => 0, 'label' => 'Pelanggan', 'icon' => 'fa-user-friends', 'color' => 'secondary', 'bg' => '#6c757d'],
                                        'total' => ['count' => count($users), 'label' => 'Total', 'icon' => 'fa-users', 'color' => 'primary', 'bg' => '#0d6efd']
                                    ];
                                    
                                    foreach ($users as $user) {
                                        $role = $user->role ?? '';
                                        if (isset($stats[$role])) {
                                            $stats[$role]['count']++;
                                        } elseif (in_array($role, ['kasir', 'front office', 'beautician'])) {
                                            $stats['staff']['count']++;
                                        }
                                    }
                                @endphp
                                
                                @foreach($stats as $key => $stat)
                                    <div class="col-md-2">
                                        <div class="stat-card text-center p-4 rounded-3 h-100" style="background: linear-gradient(135deg, {{ $stat['bg'] }}15, {{ $stat['bg'] }}05); border: 1px solid {{ $stat['bg'] }}20;">
                                            <div class="stat-icon mb-3">
                                                <div class="icon-wrapper rounded-circle mx-auto d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px; background: {{ $stat['bg'] }}; color: white;">
                                                    <i class="fas {{ $stat['icon'] }} fa-lg"></i>
                                                </div>
                                            </div>
                                            <div class="stat-content">
                                                <h3 class="stat-number fw-bold mb-1" style="color: {{ $stat['bg'] }};">{{ $stat['count'] }}</h3>
                                                <p class="stat-label text-muted mb-0 fw-medium">{{ $stat['label'] }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Section -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h6 class="mb-0 fw-semibold text-dark">
                        <i class="fas fa-filter me-2 text-primary"></i>Filter & Pencarian
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Pencarian</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" name="search"
                                       placeholder="Cari nama atau email..."
                                       value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-medium">Role</label>
                            <select class="form-select" name="role">
                                <option value="">Semua Role</option>
                                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="hrd" {{ request('role') == 'hrd' ? 'selected' : '' }}>HRD</option>
                                <option value="dokter" {{ request('role') == 'dokter' ? 'selected' : '' }}>Dokter</option>
                                <option value="beautician" {{ request('role') == 'beautician' ? 'selected' : '' }}>Beautician</option>
                                <option value="kasir" {{ request('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                                <option value="front office" {{ request('role') == 'front office' ? 'selected' : '' }}>Front Office</option>
                                <option value="pelanggan" {{ request('role') == 'pelanggan' ? 'selected' : '' }}>Pelanggan</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-medium">Status</label>
                            <select class="form-select" name="status">
                                <option value="">Semua Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="fas fa-search me-1"></i>Cari
                                </button>
                                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-refresh"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Main Data Table -->
            @if($users->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold text-dark">
                                <i class="fas fa-table me-2 text-primary"></i>Data User 
                                <span class="badge bg-primary ms-2">{{ method_exists($users, 'total') ? $users->total() : count($users) }}</span>
                            </h6>
                            <small class="text-muted">
                                Menampilkan {{ method_exists($users, 'firstItem') ? $users->firstItem() : 1 }}-{{ method_exists($users, 'lastItem') ? $users->lastItem() : count($users) }} dari {{ method_exists($users, 'total') ? $users->total() : count($users) }} data
                            </small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="border-0 fw-semibold">#</th>
                                        <th width="30%" class="border-0 fw-semibold">
                                            <i class="fas fa-user me-1 text-muted"></i>Pengguna
                                        </th>
                                        <th width="20%" class="border-0 fw-semibold">
                                            <i class="fas fa-envelope me-1 text-muted"></i>Kontak
                                        </th>
                                        <th width="15%" class="border-0 fw-semibold">
                                            <i class="fas fa-tag me-1 text-muted"></i>Role
                                        </th>
                                        <th width="15%" class="border-0 fw-semibold">
                                            <i class="fas fa-calendar me-1 text-muted"></i>Terdaftar
                                        </th>
                                        <th width="10%" class="border-0 fw-semibold text-center">Status</th>
                                        <th width="5%" class="border-0 fw-semibold text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($users as $index => $user)
                                    @php
                                        // Pastikan $user adalah object dan bukan tipe data lain
                                        if (!is_object($user)) {
                                            continue;
                                        }
                                        
                                        // Calculate row number
                                        $rowNumber = 1;
                                        if (method_exists($users, 'firstItem') && $users->firstItem()) {
                                            $rowNumber = $users->firstItem() + $loop->index;
                                        } else {
                                            $rowNumber = $loop->iteration;
                                        }
                                    @endphp
                                    <tr class="border-bottom">
                                        <td class="text-muted fw-medium">{{ $rowNumber }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-3">
                                                    @if(!empty($user->foto_profil))
                                                        <img src="{{ $user->foto_profil }}" alt="Avatar" class="rounded-circle object-cover" width="45" height="45">
                                                    @else
                                                        <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center bg-light border">
                                                            <i class="fas fa-user text-muted"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="fw-semibold text-dark">{{ $user->name ?? 'Tidak ada nama' }}</div>
                                                    <small class="text-muted">ID: {{ $user->id_user }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="text-dark">{{ $user->email ?? 'Tidak ada email' }}</div>
                                                @if(isset($user->no_telp) && $user->no_telp)
                                                    <small class="text-muted">
                                                        <i class="fas fa-phone me-1"></i>{{ $user->no_telp }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $roleLabels = [
                                                    'admin' => ['label' => 'Admin', 'color' => 'danger'],
                                                    'hrd' => ['label' => 'HRD', 'color' => 'warning'],
                                                    'dokter' => ['label' => 'Dokter', 'color' => 'info'],
                                                    'beautician' => ['label' => 'Beautician', 'color' => 'success'],
                                                    'kasir' => ['label' => 'Kasir', 'color' => 'primary'],
                                                    'front office' => ['label' => 'Front Office', 'color' => 'secondary'],
                                                    'pelanggan' => ['label' => 'Pelanggan', 'color' => 'dark']
                                                ];
                                                $roleInfo = $roleLabels[$user->role ?? ''] ?? ['label' => ucfirst($user->role ?? 'Unknown'), 'color' => 'secondary'];
                                            @endphp
                                            <span class="badge bg-{{ $roleInfo['color'] }} bg-opacity-10 text-{{ $roleInfo['color'] }} border border-{{ $roleInfo['color'] }} border-opacity-25">
                                                {{ $roleInfo['label'] }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($user->created_at)
                                                <div class="text-dark">{{ \Carbon\Carbon::parse($user->created_at)->format('d M Y') }}</div>
                                                <small class="text-muted">{{ \Carbon\Carbon::parse($user->created_at)->format('H:i') }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($user->is_active ?? true)
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">
                                                    <i class="fas fa-check-circle me-1"></i>Aktif
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">
                                                    <i class="fas fa-times-circle me-1"></i>Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $userId = $user->id ?? $user->id_user ?? null;
                                            @endphp
                                            @if($userId)
                                                <!-- Dropdown Version -->
                                                <div class="dropdown position-relative">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                            type="button" 
                                                            id="dropdownMenuButton{{ $userId }}" 
                                                            onclick="toggleDropdown({{ $userId }})"
                                                            aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow" 
                                                        id="dropdownMenu{{ $userId }}"
                                                        style="display: none;">
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('users.show', $userId) }}">
                                                                <i class="fas fa-eye me-2 text-primary"></i>Lihat Detail
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('users.edit', $userId) }}">
                                                                <i class="fas fa-edit me-2 text-warning"></i>Edit
                                                            </a>
                                                        </li>
                                                        @if($userId != auth()->id())
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <button type="button" class="dropdown-item text-danger border-0 bg-transparent w-100 text-start" onclick="confirmDelete({{ $userId }})">
                                                                    <i class="fas fa-trash me-2"></i>Hapus
                                                                </button>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                                
                                                <!-- Alternative: Simple Button Group (Fallback) -->
                                                <div class="btn-group-alternative d-none" id="btnGroup{{ $userId }}">
                                                    <a href="{{ route('users.show', $userId) }}" class="btn btn-outline-primary btn-sm me-1" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('users.edit', $userId) }}" class="btn btn-outline-warning btn-sm me-1" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if($userId != auth()->id())
                                                        <button type="button" class="btn btn-outline-danger btn-sm" title="Hapus" onclick="confirmDelete({{ $userId }})">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                                
                                                <form id="delete-form-{{ $userId }}" action="{{ route('users.destroy', $userId) }}" method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Custom Pagination -->
                    @if(method_exists($users, 'hasPages') && $users->hasPages())
                        <div class="card-footer bg-white border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="pagination-info">
                                    <small class="text-muted">
                                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                                    </small>
                                </div>
                                <div class="pagination-controls d-flex align-items-center gap-2">
                                    <!-- Previous Button -->
                                    @if($users->onFirstPage())
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <i class="fas fa-chevron-left me-1"></i>
                                            <span class="d-none d-sm-inline">Previous</span>
                                        </button>
                                    @else
                                        <a href="{{ $users->previousPageUrl() }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-chevron-left me-1"></i>
                                            <span class="d-none d-sm-inline">Previous</span>
                                        </a>
                                    @endif

                                    <!-- Page Numbers -->
                                    <div class="pagination-pages d-flex gap-1">
                                        @foreach(range(1, $users->lastPage()) as $page)
                                            @if($page == $users->currentPage())
                                                <span class="btn btn-primary btn-sm">{{ $page }}</span>
                                            @elseif($page == 1 || $page == $users->lastPage() || abs($page - $users->currentPage()) <= 2)
                                                <a href="{{ $users->url($page) }}" class="btn btn-outline-secondary btn-sm">{{ $page }}</a>
                                            @elseif($page == 2 && $users->currentPage() > 4)
                                                <span class="btn btn-outline-secondary btn-sm disabled">...</span>
                                            @elseif($page == $users->lastPage() - 1 && $users->currentPage() < $users->lastPage() - 3)
                                                <span class="btn btn-outline-secondary btn-sm disabled">...</span>
                                            @endif
                                        @endforeach
                                    </div>

                                    <!-- Next Button -->
                                    @if($users->hasMorePages())
                                        <a href="{{ $users->nextPageUrl() }}" class="btn btn-outline-primary btn-sm">
                                            <span class="d-none d-sm-inline">Next</span>
                                            <i class="fas fa-chevron-right ms-1"></i>
                                        </a>
                                    @else
                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                            <span class="d-none d-sm-inline">Next</span>
                                            <i class="fas fa-chevron-right ms-1"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <!-- Empty State -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-users fa-4x text-muted opacity-50"></i>
                        </div>
                        <h5 class="text-muted mb-3">Belum Ada Data User</h5>
                        <p class="text-muted mb-4">Silakan tambahkan user pertama untuk memulai pengelolaan sistem.</p>
                        <a href="{{ route('users.create') }}" class="btn btn-primary">
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
.avatar-placeholder {
    width: 45px;
    height: 45px;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}

.table th {
    font-weight: 600;
    background-color: #f8f9fa !important;
    border-bottom: 2px solid #dee2e6;
    color: #495057;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.table td {
    vertical-align: middle;
    border-bottom: 1px solid #f1f3f4;
    padding: 1rem 0.75rem;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
    transition: background-color 0.15s ease-in-out;
}

.card {
    border: 0;
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.card-header {
    background-color: #ffffff;
    border-bottom: 1px solid #f1f3f4;
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

.badge {
    font-size: 0.75rem;
    font-weight: 500;
    padding: 0.5em 0.75em;
    border-radius: 0.375rem;
}

.btn {
    border-radius: 0.375rem;
    font-weight: 500;
    transition: all 0.15s ease-in-out;
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
    transform: translateY(-1px);
}

.form-control, .form-select {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.input-group-text {
    border-radius: 0.375rem 0 0 0.375rem;
    background-color: #f8f9fa;
    border-color: #ced4da;
}

.dropdown-menu {
    border: 0;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
    min-width: 160px;
    z-index: 1000;
    padding: 0.5rem 0;
    position: absolute;
    top: 100%;
    right: 0;
    background-color: white;
    margin: 0;
    list-style: none;
}

.dropdown-menu.show {
    display: block !important;
}

.dropdown {
    position: relative;
}

.dropdown-toggle {
    border: 1px solid #dee2e6;
    background-color: white;
    transition: all 0.15s ease-in-out;
}

.dropdown-toggle:hover {
    background-color: #f8f9fa;
    border-color: #6c757d;
}

.dropdown-toggle:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    outline: none;
}

.dropdown-toggle::after {
    display: none; /* Hide the default Bootstrap caret */
}

.dropdown-item {
    padding: 0.5rem 1rem;
    transition: background-color 0.15s ease-in-out;
    border: none;
    display: flex;
    align-items: center;
    width: 100%;
    text-align: left;
    background: transparent;
    color: #212529;
    text-decoration: none;
    font-size: 0.875rem;
    cursor: pointer;
}

.dropdown-item:hover, .dropdown-item:focus {
    background-color: #f8f9fa;
    color: #1e2125;
    text-decoration: none;
}

.dropdown-item.text-danger:hover {
    background-color: #dc3545;
    color: white;
}

.dropdown-divider {
    height: 0;
    margin: 0.5rem 0;
    overflow: hidden;
    border-top: 1px solid #dee2e6;
}

.alert {
    border: 0;
    border-radius: 0.5rem;
    font-weight: 500;
}

.object-cover {
    object-fit: cover;
}

/* Statistics Cards */
.stat-card {
    transition: all 0.3s ease;
    border: 1px solid transparent !important;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
}

.stat-icon .icon-wrapper {
    transition: transform 0.3s ease;
}

.stat-card:hover .icon-wrapper {
    transform: scale(1.1);
}

.stat-number {
    font-size: 2rem;
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Custom Pagination */
.pagination-controls .btn {
    min-width: 45px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    border-radius: 0.375rem;
    margin: 0 1px;
}

.pagination-pages .btn {
    min-width: 38px;
    height: 38px;
    border-radius: 50%;
    font-weight: 600;
}

.pagination-info {
    font-size: 0.875rem;
    color: #6c757d;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group-vertical .btn {
        margin: 1px 0;
        border-radius: 0.25rem !important;
    }
    
    .avatar-placeholder {
        width: 35px;
        height: 35px;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .stat-card {
        margin-bottom: 1rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .pagination-controls {
        flex-direction: column;
        gap: 10px;
    }
    
    .pagination-pages {
        order: 1;
    }
    
    .pagination-info {
        order: 2;
        text-align: center;
    }
}

/* Animation for statistics cards */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.stat-card {
    animation: fadeInUp 0.5s ease-out;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }
.stat-card:nth-child(5) { animation-delay: 0.5s; }
.stat-card:nth-child(6) { animation-delay: 0.6s; }

/* Loading state */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Enhanced Pagination Styling */
.pagination-controls .btn-outline-primary:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
    transform: translateY(-1px);
}

.pagination-controls .btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
    font-weight: 600;
}

.pagination-controls .btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-pages .btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

/* Alternative button group styling */
.btn-group-alternative .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    margin: 0 1px;
}

/* Table cell positioning for dropdowns */
.table td .dropdown {
    display: inline-block;
}

/* Ensure dropdown appears above table content */
.table-responsive {
    overflow: visible;
}

.table td {
    position: relative;
}
</style>
@endpush

@push('scripts')
<script>
function confirmDelete(userId) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data user akan dihapus permanen dan tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading state
            Swal.fire({
                title: 'Menghapus...',
                text: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Submit the form
            document.getElementById('delete-form-' + userId).submit();
        }
    });
}

// Manual dropdown toggle function
function toggleDropdown(userId) {
    // Close all other dropdowns first
    document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
        if (menu.id !== 'dropdownMenu' + userId) {
            menu.style.display = 'none';
        }
    });
    
    // Toggle current dropdown
    const dropdown = document.getElementById('dropdownMenu' + userId);
    if (dropdown) {
        if (dropdown.style.display === 'none' || dropdown.style.display === '') {
            dropdown.style.display = 'block';
            dropdown.style.position = 'absolute';
            dropdown.style.top = '100%';
            dropdown.style.right = '0';
            dropdown.style.zIndex = '1000';
        } else {
            dropdown.style.display = 'none';
        }
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu').forEach(function(menu) {
            menu.style.display = 'none';
        });
    }
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap components
    initializeBootstrapComponents();
    
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Add loading state to form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
                submitBtn.disabled = true;
            }
        });
    });
});

// Initialize Bootstrap components
function initializeBootstrapComponents() {
    // Initialize all dropdowns
    const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    const dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Debug dropdown functionality
    document.querySelectorAll('.dropdown-toggle').forEach(function(dropdown) {
        dropdown.addEventListener('click', function(e) {
            console.log('Dropdown clicked:', this.id);
            // Fallback for manual dropdown toggle if Bootstrap doesn't work
            const menu = this.nextElementSibling;
            if (menu && menu.classList.contains('dropdown-menu')) {
                menu.classList.toggle('show');
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(function(menu) {
                menu.classList.remove('show');
            });
        }
    });
}

// Add search delay for better UX
let searchTimeout;
const searchInput = document.querySelector('input[name="search"]');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            // Auto-submit form after 1 second of no typing
            // searchInput.form.submit();
        }, 1000);
    });
}
</script>

<!-- Include SweetAlert2 if not already included -->
@if(!isset($swalIncluded))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endif
@endpush