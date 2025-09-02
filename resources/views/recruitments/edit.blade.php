@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- Card: Edit Lowongan --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h4 class="mb-0 text-primary">
                        <i class="fas fa-edit me-2"></i>Edit Lowongan Kerja
                    </h4>
                    <small class="text-muted">
                        {{ $recruitment->position ?? '–' }}
                    </small>
                </div>
                <div class="card-body">

                    {{-- Alerts --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('recruitments.update', $recruitment->id) }}">
                        @csrf @method('PUT')

                        <div class="row g-3">

                            {{-- Judul Pekerjaan --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text"
                                           class="form-control @error('job_title') is-invalid @enderror"
                                           id="job_title"
                                           name="job_title"
                                           placeholder="Judul Pekerjaan"
                                           value="{{ old('job_title', $recruitment->position) }}"
                                           required>
                                    <label for="job_title">Judul Pekerjaan</label>
                                    @error('job_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Posisi --}}
                            <div class="col-md-6">
                                <select class="form-select @error('id_posisi') is-invalid @enderror"
                                        id="id_posisi"
                                        name="id_posisi"
                                        required>
                                    <option value="" disabled>Posisi</option>
                                    @foreach($posisi as $pos)
                                        <option value="{{ $pos->id_posisi }}"
                                            {{ old('id_posisi', $recruitment->id_posisi) == $pos->id_posisi ? 'selected' : '' }}>
                                            {{ $pos->nama_posisi }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_posisi')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tanggal Mulai --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date"
                                           class="form-control @error('start_date') is-invalid @enderror"
                                           id="start_date"
                                           name="start_date"
                                           placeholder="Tanggal Mulai"
                                           value="{{ old('start_date', optional($recruitment->tanggal_mulai)->format('Y-m-d')) }}"
                                           required>
                                    <label for="start_date">Tanggal Mulai</label>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Deadline Lamaran --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="date"
                                           class="form-control @error('application_deadline') is-invalid @enderror"
                                           id="application_deadline"
                                           name="application_deadline"
                                           placeholder="Deadline Lamaran"
                                           min="{{ date('Y-m-d') }}"
                                           value="{{ old('application_deadline', optional($recruitment->application_deadline)->format('Y-m-d')) }}"
                                           required>
                                    <label for="application_deadline">Deadline Lamaran</label>
                                    @error('application_deadline')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Tipe Pekerjaan --}}
                            <div class="col-md-6">
                                <select class="form-select @error('employment_type') is-invalid @enderror"
                                        id="employment_type"
                                        name="employment_type"
                                        required>
                                    <option value="" disabled>Tipe Pekerjaan</option>
                                    <option value="full_time" {{ old('employment_type', $recruitment->work_type) == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                    <option value="part_time" {{ old('employment_type', $recruitment->work_type) == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                    <option value="contract"  {{ old('employment_type', $recruitment->work_type) == 'contract'  ? 'selected' : '' }}>Contract</option>
                                </select>
                                @error('employment_type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Status Lowongan --}}
                            <div class="col-md-6">
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status"
                                        name="status"
                                        required>
                                    <option value="open"  {{ old('status', $recruitment->status) == 'open'   ? 'selected' : '' }}>Buka</option>
                                    <option value="closed"{{ old('status', $recruitment->status) == 'closed' ? 'selected' : '' }}>Tutup</option>
                                </select>
                                @error('status')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Jumlah Posisi --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number"
                                           class="form-control @error('slots') is-invalid @enderror"
                                           id="slots"
                                           name="slots"
                                           placeholder="Jumlah Posisi"
                                           min="1" max="100"
                                           value="{{ old('slots', $recruitment->slots) }}"
                                           required>
                                    <label for="slots">Jumlah Posisi</label>
                                    @error('slots')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Pengalaman Minimal --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text"
                                           class="form-control @error('experience_required') is-invalid @enderror"
                                           id="experience_required"
                                           name="experience_required"
                                           placeholder="Pengalaman Minimal"
                                           value="{{ old('experience_required', $recruitment->experience_required) }}">
                                    <label for="experience_required">Pengalaman Minimal</label>
                                    @error('experience_required')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Deskripsi Pekerjaan --}}
                            <div class="col-12">
                                <label for="description" class="form-label fw-bold">Deskripsi Pekerjaan</label>
                                <textarea id="description"
                                          name="description"
                                          class="form-control @error('description') is-invalid @enderror"
                                          rows="4"
                                          required>{{ old('description', $recruitment->description) }}</textarea>
                                @error('description')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Persyaratan --}}
                            <div class="col-12">
                                <label for="requirements" class="form-label fw-bold">Persyaratan</label>
                                <textarea id="requirements"
                                          name="requirements"
                                          class="form-control @error('requirements') is-invalid @enderror"
                                          rows="4"
                                          required>{{ old('requirements', $recruitment->requirements) }}</textarea>
                                @error('requirements')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Gaji Minimum --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number"
                                           class="form-control @error('salary_min') is-invalid @enderror"
                                           id="salary_min"
                                           name="salary_min"
                                           placeholder="Gaji Minimum"
                                           min="0" step="100000"
                                           value="{{ old('salary_min', $recruitment->gaji_minimal) }}">
                                    <label for="salary_min">Gaji Minimum (Rp)</label>
                                    @error('salary_min')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Gaji Maksimum --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="number"
                                           class="form-control @error('salary_max') is-invalid @enderror"
                                           id="salary_max"
                                           name="salary_max"
                                           placeholder="Gaji Maksimum"
                                           min="0" step="100000"
                                           value="{{ old('salary_max', $recruitment->gaji_maksimal) }}">
                                    <label for="salary_max">Gaji Maksimum (Rp)</label>
                                    @error('salary_max')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                        </div> {{-- .row.g-3 --}}

                        {{-- Form Actions --}}
                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('recruitments.show', $recruitment->id) }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Simpan Perubahan
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
