@extends('layouts.app')

@section('title', 'Edit Pengguna')
@section('page-title', 'Edit Pengguna: ' . ($user->name ?? $user->nama_user ?? 'Unknown'))

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
    <a href="{{ route('users.show', $user->id ?? $user->id_user) }}" class="btn btn-outline-info rounded-pill px-4 shadow-sm">
        <i class="fas fa-eye me-2"></i> Lihat Detail
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
        max-width: 1200px;
    }
    
    .edit-form-card:hover {
        transform: translateY(-8px);
        box-shadow: 
            0 30px 80px rgba(0, 0, 0, 0.15),
            0 12px 40px rgba(31, 38, 135, 0.25);
    }
    
    .form-section-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        color: #495057;
        font-weight: 700;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border: 2px solid #e9ecef;
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
        border-color: #667eea;
        box-shadow: 
            0 0 0 0.3rem rgba(102, 126, 234, 0.15),
            inset 0 2px 4px rgba(0,0,0,0.04),
            0 4px 15px rgba(102, 126, 234, 0.1);
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
        border-color: #27ae60;
        background: linear-gradient(135deg, #f8fff8 0%, #e8f5e8 100%);
        box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.15);
    }
    
    .form-select-enhanced {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23495057' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 16px 12px;
        appearance: none;
        padding-right: 3rem;
    }
    
    .form-check-enhanced {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 16px;
        padding: 1.5rem;
        border: 2px solid #e9ecef;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin: 1rem 0;
    }
    
    .form-check-enhanced:hover {
        border-color: #667eea;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    }
    
    .form-check-input-enhanced {
        width: 1.4rem;
        height: 1.4rem;
        margin-top: 0;
        border: 2px solid #667eea;
        border-radius: 6px;
    }
    
    .form-check-input-enhanced:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
    
    .form-check-label-enhanced {
        font-weight: 600;
        color: #2c3e50;
        margin-left: 1rem;
        font-size: 1rem;
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
        color: #667eea;
        transform: translateY(-50%) scale(1.1);
    }
    
    .info-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 20px;
        padding: 2rem;
        border: 2px solid #e9ecef;
        margin: 2rem 0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    
    .info-section h6 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.1rem;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.08);
        font-size: 0.95rem;
    }
    
    .info-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    
    .info-item span:first-child {
        color: #6c757d;
        font-weight: 500;
    }
    
    .info-item span:last-child {
        color: #2c3e50;
        font-weight: 600;
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
    
    .btn-enhanced.btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-enhanced.btn-primary:hover {
        background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
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
    
    .progress-indicator {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.75rem;
        margin: 2rem 0;
        padding: 1rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 20px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .progress-step {
        width: 50px;
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .progress-step.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    }
    
    .progress-step.active::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        animation: shimmer 2s infinite;
    }
    
    @keyframes shimmer {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    
    .alert-enhanced {
        border: none;
        border-radius: 20px;
        padding: 1.5rem;
        margin: 2rem 0;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-left: 4px solid #2196f3;
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
    
    .alert-enhanced h6 {
        color: #1976d2;
        font-weight: 700;
        margin-bottom: 0.75rem;
    }
    
    .alert-enhanced ul {
        margin: 0;
        padding-left: 1.25rem;
    }
    
    .alert-enhanced li {
        margin-bottom: 0.5rem;
        color: #1565c0;
        font-weight: 500;
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
        .progress-indicator {
            gap: 0.5rem;
            padding: 0.75rem;
        }
        
        .progress-step {
            width: 40px;
            height: 5px;
        }
        
        .form-control-enhanced, .form-select-enhanced {
            padding: 0.875rem 1rem;
        }
        
        .btn-enhanced {
            padding: 0.875rem 2rem;
            font-size: 0.95rem;
        }
    }
    
    .form-label-enhanced .required {
        color: #dc3545;
        font-size: 1.1rem;
    }
    
    .form-control-enhanced {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #fafbfc;
    }
    
    .form-control-enhanced:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        background: #fff;
        transform: translateY(-1px);
    }
    
    .form-control-enhanced.is-invalid {
        border-color: #dc3545;
        background: #fff5f5;
    }
    
    .form-control-enhanced.is-valid {
        border-color: #28a745;
        background: #f8fff8;
    }
    
    .form-select-enhanced {
        border: 2px solid #e9ecef;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #fafbfc url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e") no-repeat right 0.75rem center/16px 12px;
        appearance: none;
    }
    
    .form-select-enhanced:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        background-color: #fff;
        transform: translateY(-1px);
    }
    
    .form-check-enhanced {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 12px;
        padding: 1rem;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .form-check-enhanced:hover {
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
    }
    
    .form-check-input-enhanced {
        width: 1.2rem;
        height: 1.2rem;
        margin-top: 0.1rem;
    }
    
    .form-check-label-enhanced {
        font-weight: 600;
        color: #495057;
        margin-left: 0.5rem;
    }
    
    .info-section {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
        padding: 1.5rem;
        border: 2px solid #e9ecef;
        margin: 1.5rem 0;
    }
    
    .info-section h6 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.5rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .info-item:last-child {
        border-bottom: none;
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 2px solid #e9ecef;
    }
    
    .btn-enhanced {
        padding: 0.875rem 2rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: none;
    }
    
    .btn-enhanced:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }
    
    .btn-enhanced.btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .btn-enhanced.btn-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }
    
    .invalid-feedback-enhanced {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
        padding: 0.5rem;
        background: #fff5f5;
        border-radius: 8px;
        border-left: 4px solid #dc3545;
    }
    
    .form-text-enhanced {
        color: #6c757d;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .field-icon {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
    }
    
    .progress-indicator {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin: 1rem 0;
    }
    
    .progress-step {
        width: 40px;
        height: 4px;
        background: #e9ecef;
        border-radius: 2px;
        transition: all 0.3s ease;
    }
    
    .progress-step.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    @media (max-width: 768px) {
        .edit-form-card {
            border-radius: 15px;
            margin: 1rem;
        }
        
        .form-section-header {
            margin: -1rem -1rem 1.5rem -1rem;
            padding: 1rem;
            border-radius: 15px 15px 0 0;
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .btn-enhanced {
            justify-content: center;
            width: 100%;
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
                                <h4 class="mb-1">
                                    <i class="fas fa-user-edit me-3"></i> 
                                    Edit Profil Pengguna
                                </h4>
                                <p class="mb-0">
                                    Perbarui informasi pengguna dengan lengkap dan akurat
                                </p>
                            </div>
                            <div class="d-none d-lg-block">
                                <i class="fas fa-edit fa-3x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-5">
                        <!-- Progress Indicator -->
                        <div class="progress-indicator">
                            <div class="progress-step active"></div>
                            <div class="progress-step active"></div>
                            <div class="progress-step"></div>
                        </div>

                        <form action="{{ route('users.update', $user->id ?? $user->id_user) }}" method="POST" id="editUserForm">
                            @csrf
                            @method('PUT')
                            
                            <div class="row g-4">
                                <!-- Informasi Dasar -->
                                <div class="col-lg-6">
                                    <div class="section-divider">
                                        <span><i class="fas fa-user me-2"></i>Informasi Dasar</span>
                                    </div>
                                    
                                    <div class="form-group-enhanced">
                                        <label for="name" class="form-label-enhanced">
                                            <i class="fas fa-user-tag text-primary"></i>
                                            Nama Lengkap 
                                            <span class="required">*</span>
                                        </label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control form-control-enhanced @error('name') is-invalid @enderror" 
                                                   id="name" name="name" 
                                                   value="{{ old('name', $user->name ?? $user->nama_user) }}" 
                                                   required
                                                   placeholder="Masukkan nama lengkap pengguna">
                                            <i class="fas fa-user field-icon"></i>
                                        </div>
                                        @error('name')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group-enhanced">
                                        <label for="email" class="form-label-enhanced">
                                            <i class="fas fa-envelope text-primary"></i>
                                            Alamat Email 
                                            <span class="required">*</span>
                                        </label>
                                        <div class="position-relative">
                                            <input type="email" class="form-control form-control-enhanced @error('email') is-invalid @enderror" 
                                                   id="email" name="email" 
                                                   value="{{ old('email', $user->email) }}" 
                                                   required
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

                                    <div class="form-group-enhanced">
                                        <label for="role" class="form-label-enhanced">
                                            <i class="fas fa-user-cog text-primary"></i>
                                            Peran Pengguna 
                                            <span class="required">*</span>
                                        </label>
                                        <select class="form-select form-select-enhanced @error('role') is-invalid @enderror" 
                                                id="role" name="role" required>
                                            <option value="">-- Pilih Peran Pengguna --</option>
                                            @foreach($roles as $key => $label)
                                                <option value="{{ $key }}" {{ old('role', $user->role) == $key ? 'selected' : '' }}>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('role')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group-enhanced">
                                        <label for="password" class="form-label-enhanced">
                                            <i class="fas fa-lock text-warning"></i>
                                            Password Baru
                                        </label>
                                        <div class="position-relative">
                                            <input type="password" class="form-control form-control-enhanced @error('password') is-invalid @enderror" 
                                                   id="password" name="password"
                                                   placeholder="Masukkan password baru">
                                            <i class="fas fa-eye field-icon" id="togglePassword" style="cursor: pointer;"></i>
                                        </div>
                                        <div class="form-text-enhanced mt-2">
                                            <i class="fas fa-info-circle text-info"></i>
                                            Kosongkan jika tidak ingin mengubah password
                                        </div>
                                        @error('password')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group-enhanced">
                                        <label for="password_confirmation" class="form-label-enhanced">
                                            <i class="fas fa-lock text-warning"></i>
                                            Konfirmasi Password Baru
                                        </label>
                                        <div class="position-relative">
                                            <input type="password" class="form-control form-control-enhanced" 
                                                   id="password_confirmation" name="password_confirmation"
                                                   placeholder="Ulangi password baru">
                                            <i class="fas fa-eye field-icon" id="togglePasswordConfirm" style="cursor: pointer;"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informasi Personal -->
                                <div class="col-lg-6">
                                    <div class="section-divider">
                                        <span><i class="fas fa-address-card me-2"></i>Informasi Personal</span>
                                    </div>
                                    
                                    <div class="form-group-enhanced">
                                        <label for="gender" class="form-label-enhanced">
                                            <i class="fas fa-venus-mars text-primary"></i>
                                            Jenis Kelamin 
                                            <span class="required">*</span>
                                        </label>
                                        <select class="form-select form-select-enhanced @error('gender') is-invalid @enderror" 
                                                id="gender" name="gender" required>
                                            <option value="">-- Pilih Jenis Kelamin --</option>
                                            <option value="male" {{ old('gender', $user->gender) == 'male' ? 'selected' : '' }}>
                                                👨 Laki-laki
                                            </option>
                                            <option value="female" {{ old('gender', $user->gender) == 'female' ? 'selected' : '' }}>
                                                👩 Perempuan
                                            </option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group-enhanced">
                                        <label for="phone" class="form-label-enhanced">
                                            <i class="fas fa-phone text-success"></i>
                                            Nomor Telepon
                                        </label>
                                        <div class="position-relative">
                                            <input type="tel" class="form-control form-control-enhanced @error('phone') is-invalid @enderror" 
                                                   id="phone" name="phone" 
                                                   value="{{ old('phone', $user->phone ?? $user->no_telp) }}"
                                                   placeholder="08xxxxxxxxxx">
                                            <i class="fas fa-mobile-alt field-icon"></i>
                                        </div>
                                        @error('phone')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group-enhanced">
                                        <label for="birth_date" class="form-label-enhanced">
                                            <i class="fas fa-birthday-cake text-warning"></i>
                                            Tanggal Lahir
                                        </label>
                                        <div class="position-relative">
                                            <input type="date" class="form-control form-control-enhanced @error('birth_date') is-invalid @enderror" 
                                                   id="birth_date" name="birth_date" 
                                                   value="{{ old('birth_date', $user->birth_date ? $user->birth_date->format('Y-m-d') : ($user->tanggal_lahir ? \Carbon\Carbon::parse($user->tanggal_lahir)->format('Y-m-d') : '')) }}">
                                            <i class="fas fa-calendar field-icon"></i>
                                        </div>
                                        @error('birth_date')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group-enhanced">
                                        <label for="address" class="form-label-enhanced">
                                            <i class="fas fa-map-marker-alt text-info"></i>
                                            Alamat Lengkap
                                        </label>
                                        <textarea class="form-control form-control-enhanced @error('address') is-invalid @enderror" 
                                                  id="address" name="address" rows="4"
                                                  placeholder="Masukkan alamat lengkap...">{{ old('address', $user->address) }}</textarea>
                                        @error('address')
                                            <div class="invalid-feedback-enhanced">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group-enhanced">
                                        <div class="form-check-enhanced">
                                            <input class="form-check-input form-check-input-enhanced" type="checkbox" 
                                                   id="is_active" name="is_active" value="1" 
                                                   {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label form-check-label-enhanced" for="is_active">
                                                <i class="fas fa-toggle-on text-success me-2"></i>
                                                Status Pengguna Aktif
                                            </label>
                                            <div class="form-text-enhanced mt-2">
                                                <i class="fas fa-info-circle text-info"></i>
                                                Pengguna aktif dapat login dan menggunakan sistem
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group-enhanced">
                                    <label for="address" class="form-label-enhanced">
                                        <i class="fas fa-map-marker-alt text-info"></i>
                                        Alamat Lengkap
                                    </label>
                                    <textarea class="form-control form-control-enhanced @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="4"
                                              placeholder="Masukkan alamat lengkap...">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback-enhanced">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group-enhanced">
                                    <div class="form-check-enhanced">
                                        <input class="form-check-input form-check-input-enhanced" type="checkbox" 
                                               id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label form-check-label-enhanced" for="is_active">
                                            <i class="fas fa-toggle-on text-success me-1"></i>
                                            Status Pengguna Aktif
                                        </label>
                                        <div class="form-text-enhanced mt-2">
                                            <i class="fas fa-info-circle text-info"></i>
                                            Pengguna aktif dapat login dan menggunakan sistem
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                            <!-- Informasi Sistem -->
                            <div class="col-12 p-2">
                                <div class="section-divider">
                                    <span><i class="fas fa-cogs me-2"></i>Informasi Sistem</span>
                                </div>
                                
                                <div class="info-section">
                                    <h6>
                                        <i class="fas fa-info-circle text-info"></i>
                                        Detail Pembuatan & Pembaruan
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <span><i class="fas fa-plus-circle text-success me-2"></i>Dibuat pada:</span>
                                                <span>{{ $user->created_at->format('d F Y, H:i') }} WIB</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <span><i class="fas fa-edit text-warning me-2"></i>Terakhir diupdate:</span>
                                                <span>{{ $user->updated_at->format('d F Y, H:i') }} WIB</span>
                                            </div>
                                        </div>
                                        @if($user->email_verified_at)
                                        <div class="col-12">
                                            <div class="info-item">
                                                <span><i class="fas fa-check-circle text-success me-2"></i>Email terverifikasi:</span>
                                                <span>{{ $user->email_verified_at->format('d F Y, H:i') }} WIB</span>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="col-12">
                                <div class="action-buttons">
                                    <a href="{{ route('users.show', $user->id ?? $user->id_user) }}" 
                                       class="btn-enhanced btn-secondary">
                                        <i class="fas fa-times"></i>
                                        Batal
                                    </a>
                                    <button type="submit" class="btn-enhanced btn-primary">
                                        <i class="fas fa-save"></i>
                                        Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
    const passwordConfirmField = document.getElementById('password_confirmation');
    
    if (togglePassword && passwordField) {
        togglePassword.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            // Toggle the eye / eye-slash icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }
    
    if (togglePasswordConfirm && passwordConfirmField) {
        togglePasswordConfirm.addEventListener('click', function() {
            const type = passwordConfirmField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordConfirmField.setAttribute('type', type);
            
            // Toggle the eye / eye-slash icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }
    
    // Real-time form validation
    const form = document.getElementById('editUserForm');
    const requiredFields = form.querySelectorAll('[required]');
    
    // Progress indicator update
    function updateProgress() {
        const progressSteps = document.querySelectorAll('.progress-step');
        let filledFields = 0;
        
        requiredFields.forEach(field => {
            if (field.value.trim() !== '') {
                filledFields++;
            }
        });
        
        const progress = Math.min(filledFields / requiredFields.length, 1);
        const activeSteps = Math.ceil(progress * progressSteps.length);
        
        progressSteps.forEach((step, index) => {
            if (index < activeSteps) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });
    }
    
    // Add input listeners for real-time validation
    requiredFields.forEach(field => {
        field.addEventListener('input', function() {
            updateProgress();
            
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
    const emailField = document.getElementById('email');
    if (emailField) {
        emailField.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            }
        });
    }
    
    // Phone number formatting
    const phoneField = document.getElementById('phone');
    if (phoneField) {
        phoneField.addEventListener('input', function() {
            // Remove non-numeric characters
            let value = this.value.replace(/\D/g, '');
            
            // Limit to reasonable phone number length
            if (value.length > 15) {
                value = value.substring(0, 15);
            }
            
            this.value = value;
        });
    }
    
    // Password confirmation validation
    if (passwordField && passwordConfirmField) {
        passwordConfirmField.addEventListener('input', function() {
            if (passwordField.value !== this.value) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (this.value.length > 0) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }
    
    // Form submission enhancement
    form.addEventListener('submit', function(e) {
        // Show loading state on submit button
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            submitBtn.disabled = true;
        }
        
        // Validate password confirmation if password is provided
        if (passwordField.value && passwordField.value !== passwordConfirmField.value) {
            e.preventDefault();
            passwordConfirmField.classList.add('is-invalid');
            
            // Show error message
            if (!passwordConfirmField.nextElementSibling || !passwordConfirmField.nextElementSibling.classList.contains('invalid-feedback-enhanced')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback-enhanced';
                errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i> Password konfirmasi tidak sesuai';
                passwordConfirmField.parentNode.insertBefore(errorDiv, passwordConfirmField.nextSibling);
            }
            
            // Reset submit button
            submitBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
            submitBtn.disabled = false;
            
            return false;
        }
    });
    
    // Initialize progress on page load
    updateProgress();
    
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
});
</script>
@endpush
