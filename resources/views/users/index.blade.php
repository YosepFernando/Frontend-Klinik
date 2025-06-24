@extends('layouts.app')

@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('page-actions')
<a href="{{ route('users.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Tambah User
</a>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" 
                           placeholder="Cari nama atau email..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
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
                    <select class="form-select" name="status">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="gender">
                        <option value="">Semua Gender</option>
                        <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Grid -->
    @if($users->count() > 0)
        <div class="row">
            @foreach($users as $user)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 50px; height: 50px;">
                                    <i class="bi bi-person-fill text-white fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-1">{{ $user->name }}</h5>
                                    <p class="card-text text-muted mb-0">
                                        <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'hrd' ? 'warning' : 'info') }}">
                                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-envelope"></i> {{ $user->email }}
                                </small>
                                @if($user->phone)
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-telephone"></i> {{ $user->phone }}
                                    </small>
                                @endif
                                @if($user->gender)
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-gender-{{ $user->gender == 'male' ? 'male' : 'female' }}"></i> 
                                        {{ $user->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}
                                    </small>
                                @endif
                                @if($user->birth_date)
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar"></i> {{ $user->birth_date->format('d/m/Y') }}
                                    </small>
                                @endif
                            </div>
                            
                            <div class="mb-3">
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                </span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    Dibuat: {{ $user->created_at->format('d/m/Y') }}
                                </small>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-info">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" 
                                              style="display: inline;" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $users->appends(request()->query())->links() }}
        </div>
    @else
        <div class="text-center py-5">
            <i class="bi bi-people display-1 text-muted"></i>
            <h4 class="mt-3">Tidak ada user ditemukan</h4>
            <p class="text-muted">Silakan tambah user baru atau ubah kriteria pencarian.</p>
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Tambah User Pertama
            </a>
        </div>
    @endif
</div>
@endsection
