@extends('layouts.app')

@section('title', 'Edit Pegawai - ' . ($pegawai->nama_lengkap ?? 'Tidak Diketahui'))
@section('page-title', 'Edit Pegawai')

@push('head')
{{-- Authentication token meta tags for API access --}}
@if(auth()->check())
<meta name="api-token" content="{{ auth()->user()->api_token ?? session('api_token') ?? '' }}">
<meta name="auth-token" content="{{ session('auth_token') ?? '' }}">
@endif
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('pegawai.show', $pegawai->id ?? $pegawai->id_pegawai) }}" class="btn btn-outline-info rounded-pill px-4 shadow-sm">
        <i class="fas fa-eye me-2"></i> Lihat Detail
    </a>
    <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
</div>
@endsection

@push('styles')
<style>
    /* Container utama dengan background gradient */
    .edit-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    
    .edit-form-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 24px;
        box-shadow: 
            0 20px 60px rgba(0, 0, 0, 0.1),
            0 8px 32px rgba(31, 38, 135, 0.2);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        margin: 0 auto;
        max-width: 1000px;
    }
    
    .edit-form-card:hover {
        transform: translateY(-8px);
        box-shadow: 
            0 30px 80px rgba(0, 0, 0, 0.15),
            0 12px 40px rgba(31, 38, 135, 0.25);
    }
    
    .form-section-header {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
        padding: 2.5rem;
        margin: 0;
        border-radius: 24px 24px 0 0;
        position: relative;
        overflow: hidden;
    }
    
    .form-section-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }
    
    .form-section-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .form-section-header .subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
    }
    
    .section-divider {
        position: relative;
        margin: 2.5rem 0 1.5rem 0;
        text-align: center;
    }
    
    .section-divider:first-of-type {
        margin-top: 1.5rem;
    }
    
    .section-divider span {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        padding: 0.75rem 2rem;
        border-radius: 25px;
        color: #ff9800;
        font-weight: 700;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border: 2px solid #fff3e0;
    }
    
    .form-section {
        background: linear-gradient(135deg, #fafbfc 0%, #f8f9fa 100%);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 
            inset 0 2px 4px rgba(0,0,0,0.04),
            0 4px 15px rgba(0,0,0,0.05);
        border: 1px solid rgba(255, 255, 255, 0.5);
        transition: all 0.3s ease;
        margin-bottom: 2rem;
    }
    
    .form-section:hover {
        transform: translateY(-2px);
        box-shadow: 
            inset 0 2px 4px rgba(0,0,0,0.04),
            0 8px 25px rgba(0,0,0,0.1);
    }
    
    .form-control-enhanced {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        background: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
        font-size: 1rem;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.04);
    }
    
    .form-control-enhanced:focus {
        border-color: #ffc107;
        box-shadow: 
            inset 0 2px 4px rgba(0,0,0,0.04),
            0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        background: rgba(255, 255, 255, 1);
        transform: translateY(-1px);
    }
    
    .form-select-enhanced {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        background: rgba(255, 255, 255, 0.9);
        transition: all 0.3s ease;
        font-size: 1rem;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.04);
    }
    
    .form-select-enhanced:focus {
        border-color: #ffc107;
        box-shadow: 
            inset 0 2px 4px rgba(0,0,0,0.04),
            0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        background: rgba(255, 255, 255, 1);
        transform: translateY(-1px);
    }
    
    .form-label-enhanced {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .form-label-enhanced i {
        color: #ffc107;
        font-size: 1.1rem;
    }
    
    .required-indicator {
        color: #dc3545;
        font-weight: bold;
        margin-left: 0.25rem;
    }
    
    .form-text-enhanced {
        color: #6c757d;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        font-style: italic;
    }
    
    .alert-enhanced {
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }
    
    .alert-danger.alert-enhanced {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border-left: 5px solid #dc3545;
    }
    
    .btn-enhanced {
        padding: 0.75rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: 0.25rem;
    }
    
    .btn-enhanced:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .btn-enhanced.btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
    }
    
    .btn-enhanced.btn-warning:hover {
        background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
        color: white;
    }
    
    .btn-enhanced.btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
    }
    
    .btn-enhanced.btn-info:hover {
        background: linear-gradient(135deg, #138496 0%, #0f6674 100%);
        color: white;
    }
    
    .btn-enhanced.btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }
    
    .btn-enhanced.btn-secondary:hover {
        background: linear-gradient(135deg, #495057 0%, #343a40 100%);
        color: white;
    }
    
    .validation-feedback {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        border-left: 4px solid #dc3545;
    }
    
    .form-control-enhanced.is-invalid {
        border-color: #dc3545;
        background: rgba(248, 215, 218, 0.3);
    }
    
    .form-select-enhanced.is-invalid {
        border-color: #dc3545;
        background: rgba(248, 215, 218, 0.3);
    }
    
    .form-control-enhanced.is-valid {
        border-color: #28a745;
        background: rgba(212, 237, 218, 0.3);
    }
    
    .form-select-enhanced.is-valid {
        border-color: #28a745;
        background: rgba(212, 237, 218, 0.3);
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .edit-container {
            padding: 1rem;
        }
        
        .edit-form-card {
            border-radius: 20px;
            margin: 0 0.5rem;
        }
        
        .form-section-header {
            padding: 2rem 1.5rem;
            border-radius: 20px 20px 0 0;
        }
        
        .form-section-header h2 {
            font-size: 1.5rem;
        }
        
        .form-section {
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .section-divider {
            margin: 2rem 0 1rem 0;
        }
        
        .btn-enhanced {
            padding: 0.5rem 1.5rem;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            width: 100%;
            justify-content: center;
        }
    }
    
    @media (max-width: 576px) {
        .form-section-header {
            padding: 1.5rem 1rem;
        }
        
        .form-section {
            padding: 1rem;
        }
        
        .section-divider span {
            padding: 0.5rem 1.5rem;
            font-size: 0.875rem;
        }
    }
</style>
@endpush
@section('content')
<div class="edit-container">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="edit-form-card">
                    <div class="form-section-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h2 class="mb-1">
                                    <i class="fas fa-user-edit me-3"></i> 
                                    Edit Data Pegawai
                                </h2>
                                <p class="subtitle mb-0">
                                    {{ $pegawai->nama_lengkap ?? 'Nama Tidak Diketahui' }}
                                    @if(isset($pegawai->posisi->nama_posisi))
                                        - {{ $pegawai->posisi->nama_posisi }}
                                    @endif
                                </p>
                            </div>
                            <div class="d-none d-lg-block">
                                <i class="fas fa-edit fa-4x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-5">
                        @if (session('error'))
                            <div class="alert alert-danger alert-enhanced alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Error:</strong> {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('pegawai.update', $pegawai->id ?? $pegawai->id_pegawai) }}" method="POST" id="editPegawaiForm">
                            @csrf
                            @method('PUT')
                            
                            <!-- User Account Section -->
                            <div class="section-divider">
                                <span><i class="fas fa-user-circle me-2"></i>Akun Pengguna</span>
                            </div>
                            
                            <div class="form-section">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <label for="id_user" class="form-label-enhanced">
                                            <i class="fas fa-link"></i>
                                            Link ke Akun User
                                        </label>
                                        <select class="form-select form-select-enhanced @error('id_user') is-invalid @enderror" id="id_user" name="id_user">
                                            <option value="">Pilih User (Opsional)</option>
                                            @foreach($users as $user)
                                                @php
                                                    // Transform user array to object if needed
                                                    if (is_array($user)) {
                                                        $user = (object) $user;
                                                    }
                                                    $userIdValue = $user->id ?? $user->id_user ?? '';
                                                    $currentUserId = old('id_user', $pegawai->id_user ?? '');
                                                @endphp
                                                <option value="{{ $userIdValue }}" {{ $currentUserId == $userIdValue ? 'selected' : '' }}>
                                                    {{ ($user->name ?? $user->nama_user ?? 'Unknown') }} ({{ ucfirst($user->role ?? 'user') }}) - {{ $user->email ?? 'No email' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_user')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text-enhanced">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Pilih user jika pegawai ini sudah memiliki akun login sistem
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information Section -->
                            <div class="section-divider">
                                <span><i class="fas fa-user me-2"></i>Informasi Personal</span>
                            </div>
                            
                            <div class="form-section">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="nama_lengkap" class="form-label-enhanced">
                                            <i class="fas fa-id-badge"></i>
                                            Nama Lengkap
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-enhanced @error('nama_lengkap') is-invalid @enderror" 
                                               id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap', $pegawai->nama_lengkap ?? '') }}" 
                                               required placeholder="Masukkan nama lengkap">
                                        @error('nama_lengkap')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="NIK" class="form-label-enhanced">
                                            <i class="fas fa-id-card"></i>
                                            NIK (Nomor Induk Kependudukan)
                                        </label>
                                        <input type="text" class="form-control form-control-enhanced @error('NIK') is-invalid @enderror" 
                                               id="NIK" name="NIK" value="{{ old('NIK', $pegawai->NIK ?? '') }}" 
                                               maxlength="16" placeholder="16 digit NIK">
                                        @error('NIK')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text-enhanced">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Masukkan 16 digit NIK sesuai KTP
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="tanggal_lahir" class="form-label-enhanced">
                                            <i class="fas fa-birthday-cake"></i>
                                            Tanggal Lahir
                                        </label>
                                        @php
                                            $tanggalLahir = '';
                                            if (isset($pegawai->tanggal_lahir)) {
                                                if (is_string($pegawai->tanggal_lahir)) {
                                                    try {
                                                        $tanggalLahir = \Carbon\Carbon::parse($pegawai->tanggal_lahir)->format('Y-m-d');
                                                    } catch (\Exception $e) {
                                                        $tanggalLahir = '';
                                                    }
                                                } elseif (is_object($pegawai->tanggal_lahir)) {
                                                    $tanggalLahir = $pegawai->tanggal_lahir->format('Y-m-d');
                                                }
                                            }
                                        @endphp
                                        <input type="date" class="form-control form-control-enhanced @error('tanggal_lahir') is-invalid @enderror" 
                                               id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $tanggalLahir) }}">
                                        @error('tanggal_lahir')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="jenis_kelamin" class="form-label-enhanced">
                                            <i class="fas fa-venus-mars"></i>
                                            Jenis Kelamin
                                        </label>
                                        <select class="form-select form-select-enhanced @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" name="jenis_kelamin">
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="L" {{ old('jenis_kelamin', $pegawai->jenis_kelamin ?? '') == 'L' ? 'selected' : '' }}>
                                                👨 Laki-laki
                                            </option>
                                            <option value="P" {{ old('jenis_kelamin', $pegawai->jenis_kelamin ?? '') == 'P' ? 'selected' : '' }}>
                                                👩 Perempuan
                                            </option>
                                        </select>
                                        @error('jenis_kelamin')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="agama" class="form-label-enhanced">
                                            <i class="fas fa-pray"></i>
                                            Agama
                                        </label>
                                        <select class="form-select form-select-enhanced @error('agama') is-invalid @enderror" id="agama" name="agama">
                                            <option value="">Pilih Agama</option>
                                            <option value="Islam" {{ old('agama', $pegawai->agama ?? '') == 'Islam' ? 'selected' : '' }}>🕌 Islam</option>
                                            <option value="Kristen" {{ old('agama', $pegawai->agama ?? '') == 'Kristen' ? 'selected' : '' }}>⛪ Kristen</option>
                                            <option value="Katolik" {{ old('agama', $pegawai->agama ?? '') == 'Katolik' ? 'selected' : '' }}>⛪ Katolik</option>
                                            <option value="Hindu" {{ old('agama', $pegawai->agama ?? '') == 'Hindu' ? 'selected' : '' }}>🕉️ Hindu</option>
                                            <option value="Buddha" {{ old('agama', $pegawai->agama ?? '') == 'Buddha' ? 'selected' : '' }}>☸️ Buddha</option>
                                            <option value="Khonghucu" {{ old('agama', $pegawai->agama ?? '') == 'Khonghucu' ? 'selected' : '' }}>☯️ Khonghucu</option>
                                        </select>
                                        @error('agama')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information Section -->
                            <div class="section-divider">
                                <span><i class="fas fa-address-book me-2"></i>Informasi Kontak</span>
                            </div>
                            
                            <div class="form-section">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label-enhanced">
                                            <i class="fas fa-envelope"></i>
                                            Email
                                        </label>
                                        <input type="email" class="form-control form-control-enhanced @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email', $pegawai->email ?? '') }}" 
                                               placeholder="contoh@email.com">
                                        @error('email')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text-enhanced">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Email akan otomatis terisi jika memilih akun user
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="telepon" class="form-label-enhanced">
                                            <i class="fas fa-phone"></i>
                                            Nomor Telepon
                                        </label>
                                        <input type="text" class="form-control form-control-enhanced @error('telepon') is-invalid @enderror" 
                                               id="telepon" name="telepon" value="{{ old('telepon', $pegawai->telepon ?? '') }}" 
                                               placeholder="08123456789">
                                        @error('telepon')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text-enhanced">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Masukkan nomor yang dapat dihubungi
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="alamat" class="form-label-enhanced">
                                            <i class="fas fa-map-marker-alt"></i>
                                            Alamat Lengkap
                                        </label>
                                        <textarea class="form-control form-control-enhanced @error('alamat') is-invalid @enderror" 
                                                  id="alamat" name="alamat" rows="3" 
                                                  placeholder="Masukkan alamat lengkap tempat tinggal">{{ old('alamat', $pegawai->alamat ?? '') }}</textarea>
                                        @error('alamat')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Employment Information Section -->
                            <div class="section-divider">
                                <span><i class="fas fa-briefcase me-2"></i>Informasi Kepegawaian</span>
                            </div>
                            
                            <div class="form-section">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="id_posisi" class="form-label-enhanced">
                                            <i class="fas fa-user-tie"></i>
                                            Posisi Jabatan
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <select class="form-select form-select-enhanced @error('id_posisi') is-invalid @enderror" id="id_posisi" name="id_posisi" required>
                                            <option value="">Pilih Posisi</option>
                                            @foreach($posisi as $p)
                                                @php
                                                    // Handle both array and object formats for posisi
                                                    if (is_array($p)) {
                                                        $p = (object) $p;
                                                    }
                                                    $posisiId = $p->id_posisi ?? $p->id ?? '';
                                                    $currentPosisiId = old('id_posisi', $pegawai->id_posisi ?? '');
                                                @endphp
                                                <option value="{{ $posisiId }}" {{ $currentPosisiId == $posisiId ? 'selected' : '' }}>
                                                    {{ $p->nama_posisi ?? 'Unknown Position' }}{{ isset($p->departemen) ? ' - ' . $p->departemen : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_posisi')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text-enhanced">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Pilih posisi sesuai dengan jabatan pegawai
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="tanggal_masuk" class="form-label-enhanced">
                                            <i class="fas fa-calendar-plus"></i>
                                            Tanggal Masuk Kerja
                                            <span class="required-indicator">*</span>
                                        </label>
                                        @php
                                            $tanggalMasuk = '';
                                            if (isset($pegawai->tanggal_masuk)) {
                                                if (is_string($pegawai->tanggal_masuk)) {
                                                    try {
                                                        $tanggalMasuk = \Carbon\Carbon::parse($pegawai->tanggal_masuk)->format('Y-m-d');
                                                    } catch (\Exception $e) {
                                                        $tanggalMasuk = '';
                                                    }
                                                } elseif (is_object($pegawai->tanggal_masuk)) {
                                                    $tanggalMasuk = $pegawai->tanggal_masuk->format('Y-m-d');
                                                }
                                            }
                                        @endphp
                                        <input type="date" class="form-control form-control-enhanced @error('tanggal_masuk') is-invalid @enderror" 
                                               id="tanggal_masuk" name="tanggal_masuk" value="{{ old('tanggal_masuk', $tanggalMasuk) }}" required>
                                        @error('tanggal_masuk')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="tanggal_keluar" class="form-label-enhanced">
                                            <i class="fas fa-calendar-times"></i>
                                            Tanggal Keluar (Opsional)
                                        </label>
                                        @php
                                            $tanggalKeluar = '';
                                            if (isset($pegawai->tanggal_keluar) && $pegawai->tanggal_keluar) {
                                                if (is_string($pegawai->tanggal_keluar)) {
                                                    try {
                                                        $tanggalKeluar = \Carbon\Carbon::parse($pegawai->tanggal_keluar)->format('Y-m-d');
                                                    } catch (\Exception $e) {
                                                        $tanggalKeluar = '';
                                                    }
                                                } elseif (is_object($pegawai->tanggal_keluar)) {
                                                    $tanggalKeluar = $pegawai->tanggal_keluar->format('Y-m-d');
                                                }
                                            }
                                        @endphp
                                        <input type="date" class="form-control form-control-enhanced @error('tanggal_keluar') is-invalid @enderror" 
                                               id="tanggal_keluar" name="tanggal_keluar" value="{{ old('tanggal_keluar', $tanggalKeluar) }}">
                                        @error('tanggal_keluar')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text-enhanced">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Kosongkan jika pegawai masih aktif bekerja
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons Section -->
                            <div class="section-divider">
                                <span><i class="fas fa-cogs me-2"></i>Aksi</span>
                            </div>
                            
                            <div class="form-section">
                                <div class="d-flex flex-wrap justify-content-center gap-3">
                                    <button type="submit" class="btn-enhanced btn-warning">
                                        <i class="fas fa-save me-2"></i>Update Data Pegawai
                                    </button>
                                    <a href="{{ route('pegawai.show', $pegawai->id ?? $pegawai->id_pegawai) }}" class="btn-enhanced btn-info">
                                        <i class="fas fa-eye me-2"></i>Lihat Detail
                                    </a>
                                    <a href="{{ route('pegawai.index') }}" class="btn-enhanced btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill email from selected user
    const userSelect = document.getElementById('id_user');
    const emailInput = document.getElementById('email');
    
    if (userSelect && emailInput) {
        userSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                // Extract email from option text (format: "Name (role) - email")
                const optionText = selectedOption.text;
                const emailMatch = optionText.match(/- (.+)$/);
                if (emailMatch && emailMatch[1] !== 'No email') {
                    emailInput.value = emailMatch[1];
                }
            } else {
                emailInput.value = '';
            }
        });
    }
    
    // Phone number formatting
    const phoneInput = document.getElementById('telepon');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            // Remove non-numeric characters
            let value = this.value.replace(/\D/g, '');
            
            // Limit to reasonable phone number length
            if (value.length > 15) {
                value = value.substring(0, 15);
            }
            
            this.value = value;
        });
    }
    
    // NIK formatting (16 digits)
    const nikInput = document.getElementById('NIK');
    if (nikInput) {
        nikInput.addEventListener('input', function() {
            // Remove non-numeric characters
            let value = this.value.replace(/\D/g, '');
            
            // Limit to 16 digits
            if (value.length > 16) {
                value = value.substring(0, 16);
            }
            
            this.value = value;
        });
    }
    
    // Date validation for tanggal_keluar
    const tanggalMasukInput = document.getElementById('tanggal_masuk');
    const tanggalKeluarInput = document.getElementById('tanggal_keluar');
    
    if (tanggalKeluarInput && tanggalMasukInput) {
        tanggalKeluarInput.addEventListener('change', function() {
            const tanggalMasuk = tanggalMasukInput.value;
            const tanggalKeluar = this.value;
            
            if (tanggalMasuk && tanggalKeluar && tanggalKeluar <= tanggalMasuk) {
                // Show custom alert with better styling
                showAlert('Tanggal keluar harus setelah tanggal masuk!', 'warning');
                this.value = '';
                this.classList.add('is-invalid');
            } else if (this.value) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }
    
    // Real-time form validation
    const form = document.getElementById('editPegawaiForm');
    const requiredFields = form.querySelectorAll('[required]');
    
    // Add input listeners for real-time validation
    requiredFields.forEach(field => {
        field.addEventListener('input', function() {
            // Remove error styling when user starts typing
            if (this.classList.contains('is-invalid')) {
                this.classList.remove('is-invalid');
            }
            
            // Add success styling for filled required fields
            if (this.value.trim() !== '' && this.checkValidity()) {
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
            }
        });
    });
    
    // Email validation
    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            if (this.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(this.value)) {
                    this.classList.add('is-invalid');
                    this.classList.remove('is-valid');
                } else {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                }
            }
        });
    }
    
    // Form submission enhancement
    form.addEventListener('submit', function(e) {
        // Show loading state on submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan Perubahan...';
            submitBtn.disabled = true;
        }
    });
    
    // Add smooth animations for form interactions
    const formControls = document.querySelectorAll('.form-control-enhanced, .form-select-enhanced');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
            this.parentElement.style.transition = 'transform 0.2s ease';
        });
        
        control.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });
    
    // Custom alert function
    function showAlert(message, type = 'info') {
        // Remove existing alerts
        const existingAlerts = document.querySelectorAll('.custom-alert');
        existingAlerts.forEach(alert => alert.remove());
        
        // Create new alert
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-enhanced custom-alert alert-dismissible fade show`;
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';
        
        const icon = type === 'warning' ? 'fas fa-exclamation-triangle' : 
                    type === 'success' ? 'fas fa-check-circle' : 'fas fa-info-circle';
        
        alertDiv.innerHTML = `
            <i class="${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv && alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
});
</script>
@endpush

@endsection
