@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-info text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-edit me-2"></i>Edit Lowongan Kerja
                            </h4>
                            <small class="opacity-75">
                                {{ is_object($recruitment) ? ($recruitment->position ?? 'N/A') : (is_array($recruitment) ? ($recruitment['position'] ?? 'N/A') : 'N/A') }}
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="{{ route('recruitments.show', is_object($recruitment) ? ($recruitment->id ?? 0) : (is_array($recruitment) ? ($recruitment['id'] ?? 0) : 0)) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-eye me-1"></i> Lihat
                            </a>
                            <a href="{{ route('recruitments.index') }}" class="btn btn-outline-light btn-sm">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @php
                        // Safe property access helper for recruitment data
                        $recruitmentData = [
                            'id' => is_object($recruitment) ? ($recruitment->id ?? null) : (is_array($recruitment) ? ($recruitment['id'] ?? null) : null),
                            'position' => is_object($recruitment) ? ($recruitment->position ?? '') : (is_array($recruitment) ? ($recruitment['position'] ?? '') : ''),
                            'id_posisi' => is_object($recruitment) ? ($recruitment->id_posisi ?? null) : (is_array($recruitment) ? ($recruitment['id_posisi'] ?? null) : null),
                            'employment_type' => is_object($recruitment) ? ($recruitment->employment_type ?? $recruitment->tipe_pekerjaan ?? '') : (is_array($recruitment) ? ($recruitment['employment_type'] ?? $recruitment['tipe_pekerjaan'] ?? '') : ''),
                            'slots' => is_object($recruitment) ? ($recruitment->slots ?? 1) : (is_array($recruitment) ? ($recruitment['slots'] ?? 1) : 1),
                            'description' => is_object($recruitment) ? ($recruitment->description ?? '') : (is_array($recruitment) ? ($recruitment['description'] ?? '') : ''),
                            'requirements' => is_object($recruitment) ? ($recruitment->requirements ?? '') : (is_array($recruitment) ? ($recruitment['requirements'] ?? '') : ''),
                            'salary_min' => is_object($recruitment) ? ($recruitment->salary_min ?? null) : (is_array($recruitment) ? ($recruitment['salary_min'] ?? null) : null),
                            'salary_max' => is_object($recruitment) ? ($recruitment->salary_max ?? null) : (is_array($recruitment) ? ($recruitment['salary_max'] ?? null) : null),
                            'status' => is_object($recruitment) ? ($recruitment->status ?? 'open') : (is_array($recruitment) ? ($recruitment['status'] ?? 'open') : 'open'),
                        ];
                        
                        // Handle application_deadline separately due to date formatting
                        $deadlineValue = '';
                        if (is_object($recruitment) && isset($recruitment->application_deadline)) {
                            if (is_object($recruitment->application_deadline) && method_exists($recruitment->application_deadline, 'format')) {
                                $deadlineValue = $recruitment->application_deadline->format('Y-m-d');
                            } elseif (is_string($recruitment->application_deadline)) {
                                $deadlineValue = \Carbon\Carbon::parse($recruitment->application_deadline)->format('Y-m-d');
                            }
                        } elseif (is_array($recruitment) && isset($recruitment['application_deadline'])) {
                            $deadlineValue = \Carbon\Carbon::parse($recruitment['application_deadline'])->format('Y-m-d');
                        }
                        $recruitmentData['application_deadline'] = $deadlineValue;
                    @endphp
                    
                    <form method="POST" action="{{ route('recruitments.update', $recruitmentData['id']) }}" id="editRecruitmentForm">
                        @csrf
                        @method('PUT')

                        <!-- Section 1: Basic Information -->
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-info-circle me-2"></i>Informasi Dasar
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="id_posisi" class="form-label fw-bold">
                                                <i class="fas fa-briefcase me-1 text-primary"></i>
                                                Posisi <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select form-select-lg @error('id_posisi') is-invalid @enderror" id="id_posisi" name="id_posisi" required>
                                                <option value="">-- Pilih Posisi --</option>
                                                @foreach($posisi as $pos)
                                                    <option value="{{ $pos->id_posisi }}" {{ old('id_posisi', $recruitmentData['id_posisi']) == $pos->id_posisi ? 'selected' : '' }}>
                                                        {{ $pos->nama_posisi }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('id_posisi')
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                                </div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>Pilih posisi yang sesuai dengan lowongan kerja
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="employment_type" class="form-label fw-bold">
                                                <i class="fas fa-clock me-1 text-primary"></i>
                                                Tipe Pekerjaan <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select @error('employment_type') is-invalid @enderror" id="employment_type" name="employment_type" required>
                                                <option value="">-- Pilih Tipe Pekerjaan --</option>
                                                <option value="full_time" {{ old('employment_type', $recruitmentData['employment_type']) == 'full_time' ? 'selected' : '' }}>
                                                    <i class="fas fa-user-tie"></i> Full Time
                                                </option>
                                                <option value="part_time" {{ old('employment_type', $recruitmentData['employment_type']) == 'part_time' ? 'selected' : '' }}>
                                                    <i class="fas fa-user-clock"></i> Part Time
                                                </option>
                                                <option value="contract" {{ old('employment_type', $recruitmentData['employment_type']) == 'contract' ? 'selected' : '' }}>
                                                    <i class="fas fa-file-contract"></i> Contract
                                                </option>
                                            </select>
                                            @error('employment_type')
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="slots" class="form-label fw-bold">
                                                <i class="fas fa-users me-1 text-primary"></i>
                                                Jumlah Posisi <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">
                                                    <i class="fas fa-hashtag"></i>
                                                </span>
                                                <input type="number" 
                                                       class="form-control @error('slots') is-invalid @enderror" 
                                                       id="slots" 
                                                       name="slots" 
                                                       value="{{ old('slots', $recruitmentData['slots']) }}"
                                                       min="1"
                                                       max="100"
                                                       placeholder="Contoh: 5"
                                                       required>
                                                <span class="input-group-text bg-light text-muted">orang</span>
                                            </div>
                                            @error('slots')
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Job Description -->
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-file-alt me-2"></i>Deskripsi & Persyaratan
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-4">
                                    <label for="description" class="form-label fw-bold">
                                        <i class="fas fa-clipboard-list me-1 text-primary"></i>
                                        Deskripsi Pekerjaan <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="6" 
                                              placeholder="Deskripsikan tugas dan tanggung jawab posisi ini secara detail...&#10;Contoh:&#10;- Melakukan analisis data&#10;- Membuat laporan harian&#10;- Berkomunikasi dengan klien"
                                              required>{{ old('description', $recruitmentData['description']) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-lightbulb me-1"></i>Jelaskan dengan detail agar kandidat memahami tugas yang akan dikerjakan
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="requirements" class="form-label fw-bold">
                                        <i class="fas fa-check-circle me-1 text-primary"></i>
                                        Persyaratan <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('requirements') is-invalid @enderror" 
                                              id="requirements" 
                                              name="requirements" 
                                              rows="6" 
                                              placeholder="Tuliskan persyaratan yang dibutuhkan...&#10;Contoh:&#10;- Pendidikan minimal S1&#10;- Pengalaman kerja 2 tahun&#10;- Menguasai Microsoft Office&#10;- Kemampuan komunikasi yang baik"
                                              required>{{ old('requirements', $recruitmentData['requirements']) }}</textarea>
                                    @error('requirements')
                                        <div class="invalid-feedback">
                                            <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-user-graduate me-1"></i>Sebutkan kualifikasi pendidikan, pengalaman, dan keahlian yang diperlukan
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 3: Compensation & Terms -->
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-money-bill-wave me-2"></i>Kompensasi & Ketentuan
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="salary_min" class="form-label fw-bold">
                                                <i class="fas fa-coins me-1 text-success"></i>
                                                Gaji Minimum
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">
                                                    <i class="fas fa-rupiah-sign"></i> Rp
                                                </span>
                                                <input type="number" 
                                                       class="form-control @error('salary_min') is-invalid @enderror" 
                                                       id="salary_min" 
                                                       name="salary_min" 
                                                       value="{{ old('salary_min', $recruitmentData['salary_min']) }}"
                                                       placeholder="Contoh: 5000000"
                                                       min="0"
                                                       step="100000">
                                            </div>
                                            @error('salary_min')
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                                </div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>Kosongkan jika dapat dinegosiasi
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="salary_max" class="form-label fw-bold">
                                                <i class="fas fa-coins me-1 text-success"></i>
                                                Gaji Maksimum
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">
                                                    <i class="fas fa-rupiah-sign"></i> Rp
                                                </span>
                                                <input type="number" 
                                                       class="form-control @error('salary_max') is-invalid @enderror" 
                                                       id="salary_max" 
                                                       name="salary_max" 
                                                       value="{{ old('salary_max', $recruitmentData['salary_max']) }}"
                                                       placeholder="Contoh: 8000000"
                                                       min="0"
                                                       step="100000">
                                            </div>
                                            @error('salary_max')
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                                </div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>Kosongkan jika dapat dinegosiasi
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info border-0 bg-light">
                                    <div class="d-flex">
                                        <i class="fas fa-lightbulb text-info me-2 mt-1"></i>
                                        <div>
                                            <strong>Tips:</strong> Jika Anda mengisi rentang gaji, pastikan gaji maksimum lebih besar dari gaji minimum. 
                                            Jika gaji dapat dinegosiasi, kosongkan kedua field ini.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 4: Deadline & Status -->
                        <div class="card mb-4 border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 text-primary">
                                    <i class="fas fa-calendar-check me-2"></i>Deadline & Status
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="application_deadline" class="form-label fw-bold">
                                                <i class="fas fa-calendar-alt me-1 text-warning"></i>
                                                Deadline Lamaran <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">
                                                    <i class="fas fa-calendar"></i>
                                                </span>
                                                <input type="date" 
                                                       class="form-control @error('application_deadline') is-invalid @enderror" 
                                                       id="application_deadline" 
                                                       name="application_deadline" 
                                                       value="{{ old('application_deadline', $recruitmentData['application_deadline']) }}"
                                                       min="{{ date('Y-m-d') }}"
                                                       required>
                                            </div>
                                            @error('application_deadline')
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                                </div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="fas fa-clock me-1"></i>Tanggal terakhir penerimaan lamaran
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="status" class="form-label fw-bold">
                                                <i class="fas fa-toggle-on me-1 text-primary"></i>
                                                Status Lowongan <span class="text-danger">*</span>
                                            </label>
                                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                                <option value="open" {{ old('status', $recruitmentData['status']) == 'open' ? 'selected' : '' }}>
                                                    <i class="fas fa-check-circle"></i> Buka - Menerima Lamaran
                                                </option>
                                                <option value="closed" {{ old('status', $recruitmentData['status']) == 'closed' ? 'selected' : '' }}>
                                                    <i class="fas fa-times-circle"></i> Tutup - Tidak Menerima Lamaran
                                                </option>
                                            </select>
                                            @error('status')
                                                <div class="invalid-feedback">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>{{ $message }}
                                                </div>
                                            @enderror
                                            <div class="form-text">
                                                <i class="fas fa-info-circle me-1"></i>Status dapat diubah kapan saja
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body bg-light">
                                <div class="d-flex flex-column flex-md-row gap-3 justify-content-between align-items-center">
                                    <div class="text-muted">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <small>Pastikan semua informasi sudah benar sebelum menyimpan perubahan</small>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('recruitments.show', $recruitmentData['id']) }}" 
                                           class="btn btn-light border">
                                            <i class="fas fa-times me-2"></i>Batal
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-primary" 
                                                onclick="previewChanges()">
                                            <i class="fas fa-eye me-2"></i>Preview
                                        </button>
                                        <button type="submit" 
                                                class="btn btn-primary btn-lg px-4" 
                                                id="submitBtn">
                                            <i class="fas fa-save me-2"></i>
                                            <span class="btn-text">Simpan Perubahan</span>
                                            <span class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Buttons for Quick Actions -->
<div class="position-fixed" style="bottom: 30px; right: 30px; z-index: 1000;">
    <div class="btn-group-vertical" role="group">
        <button type="button" class="btn btn-info btn-sm rounded-pill mb-2" onclick="scrollToTop()" title="Kembali ke Atas">
            <i class="fas fa-chevron-up"></i>
        </button>
        <button type="button" class="btn btn-success btn-sm rounded-pill" onclick="document.getElementById('submitBtn').click()" title="Simpan Cepat">
            <i class="fas fa-check"></i>
        </button>
    </div>
</div>

<style>
/* Consistent theme styling */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-info{
    background: linear-gradient(45deg, #17a2b8, #117a8b)
}

.card {
    transition: all 0.3s ease;
    border-radius: 15px;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-2px);
}

.card-header {
    border-bottom: 2px solid rgba(102, 126, 234, 0.1);
    background: rgba(102, 126, 234, 0.05) !important;
}

.form-control, .form-select {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
    padding: 0.75rem 1rem;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    transform: translateY(-1px);
}

.form-control.is-invalid, .form-select.is-invalid {
    border-color: #dc3545;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.form-label {
    color: #495057;
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
}

.form-text {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.input-group-text {
    border-radius: 10px 0 0 10px;
    border: 2px solid #e9ecef;
    border-right: none;
    background: rgba(102, 126, 234, 0.05);
    color: #667eea;
    font-weight: 600;
}

.btn {
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6b4190 100%);
}

.alert {
    border-radius: 12px;
    border: none;
    padding: 1rem 1.25rem;
}

.alert-info {
    background: linear-gradient(135deg, #cce7ff 0%, #e6f3ff 100%);
    color: #0c5460;
}

.invalid-feedback {
    font-size: 0.875rem;
    font-weight: 500;
    margin-top: 0.5rem;
}

.shadow-sm {
    box-shadow: 0 .125rem .25rem rgba(102, 126, 234, 0.075) !important;
}

.shadow-lg {
    box-shadow: 0 1rem 3rem rgba(102, 126, 234, 0.175) !important;
}

/* Progress indicator */
.progress-indicator {
    position: sticky;
    top: 0;
    z-index: 100;
    background: white;
    padding: 0.5rem 0;
    border-bottom: 1px solid #e9ecef;
}

.progress-bar {
    transition: width 0.3s ease;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .container-fluid {
        padding: 0.5rem;
    }
    
    .card-body {
        padding: 1.5rem !important;
    }
    
    .btn-group-vertical {
        bottom: 20px !important;
        right: 20px !important;
    }
    
    .form-control, .form-select {
        font-size: 16px; /* Prevents zoom on iOS */
    }
}

/* Loading state */
.btn:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

.loading .btn-text {
    opacity: 0.6;
}

/* Character count styling */
.char-count {
    font-size: 0.75rem;
    color: #6c757d;
    text-align: right;
    margin-top: 0.25rem;
}

.char-count.warning {
    color: #ffc107;
}

.char-count.danger {
    color: #dc3545;
}

/* Tooltip styling */
.tooltip {
    font-size: 0.875rem;
}

/* Form validation success */
.form-control.is-valid, .form-select.is-valid {
    border-color: #28a745;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73.8-.77-.76-.77-.76.77z'/%3e%3c/svg%3e");
}

.valid-feedback {
    color: #28a745;
    font-size: 0.875rem;
    font-weight: 500;
    margin-top: 0.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form elements
    const form = document.getElementById('editRecruitmentForm');
    const submitBtn = document.getElementById('submitBtn');
    const salaryMin = document.getElementById('salary_min');
    const salaryMax = document.getElementById('salary_max');
    const description = document.getElementById('description');
    const requirements = document.getElementById('requirements');
    const deadline = document.getElementById('application_deadline');
    
    // Character counters for textareas
    addCharacterCounter(description, 1000);
    addCharacterCounter(requirements, 1000);
    
    // Salary validation
    function validateSalary() {
        const minValue = parseFloat(salaryMin.value) || 0;
        const maxValue = parseFloat(salaryMax.value) || 0;
        
        salaryMax.setCustomValidity('');
        
        if (minValue > 0 && maxValue > 0 && minValue >= maxValue) {
            salaryMax.setCustomValidity('Gaji maksimum harus lebih besar dari gaji minimum');
            showFieldError(salaryMax, 'Gaji maksimum harus lebih besar dari gaji minimum');
        } else {
            showFieldSuccess(salaryMax);
        }
    }
    
    // Date validation
    function validateDeadline() {
        const today = new Date();
        const selectedDate = new Date(deadline.value);
        
        deadline.setCustomValidity('');
        
        if (selectedDate <= today) {
            deadline.setCustomValidity('Deadline harus setelah hari ini');
            showFieldError(deadline, 'Deadline harus setelah hari ini');
        } else {
            showFieldSuccess(deadline);
        }
    }
    
    // Real-time validation
    salaryMin.addEventListener('input', validateSalary);
    salaryMax.addEventListener('input', validateSalary);
    deadline.addEventListener('change', validateDeadline);
    
    // Form submission with loading state
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            
            // Find first invalid field and focus
            const firstInvalid = form.querySelector('.is-invalid, :invalid');
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            showToast('Mohon lengkapi semua field yang wajib diisi', 'error');
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.classList.add('loading');
        submitBtn.querySelector('.spinner-border').classList.remove('d-none');
        submitBtn.querySelector('.btn-text').textContent = 'Menyimpan...';
        
        showToast('Menyimpan perubahan...', 'info');
    });
    
    // Auto-save draft functionality
    let autoSaveTimer;
    const formInputs = form.querySelectorAll('input, select, textarea');
    
    formInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(saveDraft, 2000);
        });
    });
    
    // Format currency inputs
    [salaryMin, salaryMax].forEach(input => {
        input.addEventListener('input', function() {
            formatCurrency(this);
        });
    });
    
    // Load draft on page load
    loadDraft();
});

