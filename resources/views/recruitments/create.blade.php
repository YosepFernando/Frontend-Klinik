@extends('layouts.app')

@section('content')
<style>
.glass-card {
    background: rgba(255, 255, 255, 0.25);
    border-radius: 16px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(5px);
    -webkit-backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.glass-header {
    background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(80, 200, 120, 0.1));
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px 16px 0 0;
}

.section-card {
    background: rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
    transition: all 0.3s ease;
}

.section-card:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}

.section-title {
    color: #2c3e50;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid rgba(74, 144, 226, 0.3);
}

.form-label {
    font-weight: 600;
    color: #34495e;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border: 2px solid rgba(74, 144, 226, 0.2);
    border-radius: 8px;
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.8);
}

.form-control:focus, .form-select:focus {
    border-color: #4a90e2;
    box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
    background: rgba(255, 255, 255, 0.95);
}

.btn-modern {
    padding: 0.5rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
}

.btn-primary.btn-modern {
    background: linear-gradient(135deg, #4a90e2, #50c878);
    color: white;
}

.btn-primary.btn-modern:hover {
    background: linear-gradient(135deg, #357abd, #45b369);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(74, 144, 226, 0.3);
}

.btn-secondary.btn-modern {
    background: rgba(108, 117, 125, 0.8);
    color: white;
}

.btn-secondary.btn-modern:hover {
    background: rgba(108, 117, 125, 1);
    transform: translateY(-1px);
}

.floating-label {
    position: relative;
}

.floating-label .form-control::placeholder {
    color: transparent;
}

.floating-label .form-control:placeholder-shown ~ .form-label {
    color: #999;
    background: transparent;
    top: 50%;
    font-size: 1rem;
}

.floating-label .form-control:focus ~ .form-label,
.floating-label .form-control:not(:placeholder-shown) ~ .form-label {
    color: #4a90e2;
    top: 0;
    font-size: 0.75rem;
    background: white;
    padding: 0 0.5rem;
}

.invalid-feedback {
    display: block;
    font-size: 0.875rem;
    color: #dc3545;
    margin-top: 0.25rem;
}

.form-text {
    color: #6c757d;
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.success-message {
    background: linear-gradient(135deg, rgba(80, 200, 120, 0.1), rgba(74, 144, 226, 0.1));
    border: 1px solid rgba(80, 200, 120, 0.3);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    color: #155724;
}

.error-message {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.1), rgba(255, 193, 7, 0.1));
    border: 1px solid rgba(220, 53, 69, 0.3);
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
    color: #721c24;
}

.required-indicator {
    color: #dc3545;
    font-weight: bold;
}

.input-group-text {
    background: rgba(74, 144, 226, 0.1);
    border: 2px solid rgba(74, 144, 226, 0.2);
    color: #4a90e2;
    font-weight: 600;
}
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="glass-card fade-in">
                <div class="glass-header p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1 text-primary">
                                <i class="fas fa-briefcase me-2"></i>Buat Lowongan Kerja Baru
                            </h3>
                            <p class="text-muted mb-0">Tambahkan lowongan kerja baru untuk perekrutan karyawan</p>
                        </div>
                        <a href="{{ route('recruitments.index') }}" class="btn btn-secondary btn-modern">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="p-4">
                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="success-message fade-in">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="error-message fade-in">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Terdapat kesalahan dalam form:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('recruitments.store') }}" id="recruitmentForm">
                        @csrf

                        <!-- Basic Information Section -->
                        <div class="section-card p-4 mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-info-circle text-primary"></i>
                                Informasi Dasar
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="job_title" class="form-label">
                                            <i class="fas fa-tag me-1"></i>Judul Pekerjaan 
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('job_title') is-invalid @enderror" 
                                               id="job_title" 
                                               name="job_title" 
                                               value="{{ old('job_title') }}"
                                               placeholder="Contoh: Kasir Klinik, Therapist, Front Office Staff"
                                               required>
                                        @error('job_title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="slots" class="form-label">
                                            <i class="fas fa-users me-1"></i>Jumlah Lowongan 
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <input type="number" 
                                               class="form-control @error('slots') is-invalid @enderror" 
                                               id="slots" 
                                               name="slots" 
                                               value="{{ old('slots', 1) }}"
                                               min="1"
                                               required>
                                        @error('slots')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="id_posisi" class="form-label">
                                            <i class="fas fa-user-tie me-1"></i>Posisi 
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <select class="form-select @error('id_posisi') is-invalid @enderror" id="id_posisi" name="id_posisi" required>
                                            <option value="">Pilih Posisi</option>
                                            @foreach($posisi as $pos)
                                                <option value="{{ $pos->id_posisi }}" {{ old('id_posisi') == $pos->id_posisi ? 'selected' : '' }}>
                                                    {{ $pos->nama_posisi }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_posisi')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="experience_required" class="form-label">
                                            <i class="fas fa-graduation-cap me-1"></i>Pengalaman Minimal
                                        </label>
                                        <input type="text" 
                                               class="form-control @error('experience_required') is-invalid @enderror" 
                                               id="experience_required" 
                                               name="experience_required" 
                                               value="{{ old('experience_required') }}"
                                               placeholder="Contoh: 6 bulan, 1 tahun, Fresh Graduate">
                                        @error('experience_required')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Kosongkan jika tidak ada pengalaman khusus yang dibutuhkan</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Job Description Section -->
                        <div class="section-card p-4 mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-file-alt text-info"></i>
                                Deskripsi & Persyaratan
                            </h5>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>Deskripsi Pekerjaan 
                                    <span class="required-indicator">*</span>
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="4" 
                                          placeholder="Deskripsikan tugas dan tanggung jawab posisi ini..."
                                          required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="requirements" class="form-label">
                                    <i class="fas fa-list-check me-1"></i>Persyaratan 
                                    <span class="required-indicator">*</span>
                                </label>
                                <textarea class="form-control @error('requirements') is-invalid @enderror" 
                                          id="requirements" 
                                          name="requirements" 
                                          rows="4" 
                                          placeholder="Tuliskan persyaratan yang dibutuhkan..."
                                          required>{{ old('requirements') }}</textarea>
                                @error('requirements')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Age & Salary Requirements Section -->
                        <div class="section-card p-4 mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-chart-line text-success"></i>
                                Kriteria & Kompensasi
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="age_min" class="form-label">
                                            <i class="fas fa-calendar-alt me-1"></i>Usia Minimum
                                        </label>
                                        <input type="number" 
                                               class="form-control @error('age_min') is-invalid @enderror" 
                                               id="age_min" 
                                               name="age_min" 
                                               value="{{ old('age_min') }}"
                                               min="16"
                                               max="100"
                                               placeholder="16">
                                        @error('age_min')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Kosongkan jika tidak ada batasan minimum</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="age_max" class="form-label">
                                            <i class="fas fa-calendar-alt me-1"></i>Usia Maksimum
                                        </label>
                                        <input type="number" 
                                               class="form-control @error('age_max') is-invalid @enderror" 
                                               id="age_max" 
                                               name="age_max" 
                                               value="{{ old('age_max') }}"
                                               min="16"
                                               max="100"
                                               placeholder="60">
                                        @error('age_max')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Kosongkan jika tidak ada batasan maksimum</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="salary_min" class="form-label">
                                            <i class="fas fa-money-bill-wave me-1"></i>Gaji Minimum
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" 
                                                   class="form-control @error('salary_min') is-invalid @enderror" 
                                                   id="salary_min" 
                                                   name="salary_min" 
                                                   value="{{ old('salary_min') }}"
                                                   placeholder="0"
                                                   min="0">
                                            @error('salary_min')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-text">Kosongkan jika negosiasi</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="salary_max" class="form-label">
                                            <i class="fas fa-money-bill-wave me-1"></i>Gaji Maksimum
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" 
                                                   class="form-control @error('salary_max') is-invalid @enderror" 
                                                   id="salary_max" 
                                                   name="salary_max" 
                                                   value="{{ old('salary_max') }}"
                                                   placeholder="0"
                                                   min="0">
                                            @error('salary_max')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-text">Kosongkan jika negosiasi</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Timeline & Status Section -->
                        <div class="section-card p-4 mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-clock text-warning"></i>
                                Timeline & Status
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label">
                                            <i class="fas fa-play-circle me-1"></i>Tanggal Mulai 
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <input type="date" 
                                               class="form-control @error('start_date') is-invalid @enderror" 
                                               id="start_date" 
                                               name="start_date" 
                                               value="{{ old('start_date', now()->format('Y-m-d')) }}"
                                               min="{{ now()->format('Y-m-d') }}"
                                               required>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Tanggal mulai lowongan dibuka</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="application_deadline" class="form-label">
                                            <i class="fas fa-stop-circle me-1"></i>Deadline Lamaran 
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <input type="date" 
                                               class="form-control @error('application_deadline') is-invalid @enderror" 
                                               id="application_deadline" 
                                               name="application_deadline" 
                                               value="{{ old('application_deadline') }}"
                                               min="{{ now()->addDay()->format('Y-m-d') }}"
                                               required>
                                        @error('application_deadline')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Tanggal penutupan penerimaan lamaran</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="employment_type" class="form-label">
                                            <i class="fas fa-briefcase me-1"></i>Tipe Pekerjaan 
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <select class="form-select @error('employment_type') is-invalid @enderror" id="employment_type" name="employment_type" required>
                                            <option value="">Pilih Tipe Pekerjaan</option>
                                            <option value="full_time" {{ old('employment_type') == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                            <option value="part_time" {{ old('employment_type') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                            <option value="contract" {{ old('employment_type') == 'contract' ? 'selected' : '' }}>Contract</option>
                                        </select>
                                        @error('employment_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">
                                            <i class="fas fa-toggle-on me-1"></i>Status 
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="open" {{ old('status', 'open') == 'open' ? 'selected' : '' }}>Aktif (Buka)</option>
                                            <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Nonaktif (Tutup)</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-3">
                            <div class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                <small>Pastikan semua informasi telah diisi dengan benar</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('recruitments.index') }}" class="btn btn-secondary btn-modern">
                                    <i class="fas fa-times me-1"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary btn-modern" id="submitBtn">
                                    <i class="fas fa-save me-1"></i>Simpan Lowongan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const salaryMin = document.getElementById('salary_min');
    const salaryMax = document.getElementById('salary_max');
    const ageMin = document.getElementById('age_min');
    const ageMax = document.getElementById('age_max');
    const startDate = document.getElementById('start_date');
    const applicationDeadline = document.getElementById('application_deadline');
    const form = document.getElementById('recruitmentForm');
    const submitBtn = document.getElementById('submitBtn');

    // Enhanced validation functions
    function validateSalary() {
        const minValue = parseFloat(salaryMin.value) || 0;
        const maxValue = parseFloat(salaryMax.value) || 0;

        if (minValue > 0 && maxValue > 0 && minValue > maxValue) {
            salaryMax.setCustomValidity('Gaji maksimum harus lebih besar atau sama dengan gaji minimum');
            showFieldError(salaryMax, 'Gaji maksimum harus lebih besar dari minimum');
        } else {
            salaryMax.setCustomValidity('');
            clearFieldError(salaryMax);
        }
    }

    function validateAge() {
        const minAge = parseInt(ageMin.value) || 0;
        const maxAge = parseInt(ageMax.value) || 0;

        if (minAge > 0 && maxAge > 0 && minAge > maxAge) {
            ageMax.setCustomValidity('Usia maksimum harus lebih besar atau sama dengan usia minimum');
            showFieldError(ageMax, 'Usia maksimum harus lebih besar dari minimum');
        } else {
            ageMax.setCustomValidity('');
            clearFieldError(ageMax);
        }
    }

    function validateDates() {
        const startDateValue = new Date(startDate.value);
        const deadlineValue = new Date(applicationDeadline.value);

        if (startDate.value && applicationDeadline.value && startDateValue >= deadlineValue) {
            applicationDeadline.setCustomValidity('Deadline lamaran harus setelah tanggal mulai');
            showFieldError(applicationDeadline, 'Deadline harus setelah tanggal mulai');
        } else {
            applicationDeadline.setCustomValidity('');
            clearFieldError(applicationDeadline);
        }
    }

    function showFieldError(field, message) {
        field.classList.add('is-invalid');
        let feedback = field.parentElement.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentElement.appendChild(feedback);
        }
        feedback.textContent = message;
    }

    function clearFieldError(field) {
        field.classList.remove('is-invalid');
        const feedback = field.parentElement.querySelector('.invalid-feedback');
        if (feedback && !feedback.dataset.server) {
            feedback.remove();
        }
    }

    // Real-time validation
    salaryMin.addEventListener('input', validateSalary);
    salaryMax.addEventListener('input', validateSalary);
    ageMin.addEventListener('input', validateAge);
    ageMax.addEventListener('input', validateAge);
    startDate.addEventListener('change', validateDates);
    applicationDeadline.addEventListener('change', validateDates);

    // Update minimum date for deadline when start date changes
    startDate.addEventListener('change', function() {
        if (startDate.value) {
            const nextDay = new Date(startDate.value);
            nextDay.setDate(nextDay.getDate() + 1);
            applicationDeadline.min = nextDay.toISOString().split('T')[0];
        }
        validateDates();
    });

    // Form submission enhancement
    form.addEventListener('submit', function(e) {
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';
        submitBtn.disabled = true;
        
        // Re-enable after 5 seconds in case of issues
        setTimeout(() => {
            submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Lowongan';
            submitBtn.disabled = false;
        }, 5000);
    });

    // Character count for textareas
    function addCharacterCount(textarea, maxLength = 1000) {
        const countElement = document.createElement('div');
        countElement.className = 'form-text text-end';
        countElement.style.fontSize = '0.75rem';
        textarea.parentElement.appendChild(countElement);

        function updateCount() {
            const remaining = maxLength - textarea.value.length;
            countElement.textContent = `${textarea.value.length}/${maxLength} karakter`;
            countElement.style.color = remaining < 50 ? '#dc3545' : '#6c757d';
        }

        textarea.addEventListener('input', updateCount);
        updateCount();
    }

    // Add character counters to textareas
    const description = document.getElementById('description');
    const requirements = document.getElementById('requirements');
    
    if (description) addCharacterCount(description, 1000);
    if (requirements) addCharacterCount(requirements, 1000);

    // Salary formatting
    function formatCurrency(input) {
        input.addEventListener('input', function() {
            let value = this.value.replace(/[^\d]/g, '');
            if (value) {
                this.value = parseInt(value).toLocaleString('id-ID');
            }
        });

        input.addEventListener('blur', function() {
            let value = this.value.replace(/[^\d]/g, '');
            if (value) {
                this.value = parseInt(value).toLocaleString('id-ID');
            }
        });

        input.addEventListener('focus', function() {
            this.value = this.value.replace(/[^\d]/g, '');
        });
    }

    // Apply currency formatting to salary fields
    // formatCurrency(salaryMin);
    // formatCurrency(salaryMax);

    // Auto-save functionality for larger forms (optional)
    let autoSaveTimer;
    const formFields = form.querySelectorAll('input, select, textarea');
    
    formFields.forEach(field => {
        field.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                // Auto-save to localStorage
                const formData = new FormData(form);
                const data = Object.fromEntries(formData);
                localStorage.setItem('recruitment_draft', JSON.stringify(data));
                
                // Show auto-save indicator
                showAutoSaveIndicator();
            }, 2000);
        });
    });

    function showAutoSaveIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'position-fixed';
        indicator.style.cssText = `
            top: 20px; right: 20px; z-index: 1050;
            background: rgba(40, 167, 69, 0.9);
            color: white; padding: 8px 16px;
            border-radius: 20px; font-size: 0.875rem;
            backdrop-filter: blur(10px);
        `;
        indicator.innerHTML = '<i class="fas fa-check me-1"></i>Draft tersimpan';
        document.body.appendChild(indicator);

        setTimeout(() => {
            indicator.style.opacity = '0';
            indicator.style.transform = 'translateX(100%)';
            setTimeout(() => indicator.remove(), 300);
        }, 2000);
    }

    // Load draft if available
    const savedDraft = localStorage.getItem('recruitment_draft');
    if (savedDraft && !form.querySelector('[name="job_title"]').value) {
        try {
            const draftData = JSON.parse(savedDraft);
            Object.keys(draftData).forEach(key => {
                const field = form.querySelector(`[name="${key}"]`);
                if (field && draftData[key]) {
                    field.value = draftData[key];
                }
            });
            
            // Show draft loaded notification
            const notification = document.createElement('div');
            notification.className = 'alert alert-info alert-dismissible fade show';
            notification.innerHTML = `
                <i class="fas fa-info-circle me-2"></i>
                Draft formulir telah dimuat. 
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            form.insertBefore(notification, form.firstChild);
        } catch (e) {
            console.log('Error loading draft:', e);
        }
    }

    // Clear draft on successful submission
    form.addEventListener('submit', function() {
        localStorage.removeItem('recruitment_draft');
    });

    // Enhanced form animations
    const sectionCards = document.querySelectorAll('.section-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    sectionCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(card);
    });
});
</script>
@endsection
