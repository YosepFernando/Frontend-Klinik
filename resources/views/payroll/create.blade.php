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
    color: #4a90e2;
    font-weight: 600;
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

.invalid-feedback {
    display: block;
    font-size: 0.875rem;
    color: #dc3545;
    margin-top: 0.25rem;
}

.calculation-display {
    background: rgba(74, 144, 226, 0.1);
    border: 2px solid rgba(74, 144, 226, 0.3);
    border-radius: 8px;
    padding: 1rem;
    margin-top: 1rem;
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
                                <i class="fas fa-plus-circle me-2"></i>Tambah Data Gaji
                            </h3>
                            <p class="text-muted mb-0">Buat data gaji baru untuk karyawan</p>
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

                    <form method="POST" action="{{ route('payroll.store') }}" id="payrollForm">
                        @csrf

                        <!-- Basic Information Section -->
                        <div class="section-card p-4 mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-info-circle text-primary"></i>
                                Informasi Dasar
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="pegawai_id" class="form-label">
                                            <i class="fas fa-user me-1"></i>Pilih Pegawai 
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <select class="form-select @error('pegawai_id') is-invalid @enderror" 
                                                id="pegawai_id" name="pegawai_id" required>
                                            <option value="">Pilih Pegawai</option>
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee['id_pegawai'] ?? $employee['id'] }}" 
                                                        {{ old('pegawai_id') == ($employee['id_pegawai'] ?? $employee['id']) ? 'selected' : '' }}>
                                                    {{ $employee['nama_lengkap'] ?? $employee['nama'] ?? 'Nama tidak tersedia' }}
                                                    @if(isset($employee['posisi']) || isset($employee['jabatan']))
                                                        - {{ $employee['posisi'] ?? $employee['jabatan'] }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('pegawai_id')
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
                                                <option value="{{ $i }}" {{ old('periode_bulan', date('n')) == $i ? 'selected' : '' }}>
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
                                                <option value="{{ $year }}" {{ old('periode_tahun', date('Y')) == $year ? 'selected' : '' }}>
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

                        <!-- Salary Details Section -->
                        <div class="section-card p-4 mb-4">
                            <h5 class="section-title">
                                <i class="fas fa-money-bill-wave text-success"></i>
                                Detail Gaji
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="gaji_pokok" class="form-label">
                                            <i class="fas fa-wallet me-1"></i>Gaji Pokok 
                                            <span class="required-indicator">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" 
                                                   class="form-control @error('gaji_pokok') is-invalid @enderror" 
                                                   id="gaji_pokok" 
                                                   name="gaji_pokok" 
                                                   value="{{ old('gaji_pokok') }}"
                                                   placeholder="0"
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
                                                   value="{{ old('gaji_kehadiran', 0) }}"
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
                                                   value="{{ old('gaji_bonus', 0) }}"
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
                                            <option value="Belum Terbayar" {{ old('status', 'Belum Terbayar') == 'Belum Terbayar' ? 'selected' : '' }}>
                                                Belum Terbayar
                                            </option>
                                            <option value="Terbayar" {{ old('status') == 'Terbayar' ? 'selected' : '' }}>
                                                Terbayar
                                            </option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Removed potongan field as it's not in the new API -->
                                <div class="col-md-6">
                                    <!-- Live Calculation Display -->
                                    <div class="calculation-display">
                                        <label class="form-label">
                                            <i class="fas fa-calculator me-1"></i>Total Gaji
                                        </label>
                                        <h4 class="text-success mb-0" id="total-display">Rp 0</h4>
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
                            
                            <div class="mb-3">
                                <label for="keterangan" class="form-label">
                                    <i class="fas fa-comment me-1"></i>Keterangan
                                </label>
                                <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                          id="keterangan" 
                                          name="keterangan" 
                                          rows="3" 
                                          placeholder="Tambahkan catatan atau keterangan tambahan...">{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Maksimal 1000 karakter</div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-3">
                            <div class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                <small>Pastikan semua informasi telah diisi dengan benar</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('payroll.index') }}" class="btn btn-secondary btn-modern">
                                    <i class="fas fa-times me-1"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary btn-modern" id="submitBtn">
                                    <i class="fas fa-save me-1"></i>Simpan Data Gaji
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
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...';
        submitBtn.disabled = true;
        
        // Re-enable after 5 seconds in case of issues
        setTimeout(() => {
            submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Simpan Data Gaji';
            submitBtn.disabled = false;
        }, 5000);
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

    // Apply currency formatting
    [gajiPokok, tunjangan, bonus, lembur, potongan].forEach(formatCurrency);

    // Validation for duplicate payroll
    const pegawaiSelect = document.getElementById('pegawai_id');
    const bulanSelect = document.getElementById('periode_bulan');
    const tahunSelect = document.getElementById('periode_tahun');

    function checkDuplicate() {
        const pegawaiId = pegawaiSelect.value;
        const bulan = bulanSelect.value;
        const tahun = tahunSelect.value;

        if (pegawaiId && bulan && tahun) {
            // You could add AJAX call here to check for duplicates
            // For now, just show a visual indicator
            const existingWarning = document.querySelector('.duplicate-warning');
            if (existingWarning) {
                existingWarning.remove();
            }
        }
    }

    pegawaiSelect.addEventListener('change', checkDuplicate);
    bulanSelect.addEventListener('change', checkDuplicate);
    tahunSelect.addEventListener('change', checkDuplicate);

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
