@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Edit Lowongan: {{ $recruitment->position }}</h4>
                    <a href="{{ route('recruitments.show', $recruitment) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('recruitments.update', $recruitment) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="id_posisi" class="form-label">Posisi <span class="text-danger">*</span></label>
                            <select class="form-select @error('id_posisi') is-invalid @enderror" id="id_posisi" name="id_posisi" required>
                                <option value="">Pilih Posisi</option>
                                @foreach($posisi as $pos)
                                    <option value="{{ $pos->id_posisi }}" {{ old('id_posisi', $recruitment->id_posisi) == $pos->id_posisi ? 'selected' : '' }}>
                                        {{ $pos->nama_posisi }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_posisi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="employment_type" class="form-label">Tipe Pekerjaan <span class="text-danger">*</span></label>
                                    <select class="form-select @error('employment_type') is-invalid @enderror" id="employment_type" name="employment_type" required>
                                        <option value="">Pilih Tipe Pekerjaan</option>
                                        <option value="full_time" {{ old('employment_type', $recruitment->employment_type) == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                        <option value="part_time" {{ old('employment_type', $recruitment->employment_type) == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                        <option value="contract" {{ old('employment_type', $recruitment->employment_type) == 'contract' ? 'selected' : '' }}>Contract</option>
                                    </select>
                                    @error('employment_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="slots" class="form-label">Jumlah Posisi <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('slots') is-invalid @enderror" 
                                           id="slots" 
                                           name="slots" 
                                           value="{{ old('slots', $recruitment->slots) }}"
                                           min="1"
                                           required>
                                    @error('slots')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi Pekerjaan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Deskripsikan tugas dan tanggung jawab posisi ini..."
                                      required>{{ old('description', $recruitment->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="requirements" class="form-label">Persyaratan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('requirements') is-invalid @enderror" 
                                      id="requirements" 
                                      name="requirements" 
                                      rows="4" 
                                      placeholder="Tuliskan persyaratan yang dibutuhkan..."
                                      required>{{ old('requirements', $recruitment->requirements) }}</textarea>
                            @error('requirements')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="salary_min" class="form-label">Gaji Minimum</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" 
                                               class="form-control @error('salary_min') is-invalid @enderror" 
                                               id="salary_min" 
                                               name="salary_min" 
                                               value="{{ old('salary_min', $recruitment->salary_min) }}"
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
                                    <label for="salary_max" class="form-label">Gaji Maksimum</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" 
                                               class="form-control @error('salary_max') is-invalid @enderror" 
                                               id="salary_max" 
                                               name="salary_max" 
                                               value="{{ old('salary_max', $recruitment->salary_max) }}"
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

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="application_deadline" class="form-label">Deadline Lamaran <span class="text-danger">*</span></label>
                                    <input type="date" 
                                           class="form-control @error('application_deadline') is-invalid @enderror" 
                                           id="application_deadline" 
                                           name="application_deadline" 
                                           value="{{ old('application_deadline', $recruitment->application_deadline->format('Y-m-d')) }}"
                                           required>
                                    @error('application_deadline')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="open" {{ old('status', $recruitment->status) == 'open' ? 'selected' : '' }}>Buka</option>
                                        <option value="closed" {{ old('status', $recruitment->status) == 'closed' ? 'selected' : '' }}>Tutup</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('recruitments.show', $recruitment) }}" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Lowongan
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
    const salaryMin = document.getElementById('salary_min');
    const salaryMax = document.getElementById('salary_max');

    function validateSalary() {
        const minValue = parseFloat(salaryMin.value) || 0;
        const maxValue = parseFloat(salaryMax.value) || 0;

        if (minValue > 0 && maxValue > 0 && minValue > maxValue) {
            salaryMax.setCustomValidity('Gaji maksimum harus lebih besar atau sama dengan gaji minimum');
        } else {
            salaryMax.setCustomValidity('');
        }
    }

    salaryMin.addEventListener('input', validateSalary);
    salaryMax.addEventListener('input', validateSalary);
});
</script>
@endsection