// Helper functions
function addCharacterCounter(textarea, maxLength) {
    const counter = document.createElement('div');
    counter.className = 'char-count';
    textarea.parentNode.insertBefore(counter, textarea.nextSibling);
    
    function updateCounter() {
        const remaining = maxLength - textarea.value.length;
        counter.textContent = `${textarea.value.length}/${maxLength} karakter`;
        
        counter.className = 'char-count';
        if (remaining < 100) counter.classList.add('warning');
        if (remaining < 50) counter.classList.add('danger');
    }
    
    textarea.addEventListener('input', updateCounter);
    updateCounter();
}

function showFieldError(field, message) {
    field.classList.add('is-invalid');
    field.classList.remove('is-valid');
    
    let feedback = field.parentNode.querySelector('.invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        field.parentNode.appendChild(feedback);
    }
    feedback.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i>${message}`;
}

function showFieldSuccess(field) {
    if (field.value.trim() !== '') {
        field.classList.add('is-valid');
        field.classList.remove('is-invalid');
    }
}

function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    if (value) {
        value = parseInt(value).toLocaleString('id-ID');
        // Store original value for form submission
        input.dataset.originalValue = input.value.replace(/\D/g, '');
    }
}

function previewChanges() {
    const formData = new FormData(document.getElementById('editRecruitmentForm'));
    const preview = window.open('', '_blank', 'width=800,height=600');
    
    preview.document.write(`
        <html>
            <head>
                <title>Preview Lowongan</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
            </head>
            <body class="bg-light p-4">
                <div class="container">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h4><i class="fas fa-eye me-2"></i>Preview Lowongan</h4>
                        </div>
                        <div class="card-body">
                            <h5>${formData.get('id_posisi') ? 'Posisi Terpilih' : 'No Position'}</h5>
                            <p><strong>Tipe:</strong> ${formData.get('employment_type')}</p>
                            <p><strong>Jumlah Posisi:</strong> ${formData.get('slots')} orang</p>
                            <p><strong>Deadline:</strong> ${formData.get('application_deadline')}</p>
                            <p><strong>Status:</strong> ${formData.get('status') === 'open' ? 'Buka' : 'Tutup'}</p>
                            <hr>
                            <h6>Deskripsi:</h6>
                            <p style="white-space: pre-line;">${formData.get('description')}</p>
                            <h6>Persyaratan:</h6>
                            <p style="white-space: pre-line;">${formData.get('requirements')}</p>
                        </div>
                    </div>
                </div>
            </body>
        </html>
    `);
}

function saveDraft() {
    const formData = new FormData(document.getElementById('editRecruitmentForm'));
    const draftData = {};
    
    for (let [key, value] of formData.entries()) {
        draftData[key] = value;
    }
    
    localStorage.setItem('recruitment_edit_draft', JSON.stringify(draftData));
    showToast('Draft tersimpan otomatis', 'success', 2000);
}

function loadDraft() {
    const draft = localStorage.getItem('recruitment_edit_draft');
    if (draft) {
        const draftData = JSON.parse(draft);
        const form = document.getElementById('editRecruitmentForm');
        
        Object.keys(draftData).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field && field.value === '') {
                field.value = draftData[key];
            }
        });
    }
}

function clearDraft() {
    localStorage.removeItem('recruitment_edit_draft');
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, duration);
}

// Form submission success handler
window.addEventListener('beforeunload', function(e) {
    const form = document.getElementById('editRecruitmentForm');
    if (form && form.dataset.changed === 'true') {
        e.preventDefault();
        e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
    }
});

// Track form changes
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editRecruitmentForm');
    const formInputs = form.querySelectorAll('input, select, textarea');
    
    formInputs.forEach(input => {
        input.addEventListener('change', function() {
            form.dataset.changed = 'true';
        });
    });
    
    form.addEventListener('submit', function() {
        form.dataset.changed = 'false';
        clearDraft();
    });
});
</script>
@endsection
