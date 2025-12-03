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
                        
                        <!-- Data Pribadi - Disabled Input dari Profil User -->
                        <div class="mb-4">
                            <h5 class="mb-3">Data Pribadi</h5>
                            <div class="alert alert-info">
                                <small><i class="fas fa-info-circle"></i> Data pribadi diambil dari profil akun Anda. 
                                Untuk mengubah data pribadi, silakan kunjungi <a href="{{ route('profile.edit') }}">halaman profil</a>.</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="nama_lengkap" disabled
                                               value="{{ $userProfile['nama_user'] ?? 'Belum diisi' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nik" class="form-label">NIK</label>
                                        <input type="text" class="form-control {{ empty($userProfile['biodata']['NIK'] ?? null) ? 'border-warning' : '' }}" 
                                               id="nik" disabled maxlength="16"
                                               value="{{ ($userProfile['biodata']['NIK'] ?? null) ?: 'Belum diisi' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" disabled
                                               value="{{ $userProfile['email'] ?? 'Belum diisi' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telepon" class="form-label">Telepon</label>
                                        <input type="text" class="form-control {{ (empty($userProfile['no_telp']) && empty($userProfile['biodata']['telepon'] ?? null)) ? 'border-warning' : '' }}" 
                                               id="telepon" disabled
                                               value="{{ $userProfile['no_telp'] ?? (($userProfile['biodata']['telepon'] ?? null) ?: 'Belum diisi') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control {{ empty($userProfile['biodata']['alamat'] ?? null) ? 'border-warning' : '' }}" 
                                          id="alamat" disabled rows="2">{{ ($userProfile['biodata']['alamat'] ?? null) ?: 'Belum diisi' }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                        <input type="text" class="form-control {{ empty($userProfile['biodata']['jenis_kelamin'] ?? null) ? 'border-warning' : '' }}" 
                                               id="jenis_kelamin" disabled
                                               value="{{ 
                                                   ($userProfile['biodata']['jenis_kelamin'] ?? null) == 'L' ? 'Laki-laki' : 
                                                   (($userProfile['biodata']['jenis_kelamin'] ?? null) == 'P' ? 'Perempuan' : 'Belum diisi') 
                                               }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="agama" class="form-label">Agama</label>
                                        <input type="text" class="form-control" id="agama" disabled
                                               value="{{ ($userProfile['biodata']['agama'] ?? null) ?: 'Belum diisi' }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="tanggal_lahir_display" class="form-label">Tanggal Lahir</label>
                                        <input type="text" class="form-control" id="tanggal_lahir_display" disabled
                                               value="{{ 
                                                   !empty($userProfile['biodata']['tanggal_lahir'] ?? null) 
                                                       ? \Carbon\Carbon::parse($userProfile['biodata']['tanggal_lahir'])->format('d-m-Y')
                                                       : (!empty($userProfile['tanggal_lahir'] ?? null) 
                                                           ? \Carbon\Carbon::parse($userProfile['tanggal_lahir'])->format('d-m-Y')
                                                           : 'Belum diisi') }}">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Informasi Tambahan -->
                            <h5 class="mb-3">Informasi Tambahan</h5>
                            
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

                            <hr>

                            <!-- Dokumen Lamaran -->
                            <h5 class="mb-3">Dokumen Lamaran</h5>

                            <div class="mb-3">
                                <label for="cv" class="form-label">CV/Resume <span class="text-danger">*</span></label>
                                <input type="file" class="form-control @error('cv') is-invalid @enderror" 
                                       id="cv" name="cv" accept=".pdf,.doc,.docx" required>
                                @error('cv')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Format: PDF, DOC, DOCX. Maksimal 2MB</small>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-info-circle"></i> Informasi Penting:</h6>
                            <ul class="mb-0">
                                <li>Pastikan data pribadi di profil sudah lengkap dan benar</li>
                                <li>Upload CV/Resume dalam format PDF, DOC, atau DOCX (maksimal 2MB)</li>
                                <li>Setelah melamar, Anda dapat memantau status melalui halaman status lamaran</li>
                                <li>Proses seleksi akan dilakukan melalui 3 tahap berurutan</li>
                                <li>Tim HRD akan menghubungi Anda untuk tahap berikutnya jika lolos seleksi</li>
                            </ul>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('recruitments.show', $recruitment->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Kirim Lamaran
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-warning {
    border-color: #ffc107 !important;
}
</style>
@endsection

@push('styles')
<style>
.alert-info {
    background-color: #e3f2fd;
    border-color: #2196f3;
    color: #1976d2;
}
.border-warning {
    border-color: #ffc107 !important;
}
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle session error messages dengan Sweet Alert
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: '{{ session('error') }}',
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33'
            });
        @endif

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                confirmButtonText: 'OK',
                confirmButtonColor: '#28a745'
            });
        @endif

        @if (session('info'))
            Swal.fire({
                icon: 'info',
                title: 'Informasi',
                text: '{{ session('info') }}',
                confirmButtonText: 'OK',
                confirmButtonColor: '#17a2b8'
            });
        @endif

        // Check incomplete data and show warning with Sweet Alert
        @php
            $incompleteData = [];
            $biodata = $userProfile['biodata'] ?? [];
            
            if (empty($biodata['NIK'] ?? null)) $incompleteData[] = 'NIK';
            if (empty($userProfile['no_telp']) && empty($biodata['telepon'] ?? null)) $incompleteData[] = 'Nomor Telepon';
            if (empty($biodata['alamat'] ?? null)) $incompleteData[] = 'Alamat';
            if (empty($biodata['jenis_kelamin'] ?? null)) $incompleteData[] = 'Jenis Kelamin';
        @endphp

        @if (!empty($incompleteData))
            Swal.fire({
                icon: 'warning',
                title: 'Data Tidak Lengkap!',
                html: 'Data berikut belum diisi: <strong>{{ implode(', ', $incompleteData) }}</strong><br><br>Anda tetap dapat melanjutkan lamaran, namun disarankan untuk melengkapi profil.',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-edit"></i> Lengkapi Profil',
                cancelButtonText: 'Lanjutkan Lamar',
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route('profile.edit') }}';
                }
            });
        @endif

        // Custom confirm untuk form submission
        const form = document.querySelector('form');
        const submitButton = document.querySelector('button[type="submit"]');
        
        if (form && submitButton) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Konfirmasi Lamaran',
                    text: 'Yakin ingin mengirim lamaran untuk posisi {{ $recruitment->position }}?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-paper-plane"></i> Ya, Kirim Lamaran',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Mengirim Lamaran...',
                            text: 'Mohon tunggu sebentar',
                            icon: 'info',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            willOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Submit form
                        form.submit();
                    }
                });
            });
        }
    });
</script>
@endpush
