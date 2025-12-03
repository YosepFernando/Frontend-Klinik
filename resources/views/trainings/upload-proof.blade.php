@extends('layouts.app')

@section('title', 'Upload Bukti Pelatihan')

@section('content')
<div class="container-fluid px-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">
                    <i class="fas fa-upload me-2"></i>Upload Bukti Pelatihan
                </h1>
                <a href="{{ route('trainings.show', $training['id_pelatihan']) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <!-- Training Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Informasi Pelatihan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="text-primary">{{ $training['judul'] }}</h5>
                            <div class="mt-3">
                                <p class="mb-2">
                                    <i class="fas fa-calendar text-primary me-2"></i>
                                    <strong>Jadwal:</strong> 
                                    {{ \Carbon\Carbon::parse($training['jadwal_pelatihan'])->format('d F Y, H:i') }} WIB
                                </p>
                                <p class="mb-2">
                                    <i class="fas fa-tag text-primary me-2"></i>
                                    <strong>Jenis:</strong> 
                                    <span class="badge bg-info">{{ ucfirst($training['jenis_pelatihan']) }}</span>
                                </p>
                                @if(isset($training['durasi']) && $training['durasi'])
                                <p class="mb-2">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    <strong>Durasi:</strong> {{ $training['durasi'] }} Menit
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upload Bukti Kehadiran</h6>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Petunjuk:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Upload foto/screenshot sebagai bukti kehadiran Anda dalam pelatihan</li>
                            <li>Format file yang diperbolehkan: JPG, JPEG, PNG, atau PDF</li>
                            <li>Ukuran maksimal file: 5 MB</li>
                            <li>Pastikan file yang diupload jelas dan dapat dibaca</li>
                        </ul>
                    </div>

                    <form action="{{ route('trainings.upload-proof.store', $training['id_pelatihan']) }}" 
                          method="POST" 
                          enctype="multipart/form-data"
                          id="uploadProofForm">
                        @csrf

                        <div class="mb-4">
                            <label for="bukti_file" class="form-label">
                                <i class="fas fa-file-upload me-1"></i>File Bukti <span class="text-danger">*</span>
                            </label>
                            <input type="file" 
                                   class="form-control @error('bukti_file') is-invalid @enderror" 
                                   id="bukti_file" 
                                   name="bukti_file" 
                                   accept=".pdf,.jpg,.jpeg,.png"
                                   required>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Format: PDF, JPG, JPEG, PNG. Maksimal 5MB
                            </div>
                            @error('bukti_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Preview Container -->
                            <div id="filePreview" class="mt-3" style="display: none;">
                                <label class="form-label">Preview:</label>
                                <div class="border rounded p-2">
                                    <img id="imagePreview" src="" alt="Preview" class="img-fluid" style="max-height: 300px; display: none;">
                                    <div id="pdfPreview" style="display: none;">
                                        <i class="fas fa-file-pdf fa-3x text-danger"></i>
                                        <p class="mt-2 mb-0" id="pdfFileName"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="keterangan" class="form-label">
                                <i class="fas fa-comment me-1"></i>Keterangan (Opsional)
                            </label>
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                      id="keterangan" 
                                      name="keterangan" 
                                      rows="4"
                                      placeholder="Tambahkan keterangan jika diperlukan (misal: lokasi foto, waktu, dll)">{{ old('keterangan') }}</textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Maksimal 500 karakter
                            </div>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('trainings.show', $training['id_pelatihan']) }}" 
                               class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-upload me-1"></i>Upload Bukti
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('bukti_file');
    const filePreview = document.getElementById('filePreview');
    const imagePreview = document.getElementById('imagePreview');
    const pdfPreview = document.getElementById('pdfPreview');
    const pdfFileName = document.getElementById('pdfFileName');
    const form = document.getElementById('uploadProofForm');
    const submitBtn = document.getElementById('submitBtn');

    // File preview
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size (5MB)
            if (file.size > 5 * 1024 * 1024) {
                alert('Ukuran file terlalu besar! Maksimal 5MB');
                fileInput.value = '';
                filePreview.style.display = 'none';
                return;
            }

            filePreview.style.display = 'block';
            
            if (file.type.startsWith('image/')) {
                // Show image preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    pdfPreview.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else if (file.type === 'application/pdf') {
                // Show PDF info
                pdfFileName.textContent = file.name;
                imagePreview.style.display = 'none';
                pdfPreview.style.display = 'block';
            }
        } else {
            filePreview.style.display = 'none';
        }
    });

    // Form submission with loading state
    form.addEventListener('submit', function(e) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Mengupload...';
    });
});
</script>
@endpush
@endsection
