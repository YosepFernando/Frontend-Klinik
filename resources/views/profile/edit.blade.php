@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Profile</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <!-- Informasi Personal -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Informasi Personal</h5>
                                
                                <div class="mb-3">
                                    <label for="nama_user" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama_user" name="nama_user" 
                                           value="{{ old('nama_user', $profile['nama_user'] ?? '') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="{{ old('email', $profile['email'] ?? '') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label for="no_telp" class="form-label">No. Telepon</label>
                                    <input type="text" class="form-control" id="no_telp" name="no_telp" 
                                           value="{{ old('no_telp', $profile['no_telp'] ?? '') }}">
                                </div>

                                <div class="mb-3">
                                    <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" 
                                           value="{{ old('tanggal_lahir', 
                                               !empty($profile['biodata']['tanggal_lahir']) 
                                                   ? (strpos($profile['biodata']['tanggal_lahir'], '-') !== false 
                                                       ? \Carbon\Carbon::parse($profile['biodata']['tanggal_lahir'])->format('Y-m-d') 
                                                       : $profile['biodata']['tanggal_lahir'])
                                                   : (!empty($profile['tanggal_lahir']) 
                                                       ? (strpos($profile['tanggal_lahir'], '-') !== false 
                                                           ? \Carbon\Carbon::parse($profile['tanggal_lahir'])->format('Y-m-d') 
                                                           : $profile['tanggal_lahir'])
                                                       : '')) }}">
                                </div>
                            </div>

                            <!-- Biodata -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Biodata</h5>
                                
                                <div class="mb-3">
                                    <label for="NIK" class="form-label">NIK (16 digit sesuai ktp)</label>
                                    <input type="text" class="form-control" id="NIK" name="NIK" 
                                           value="{{ old('NIK', $profile['biodata']['NIK'] ?? '') }}" maxlength="16"
                                           pattern="[0-9]{16}" title="NIK harus 16 digit angka">
                                </div>

                                <div class="mb-3">
                                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                                    <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="L" {{ old('jenis_kelamin', $profile['biodata']['jenis_kelamin'] ?? '') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ old('jenis_kelamin', $profile['biodata']['jenis_kelamin'] ?? '') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="agama" class="form-label">Agama</label>
                                    <select class="form-select" id="agama" name="agama">
                                        <option value="">Pilih Agama</option>
                                        <option value="Islam" {{ old('agama', $profile['biodata']['agama'] ?? '') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                        <option value="Kristen" {{ old('agama', $profile['biodata']['agama'] ?? '') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                        <option value="Katolik" {{ old('agama', $profile['biodata']['agama'] ?? '') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                        <option value="Hindu" {{ old('agama', $profile['biodata']['agama'] ?? '') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                        <option value="Buddha" {{ old('agama', $profile['biodata']['agama'] ?? '') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                        <option value="Konghucu" {{ old('agama', $profile['biodata']['agama'] ?? '') == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <textarea class="form-control" id="alamat" name="alamat" rows="3">{{ old('alamat', $profile['biodata']['alamat'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Change Password Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Ubah Password (Opsional)</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Password Saat Ini</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password">
                                            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">Password Baru</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                    <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Batal
                                    </a>
                                </div>
                            </div>
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
    /* Konsistensi untuk semua form input */
    .form-control, .form-select {
        height: 38px;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    /* Konsistensi untuk textarea */
    textarea.form-control {
        height: auto;
        min-height: 80px;
        resize: vertical;
    }
    
    /* Konsistensi untuk label */
    .form-label {
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #212529;
    }
    
    /* Konsistensi margin bottom untuk form group */
    .mb-3 {
        margin-bottom: 1rem !important;
    }
    
    /* Konsistensi untuk heading */
    h5.mb-3 {
        color: #495057;
        font-weight: 600;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 0.5rem;
    }
    
    /* Button spacing */
    .d-flex.gap-2 > * + * {
        margin-left: 0.5rem;
    }
    
    /* Card styling untuk konsistensi */
    .card {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1.25rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    /* Small text consistency */
    .form-text {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 0.25rem;
    }
    
    /* Required field indicator */
    .text-danger {
        color: #dc3545 !important;
    }
</style>
@endpush

@section('scripts')
<script>
// Validasi password
document.getElementById('new_password').addEventListener('input', function() {
    const currentPassword = document.getElementById('current_password');
    const newPassword = this;
    const confirmPassword = document.getElementById('new_password_confirmation');
    
    if (newPassword.value) {
        currentPassword.required = true;
        confirmPassword.required = true;
    } else {
        currentPassword.required = false;
        confirmPassword.required = false;
    }
});
</script>
@endsection
