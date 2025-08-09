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

.required-indicator {
    color: #dc3545;
    font-weight: bold;
}

.input-group-text {
    background: rgba(74, 144, 226, 0.1);
    border: 2px solid rgba(74, 144, 226, 0.2);
    border-right: 0;
    border-radius: 8px 0 0 8px;
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

.calculation-display {
    padding: 1rem;
    background: linear-gradient(135deg, rgba(74, 144, 226, 0.05), rgba(80, 200, 120, 0.05));
    border-radius: 8px;
    border: 1px solid rgba(74, 144, 226, 0.2);
    text-align: center;
}
</style>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="glass-card fade-in">
                <div class="glass-header p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-1 text-primary">
                                <i class="fas fa-edit me-2"></i>Edit Data Gaji
                            </h3>
                            <p class="text-muted mb-0">Ubah data gaji karyawan</p>
                        </div>
                        <a href="{{ route('payroll.index') }}" class="btn btn-secondary btn-modern">
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

                    @if(session('error'))
                        <div class="error-message fade-in">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            @if(strpos(session('error'), 'Sesi') !== false || strpos(session('error'), 'login') !== false)
                                <div class="mt-2">
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-sign-in-alt me-1"></i> Login Kembali
                                    </a>
                                </div>
                            @endif
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

                    @if(config('app.debug'))
                        <!-- Debug Information (only shown in debug mode) -->
                        <div class="alert alert-info" style="font-size: 0.8em;">
                            <strong>Debug Info:</strong><br>
                            Session authenticated: {{ session('authenticated') ? 'true' : 'false' }}<br>
                            User ID: {{ session('user_id') ?? 'null' }}<br>
                            User Role: {{ session('user_role') ?? 'null' }}<br>
                            API Token: {{ session('api_token') ? 'present' : 'missing' }}<br>
                            Auth User: {{ auth_user() ? auth_user()->name . ' (' . auth_user()->role . ')' : 'null' }}<br>
                            Can Manage Payroll: {{ can_manage_payroll() ? 'true' : 'false' }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('payroll.update', $payroll['id_gaji']) }}" id="payrollForm">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information Section -->
                        <div class="section-card p-4 mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-info-circle text-primary"></i>
                                Informasi Dasar
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="id_pegawai" class="form-label">
                                            <i class="fas fa-user me-1"></i>Pegawai 
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <select class="form-select @error('id_pegawai') is-invalid @enderror" 
                                                id="id_pegawai" name="id_pegawai" required>
                                            <option value="">Pilih Pegawai</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee['id_pegawai'] }}" 
                                                        {{ old('id_pegawai', $payroll['id_pegawai']) == $employee['id_pegawai'] ? 'selected' : '' }}>
                                                    {{ $employee['nama_lengkap'] ?? 'Nama tidak tersedia' }}
                                                    @if(isset($employee['posisi']))
                                                        - {{ $employee['posisi']['nama_posisi'] ?? '' }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('id_pegawai')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="periode_bulan" class="form-label">
                                            <i class="fas fa-calendar me-1"></i>Bulan 
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <select class="form-select @error('periode_bulan') is-invalid @enderror" 
                                                id="periode_bulan" name="periode_bulan" required>
                                            <option value="">Pilih Bulan</option>
                                            @for($i = 1; $i <= 12; $i++)
                                                <option value="{{ $i }}" {{ old('periode_bulan', $payroll['periode_bulan']) == $i ? 'selected' : '' }}>
                                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                                </option>
                                            @endfor
                                        </select>
                                        @error('periode_bulan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="periode_tahun" class="form-label">
                                            <i class="fas fa-calendar-alt me-1"></i>Tahun 
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <select class="form-select @error('periode_tahun') is-invalid @enderror" 
                                                id="periode_tahun" name="periode_tahun" required>
                                            <option value="">Pilih Tahun</option>
                                            @for($year = date('Y') + 1; $year >= 2020; $year--)
                                                <option value="{{ $year }}" {{ old('periode_tahun', $payroll['periode_tahun']) == $year ? 'selected' : '' }}>
                                                    {{ $year }}
                                                </option>
                                            @endfor
                                        </select>
                                        @error('periode_tahun')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Salary Information Section -->
                        <div class="section-card p-4 mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-money-bill-wave text-success"></i>
                                Informasi Gaji
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gaji_pokok" class="form-label">
                                            <i class="fas fa-dollar-sign me-1"></i>Gaji Pokok 
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" 
                                                   class="form-control @error('gaji_pokok') is-invalid @enderror" 
                                                   id="gaji_pokok" 
                                                   name="gaji_pokok" 
                                                   value="{{ old('gaji_pokok', $payroll['gaji_pokok']) }}"
                                                   placeholder="Masukkan gaji pokok"
                                                   min="0"
                                                   step="1000"
                                                   required>
                                        </div>
                                        @error('gaji_pokok')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gaji_kehadiran" class="form-label">
                                            <i class="fas fa-plus-circle me-1"></i>Gaji Kehadiran
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" 
                                                   class="form-control @error('gaji_kehadiran') is-invalid @enderror" 
                                                   id="gaji_kehadiran" 
                                                   name="gaji_kehadiran" 
                                                   value="{{ old('gaji_kehadiran', $payroll['gaji_kehadiran']) }}"
                                                   placeholder="0"
                                                   min="0"
                                                   step="1000">
                                        </div>
                                        @error('gaji_kehadiran')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gaji_bonus" class="form-label">
                                            <i class="fas fa-gift me-1"></i>Bonus
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" 
                                                   class="form-control @error('gaji_bonus') is-invalid @enderror" 
                                                   id="gaji_bonus" 
                                                   name="gaji_bonus" 
                                                   value="{{ old('gaji_bonus', $payroll['gaji_bonus']) }}"
                                                   placeholder="0"
                                                   min="0"
                                                   step="1000">
                                        </div>
                                        @error('gaji_bonus')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">
                                            <i class="fas fa-tag me-1"></i>Status
                                        </label>
                                        <select class="form-select @error('status') is-invalid @enderror" 
                                                id="status" name="status" required>
                                            <option value="Belum Terbayar" {{ old('status', $payroll['status']) == 'Belum Terbayar' ? 'selected' : '' }}>
                                                Belum Terbayar
                                            </option>
                                            <option value="Terbayar" {{ old('status', $payroll['status']) == 'Terbayar' ? 'selected' : '' }}>
                                                Terbayar
                                            </option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Live Calculation Display -->
                                    <div class="calculation-display">
                                        <label class="form-label">
                                            <i class="fas fa-calculator me-1"></i>Total Gaji
                                        </label>
                                        <h4 class="text-success mb-0" id="total-display">
                                            Rp {{ number_format($payroll['gaji_total'], 0, ',', '.') }}
                                        </h4>
                                        <small class="text-muted">Perhitungan otomatis</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information Section -->
                        <div class="section-card p-4 mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-sticky-note text-warning"></i>
                                Informasi Tambahan
                            </h5>
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="keterangan" class="form-label">
                                            <i class="fas fa-comment-alt me-1"></i>Keterangan
                                        </label>
                                        <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                                  id="keterangan" 
                                                  name="keterangan"
                                                  rows="3"
                                                  placeholder="Catatan tambahan (opsional)">{{ old('keterangan', $payroll['keterangan'] ?? '') }}</textarea>
                                        @error('keterangan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('payroll.show', $payroll['id_gaji']) }}" class="btn btn-secondary btn-modern">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary btn-modern" id="submitBtn">
                                <i class="fas fa-save me-1"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gajiPokok = document.getElementById('gaji_pokok');
    const gajiKehadiran = document.getElementById('gaji_kehadiran');
    const gajiBonus = document.getElementById('gaji_bonus');
    const totalDisplay = document.getElementById('total-display');
    const form = document.getElementById('payrollForm');
    const submitBtn = document.getElementById('submitBtn');

    // Real-time calculation
    function calculateTotal() {
        const gajiPokokValue = parseFloat(gajiPokok.value) || 0;
        const gajiKehadiranValue = parseFloat(gajiKehadiran.value) || 0;
        const gajiBonusValue = parseFloat(gajiBonus.value) || 0;

        const total = gajiPokokValue + gajiKehadiranValue + gajiBonusValue;
        
        totalDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.max(0, total));
        
        // Update color based on total
        if (total > 0) {
            totalDisplay.className = 'text-success mb-0';
        } else {
            totalDisplay.className = 'text-warning mb-0';
        }
    }

    // Add event listeners for real-time calculation
    [gajiPokok, gajiKehadiran, gajiBonus].forEach(input => {
        input.addEventListener('input', calculateTotal);
    });

    // Initial calculation
    calculateTotal();

    // Form submission enhancement
    form.addEventListener('submit', function(e) {
        // Check authentication before submission
        @if(!session('api_token') || !session('authenticated'))
            e.preventDefault();
            alert('Sesi Anda telah berakhir. Halaman akan dialihkan ke login.');
            window.location.href = '{{ route("login") }}';
            return false;
        @endif
        
        // Validate required fields
        const requiredFields = ['id_pegawai', 'periode_bulan', 'periode_tahun', 'gaji_pokok', 'status'];
        let hasError = false;
        
        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && (!field.value || field.value.trim() === '')) {
                field.classList.add('is-invalid');
                hasError = true;
            } else if (field) {
                field.classList.remove('is-invalid');
            }
        });
        
        if (hasError) {
            e.preventDefault();
            alert('Mohon lengkapi semua field yang wajib diisi.');
            return false;
        }
        
        // Check if total gaji is valid
        const total = parseFloat(gajiPokok.value || 0) + 
                     parseFloat(gajiKehadiran.value || 0) + 
                     parseFloat(gajiBonus.value || 0);
        
        if (total <= 0) {
            e.preventDefault();
            alert('Total gaji harus lebih dari 0.');
            gajiPokok.focus();
            return false;
        }
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';
        submitBtn.disabled = true;
        
        // Add hidden input to track update attempt
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'update_timestamp';
        hiddenInput.value = new Date().toISOString();
        form.appendChild(hiddenInput);
        
        // Re-enable after 10 seconds in case of issues
        setTimeout(() => {
            submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Perubahan';
            submitBtn.disabled = false;
        }, 10000);
    });

    // Number formatting for better UX
    function formatCurrency(input) {
        input.addEventListener('blur', function() {
            if (this.value) {
                // Remove any non-numeric characters and format
                let value = this.value.replace(/[^\d]/g, '');
                if (value) {
                    this.value = parseInt(value);
                }
            }
        });
    }

    // Apply formatting to currency inputs
    [gajiPokok, gajiKehadiran, gajiBonus].forEach(input => {
        formatCurrency(input);
    });

    // Character count for textarea
    const keterangan = document.getElementById('keterangan');
    const maxLength = 1000;
    
    const countElement = document.createElement('div');
    countElement.className = 'form-text text-end';
    countElement.style.fontSize = '0.75rem';
    keterangan.parentElement.appendChild(countElement);

    function updateCharCount() {
        const remaining = maxLength - keterangan.value.length;
        countElement.textContent = `${keterangan.value.length}/${maxLength} karakter`;
        countElement.style.color = remaining < 50 ? '#dc3545' : '#6c757d';
    }

    keterangan.addEventListener('input', updateCharCount);
    updateCharCount();
});
</script>
@endsection
