@extends('layouts.app')

@section('title', 'Tambah Pelatihan Baru')
@section('page-title', 'Tambah Pelatihan Baru')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    // Setup CSRF token untuk semua AJAX requests
    document.addEventListener('DOMContentLoaded', function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
    });
</script>
@endpush

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('trainings.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
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
    
    /* .create-form-card:hover {
        transform: translateY(-8px);
        box-shadow: 
            0 30px 80px rgba(0, 0, 0, 0.15),
            0 12px 40px rgba(31, 38, 135, 0.25);
    } */
    
    .form-section-header {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
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
        color: #17a2b8;
        font-weight: 700;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border: 2px solid #e1f4f6;
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
    
    /* .form-section:hover {
        transform: translateY(-2px);
        box-shadow: 
            inset 0 2px 4px rgba(0,0,0,0.04),
            0 8px 25px rgba(0,0,0,0.1);
    } */
    
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
        border-color: #17a2b8;
        box-shadow: 
            inset 0 2px 4px rgba(0,0,0,0.04),
            0 0 0 0.2rem rgba(23, 162, 184, 0.25);
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
        border-color: #17a2b8;
        box-shadow: 
            inset 0 2px 4px rgba(0,0,0,0.04),
            0 0 0 0.2rem rgba(23, 162, 184, 0.25);
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
        color: #17a2b8;
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
    
    .form-check-enhanced {
        background: linear-gradient(135deg, #e3f7fa 0%, #b2ebf2 100%);
        border-radius: 12px;
        padding: 1rem;
        border: 2px solid #17a2b8;
        margin-top: 1rem;
    }
    
    .form-check-enhanced .form-check-input {
        transform: scale(1.2);
        margin-top: 0.2rem;
    }
    
    .form-check-enhanced .form-check-label {
        font-weight: 600;
        color: #0c5460;
        margin-left: 0.5rem;
    }
    
    .dynamic-field {
        background: linear-gradient(135deg, #fff9c4 0%, #ffecb3 100%);
        border: 2px solid #ffc107;
        border-radius: 16px;
        padding: 1.5rem;
        margin-top: 1rem;
        transition: all 0.3s ease;
    }
    
    .dynamic-field.show {
        animation: slideInUp 0.5s ease;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
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
    
    .btn-enhanced.btn-primary {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
    }
    
    .btn-enhanced.btn-primary:hover {
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
    
    .training-type-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px solid #dee2e6;
        border-radius: 16px;
        padding: 1rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        margin-bottom: 1rem;
    }
    
    .training-type-card:hover {
        transform: translateY(-2px);
        border-color: #17a2b8;
        box-shadow: 0 4px 15px rgba(23, 162, 184, 0.2);
    }
    
    .training-type-card.selected {
        background: linear-gradient(135deg, #e3f7fa 0%, #b2ebf2 100%);
        border-color: #17a2b8;
        color: #0c5460;
    }
    
    .training-type-card i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        color: #17a2b8;
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

    /* Employee Selection Styles */
    .employee-selection-container {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e9ecef;
    }

    .employee-selection-header {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 1rem;
    }

    .employee-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
        max-height: 400px;
        overflow-y: auto;
        padding: 1rem 0;
    }

    .employee-card {
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .employee-card:hover {
        border-color: #007bff;
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
        transform: translateY(-2px);
    }

    .employee-card .form-check {
        margin: 0;
        height: 100%;
    }

    .employee-card .form-check-input {
        display: none;
    }

    .employee-label {
        display: block;
        padding: 1rem;
        cursor: pointer;
        margin: 0;
        height: 100%;
        width: 100%;
    }

    .employee-info {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .employee-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
        flex-shrink: 0;
    }

    .employee-details {
        flex: 1;
    }

    .employee-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }

    .employee-position {
        color: #6c757d;
        font-size: 0.8rem;
        margin-bottom: 0.25rem;
    }

    .employee-id {
        color: #8f9bb3;
        font-size: 0.75rem;
    }

    .employee-card .form-check-input:checked + .employee-label {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
    }

    .employee-card .form-check-input:checked + .employee-label .employee-name,
    .employee-card .form-check-input:checked + .employee-label .employee-position,
    .employee-card .form-check-input:checked + .employee-label .employee-id {
        color: white;
    }

    .employee-card .form-check-input:checked + .employee-label .employee-avatar {
        background: rgba(255, 255, 255, 0.2);
    }

    .selection-summary {
        background: white;
        border-radius: 8px;
        padding: 1rem;
    }

    /* Position Filter Buttons */
    .position-filter-container {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        border: 1px solid #e9ecef;
    }

    .position-filter-btn {
        border-radius: 20px;
        padding: 0.4rem 1rem;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 2px solid #17a2b8;
        color: #17a2b8;
        background: white;
    }

    .position-filter-btn:hover {
        background: #17a2b8;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(23, 162, 184, 0.3);
    }

    .position-filter-btn.active {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white;
        border-color: #138496;
        box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
    }

    .position-filter-btn i {
        font-size: 0.9rem;
    }

    /* Employee card hide/show animation */
    .employee-card {
        transition: all 0.3s ease;
    }

    .employee-card.hidden {
        display: none;
        opacity: 0;
        transform: scale(0.8);
    }

    @media (max-width: 768px) {
        .employee-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        
        .employee-selection-header .btn-group {
            flex-direction: column;
        }
        
        .employee-selection-header .btn {
            margin-bottom: 0.25rem;
        }

        .position-filter-btn {
            font-size: 0.75rem;
            padding: 0.3rem 0.75rem;
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
                                <h2 class="mb-1">
                                    <i class="fas fa-graduation-cap me-3"></i> 
                                    Tambah Pelatihan Baru
                                </h2>
                                <p class="subtitle mb-0">
                                    Buat pelatihan untuk meningkatkan kompetensi pegawai
                                </p>
                            </div>
                            <div class="d-none d-lg-block">
                                <i class="fas fa-chalkboard-teacher fa-4x opacity-25"></i>
                            </div>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    <div class="card-body p-5">
                        <form action="{{ route('trainings.store') }}" method="POST" id="createTrainingForm">
                            @csrf
                            
                            <!-- Basic Information Section -->
                            <div class="section-divider">
                                <span><i class="fas fa-info-circle me-2"></i>Informasi Dasar</span>
                            </div>
                            
                            <div class="form-section">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="judul" class="form-label-enhanced">
                                            <i class="fas fa-heading"></i>
                                            Judul Pelatihan
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <input type="text" class="form-control form-control-enhanced @error('judul') is-invalid @enderror" 
                                               id="judul" name="judul" value="{{ old('judul') }}" maxlength="100" required
                                               placeholder="Masukkan judul pelatihan">
                                        @error('judul')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text-enhanced">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Maksimal 100 karakter
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="jenis_pelatihan" class="form-label-enhanced">
                                            <i class="fas fa-tags"></i>
                                            Jenis Pelatihan
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <select class="form-select form-select-enhanced @error('jenis_pelatihan') is-invalid @enderror" 
                                                id="jenis_pelatihan" name="jenis_pelatihan" required onchange="toggleLocationUrl()">
                                            <option value="">Pilih Jenis Pelatihan</option>
                                            <option value="online" {{ old('jenis_pelatihan') == 'online' ? 'selected' : '' }}>
                                                🎥 Online Meeting (Zoom/Teams)
                                            </option>
                                            <option value="offline" {{ old('jenis_pelatihan') == 'offline' ? 'selected' : '' }}>
                                                🏢 Offline/Tatap Muka
                                            </option>
                                        </select>
                                        @error('jenis_pelatihan')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text-enhanced">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Pilih format pelatihan yang sesuai
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="deskripsi" class="form-label-enhanced">
                                            <i class="fas fa-align-left"></i>
                                            Deskripsi Pelatihan
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <textarea class="form-control form-control-enhanced @error('deskripsi') is-invalid @enderror" 
                                                  id="deskripsi" name="deskripsi" rows="4" required
                                                  placeholder="Jelaskan tujuan dan materi pelatihan secara detail">{{ old('deskripsi') }}</textarea>
                                        @error('deskripsi')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text-enhanced">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Berikan deskripsi yang jelas dan menarik
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Training Details Section -->
                            <div class="section-divider">
                                <span><i class="fas fa-cogs me-2"></i>Detail Pelatihan</span>
                            </div>
                            
                            <div class="form-section">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="tanggal" class="form-label-enhanced">
                                            <i class="fas fa-calendar-alt"></i> 
                                            Tanggal Pelatihan
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <input type="datetime-local" class="form-control form-control-enhanced @error('tanggal') is-invalid @enderror" 
                                            id="tanggal" name="tanggal" value="{{ old('tanggal') }}" required>
                                        @error('tanggal')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text-enhanced">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Pilih kapan tanggal dan waktu pelatihan akan dilaksanakan
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="durasi" class="form-label-enhanced">
                                            <i class="fas fa-clock"></i>
                                            Durasi Pelatihan (menit)
                                            <span class="text-muted">(Opsional)</span>
                                        </label>
                                        <input type="number" class="form-control form-control-enhanced @error('durasi') is-invalid @enderror" 
                                               id="durasi" name="durasi" value="{{ old('durasi') }}" min="1" max="9999"
                                               placeholder="Contoh: 120">
                                        @error('durasi')
                                            <div class="validation-feedback">
                                                <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                            </div>
                                        @enderror
                                        <div class="form-text-enhanced">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Estimasi durasi pelatihan dalam menit 
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Konten Pelatihan -->
                            <div class="section-divider">
                                <span><i class="fas fa-file-alt me-2"></i>Konten Pelatihan</span>
                            </div>

                            <div class="form-section">
                                {{-- URL Field --}}
                                <div class="dynamic-field" id="url_field" style="display:none">
                                    <label for="link_url_input" class="form-label-enhanced">
                                        <i class="fas fa-link"></i>
                                        Link URL Pelatihan
                                        <span class="required-indicator">*</span>
                                    </label>
                                    <input 
                                        type="url"
                                        id="link_url_input"
                                        name="link_url"
                                        class="form-control form-control-enhanced @error('link_url') is-invalid @enderror"
                                        placeholder="https://example.com/..."
                                        value="{{ old('link_url') }}"
                                    >
                                    @error('link_url')
                                        <div class="validation-feedback">
                                            <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                        </div>
                                    @enderror
                                    <div class="form-text-enhanced">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <span id="url_help">Masukkan link video atau dokumen pelatihan</span>
                                    </div>
                                </div>

                                {{-- Alamat Field --}}
                                <div class="dynamic-field" id="location_field" style="display:none">
                                    <label for="link_url_textarea" class="form-label-enhanced">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Alamat Pelatihan
                                        <span class="required-indicator">*</span>
                                    </label>
                                    <textarea
                                        id="link_url_textarea"
                                        name="link_url"
                                        rows="3"
                                        class="form-control form-control-enhanced @error('link_url') is-invalid @enderror"
                                        placeholder="Masukkan alamat lengkap lokasi pelatihan"
                                    >{{ old('link_url') }}</textarea>
                                    @error('link_url')
                                        <div class="validation-feedback">
                                            <i class="fas fa-exclamation-triangle me-2"></i>{{ $message }}
                                        </div>
                                    @enderror
                                    <div class="form-text-enhanced">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Berikan alamat yang jelas dan mudah ditemukan
                                    </div>
                                </div>

                                {{-- Placeholder --}}
                                <div id="no_type_placeholder" class="text-center p-4">
                                    <i class="fas fa-hand-point-up fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Pilih Jenis Pelatihan</h5>
                                    <p class="text-muted">Pilih jenis pelatihan di atas untuk menampilkan field yang sesuai</p>
                                </div>
                            </div>

                            <!-- Employee Selection Section -->
                            <div class="section-divider">
                                <span><i class="fas fa-users me-2"></i>Peserta Pelatihan</span>
                            </div>
                            
                            <div class="form-section">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="form-label-enhanced">
                                            <i class="fas fa-user-check"></i>
                                            Pilih Peserta
                                            <span class="text-muted">(Opsional)</span>
                                        </div>
                                        
                                        <div class="employee-selection-container">
                                            @if(isset($employees) && !empty($employees))
                                                <div class="employee-selection-header mb-3">
                                                    <div class="d-flex flex-column gap-3">
                                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                            <span class="text-muted">
                                                                <i class="fas fa-info-circle me-1"></i>
                                                                Pilih pegawai yang akan mengikuti pelatihan ini
                                                            </span>
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <button type="button" class="btn btn-outline-primary" id="selectAllEmployees">
                                                                    <i class="fas fa-check-double me-1"></i>Pilih Semua
                                                                </button>
                                                                <button type="button" class="btn btn-outline-secondary" id="clearAllEmployees">
                                                                    <i class="fas fa-times me-1"></i>Bersihkan
                                                                </button>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Filter by Position -->
                                                        <div class="position-filter-container">
                                                            <label class="form-label-enhanced mb-2">
                                                                <i class="fas fa-filter"></i>
                                                                Filter Berdasarkan Posisi
                                                            </label>
                                                            <div class="d-flex gap-2 flex-wrap" id="positionFilterButtons">
                                                                @php
                                                                    // Ambil semua posisi unik dari employees
                                                                    $positions = collect($employees)
                                                                        ->pluck('posisi.nama_posisi')
                                                                        ->unique()
                                                                        ->filter(function($position) {
                                                                            // Filter out Admin dan null values
                                                                            return $position && strtolower($position) !== 'admin';
                                                                        })
                                                                        ->sort()
                                                                        ->values();
                                                                @endphp
                                                                
                                                                <button type="button" class="btn btn-sm btn-outline-info position-filter-btn active" data-position="all">
                                                                    <i class="fas fa-users me-1"></i>Semua Posisi
                                                                </button>
                                                                
                                                                @foreach($positions as $position)
                                                                    <button type="button" class="btn btn-sm btn-outline-info position-filter-btn" data-position="{{ $position }}">
                                                                        <i class="fas fa-briefcase me-1"></i>{{ $position }}
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="employee-grid" id="employeeGrid">
                                                    @foreach($employees as $employee)
                                                        <div class="employee-card" data-position="{{ $employee['posisi']['nama_posisi'] ?? 'Tidak ada posisi' }}">
                                                            <div class="form-check">
                                                                <input class="form-check-input employee-checkbox" 
                                                                       type="checkbox" 
                                                                       value="{{ $employee['id_pegawai'] ?? $employee['id'] }}" 
                                                                       name="participants[]" 
                                                                       id="employee_{{ $employee['id_pegawai'] ?? $employee['id'] }}">
                                                                <label class="form-check-label employee-label" 
                                                                       for="employee_{{ $employee['id_pegawai'] ?? $employee['id'] }}">
                                                                    <div class="employee-info">
                                                                        <div class="employee-avatar">
                                                                            {{ strtoupper(substr($employee['nama_lengkap'] ?? $employee['nama_user'] ?? 'U', 0, 1)) }}
                                                                        </div>
                                                                        <div class="employee-details">
                                                                            <div class="employee-name">
                                                                                {{ $employee['nama_lengkap'] ?? $employee['nama_user'] ?? 'Nama tidak tersedia' }}
                                                                            </div>
                                                                            <div class="employee-position">
                                                                                {{ $employee['posisi']['nama_posisi'] ?? 'Posisi tidak tersedia' }}
                                                                            </div>
                                                                            <div class="employee-id">
                                                                                ID: {{ $employee['id_pegawai'] ?? $employee['id'] ?? '-' }}
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                
                                                <div class="selection-summary mt-3">
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        <span id="selectionCount">0</span> pegawai dipilih untuk mengikuti pelatihan ini.
                                                    </div>
                                                </div>
                                            @else
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    Tidak ada data pegawai yang tersedia. Pastikan Anda memiliki koneksi ke server.
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="form-text-enhanced">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Jika tidak ada peserta yang dipilih, pelatihan akan terbuka untuk semua pegawai
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
                                    <button type="submit" class="btn-enhanced btn-primary">
                                        <i class="fas fa-save me-2"></i>Simpan Pelatihan
                                    </button>
                                    <a href="{{ route('trainings.index') }}" class="btn-enhanced btn-secondary">
                                        <i class="fas fa-times me-2"></i>Batal
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
    const jenisSelect    = document.getElementById('jenis_pelatihan');
    const urlField       = document.getElementById('url_field');
    const locationField  = document.getElementById('location_field');
    const placeholder    = document.getElementById('no_type_placeholder');
    const urlInput       = document.getElementById('link_url_input');
    const locationInput  = document.getElementById('link_url_textarea');
    const helpText       = document.getElementById('url_help');
    const form = document.getElementById('createTrainingForm');
    
    // Main toggle function for dynamic fields
    function toggleLocationUrl() {
        // Reset semua
        [urlField, locationField, placeholder].forEach(el => el.style.display = 'none');
        [urlInput, locationInput].forEach(f => {
            f.removeAttribute('required');
            f.removeAttribute('name'); // HAPUS name agar tidak dikirim
            f.classList.remove('is-invalid','is-valid');
        });

        const val = jenisSelect.value;
        if (val === 'offline') {
            locationField.style.display = 'block';
            locationInput.setAttribute('required', 'required');
            locationInput.setAttribute('name', 'link_url'); // SET name
        } else if (['online', 'video', 'document'].includes(val)) {
            urlField.style.display = 'block';
            urlInput.setAttribute('required', 'required');
            urlInput.setAttribute('name', 'link_url'); // SET name

            // Bantuan berdasarkan jenis
            if (val === 'online') {
                helpText.innerHTML = '<i class="fas fa-video me-1"></i>Masukkan link Meeting (Zoom, Teams, Google Meet, dll)';
            } else if (val === 'video') {
                helpText.innerHTML = '<i class="fas fa-video me-1"></i>Masukkan link video pelatihan (YouTube, Vimeo, dll)';
            } else if (val === 'document') {
                helpText.innerHTML = '<i class="fas fa-file-pdf me-1"></i>Masukkan link dokumen atau e-learning';
            }
        } else {
            placeholder.style.display = 'block';
        }
    }

    // Setup event listeners
    jenisSelect.addEventListener('change', toggleLocationUrl);
    
    // Initialize form
    toggleLocationUrl();
    setupValidation();
    setupAnimations();

    
    // Smooth show animation for fields
    function showField(field) {
        field.style.display = 'block';
        field.classList.add('show');
        field.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    // Setup form validation
    function setupValidation() {
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            field.addEventListener('input', function() {
                validateField(this);
            });
            
            field.addEventListener('blur', function() {
                validateField(this);
            });
        });
        
        // Special validation for URL field
        const urlInput = document.getElementById('link_url_input');
        if (urlInput) {
            urlInput.addEventListener('input', function() {
                if (this.value) {
                    const urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
                    if (urlPattern.test(this.value)) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                    }
                }
            });
        }
                
        // Character count for title
        const judulInput = document.getElementById('judul');
        const judulContainer = judulInput.parentElement;
        const charCounter = document.createElement('div');
        charCounter.className = 'form-text-enhanced text-end mt-2';
        charCounter.innerHTML = '<i class="fas fa-keyboard me-1"></i><span id="char-count">0</span>/100 karakter';
        judulContainer.appendChild(charCounter);
        
        judulInput.addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('char-count').textContent = count;
            
            if (count > 80) {
                charCounter.style.color = '#dc3545';
            } else if (count > 60) {
                charCounter.style.color = '#ffc107';
            } else {
                charCounter.style.color = '#6c757d';
            }
        });
    }
    
    // Field validation function
    function validateField(field) {
        if (field.hasAttribute('required')) {
            if (field.value.trim() === '') {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
            } else {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            }
        }
    }
    
    // Setup animations
    function setupAnimations() {
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
    }
    
    // Form submission with enhanced validation and error handling
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('button[type="submit"]');
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        let firstInvalidField = null;
        
        // Clear previous errors
        form.querySelectorAll('.is-invalid').forEach(field => {
            field.classList.remove('is-invalid');
        });
        
        // Validate each required field
        requiredFields.forEach(field => {
            if (field.value.trim() === '') {
                field.classList.add('is-invalid');
                isValid = false;
                if (!firstInvalidField) firstInvalidField = field;
                
                // Show error message
                const existingFeedback = field.parentNode.querySelector('.validation-feedback');
                if (!existingFeedback) {
                    const feedbackDiv = document.createElement('div');
                    feedbackDiv.className = 'validation-feedback';
                    feedbackDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Field ini wajib diisi`;
                    field.parentNode.appendChild(feedbackDiv);
                }
            }
        });
        
        // Validate URL format for online trainings
        const jenisValue = document.getElementById('jenis_pelatihan').value;
        const urlInput = document.getElementById('link_url_input');
        
        if (['video', 'document', 'zoom'].includes(jenisValue) && urlInput && urlInput.value) {
            try {
                new URL(urlInput.value);
            } catch (error) {
                urlInput.classList.add('is-invalid');
                isValid = false;
                if (!firstInvalidField) firstInvalidField = urlInput;
                showNotification('⚠️ Format URL tidak valid', 'warning');
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            showNotification('⚠️ Mohon lengkapi semua field yang wajib diisi', 'warning');
            if (firstInvalidField) {
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalidField.focus();
            }
            return;
        }
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan Pelatihan...';
        submitBtn.disabled = true;
        
        showNotification('💾 Sedang menyimpan pelatihan...', 'info');
        
        // Form akan submit secara normal setelah validasi
    });
    
    // Custom notification system
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.custom-notification');
        existingNotifications.forEach(notification => notification.remove());
        
        // Create new notification
        const notification = document.createElement('div');
        notification.className = `custom-notification alert alert-${type} alert-dismissible fade show`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 350px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border: none;
        `;
        
        const icon = type === 'warning' ? 'fas fa-exclamation-triangle' : 
                    type === 'success' ? 'fas fa-check-circle' : 
                    type === 'danger' ? 'fas fa-times-circle' : 'fas fa-info-circle';
        
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="${icon} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            if (notification && notification.parentNode) {
                notification.remove();
            }
        }, 4000);
    }

    // Employee Selection Functionality
    function setupEmployeeSelection() {
        const selectAllBtn = document.getElementById('selectAllEmployees');
        const clearAllBtn = document.getElementById('clearAllEmployees');
        const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');
        const selectionCount = document.getElementById('selectionCount');
        const positionFilterBtns = document.querySelectorAll('.position-filter-btn');
        const employeeCards = document.querySelectorAll('.employee-card');

        let currentFilter = 'all';

        function updateSelectionCount() {
            const visibleCheckboxes = Array.from(employeeCheckboxes).filter(checkbox => {
                const card = checkbox.closest('.employee-card');
                return card && !card.classList.contains('hidden');
            });
            const checkedCount = visibleCheckboxes.filter(checkbox => checkbox.checked).length;
            if (selectionCount) {
                selectionCount.textContent = checkedCount;
            }
        }

        function filterByPosition(position) {
            currentFilter = position;
            
            employeeCards.forEach(card => {
                const cardPosition = card.getAttribute('data-position');
                
                if (position === 'all' || cardPosition === position) {
                    card.classList.remove('hidden');
                    // Animate show
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'scale(1)';
                    }, 10);
                } else {
                    card.classList.add('hidden');
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.8)';
                }
            });

            updateSelectionCount();
        }

        function selectVisibleEmployees() {
            const visibleCheckboxes = Array.from(employeeCheckboxes).filter(checkbox => {
                const card = checkbox.closest('.employee-card');
                return card && !card.classList.contains('hidden');
            });
            
            visibleCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            
            updateSelectionCount();
            
            if (currentFilter === 'all') {
                showNotification('✅ Semua pegawai telah dipilih', 'success');
            } else {
                showNotification(`✅ Semua pegawai dengan posisi "${currentFilter}" telah dipilih`, 'success');
            }
        }

        // Position filter buttons
        positionFilterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                // Remove active class from all buttons
                positionFilterBtns.forEach(b => b.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Filter employees
                const position = this.getAttribute('data-position');
                filterByPosition(position);
                
                // Show notification
                if (position === 'all') {
                    showNotification('📋 Menampilkan semua pegawai', 'info');
                } else {
                    const visibleCount = Array.from(employeeCards).filter(card => !card.classList.contains('hidden')).length;
                    showNotification(`📋 Menampilkan ${visibleCount} pegawai dengan posisi "${position}"`, 'info');
                }
            });
        });

        // Select all employees (visible only)
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', selectVisibleEmployees);
        }

        // Clear all selections
        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function() {
                employeeCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateSelectionCount();
                showNotification('🗑️ Pilihan pegawai telah dibersihkan', 'info');
            });
        }

        // Update count when individual checkboxes change
        employeeCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectionCount);
        });

        // Initialize count
        updateSelectionCount();
    }

    // Setup employee selection after DOM is ready
    setupEmployeeSelection();
    
    // Expose toggle function globally
    window.toggleLocationUrl = toggleLocationUrl;
});
</script>
@endpush

@endsection
