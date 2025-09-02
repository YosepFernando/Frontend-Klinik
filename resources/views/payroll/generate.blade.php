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

.btn-warning.btn-modern {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.btn-warning.btn-modern:hover {
    background: linear-gradient(135deg, #e0a800, #dc6705);
    transform: translateY(-1px);
    box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
}

.employee-card {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 8px;
    border: 2px solid rgba(74, 144, 226, 0.2);
    transition: all 0.3s ease;
}

.employee-card:hover {
    border-color: #4a90e2;
    transform: translateY(-1px);
}

.employee-card.selected {
    border-color: #4a90e2;
    background: rgba(74, 144, 226, 0.1);
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
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
                                <i class="fas fa-calculator me-2"></i>Generate Payroll
                            </h3>
                            <p class="text-muted mb-0">Generate gaji otomatis untuk periode tertentu</p>
                        </div>
                        <a href="{{ route('payroll.index') }}" class="btn btn-secondary btn-modern">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="p-4">
                    <form method="POST" action="{{ route('payroll.generate') }}" id="generateForm">
                        @csrf

                        <!-- Period Selection -->
                        <div class="section-card p-4 mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-calendar text-primary me-2"></i>Pilih Periode
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="bulan" class="form-label fw-bold">
                                            <i class="fas fa-calendar-alt me-1"></i>Bulan <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('bulan') is-invalid @enderror" 
                                                id="bulan" name="bulan" required>
                                            <option value="">Pilih Bulan</option>
                                            @for($i = 1; $i <= 12; $i++)
                                                <option value="{{ $i }}" {{ old('bulan', date('n')) == $i ? 'selected' : '' }}>
                                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                                </option>
                                            @endfor
                                        </select>
                                        @error('bulan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tahun" class="form-label fw-bold">
                                            <i class="fas fa-calendar me-1"></i>Tahun <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select @error('tahun') is-invalid @enderror" 
                                                id="tahun" name="tahun" required>
                                            <option value="">Pilih Tahun</option>
                                            @for($year = date('Y') + 1; $year >= 2020; $year--)
                                                <option value="{{ $year }}" {{ old('tahun', date('Y')) == $year ? 'selected' : '' }}>
                                                    {{ $year }}
                                                </option>
                                            @endfor
                                        </select>
                                        @error('tahun')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Employee Selection -->
                        <div class="section-card p-4 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">
                                    <i class="fas fa-users text-info me-2"></i>Pilih Pegawai
                                </h5>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">
                                        <i class="fas fa-check-square me-1"></i>Pilih Semua
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">
                                        <i class="fas fa-square me-1"></i>Batal Semua
                                    </button>
                                </div>
                            </div>
                            
                            <div class="row">
                                @if($employees->count() > 0)
                                    @foreach($employees as $employee)
                                        <div class="col-md-6 col-lg-4 mb-3">
                                            <div class="employee-card p-3">
                                                <div class="form-check">
                                                    <input class="form-check-input employee-checkbox" 
                                                           type="checkbox" 
                                                           name="pegawai_ids[]" 
                                                           value="{{ $employee['id_pegawai'] }}"
                                                           id="employee_{{ $employee['id_pegawai'] }}">
                                                    <label class="form-check-label w-100" 
                                                           for="employee_{{ $employee['id_pegawai'] }}">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-grow-1">
                                                                <div class="fw-bold">
                                                                    {{ $employee['nama_lengkap'] ?? 'Nama tidak tersedia' }}
                                                                </div>
                                                                <small class="text-muted">
                                                                    {{ $employee['posisi']['nama_posisi'] ?? 'Posisi tidak tersedia' }}
                                                                </small>
                                                                @if(isset($employee['posisi']['gaji_pokok']))
                                                                    <div class="text-success small">
                                                                        Gaji Pokok: Rp {{ number_format($employee['posisi']['gaji_pokok'], 0, ',', '.') }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <i class="fas fa-user-circle fa-2x text-muted"></i>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="text-center py-4">
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Tidak ada data pegawai</h5>
                                            <p class="text-muted">Silakan tambahkan data pegawai terlebih dahulu.</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="mt-3 p-3 bg-light rounded">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Catatan:</strong> Jika tidak ada pegawai yang dipilih, sistem akan generate payroll untuk semua pegawai aktif. 
                                    Pastikan data gaji pokok pegawai sudah terisi dengan benar.
                                </small>
                            </div>
                        </div>

                        <!-- Generation Options -->
                        <div class="section-card p-4 mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-cogs text-warning me-2"></i>Opsi Generate
                            </h5>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb me-2"></i>
                                <strong>Informasi:</strong> Sistem akan menghitung gaji berdasarkan data gaji pokok, 
                                absensi, dan aturan perhitungan yang sudah ditetapkan. Pastikan data absensi untuk 
                                periode ini sudah lengkap.
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-body text-center">
                                            <i class="fas fa-calculator fa-2x text-primary mb-2"></i>
                                            <h6>Perhitungan Otomatis</h6>
                                            <small class="text-muted">
                                                Gaji dihitung berdasarkan absensi dan aturan yang sudah ditetapkan
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-body text-center">
                                            <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                            <h6>Include Bonus & Potongan</h6>
                                            <small class="text-muted">
                                                Sistem akan memperhitungkan bonus dan potongan yang ada
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center pt-3">
                            <div class="text-muted">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <small>Proses generate payroll tidak dapat dibatalkan. Pastikan periode dan pegawai sudah benar.</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('payroll.index') }}" class="btn btn-secondary btn-modern">
                                    <i class="fas fa-times me-1"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-warning btn-modern" id="generateBtn">
                                    <i class="fas fa-calculator me-1"></i>Generate Payroll
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
    const selectAllBtn = document.getElementById('selectAll');
    const deselectAllBtn = document.getElementById('deselectAll');
    const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');
    const generateBtn = document.getElementById('generateBtn');
    const form = document.getElementById('generateForm');

    // Select all employees
    selectAllBtn.addEventListener('click', function() {
        employeeCheckboxes.forEach(checkbox => {
            checkbox.checked = true;
            updateEmployeeCard(checkbox);
        });
    });

    // Deselect all employees
    deselectAllBtn.addEventListener('click', function() {
        employeeCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
            updateEmployeeCard(checkbox);
        });
    });

    // Update employee card appearance
    function updateEmployeeCard(checkbox) {
        const card = checkbox.closest('.employee-card');
        if (checkbox.checked) {
            card.classList.add('selected');
        } else {
            card.classList.remove('selected');
        }
    }

    // Add event listeners to checkboxes
    employeeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateEmployeeCard(this);
        });
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        const bulan = document.getElementById('bulan').value;
        const tahun = document.getElementById('tahun').value;
        
        if (!bulan || !tahun) {
            e.preventDefault();
            alert('Mohon pilih bulan dan tahun terlebih dahulu.');
            return;
        }

        const monthName = document.getElementById('bulan').options[document.getElementById('bulan').selectedIndex].text;
        
        if (!confirm(`Yakin ingin generate payroll untuk ${monthName} ${tahun}?`)) {
            e.preventDefault();
            return;
        }

        // Show loading state
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
        generateBtn.disabled = true;
    });

    // Preview selected count
    function updateSelectedCount() {
        const selectedCount = document.querySelectorAll('.employee-checkbox:checked').length;
        const totalCount = employeeCheckboxes.length;
        
        let existingBadge = document.querySelector('.selected-count-badge');
        if (existingBadge) {
            existingBadge.remove();
        }
        
        if (selectedCount > 0) {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary selected-count-badge ms-2';
            badge.textContent = `${selectedCount}/${totalCount} dipilih`;
            selectAllBtn.parentElement.appendChild(badge);
        }
    }

    // Update count on checkbox change
    employeeCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });

    // Initial count update
    updateSelectedCount();
});
</script>
@endsection
