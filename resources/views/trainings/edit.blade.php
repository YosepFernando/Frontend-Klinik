@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-graduation-cap me-2"></i>Edit Pelatihan</h2>
                <div>
                    <a href="{{ route('trainings.show', $training->id ?? $training->id_pelatihan) }}" class="btn btn-info me-2">
                        <i class="fas fa-eye me-1"></i>Detail
                    </a>
                    <a href="{{ route('trainings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('trainings.update', $training->id ?? $training->id_pelatihan) }}" method="POST">
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
                                            id="jenis_pelatihan" name="jenis_pelatihan" required onchange="toggleConditionalFields()">
                                        <option value="">Pilih Jenis Pelatihan</option>
                                        <option value="video" {{ old('jenis_pelatihan', $training->jenis_pelatihan) == 'video' ? 'selected' : '' }}>Video Online</option>
                                        <option value="document" {{ old('jenis_pelatihan', $training->jenis_pelatihan) == 'document' ? 'selected' : '' }}>Dokumen</option>
                                        <option value="zoom" {{ old('jenis_pelatihan', $training->jenis_pelatihan) == 'zoom' ? 'selected' : '' }}>Zoom Meeting</option>
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

                        <div class="mb-3">
                            <label for="jadwal_pelatihan" class="form-label">Jadwal Pelatihan</label>
                            <input type="datetime-local" class="form-control @error('jadwal_pelatihan') is-invalid @enderror" 
                                   id="jadwal_pelatihan" name="jadwal_pelatihan" 
                                   value="{{ old('jadwal_pelatihan', $training->jadwal_pelatihan ? \Carbon\Carbon::parse($training->jadwal_pelatihan)->format('Y-m-d\TH:i') : '') }}">
                            @error('jadwal_pelatihan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Tentukan waktu pelaksanaan pelatihan.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="durasi" class="form-label">Durasi (dalam menit)</label>
                                    <input type="number" class="form-control @error('durasi') is-invalid @enderror" 
                                           id="durasi" name="durasi" value="{{ old('durasi', $training->durasi) }}" min="1">
                                    @error('durasi')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Estimasi durasi pelatihan dalam menit.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3" id="url_field" style="display: none;">
                            <label for="link_url" class="form-label">Link URL <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-link"></i>
                                </span>
                                <input type="url" class="form-control @error('link_url') is-invalid @enderror" 
                                       id="link_url" name="link_url" value="{{ old('link_url', $training->link_url) }}"
                                       placeholder="https://example.com">
                                @if($training->jenis_pelatihan === 'zoom')
                                <button type="button" class="btn btn-outline-primary" onclick="validateZoomUrl()">
                                    <i class="fas fa-check me-1"></i>Validasi URL
                                </button>
                                @endif
                            </div>
                            @error('link_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text" id="url_help_text"></div>
                            
                            @if($training->jenis_pelatihan === 'zoom')
                            <div class="alert alert-info mt-2 p-2">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small>Pastikan URL Zoom Meeting valid dan berisi ID Meeting yang benar</small>
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="mb-3" id="offline_address_field" style="display: none;">
                            <label for="offline_address" class="form-label">Alamat/Lokasi Pelatihan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-map-marker-alt"></i>
                                </span>
                                <textarea class="form-control @error('link_url') is-invalid @enderror" 
                                          id="offline_address" name="link_url" rows="3" 
                                          placeholder="Masukkan alamat lengkap tempat pelatihan...">{{ old('link_url', $training->jenis_pelatihan === 'offline' ? $training->link_url : '') }}</textarea>
                            </div>
                            @error('link_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Masukkan alamat lengkap tempat pelaksanaan pelatihan offline.</div>
                        </div>

                        <hr class="my-4">

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
function toggleConditionalFields() {
    const jenisSelect = document.getElementById('jenis_pelatihan');
    const urlField = document.getElementById('url_field');
    const urlInput = document.getElementById('link_url');
    const offlineAddressField = document.getElementById('offline_address_field');
    const offlineAddressInput = document.getElementById('offline_address');
    const urlHelpText = document.getElementById('url_help_text');
    const jadwalField = document.getElementById('jadwal_pelatihan');

    // Hide both fields initially
    urlField.style.display = 'none';
    offlineAddressField.style.display = 'none';
    urlInput.removeAttribute('required');
    urlInput.disabled = true;
    offlineAddressInput.removeAttribute('required');
    offlineAddressInput.disabled = true;
    
    const selectedType = jenisSelect.value;

    // Jenis pelatihan yang membutuhkan URL
    const onlineTypes = ['video', 'document', 'zoom', 'video/meet', 'video/online meet'];
    
    if (selectedType === 'offline') {
        // Show offline address field
        offlineAddressField.style.display = 'block';
        offlineAddressInput.setAttribute('required', 'required');
        offlineAddressInput.disabled = false;
        
        // Make field focusable after display
        setTimeout(() => {
            offlineAddressInput.focus();
        }, 100);
        
    } else if (onlineTypes.includes(selectedType)) {
        urlField.style.display = 'block';
        urlInput.setAttribute('required', 'required');
        urlInput.disabled = false;
        
        // Make field focusable after display
        setTimeout(() => {
            urlInput.focus();
        }, 100);
        
        // Update help text based on type
        if (selectedType === 'video') {
            urlHelpText.textContent = 'Masukkan link video pelatihan (YouTube, Vimeo, dll)';
            urlInput.placeholder = 'https://www.youtube.com/watch?v=example';
        } else if (selectedType === 'zoom') {
            urlHelpText.textContent = 'Masukkan link Zoom Meeting untuk pelatihan';
            urlInput.placeholder = 'https://zoom.us/j/example';
            
            // For Zoom, highlight the jadwal field
            if (jadwalField) {
                jadwalField.classList.add('border-primary');
                jadwalField.style.boxShadow = '0 0 0 0.2rem rgba(45, 140, 255, 0.25)';
                
                // Add a note about schedule importance for Zoom
                const jadwalNote = document.createElement('div');
                jadwalNote.className = 'text-primary mt-1';
                jadwalNote.innerHTML = '<i class="fas fa-info-circle"></i> Jadwal penting untuk Meeting Zoom';
                
                const existingNote = jadwalField.parentElement.querySelector('.text-primary');
                if (!existingNote) {
                    jadwalField.parentElement.appendChild(jadwalNote);
                }
            }
        } else if (selectedType === 'video/meet' || selectedType === 'video/online meet') {
            urlHelpText.textContent = 'Masukkan link meeting online untuk pelatihan';
            urlInput.placeholder = 'https://meet.google.com/example';
        } else if (selectedType === 'document') {
            urlHelpText.textContent = 'Masukkan link dokumen pelatihan (Google Drive, Dropbox, dll)';
            urlInput.placeholder = 'https://drive.google.com/example';
        } else {
            urlHelpText.textContent = 'Masukkan link URL untuk pelatihan';
            urlInput.placeholder = 'https://example.com';
        }
    }
    
    // Reset jadwal field styling if not zoom
    if (selectedType !== 'zoom' && jadwalField) {
        jadwalField.classList.remove('border-primary');
        jadwalField.style.boxShadow = '';
        
        const existingNote = jadwalField.parentElement.querySelector('.text-primary');
        if (existingNote) {
            existingNote.remove();
        }
    }
}

// Initialize on page load to set the correct initial state
document.addEventListener('DOMContentLoaded', function() {
    toggleConditionalFields();
    
    // Add validation for URL format
    const urlInput = document.getElementById('link_url');
    if (urlInput) {
        urlInput.addEventListener('input', function() {
            validateUrl(this);
        });
    }
});

// URL validation function
function validateUrl(input) {
    if (input.value && input.value.trim() !== '') {
        const urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
        
        if (urlPattern.test(input.value)) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.add('is-invalid');
            input.classList.remove('is-valid');
            
            // Check for missing https://
            if (!input.value.startsWith('http')) {
                const helpText = document.getElementById('url_help_text');
                if (helpText) {
                    helpText.innerHTML = '<span class="text-warning">URL harus dimulai dengan http:// atau https://</span>';
                }
            }
        }
    } else {
        input.classList.remove('is-valid', 'is-invalid');
    }
}

// Function to validate Zoom URL
function validateZoomUrl() {
    const urlInput = document.getElementById('link_url');
    const helpText = document.getElementById('url_help_text');
    const url = urlInput.value.trim();
    
    if (!url) {
        helpText.innerHTML = '<span class="text-danger">URL tidak boleh kosong</span>';
        return;
    }
    
    // Check if it's a proper URL
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
        helpText.innerHTML = '<span class="text-warning">URL harus dimulai dengan http:// atau https://</span>';
        return;
    }
    
    // Check if it's a Zoom URL
    if (!url.includes('zoom.us')) {
        helpText.innerHTML = '<span class="text-warning">URL sepertinya bukan dari zoom.us</span>';
        return;
    }
    
    // Check if it contains meeting ID
    if (!url.includes('/j/') && !url.includes('/meeting/')) {
        helpText.innerHTML = '<span class="text-warning">URL tidak berisi ID meeting (/j/XXXX or /meeting/XXXX)</span>';
        return;
    }
    
    // Looks good
    helpText.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>URL Zoom Meeting valid!</span>';
    urlInput.classList.add('is-valid');
    urlInput.classList.remove('is-invalid');
}
</script>
@endsection