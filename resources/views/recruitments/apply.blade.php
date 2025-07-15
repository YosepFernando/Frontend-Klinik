@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Lamar Pekerjaan: {{ $recruitment->position }}</h4>
                </div>
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><strong>Posisi:</strong> {{ $recruitment->position }}</h6>
                                <p><strong>Tipe:</strong> {{ $recruitment->employment_type_display }}</p>
                                <p><strong>Deadline:</strong> {{ $recruitment->application_deadline->format('d M Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6>Proses Seleksi 3 Tahap:</h6>
                                    <ol class="mb-0">
                                        <li>Seleksi Berkas</li>
                                        <li>Wawancara</li>
                                        <li>Hasil Akhir</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('recruitments.apply', $recruitment->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                           id="full_name" name="full_name" value="{{ old('full_name') }}" required
                                           placeholder="Masukkan nama lengkap Anda">
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nik" class="form-label">NIK <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nik') is-invalid @enderror" 
                                           id="nik" name="nik" value="{{ old('nik') }}" required
                                           placeholder="Nomor Induk Kependudukan" maxlength="16" pattern="[0-9]{16}">
                                    @error('nik')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">16 digit angka sesuai KTP</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required
                                           placeholder="contoh@email.com">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Telepon <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}" required
                                           placeholder="08xxxxxxxxxx">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" required
                                      placeholder="Alamat lengkap tempat tinggal">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="education" class="form-label">Pendidikan Terakhir <span class="text-danger">*</span></label>
                            <select class="form-select @error('education') is-invalid @enderror" id="education" name="education" required>
                                <option value="">Pilih Pendidikan Terakhir</option>
                                <option value="SD" {{ old('education') == 'SD' ? 'selected' : '' }}>SD/Sederajat</option>
                                <option value="SMP" {{ old('education') == 'SMP' ? 'selected' : '' }}>SMP/Sederajat</option>
                                <option value="SMA" {{ old('education') == 'SMA' ? 'selected' : '' }}>SMA/SMK/Sederajat</option>
                                <option value="D1" {{ old('education') == 'D1' ? 'selected' : '' }}>Diploma 1 (D1)</option>
                                <option value="D2" {{ old('education') == 'D2' ? 'selected' : '' }}>Diploma 2 (D2)</option>
                                <option value="D3" {{ old('education') == 'D3' ? 'selected' : '' }}>Diploma 3 (D3)</option>
                                <option value="D4" {{ old('education') == 'D4' ? 'selected' : '' }}>Diploma 4 (D4)</option>
                                <option value="S1" {{ old('education') == 'S1' ? 'selected' : '' }}>Sarjana (S1)</option>
                                <option value="S2" {{ old('education') == 'S2' ? 'selected' : '' }}>Magister (S2)</option>
                                <option value="S3" {{ old('education') == 'S3' ? 'selected' : '' }}>Doktor (S3)</option>
                            </select>
                            @error('education')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="cover_letter" class="form-label">Surat Lamaran <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('cover_letter') is-invalid @enderror" 
                                      id="cover_letter" name="cover_letter" rows="6" required
                                      placeholder="Tulis surat lamaran Anda di sini...">{{ old('cover_letter') }}</textarea>
                            @error('cover_letter')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Maksimal 5000 karakter</small>
                        </div>

                        <div class="mb-3">
                            <label for="cv" class="form-label">CV/Resume <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('cv') is-invalid @enderror" 
                                   id="cv" name="cv" accept=".pdf,.doc,.docx" required>
                            @error('cv')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Format: PDF, DOC, DOCX. Maksimal 2MB</small>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-info-circle"></i> Informasi Penting:</h6>
                            <ul class="mb-0">
                                <li>Pastikan semua dokumen telah diisi dengan benar</li>
                                <li>Setelah melamar, Anda dapat memantau status melalui halaman status lamaran</li>
                                <li>Proses seleksi akan dilakukan melalui 3 tahap berurutan</li>
                                <li>Tim HRD akan menghubungi Anda untuk tahap berikutnya jika lolos seleksi</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('recruitments.show', $recruitment->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary" 
                                    onclick="return confirm('Yakin ingin mengirim lamaran untuk posisi {{ $recruitment->position }}?')">
                                <i class="fas fa-paper-plane"></i> Kirim Lamaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.alert-info {
    background-color: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}
</style>
@endpush
