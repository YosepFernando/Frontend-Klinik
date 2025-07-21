@extends('layouts.app')

@section('title', 'Tambah Pegawai Baru')
@section('page-title', 'Tambah Pegawai Baru')

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
    <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
</div>
@endsection

@push('styles')
<style>
    /* Container utama dengan background gradient */
    .create-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    
    .create-form-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1),
            0 8px 32px rgba(31, 38, 135, 0.2);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        margin: 0 auto;
        max-width: 1000px;
    }
    
    .create-form-card:hover {
        transform: translateY(-8px);
        box-shadow: 
            0 30px 80px rgba(0, 0, 0, 0.15),
            0 12px 40px rgba(31, 38, 135, 0.25);
    }
    
    .form-section-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
    
    .form-section-header h4 {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .form-section-header p {
        font-size: 1rem;
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
    
    .section-divider span {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        padding: 0.5rem 1.5rem;
        border-radius: 25px;
        color: #2e7d32;
        font-weight: 700;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border: 2px solid #e8f5e9;
    }
    
    .form-group-enhanced {
        position: relative;
        margin-bottom: 2rem;
    }
    
    .form-label-enhanced {
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.95rem;
        text-transform: capitalize;
    }
    
    .form-label-enhanced i {
        font-size: 1.1rem;
        color: #28a745;
    }
    
    .form-label-enhanced .required {
        color: #e74c3c;
        font-size: 1.2rem;
        margin-left: auto;
    }
    
    .form-control-enhanced, .form-select-enhanced {
        border: 2px solid #e9ecef;
        border-radius: 16px;
        padding: 1rem 1.25rem;
        font-size: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: linear-gradient(135deg, #fafbfc 0%, #f8f9fa 100%);
        font-weight: 500;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.04);
    }
    
    .form-control-enhanced:focus, .form-select-enhanced:focus {
        border-color: #28a745;
        box-shadow: 
            0 0 0 0.3rem rgba(40, 167, 69, 0.15),
            inset 0 2px 4px rgba(0,0,0,0.04),
            0 4px 15px rgba(40, 167, 69, 0.1);
        background: #fff;
        transform: translateY(-2px);
        outline: none;
    }
    
    .form-control-enhanced.is-invalid {
        border-color: #e74c3c;
        background: linear-gradient(135deg, #fff5f5 0%, #ffebee 100%);
        box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.15);
    }
    
    .form-control-enhanced.is-valid {
        border-color: #28a745;
        background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%);
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.15);
    }
    
    .form-select-enhanced {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23495057' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px 12px;
        appearance: none;
        padding-right: 3rem;
    }
    
    .field-icon {
        position: absolute;
        right: 1.25rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }
    
    .form-control-enhanced:focus + .field-icon {
        color: #28a745;
        transform: translateY(-50%) scale(1.1);
    }
    
    .invalid-feedback-enhanced {
        color: #e74c3c;
        font-size: 0.875rem;
        font-weight: 600;
        margin-top: 0.5rem;
        display: block;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #fff5f5 0%, #ffebee 100%);
        border-radius: 12px;
        border-left: 4px solid #e74c3c;
        box-shadow: 0 2px 8px rgba(231, 76, 60, 0.15);
    }
    
    .form-text-enhanced {
        color: #6c757d;
        font-size: 0.875rem;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }
    
    .form-text-enhanced.text-danger {
        color: #dc3545 !important;
    }
    
    .form-text-enhanced a {
        color: inherit;
        font-weight: 600;
    }
    
    .form-text-enhanced a:hover {
        color: #0d6efd;
    }
    
    /* Loading animation for API calls */
    .fa-spinner.fa-spin {
        animation: fa-spin 1s infinite linear;
    }
    
    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* API status indicators */
    .api-status {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        margin-top: 0.5rem;
    }
    
    .api-status.loading {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        color: #1976d2;
        border-left: 4px solid #2196f3;
    }
    
    .api-status.error {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        color: #c62828;
        border-left: 4px solid #f44336;
    }
    
    .api-status.success {
        background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
        color: #2e7d32;
        border-left: 4px solid #4caf50;
    }
    
    .action-buttons {
        display: flex;
        gap: 1.5rem;
        justify-content: center;
        align-items: center;
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 2px solid rgba(0,0,0,0.05);
    }
    
    .btn-enhanced {
        padding: 1rem 2.5rem;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1rem;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
        border: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        min-width: 180px;
        justify-content: center;
    }
    
    .btn-enhanced:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
    }
    
    .btn-enhanced:active {
        transform: translateY(-2px);
    }
    
    .btn-enhanced.btn-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
    }
    
    .btn-enhanced.btn-success:hover {
        background: linear-gradient(135deg, #218838 0%, #1ea085 100%);
        color: white;
    }
    
    .btn-enhanced.btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }
    
    .btn-enhanced.btn-secondary:hover {
        background: linear-gradient(135deg, #5a6268 0%, #3d4145 100%);
        color: white;
    }
    
    .alert-enhanced {
        border: none;
        border-radius: 20px;
        padding: 1.5rem;
        margin: 2rem 0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border-left: 4px solid #ffc107;
    }
    
    .alert-enhanced.alert-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        border-left-color: #dc3545;
        color: #721c24;
    }
    
    .alert-enhanced.alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border-left-color: #28a745;
        color: #155724;
    }
    
    .alert-enhanced .alert-content {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .alert-enhanced i {
        font-size: 1.5rem;
        margin-top: 0.25rem;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .create-container {
            padding: 1rem;
        }
        
        .create-form-card {
            border-radius: 20px;
            margin: 0 0.5rem;
        }
        
        .form-section-header {
            padding: 2rem 1.5rem;
            border-radius: 20px 20px 0 0;
        }
        
        .form-section-header h4 {
            font-size: 1.5rem;
        }
        
        .card-body {
            padding: 1.5rem !important;
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 1rem;
        }
        
        .btn-enhanced {
            width: 100%;
            min-width: auto;
        }
        
        .section-divider {
            margin: 2rem 0 1rem 0;
        }
        
        .form-group-enhanced {
            margin-bottom: 1.5rem;
        }
    }
    
    @media (max-width: 576px) {
        .form-control-enhanced, .form-select-enhanced {
            padding: 0.875rem 1rem;
        }
        
        .btn-enhanced {
            padding: 0.875rem 2rem;
            font-size: 0.95rem;
        }
    }
</style>
@endpush

@section('content')
<div class="create-container">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="create-form-card">
                    <div class="form-section-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h4 class="mb-1">
                                    <i class="fas fa-user-plus me-3"></i> 
                                    Tambah Pegawai Baru
                                </h4>
                                <p class="mb-0">
                                    Lengkapi informasi pegawai dengan data yang akurat dan lengkap
                                </p>
                            </div>
                            <div class="d-none d-lg-block">
                                <i class="fas fa-users fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-5">
                        @if (session('error'))
                            <div class="alert-enhanced alert-danger">
                                <div class="alert-content">
                                    <i class="fas fa-exclamation-triangle text-danger"></i>
                                    <div>
                                        <strong>Error!</strong>
                                        <p class="mb-0 mt-1">{{ session('error') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('pegawai.store') }}" method="POST" id="createPegawaiForm">
                            @csrf
                            
                            <!-- Link to User Account -->
                            <div class="section-divider">
                                <span><i class="fas fa-user-circle me-2"></i>Akun Pengguna</span>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="form-group-enhanced">
                                        <label for="id_user" class="form-label-enhanced">
                                            <i class="fas fa-user-check text-success"></i>
                                            Pilih Akun User
                                            <span class="required">*</span>
                                        </label>
                                        <select class="form-select form-select-enhanced @error('id_user') is-invalid @enderror" 
                                                id="id_user" 
                                                name="id_user" 
                                                required>
                                            <option value="">-- Pilih User --</option>
                                        </select>
                                        <div id="user-loading" class="api-status loading mt-2 d-none">
                                            <i class="fas fa-spinner fa-spin me-2"></i>
                                            Memuat daftar user...
                                        </div>
                                        <div id="user-error" class="api-status error mt-2 d-none">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            <span>Error memuat data</span>
                                            <button class="btn btn-link btn-sm text-danger p-0 ms-2" id="retry-users">
                                                Coba Lagi
                                            </button>
                                        </div>
                                        @error('id_user')
                                        <div class="invalid-feedback-enhanced">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                        <div class="form-text-enhanced">
                                            <i class="fas fa-info-circle text-info me-1"></i>
                                            <span id="user-count">0</span> user tersedia untuk dipilih
                                        </div>
                                        <div class="form-text-enhanced mt-2">
                                            <i class="fas fa-filter text-muted me-1"></i>
                                            Hanya menampilkan user yang belum terdaftar sebagai pegawai
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert-enhanced alert-info mb-4">
                                <div class="alert-content">
                                    <i class="fas fa-info-circle"></i>
                                    <div>
                                        <strong>Informasi!</strong>
                                        <ul class="mb-0 mt-1">
                                            <li>Data akan otomatis terisi dari akun user yang dipilih</li>
                                            <li>Pastikan memilih akun user yang belum terdaftar sebagai pegawai</li>
                                            <li>Jika user yang dibutuhkan tidak ada, silakan buat akun user baru terlebih dahulu</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <div class="section-divider">
                                <span><i class="fas fa-user me-2"></i>Informasi Personal</span>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="form-group-enhanced">
                                        <label for="nama_lengkap" class="form-label-enhanced">
                                            <i class="fas fa-user-tag"></i>
                                            Nama Lengkap 
                                            <span class="required">*</span>
                                        </label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control form-control-enhanced @error('nama_lengkap') is-invalid @enderror" 
                                                   id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" 
                                                   required placeholder="Masukkan nama lengkap pegawai">
                                            <i class="fas fa-user field-icon"></i>
                                        </div>
                                        @error('nama_lengkap')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group-enhanced">
                                        <label for="NIK" class="form-label-enhanced">
                                            <i class="fas fa-id-card"></i>
                                            NIK
                                        </label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control form-control-enhanced @error('NIK') is-invalid @enderror" 
                                                   id="NIK" name="NIK" value="{{ old('NIK') }}" maxlength="16"
                                                   placeholder="Nomor Induk Kependudukan">
                                            <i class="fas fa-credit-card field-icon"></i>
                                        </div>
                                        @error('NIK')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="form-group-enhanced">
                                        <label for="tanggal_lahir" class="form-label-enhanced">
                                            <i class="fas fa-birthday-cake"></i>
                                            Tanggal Lahir
                                        </label>
                                        <div class="position-relative">
                                            <input type="date" class="form-control form-control-enhanced @error('tanggal_lahir') is-invalid @enderror" 
                                                   id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}">
                                            <i class="fas fa-calendar field-icon"></i>
                                        </div>
                                        @error('tanggal_lahir')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group-enhanced">
                                        <label for="jenis_kelamin" class="form-label-enhanced">
                                            <i class="fas fa-venus-mars"></i>
                                            Jenis Kelamin
                                        </label>
                                        <select class="form-select form-select-enhanced @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" name="jenis_kelamin">
                                            <option value="">-- Pilih Jenis Kelamin --</option>
                                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>👨 Laki-laki</option>
                                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>👩 Perempuan</option>
                                        </select>
                                        @error('jenis_kelamin')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="form-group-enhanced">
                                        <label for="agama" class="form-label-enhanced">
                                            <i class="fas fa-pray"></i>
                                            Agama
                                        </label>
                                        <select class="form-select form-select-enhanced @error('agama') is-invalid @enderror" id="agama" name="agama">
                                            <option value="">-- Pilih Agama --</option>
                                            <option value="Islam" {{ old('agama') == 'Islam' ? 'selected' : '' }}>☪️ Islam</option>
                                            <option value="Kristen" {{ old('agama') == 'Kristen' ? 'selected' : '' }}>✝️ Kristen</option>
                                            <option value="Katolik" {{ old('agama') == 'Katolik' ? 'selected' : '' }}>✝️ Katolik</option>
                                            <option value="Hindu" {{ old('agama') == 'Hindu' ? 'selected' : '' }}>🕉️ Hindu</option>
                                            <option value="Buddha" {{ old('agama') == 'Buddha' ? 'selected' : '' }}>☸️ Buddha</option>
                                            <option value="Khonghucu" {{ old('agama') == 'Khonghucu' ? 'selected' : '' }}>☯️ Khonghucu</option>
                                        </select>
                                        @error('agama')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="section-divider">
                                <span><i class="fas fa-address-book me-2"></i>Informasi Kontak</span>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="form-group-enhanced">
                                        <label for="email" class="form-label-enhanced">
                                            <i class="fas fa-envelope"></i>
                                            Email
                                        </label>
                                        <div class="position-relative">
                                            <input type="email" class="form-control form-control-enhanced @error('email') is-invalid @enderror" 
                                                   id="email" name="email" value="{{ old('email') }}"
                                                   placeholder="contoh@email.com">
                                            <i class="fas fa-at field-icon"></i>
                                        </div>
                                        @error('email')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group-enhanced">
                                        <label for="telepon" class="form-label-enhanced">
                                            <i class="fas fa-phone"></i>
                                            Telepon
                                        </label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control form-control-enhanced @error('telepon') is-invalid @enderror" 
                                                   id="telepon" name="telepon" value="{{ old('telepon') }}"
                                                   placeholder="08xxxxxxxxxx">
                                            <i class="fas fa-mobile-alt field-icon"></i>
                                        </div>
                                        @error('telepon')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-12">
                                    <div class="form-group-enhanced">
                                        <label for="alamat" class="form-label-enhanced">
                                            <i class="fas fa-map-marker-alt"></i>
                                            Alamat Lengkap
                                        </label>
                                        <textarea class="form-control form-control-enhanced @error('alamat') is-invalid @enderror" 
                                                  id="alamat" name="alamat" rows="4"
                                                  placeholder="Masukkan alamat lengkap...">{{ old('alamat') }}</textarea>
                                        @error('alamat')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Employment Information -->
                            <div class="section-divider">
                                <span><i class="fas fa-briefcase me-2"></i>Informasi Kepegawaian</span>
                            </div>
                            
                            <div class="row g-4">
                                <div class="col-lg-6">
                                    <div class="form-group-enhanced">
                                        <label for="id_posisi" class="form-label-enhanced">
                                            <i class="fas fa-user-tie"></i>
                                            Posisi 
                                            <span class="required">*</span>
                                        </label>
                                        <select class="form-select form-select-enhanced @error('id_posisi') is-invalid @enderror" id="id_posisi" name="id_posisi" required>
                                            <option value="">-- Pilih Posisi --</option>
                                            @foreach($posisi as $p)
                                                @php
                                                    // Transform posisi array to object if needed
                                                    if (is_array($p)) {
                                                        $p = (object) $p;
                                                    }
                                                @endphp
                                                <option value="{{ $p->id_posisi ?? '' }}" {{ old('id_posisi') == ($p->id_posisi ?? '') ? 'selected' : '' }}>
                                                    {{ ($p->nama_posisi ?? 'Unknown') }} - {{ ($p->departemen ?? 'No Department') }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_posisi')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group-enhanced">
                                        <label for="tanggal_masuk" class="form-label-enhanced">
                                            <i class="fas fa-calendar-plus"></i>
                                            Tanggal Masuk 
                                            <span class="required">*</span>
                                        </label>
                                        <div class="position-relative">
                                            <input type="date" class="form-control form-control-enhanced @error('tanggal_masuk') is-invalid @enderror" 
                                                   id="tanggal_masuk" name="tanggal_masuk" value="{{ old('tanggal_masuk', date('Y-m-d')) }}" required>
                                            <i class="fas fa-calendar field-icon"></i>
                                        </div>
                                        @error('tanggal_masuk')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="action-buttons">
                                <a href="{{ route('pegawai.index') }}" class="btn-enhanced btn-secondary">
                                    <i class="fas fa-arrow-left"></i>
                                    Kembali
                                </a>
                                <button type="submit" class="btn-enhanced btn-success">
                                    <i class="fas fa-save"></i>
                                    Simpan Pegawai
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const userSelect = document.getElementById('id_user');
    const emailInput = document.getElementById('email');
    const namaInput = document.getElementById('nama_lengkap');
    const userLoading = document.getElementById('user-loading');
    const userError = document.getElementById('user-error');
    const userCount = document.getElementById('user-count');
    const retryBtn = document.getElementById('retry-users');
    const form = document.getElementById('createPegawaiForm');
    
    // Global variables
    let allUsers = [];
    let filteredUsers = [];
    let selectedOldValue = "{{ old('id_user') }}";
    let authToken = null;
    
    // Get authentication token from various sources
    function getAuthToken() {
        // Priority order: session storage, local storage, meta tag, session data
        const sources = [
            () => sessionStorage.getItem('auth_token'),
            () => sessionStorage.getItem('api_token'),
            () => localStorage.getItem('auth_token'),
            () => localStorage.getItem('api_token'),
            () => document.querySelector('meta[name="api-token"]')?.getAttribute('content'),
            () => document.querySelector('meta[name="auth-token"]')?.getAttribute('content'),
            () => '{{ session("api_token") ?? "" }}',
            () => '{{ session("auth_token") ?? "" }}',
            @if(auth()->check())
            () => '{{ auth()->user()->api_token ?? "" }}',
            () => '{{ auth()->user()->remember_token ?? "" }}',
            @endif
            // Fallback: try to get from cookies
            () => getCookie('auth_token'),
            () => getCookie('api_token'),
        ];
        
        for (const getToken of sources) {
            try {
                const token = getToken();
                if (token && token.trim() !== '' && token !== 'null' && token !== 'undefined') {
                    console.log('✅ Found authentication token from source');
                    return token.trim();
                }
            } catch (e) {
                console.warn('⚠️ Error getting token from source:', e);
            }
        }
        
        console.warn('⚠️ No authentication token found in any source');
        return null;
    }
    
    // Helper function to get cookie value
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) {
            return parts.pop().split(';').shift();
        }
        return null;
    }
    
    // Initialize authentication token
    authToken = getAuthToken();
    
    // Konfigurasi API
    const API_CONFIG = {
        baseUrl: '{{ url("http://127.0.0.1:8002") }}',
        endpoints: {
            users: '/api/users',
            pegawai: '/api/pegawai',
        },
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    };
    
    // Update API headers with authentication token
    function updateAPIHeaders() {
        const token = getAuthToken();
        if (token) {
            API_CONFIG.headers['Authorization'] = `Bearer ${token}`;
            console.log('✅ API headers updated with authentication token');
        } else {
            delete API_CONFIG.headers['Authorization'];
            console.warn('⚠️ No authentication token available for API calls');
        }
        return !!token;
    }
    
    // Ambil daftar pegawai untuk digunakan sebagai filter
    async function fetchPegawaiData() {
        try {
            const response = await fetch(`${API_CONFIG.baseUrl}${API_CONFIG.endpoints.pegawai}`, {
                method: 'GET',
                headers: API_CONFIG.headers
            });
            
            if (!response.ok) {
                throw new Error('Gagal mengambil data pegawai');
            }
            
            const data = await response.json();
            return data.data?.map(pegawai => pegawai.id_user) || [];
        } catch (error) {
            console.error('Error mengambil data pegawai:', error);
            return [];
        }
    }

    // Ambil data user dari API
    async function fetchUsers() {
        try {
            showLoading(true);
            
            // Pastikan token tersedia
            if (!updateAPIHeaders()) {
                throw new Error('Token autentikasi tidak tersedia. Silakan login kembali.');
            }
            
            console.log('🔄 Mengambil data user dari API...');
            
            // Ambil data pegawai untuk filter
            const existingUserIds = await fetchPegawaiData();
            console.log('� User ID yang sudah terdaftar sebagai pegawai:', existingUserIds);
            
            // Ambil daftar user
            const response = await fetch(`${API_CONFIG.baseUrl}${API_CONFIG.endpoints.users}`, {
                method: 'GET',
                headers: API_CONFIG.headers
            });
            
            console.log('📊 API Response status:', response.status);
            
            if (response.status === 401) {
                throw new Error('Sesi login telah berakhir. Silakan login kembali.');
            }
            
            if (response.status === 403) {
                throw new Error('Anda tidak memiliki akses untuk melihat data user.');
            }
            
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`HTTP ${response.status}: ${response.statusText}${errorText ? ' - ' + errorText : ''}`);
            }
            
            const data = await response.json();
            console.log('📦 API Response data:', data);
            
            // Check API response status
            if (data.status && data.status !== 'success') {
                throw new Error(data.message || 'API returned error status');
            }
            
            // Handle different API response formats
            // New format: data.data.users (nested structure)
            if (data.data && data.data.users) {
                allUsers = data.data.users;
                
                // Log pagination info if available
                if (data.data.pagination) {
                    console.log('📄 Pagination info:', data.data.pagination);
                    const pagination = data.data.pagination;
                    console.log(`📊 Showing ${allUsers.length} of ${pagination.total} users (Page ${pagination.current_page}/${pagination.total_pages})`);
                }
            } else if (data.users) {
                allUsers = data.users;
            } else if (data.data && Array.isArray(data.data)) {
                allUsers = data.data;
            } else if (Array.isArray(data)) {
                allUsers = data;
            } else {
                allUsers = [];
            }
            
            if (!Array.isArray(allUsers)) {
                console.error('❌ Invalid API response format:', data);
                throw new Error('Format data API tidak valid. Harapkan array user.');
            }
            
            // Filter user yang belum terdaftar sebagai pegawai dan bukan pelanggan
            const filteredUsers = allUsers.filter(user => {
                const role = user.role || '';
                const userId = user.id_user || user.id;
                return role !== 'pelanggan' && !existingUserIds.includes(userId);
            });
            
            console.log(`🔍 Filtered ${allUsers.length} users menjadi ${filteredUsers.length} user yang dapat dipilih`);
            allUsers = filteredUsers;
            
            populateUserSelect(allUsers);
            showLoading(false);
            
            console.log(`✅ Successfully loaded ${allUsers.length} users from API`);
            
        } catch (error) {
            console.error('❌ Error fetching users:', error);
            
            // Handle berbagai jenis error
            if (error.message.includes('login') || error.message.includes('Authentication') || error.message.includes('token')) {
                showAuthError('Sesi login Anda telah berakhir. Silakan login kembali.');
            } else if (error.message.includes('network') || error.message.includes('Failed to fetch')) {
                showError('Gagal terhubung ke server. Periksa koneksi internet Anda.');
            } else {
                showError(error.message);
            }
        }
    }
    
    // Populate user select dropdown
    function populateUserSelect(users) {
        userSelect.innerHTML = '<option value="">-- Pilih User --</option>';
        
        users.forEach(user => {
            const option = document.createElement('option');
            // Use the correct field names from API response
            const userId = user.id_user || user.id;
            const userName = user.nama_user || user.name;
            const userEmail = user.email || 'No email';
            const userRole = user.role || 'user';
            
            option.value = userId;
            option.textContent = `${userName} (${userRole}) - ${userEmail}`;
            option.dataset.userData = JSON.stringify({
                id: userId,
                id_user: userId,
                name: userName,
                nama_user: userName,
                email: userEmail,
                role: userRole
            });
            
            // Restore old value if exists
            if (selectedOldValue && selectedOldValue == userId) {
                option.selected = true;
                autoFillFromUser({
                    id: userId,
                    name: userName,
                    email: userEmail,
                    role: userRole
                });
            }
            
            userSelect.appendChild(option);
        });
        
        filteredUsers = users;
        updateUserCount(users.length);
    }
    
    // Show/hide loading state
    function showLoading(show) {
        if (show) {
            userLoading.classList.remove('d-none');
            userError.classList.add('d-none');
            userSelect.innerHTML = '<option value="">-- Loading users... --</option>';
            userSelect.disabled = true;
        } else {
            userLoading.classList.add('d-none');
            userSelect.disabled = false;
        }
    }
    
    // Show/hide error state
    function showError(message) {
        showLoading(false);
        userError.classList.remove('d-none');
        userError.querySelector('i').nextSibling.textContent = ` ${message}. `;
        userSelect.innerHTML = '<option value="">-- Error loading users --</option>';
    }
    
    // Show authentication error with redirect option
    function showAuthError(message) {
        showLoading(false);
        userError.classList.remove('d-none');
        userError.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            ${message} 
            <a href="#" id="auth-redirect" class="text-decoration-underline ms-2">Login Ulang</a>
        `;
        userSelect.innerHTML = '<option value="">-- Authentication Required --</option>';
        
        // Add event listener for login redirect
        const authRedirect = document.getElementById('auth-redirect');
        if (authRedirect) {
            authRedirect.addEventListener('click', function(e) {
                e.preventDefault();
                // Redirect to login page or show login modal
                if (confirm('Sesi login Anda telah berakhir. Redirect ke halaman login?')) {
                    window.location.href = '{{ route("login") ?? "/login" }}';
                }
            });
        }
    }
    
    // Update user count display
    function updateUserCount(count) {
        userCount.textContent = count;
    }
    
    // Auto-fill form fields from selected user
    function autoFillFromUser(user) {
        // Use correct field names from API
        const userName = user.nama_user || user.name;
        const userEmail = user.email;
        
        // Auto-fill name if empty
        if (namaInput && !namaInput.value && userName) {
            namaInput.value = userName;
            namaInput.classList.add('is-valid');
            namaInput.classList.remove('is-invalid');
        }
        
        // Auto-fill email if available
        if (emailInput && userEmail && userEmail !== 'No email') {
            emailInput.value = userEmail;
            emailInput.classList.add('is-valid');
            emailInput.classList.remove('is-invalid');
        }
    }
    
    // Search/filter users
    function filterUsers(searchTerm) {
        const filtered = allUsers.filter(user => {
            const userName = user.nama_user || user.name || '';
            const userEmail = user.email || '';
            const userRole = user.role || '';
            
            return userName.toLowerCase().includes(searchTerm.toLowerCase()) ||
                   userEmail.toLowerCase().includes(searchTerm.toLowerCase()) ||
                   userRole.toLowerCase().includes(searchTerm.toLowerCase());
        });
        
        populateUserSelect(filtered);
        return filtered;
    }
    
    // Event Listeners
    
    // User selection change
    if (userSelect) {
        userSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (selectedOption.value && selectedOption.dataset.userData) {
                const userData = JSON.parse(selectedOption.dataset.userData);
                autoFillFromUser(userData);
                
                // Mark user field as valid
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            } else {
                // Clear fields when no user selected
                if (emailInput) {
                    emailInput.value = '';
                    emailInput.classList.remove('is-valid', 'is-invalid');
                }
                if (namaInput) {
                    namaInput.value = '';
                    namaInput.classList.remove('is-valid', 'is-invalid');
                }
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
        
        // Add search functionality to select
        userSelect.addEventListener('keyup', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                populateUserSelect(allUsers);
                return;
            }
            
            // Simple search implementation
            const searchTerm = this.value;
            if (searchTerm && searchTerm.length > 2) {
                setTimeout(() => {
                    filterUsers(searchTerm);
                }, 300);
            }
        });
    }
    
    // Retry button
    if (retryBtn) {
        retryBtn.addEventListener('click', function(e) {
            e.preventDefault();
            fetchUsers();
        });
    }
    
    // Enhanced form validation for required user field
    if (form) {
        form.addEventListener('submit', function(e) {
            // Check if user is selected
            if (!userSelect.value) {
                e.preventDefault();
                userSelect.classList.add('is-invalid');
                userSelect.focus();
                
                // Show alert
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert-enhanced alert-danger';
                alertDiv.innerHTML = `
                    <div class="alert-content">
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        <div>
                            <strong>Peringatan!</strong>
                            <p class="mb-0 mt-1">Silakan pilih akun user terlebih dahulu sebelum menyimpan data pegawai.</p>
                        </div>
                    </div>
                `;
                
                const cardBody = document.querySelector('.card-body');
                cardBody.insertBefore(alertDiv, cardBody.firstChild);
                
                // Remove alert after 5 seconds
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
                
                return;
            }
            
            // Show loading state on submit button
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
                submitBtn.disabled = true;
            }
        });
    }
    
    // Initialize: Load users from API
    fetchUsers();
    
    // Optional: Refresh users every 5 minutes
    setInterval(() => {
        console.log('🔄 Refreshing user data...');
        fetchUsers();
    }, 5 * 60 * 1000);
});
</script>
@endsection
