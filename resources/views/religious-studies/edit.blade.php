@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-cash-register me-2"></i>Edit Penggajian</h2>
                <div>
                    <a href="{{ route('religious-studies.show', $religiousStudy) }}" class="btn btn-info me-2">
                        <i class="fas fa-eye me-1"></i>Detail
                    </a>
                    <a href="{{ route('religious-studies.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('religious-studies.update', $religiousStudy) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Judul Pengajian <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $religiousStudy->title) }}" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="leader_id" class="form-label">Pemateri <span class="text-danger">*</span></label>
                                    <select class="form-select @error('leader_id') is-invalid @enderror" 
                                            id="leader_id" name="leader_id" required>
                                        <option value="">Pilih Pemateri</option>
                                        @foreach($leaders as $leader)
                                        <option value="{{ $leader->id }}" 
                                                {{ old('leader_id', $religiousStudy->leader_id) == $leader->id ? 'selected' : '' }}>
                                            {{ $leader->name }} ({{ ucfirst($leader->role) }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('leader_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="4" required>{{ old('description', $religiousStudy->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="study_date" class="form-label">Tanggal Pengajian <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('study_date') is-invalid @enderror" 
                                           id="study_date" name="study_date" 
                                           value="{{ old('study_date', $religiousStudy->study_date->format('Y-m-d')) }}" required>
                                    @error('study_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Waktu Mulai <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                           id="start_time" name="start_time" 
                                           value="{{ old('start_time', $religiousStudy->start_time) }}" required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">Waktu Selesai <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                           id="end_time" name="end_time" 
                                           value="{{ old('end_time', $religiousStudy->end_time) }}" required>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Lokasi <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                           id="location" name="location" value="{{ old('location', $religiousStudy->location) }}" required>
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_participants" class="form-label">Kapasitas Maksimal <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('max_participants') is-invalid @enderror" 
                                           id="max_participants" name="max_participants" 
                                           value="{{ old('max_participants', $religiousStudy->max_participants) }}" 
                                           min="{{ $religiousStudy->participants->count() }}" max="100" required>
                                    @error('max_participants')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Minimal {{ $religiousStudy->participants->count() }} (jumlah peserta saat ini)
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="">Pilih Status</option>
                                        <option value="scheduled" {{ old('status', $religiousStudy->status) == 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
                                        <option value="ongoing" {{ old('status', $religiousStudy->status) == 'ongoing' ? 'selected' : '' }}>Berlangsung</option>
                                        <option value="completed" {{ old('status', $religiousStudy->status) == 'completed' ? 'selected' : '' }}>Selesai</option>
                                        <option value="cancelled" {{ old('status', $religiousStudy->status) == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="materials" class="form-label">Materi Pengajian</label>
                            <textarea class="form-control @error('materials') is-invalid @enderror" 
                                      id="materials" name="materials" rows="4" 
                                      placeholder="Daftar materi yang akan disampaikan...">{{ old('materials', $religiousStudy->materials) }}</textarea>
                            @error('materials')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Current Participants Info -->
                        @if($religiousStudy->participants->count() > 0)
                        <div class="alert alert-info">
                            <strong><i class="fas fa-info-circle me-2"></i>Informasi Peserta:</strong>
                            Pengajian ini memiliki {{ $religiousStudy->participants->count() }} peserta terdaftar. 
                            Pastikan perubahan tidak mengganggu peserta yang sudah terdaftar.
                        </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <div>
                                <a href="{{ route('religious-studies.show', $religiousStudy) }}" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-times me-2"></i>Batal
                                </a>
                                <a href="{{ route('religious-studies.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-list me-2"></i>Daftar
                                </a>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Pengajian
                                </button>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-info-circle me-1"></i>Fitur hapus tidak tersedia
                                </small>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
