@extends('layouts.app')

@section('title', 'Detail User')
@section('page-title', 'Detail User: ' . ($user->name ?? $user->nama_user ?? 'Unknown'))

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
    <a href="{{ route('users.edit', $user->id ?? $user->id_user) }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
        <i class="fas fa-edit me-2"></i> Edit Profil
    </a>
    @if(($user->id ?? $user->id_user) != auth()->id())
        <form action="{{ route('users.destroy', $user->id ?? $user->id_user) }}" method="POST" 
              style="display: inline;" 
              onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger rounded-pill px-4 shadow-sm">
                <i class="fas fa-trash me-2"></i> Hapus
            </button>
        </form>
    @endif
</div>
@endsection

@push('styles')
<style>
    .user-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        backdrop-filter: blur(8px);
    }
    
    .user-avatar-placeholder {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid #fff;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        backdrop-filter: blur(8px);
    }
    
    .profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }
    
    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
    }
    
    .info-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 20px;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        padding: 1.5rem;
    }
    
    .info-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 50px rgba(31, 38, 135, 0.3);
    }
    
    .info-item {
        padding: 1.25rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .info-item:hover {
        background: rgba(102, 126, 234, 0.05);
        border-radius: 10px;
        margin: 0 -1rem;
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    .info-label {
        font-weight: 600;
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .info-value {
        font-size: 1.1rem;
        color: #495057;
        font-weight: 500;
    }
    
    .status-badge {
        padding: 0.6rem 1.2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .activity-stat {
        text-align: center;
        padding: 2rem 1.5rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        margin-bottom: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }
    
    .activity-stat::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
        transition: left 0.5s;
    }
    
    .activity-stat:hover::before {
        left: 100%;
    }
    
    .activity-stat:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
    }
    
    .activity-stat-icon {
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
        transition: all 0.3s ease;
    }
    
    .activity-stat:hover .activity-stat-icon {
        transform: scale(1.1);
    }
    
    .activity-stat-number {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    
    .activity-stat-label {
        font-size: 0.9rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .section-title {
        font-size: 1.4rem;
        font-weight: 600;
        color: #495057;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .section-title::before {
        content: '';
        width: 5px;
        height: 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 3px;
        box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
    }
    
    .quick-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }
    
    .quick-action-btn {
        padding: 0.875rem 1.75rem;
        border-radius: 15px;
        border: none;
        font-weight: 600;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    
    .quick-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }
    
    .timeline-item {
        display: flex;
        align-items: center;
        padding: 1.25rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        margin-bottom: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border-left: 4px solid transparent;
    }
    
    .timeline-item:hover {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        transform: translateX(10px);
        border-left-color: #fff;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }
    
    .timeline-icon {
        width: 55px;
        height: 55px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 1.25rem;
        flex-shrink: 0;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }
    
    .glass-card {
        background: rgba(255, 255, 255, 0.25);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        backdrop-filter: blur(8px);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.18);
    }
    
    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .user-badge {
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 0.9rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
    }
    
    @media (max-width: 768px) {
        .profile-header {
            padding: 1.5rem;
            text-align: center;
            border-radius: 15px;
        }
        
        .quick-actions {
            justify-content: center;
        }
        
        .activity-stat {
            margin-bottom: 1rem;
            padding: 1.5rem;
        }
        
        .info-card {
            border-radius: 15px;
        }
        
        .section-title {
            font-size: 1.2rem;
        }
    }
    
    /* Additional animations */
    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(102, 126, 234, 0); }
        100% { box-shadow: 0 0 0 0 rgba(102, 126, 234, 0); }
    }
    
    .pulse-animation {
        animation: pulse 2s infinite;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- User Profile Card -->
        <div class="col-lg-4">
            <div class="info-card border-0 mb-4">
                <div class="card-body text-center p-4">
                    <div class="avatar-wrapper mb-4 position-relative">
                        @if(!empty($user->foto_profil))
                            <img src="{{ $user->foto_profil }}" alt="Avatar" class="user-avatar pulse-animation">
                        @else
                            <div class="user-avatar-placeholder pulse-animation">
                                <i class="fas fa-user text-white" style="font-size: 3.5rem;"></i>
                            </div>
                        @endif
                        <!-- Online Status Indicator -->
                        <div class="position-absolute bottom-0 end-0 translate-middle">
                            <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }} rounded-pill p-2">
                                <i class="fas fa-circle" style="font-size: 0.5rem;"></i>
                            </span>
                        </div>
                    </div>
                    
                    <h3 class="mb-2 gradient-text fw-bold">
                        {{ $user->name ?? $user->nama_user ?? 'Tidak ada nama' }}
                    </h3>
                    <p class="text-muted mb-3 fs-5">
                        <i class="fas fa-envelope me-2"></i>
                        {{ $user->email ?? 'Tidak ada email' }}
                    </p>
                    
                    @php
                        $roleLabels = [
                            'admin' => ['label' => 'Admin', 'color' => 'danger', 'icon' => 'crown'],
                            'hrd' => ['label' => 'HRD', 'color' => 'warning', 'icon' => 'users-cog'],
                            'dokter' => ['label' => 'Dokter', 'color' => 'info', 'icon' => 'user-md'],
                            'beautician' => ['label' => 'Beautician', 'color' => 'success', 'icon' => 'spa'],
                            'kasir' => ['label' => 'Kasir', 'color' => 'primary', 'icon' => 'cash-register'],
                            'front_office' => ['label' => 'Front Office', 'color' => 'secondary', 'icon' => 'concierge-bell'],
                            'front office' => ['label' => 'Front Office', 'color' => 'secondary', 'icon' => 'concierge-bell'],
                            'pelanggan' => ['label' => 'Pelanggan', 'color' => 'dark', 'icon' => 'user-friends']
                        ];
                        $roleInfo = $roleLabels[$user->role ?? ''] ?? ['label' => ucfirst($user->role ?? 'Unknown'), 'color' => 'secondary', 'icon' => 'user'];
                    @endphp
                    
                    <div class="mb-4">
                        <span class="user-badge bg-{{ $roleInfo['color'] }} text-white">
                            <i class="fas fa-{{ $roleInfo['icon'] }} me-2"></i>
                            {{ $roleInfo['label'] }}
                        </span>
                    </div>
                    
                    <div class="mb-4">
                        <span class="user-badge bg-{{ $user->is_active ? 'success' : 'secondary' }} text-white">
                            <i class="fas fa-{{ $user->is_active ? 'check' : 'times' }}-circle me-2"></i>
                            {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>

                    <!-- Quick Actions -->
                    <div class="quick-actions justify-content-center">
                        @if(($user->id ?? $user->id_user) != auth()->id())
                            <button type="button" class="quick-action-btn btn-{{ $user->is_active ? 'warning' : 'success' }}" onclick="toggleUserStatus()">
                                <i class="fas fa-{{ $user->is_active ? 'pause' : 'play' }}-circle"></i>
                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        @endif
                        <a href="mailto:{{ $user->email }}" class="quick-action-btn btn-info text-black">
                            <i class="fas fa-envelope"></i>
                            Email
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="col-lg-8">
            <!-- Activity Summary -->
            <div class="info-card border-0 mb-4">
                <div class="card-header bg-transparent border-bottom-0 pb-0">
                    <h4 class="section-title">
                        <i class="fas fa-chart-line text-primary"></i> 
                        Ringkasan Aktivitas
                    </h4>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <div class="col-md-3 col-6">
                            <div class="activity-stat">
                                <div class="activity-stat-icon text-primary">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="activity-stat-number text-primary">0</div>
                                <div class="activity-stat-label">Total Login</div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-6">
                            <div class="activity-stat">
                                <div class="activity-stat-icon text-success">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="activity-stat-number text-success">0</div>
                                <div class="activity-stat-label">Aktivitas</div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-6">
                            <div class="activity-stat">
                                <div class="activity-stat-icon text-info">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <div class="activity-stat-number text-info">0</div>
                                <div class="activity-stat-label">Pelatihan</div>
                            </div>
                        </div>
                        
                        <div class="col-md-3 col-6">
                            <div class="activity-stat">
                                <div class="activity-stat-icon text-warning">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="activity-stat-number text-warning">-</div>
                                <div class="activity-stat-label">Rating</div>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row">
                        <div class="col-12">
                            <h5 class="text-muted mb-3">
                                <i class="fas fa-clock me-2"></i>Aktivitas Terakhir
                            </h5>
                            <div class="timeline-item">
                                <div class="timeline-icon">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold">Update profil</h6>
                                    <p class="text-muted mb-0">
                                        @if($user->updated_at)
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            {{ $user->updated_at->diffForHumans() }}
                                        @else
                                            Tidak ada data aktivitas
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Information -->
            <div class="info-card border-0 mb-4">
                <div class="card-header bg-transparent border-bottom-0 pb-0">
                    <h4 class="section-title">
                        <i class="fas fa-info-circle text-primary"></i> 
                        Informasi Detail
                    </h4>
                </div>
                <div class="card-body pt-0">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="text-muted mb-3">
                                    <i class="fas fa-user me-2"></i>Informasi Dasar
                                </h5>
                                
                                <div class="info-item">
                                    <div class="info-label">ID User</div>
                                    <div class="info-value">
                                        <span class="badge bg-light text-dark border">
                                            #{{ $user->id_user ?? $user->id ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Nama Lengkap</div>
                                    <div class="info-value">
                                        <i class="fas fa-user-tag me-2 text-primary"></i>
                                        {{ $user->name ?? $user->nama_user ?? 'Tidak ada nama' }}
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Alamat Email</div>
                                    <div class="info-value">
                                        <i class="fas fa-envelope me-2 text-primary"></i>
                                        <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                                            {{ $user->email ?? 'Tidak ada email' }}
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Peran Pengguna</div>
                                    <div class="info-value">
                                        <span class="badge bg-{{ $roleInfo['color'] }} text-white">
                                            <i class="fas fa-{{ $roleInfo['icon'] }} me-1"></i>
                                            {{ $roleInfo['label'] }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Status Akun</div>
                                    <div class="info-value">
                                        <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }} text-white">
                                            <i class="fas fa-{{ $user->is_active ? 'check' : 'times' }}-circle me-1"></i>
                                            {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="text-muted mb-3">
                                    <i class="fas fa-address-card me-2"></i>Informasi Personal
                                </h5>
                                
                                <div class="info-item">
                                    <div class="info-label">Nomor Telepon</div>
                                    <div class="info-value">
                                        @if($user->no_telp ?? $user->phone)
                                            <i class="fas fa-phone me-2 text-success"></i>
                                            <a href="tel:{{ $user->no_telp ?? $user->phone }}" class="text-decoration-none">
                                                {{ $user->no_telp ?? $user->phone }}
                                            </a>
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-phone-slash me-2"></i>
                                                Tidak tersedia
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Jenis Kelamin</div>
                                    <div class="info-value">
                                        @if($user->gender)
                                            <i class="fas fa-{{ $user->gender == 'male' ? 'mars' : 'venus' }} me-2 text-{{ $user->gender == 'male' ? 'primary' : 'danger' }}"></i>
                                            {{ $user->gender == 'male' ? 'Laki-laki' : 'Perempuan' }}
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-question-circle me-2"></i>
                                                Tidak diketahui
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Tanggal Lahir</div>
                                    <div class="info-value">
                                        @if($user->tanggal_lahir || $user->birth_date)
                                            <i class="fas fa-birthday-cake me-2 text-warning"></i>
                                            {{ $user->birth_date ? $user->birth_date->format('d F Y') : (\Carbon\Carbon::parse($user->tanggal_lahir)->format('d F Y')) }}
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-calendar-times me-2"></i>
                                                Tidak tersedia
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="info-label">Alamat Lengkap</div>
                                    <div class="info-value">
                                        @if($user->address)
                                            <i class="fas fa-map-marker-alt me-2 text-info"></i>
                                            {{ $user->address }}
                                        @else
                                            <span class="text-muted">
                                                <i class="fas fa-map-marker-alt me-2"></i>
                                                Tidak tersedia
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-top pt-4">
                        <!-- System Information -->
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-muted mb-3">
                                    <i class="fas fa-cog me-2"></i>Informasi Sistem
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <div class="info-label">Dibuat</div>
                                            <div class="info-value">
                                                @if($user->created_at)
                                                    <i class="fas fa-calendar-plus me-2 text-success"></i>
                                                    <div>{{ $user->created_at->format('d F Y') }}</div>
                                                    <small class="text-muted">{{ $user->created_at->format('H:i') }} WIB</small>
                                                @else
                                                    <span class="text-muted">
                                                        <i class="fas fa-calendar-times me-2"></i>
                                                        Tidak tersedia
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <div class="info-label">Terakhir Update</div>
                                            <div class="info-value">
                                                @if($user->updated_at)
                                                    <i class="fas fa-calendar-check me-2 text-warning"></i>
                                                    <div>{{ $user->updated_at->format('d F Y') }}</div>
                                                    <small class="text-muted">{{ $user->updated_at->format('H:i') }} WIB</small>
                                                @else
                                                    <span class="text-muted">
                                                        <i class="fas fa-calendar-times me-2"></i>
                                                        Tidak tersedia
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        @if($user->email_verified_at)
                                        <div class="info-item">
                                            <div class="info-label">Email Verified</div>
                                            <div class="info-value">
                                                <i class="fas fa-envelope-check me-2 text-success"></i>
                                                <div>{{ $user->email_verified_at->format('d F Y') }}</div>
                                                <small class="text-muted">{{ $user->email_verified_at->format('H:i') }} WIB</small>
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
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleUserStatus() {
    Swal.fire({
        title: 'Konfirmasi Perubahan Status',
        text: 'Apakah Anda yakin ingin mengubah status pengguna ini?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Ubah!',
        cancelButtonText: 'Batal',
        backdrop: `
            rgba(0,0,123,0.4)
            url("/images/nyan-cat.gif")
            left top
            no-repeat
        `
    }).then((result) => {
        if (result.isConfirmed) {
            // Implementasi placeholder
            Swal.fire({
                title: 'Fitur Segera Tersedia',
                text: 'Fitur ubah status pengguna akan segera tersedia dalam pembaruan berikutnya',
                icon: 'info',
                confirmButtonText: 'Mengerti',
                timer: 3000,
                timerProgressBar: true
            });
        }
    });
}

// Tambahkan animasi halus saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Animasi kartu saat scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe semua kartu
    document.querySelectorAll('.info-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
    
    // Tambahkan animasi klik untuk statistik aktivitas
    document.querySelectorAll('.activity-stat').forEach(stat => {
        stat.addEventListener('click', function() {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
    });
});
</script>

<!-- SweetAlert2 dengan styling kustom -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.swal2-popup {
    border-radius: 20px !important;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25) !important;
}
.swal2-title {
    color: #495057 !important;
    font-weight: 600 !important;
}
.swal2-confirm {
    border-radius: 25px !important;
    padding: 10px 30px !important;
    font-weight: 600 !important;
}
.swal2-cancel {
    border-radius: 25px !important;
    padding: 10px 30px !important;
    font-weight: 600 !important;
}
</style>
@endpush

@endsection
