@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-graduation-cap me-2"></i>Edit Pelatihan</h2>
                <div>
                    <a href="{{ route('trainings.show', $training) }}" class="btn btn-info me-2">
                        <i class="fas fa-eye me-1"></i>Detail
                    </a>
                    <a href="{{ route('trainings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('trainings.update', $training) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="judul" class="form-label">Judul Pelatihan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror" 
                                           id="judul" name="judul" value="{{ old('judul', $training->judul) }}" maxlength="100" required>
                                    @error('judul')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jenis_pelatihan" class="form-label">Jenis Pelatihan <span class="text-danger">*</span></label>
                                    <select class="form-select @error('jenis_pelatihan') is-invalid @enderror" 
                                            id="jenis_pelatihan" name="jenis_pelatihan" required onchange="toggleLocationUrl()">
                                        <option value="">Pilih Jenis Pelatihan</option>
                                        <option value="video" {{ old('jenis_pelatihan', $training->jenis_pelatihan) == 'video' ? 'selected' : '' }}>Video Online</option>
                                        <option value="document" {{ old('jenis_pelatihan', $training->jenis_pelatihan) == 'document' ? 'selected' : '' }}>Dokumen</option>
                                        <option value="offline" {{ old('jenis_pelatihan', $training->jenis_pelatihan) == 'offline' ? 'selected' : '' }}>Offline/Tatap Muka</option>
                                    </select>
                                    @error('jenis_pelatihan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                      id="deskripsi" name="deskripsi" rows="4" required>{{ old('deskripsi', $training->deskripsi) }}</textarea>
                            @error('deskripsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="durasi" class="form-label">Durasi (dalam jam)</label>
                                    <input type="number" class="form-control @error('durasi') is-invalid @enderror" 
                                           id="durasi" name="durasi" value="{{ old('durasi', $training->durasi) }}" min="1">
                                    @error('durasi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Estimasi durasi pelatihan dalam jam (opsional)</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                               id="is_active" name="is_active" value="1" {{ old('is_active', $training->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Status Aktif
                                        </label>
                                    </div>
                                    <div class="form-text">Centang untuk mengaktifkan pelatihan</div>
                                </div>
                            </div>
                        </div>

                        <!-- URL Field (untuk video/document) -->
                        <div class="mb-3" id="url_field" style="display: none;">
                            <label for="link_url" class="form-label">Link URL <span class="text-danger">*</span></label>
                            <input type="url" class="form-control @error('link_url') is-invalid @enderror" 
                                   id="link_url" name="link_url" value="{{ old('link_url', $training->link_url) }}">
                            @error('link_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text" id="url_help">Masukkan link video atau dokumen pelatihan</div>
                        </div>

                        <!-- Location Field (untuk offline) -->
                        <div class="mb-3" id="location_field" style="display: none;">
                            <label for="konten" class="form-label">Lokasi Pelatihan <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('konten') is-invalid @enderror" 
                                      id="konten" name="konten" rows="3">{{ old('konten', $training->konten) }}</textarea>
                            @error('konten')
                                <div class="invalid-feedback">{{ $message }}</div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('trainings.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Pelatihan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleLocationUrl() {
    const jenisSelect = document.getElementById('jenis_pelatihan');
    const urlField = document.getElementById('url_field');
    const locationField = document.getElementById('location_field');
    const urlInput = document.getElementById('link_url');
    const locationInput = document.getElementById('konten');
    
    if (jenisSelect.value === 'offline') {
        urlField.style.display = 'none';
        locationField.style.display = 'block';
        urlInput.removeAttribute('required');
        locationInput.setAttribute('required', 'required');
    } else if (jenisSelect.value === 'video' || jenisSelect.value === 'document') {
        urlField.style.display = 'block';
        locationField.style.display = 'none';
        urlInput.setAttribute('required', 'required');
        locationInput.removeAttribute('required');
        
        // Update help text based on type
        const helpText = document.getElementById('url_help');
        if (jenisSelect.value === 'video') {
            helpText.textContent = 'Masukkan link video pelatihan (YouTube, Vimeo, dll)';
        } else {
            helpText.textContent = 'Masukkan link dokumen pelatihan (Google Drive, Dropbox, dll)';
        }
    } else {
        urlField.style.display = 'none';
        locationField.style.display = 'none';
        urlInput.removeAttribute('required');
        locationInput.removeAttribute('required');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleLocationUrl();
});
</script>
@endsection
