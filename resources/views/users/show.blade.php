@extends('layouts.app')

@section('title', 'Detail User')
@section('page-title', 'Detail User: ' . $user->name)

@section('page-actions')
<div class="btn-group">
    <a href="{{ route('users.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning">
        <i class="bi bi-pencil"></i> Edit
    </a>
    @if($user->id !== auth()->id())
        <form action="{{ route('users.destroy', $user) }}" method="POST" 
              style="display: inline;" 
              onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash"></i> Hapus
            </button>
        </form>
    @endif
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- User Profile Card -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <i class="bi bi-person-fill text-white" style="font-size: 2.5rem;"></i>
                    </div>
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                    
                    <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'hrd' ? 'warning' : 'info') }} fs-6 mb-3">
                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                    </span>
                    
                    <div class="mb-3">
                        <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }} fs-6">
                            {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>

                    @if($user->id !== auth()->id())
                        <form action="{{ route('users.toggle-status', $user) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-{{ $user->is_active ? 'warning' : 'success' }}">
                                <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}-circle"></i>
                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-info-circle"></i> Informasi Detail
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Informasi Dasar</h6>
                            
                            <table class="table table-borderless">
                                <tr>
                                    <td width="120"><strong>Nama:</strong></td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Role:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'hrd' ? 'warning' : 'info') }}">
                                            {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                            {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Informasi Personal</h6>
                            
                            <table class="table table-borderless">
                                <tr>
                                    <td width="120"><strong>Telepon:</strong></td>
                                    <td>{{ $user->phone ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Kelamin:</strong></td>
                                    <td>
                                        @if($user->gender)
                                            <i class="bi bi-gender-{{ $user->gender == 'male' ? 'male' : 'female' }}"></i>
                                            {{ $user->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Lahir:</strong></td>
                                    <td>{{ $user->birth_date ? $user->birth_date->format('d F Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat:</strong></td>
                                    <td>{{ $user->address ?: '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- System Information -->
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-3">Informasi Sistem</h6>
                            
                            <table class="table table-borderless">
                                <tr>
                                    <td width="120"><strong>Dibuat:</strong></td>
                                    <td>{{ $user->created_at->format('d F Y, H:i') }} WIB</td>
                                </tr>
                                <tr>
                                    <td><strong>Terakhir Update:</strong></td>
                                    <td>{{ $user->updated_at->format('d F Y, H:i') }} WIB</td>
                                </tr>
                                @if($user->email_verified_at)
                                <tr>
                                    <td><strong>Email Verified:</strong></td>
                                    <td>{{ $user->email_verified_at->format('d F Y, H:i') }} WIB</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Summary (based on role) -->
            @if(!$user->isPelanggan())
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-activity"></i> Ringkasan Aktivitas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            @if($user->isDokter() || $user->isBeautician())
                                <div class="col-md-3">
                                    <div class="p-3">
                                        <h4 class="text-primary">{{ $user->staffAppointments->count() }}</h4>
                                        <p class="text-muted mb-0">Appointment Handled</p>
                                    </div>
                                </div>
                            @endif
                            
                            @if(!$user->isPelanggan())
                                <div class="col-md-3">
                                    <div class="p-3">
                                        <h4 class="text-success">{{ $user->attendances->count() }}</h4>
                                        <p class="text-muted mb-0">Total Absensi</p>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="col-md-3">
                                <div class="p-3">
                                    <h4 class="text-info">{{ $user->trainingParticipations->count() }}</h4>
                                    <p class="text-muted mb-0">Pelatihan Diikuti</p>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="p-3">
                                    <h4 class="text-warning">{{ $user->religiousStudyParticipations->count() }}</h4>
                                    <p class="text-muted mb-0">Penggajian Diikuti</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
