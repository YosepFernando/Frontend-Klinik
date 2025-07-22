@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Tambah Absensi Manual
                    </h4>
                    <div>
                        <a href="{{ route('absensi.report') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali ke Laporan
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('absensi.admin-store') }}" method="POST">
                        @csrf
                        
                        <!-- Employee Selection -->
                        <div class="mb-3">
                            <label for="user_id" class="form-label">
                                <i class="fas fa-user me-1"></i>Karyawan <span class="text-danger">*</span>
                            </label>
                            <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">Pilih Karyawan</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ ucfirst($user->role) }})
                                        @if($user->pegawai && $user->pegawai->posisi)
                                            - {{ $user->pegawai->posisi->nama_posisi }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Date -->
                        <div class="mb-3">
                            <label for="tanggal_absensi" class="form-label">
                                <i class="fas fa-calendar me-1"></i>Tanggal Absensi <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_absensi" id="tanggal_absensi" 
                                   class="form-control @error('tanggal_absensi') is-invalid @enderror" 
                                   value="{{ old('tanggal_absensi', date('Y-m-d')) }}" required>
                            @error('tanggal_absensi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">
                                <i class="fas fa-info-circle me-1"></i>Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="">Pilih Status</option>
                                <option value="Hadir" {{ old('status') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="Terlambat" {{ old('status') == 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                                <option value="Sakit" {{ old('status') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="Izin" {{ old('status') == 'Izin' ? 'selected' : '' }}>Izin</option>
                                <option value="Tidak Hadir" {{ old('status') == 'Tidak Hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Time Fields -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jam_masuk" class="form-label">
                                        <i class="fas fa-clock me-1"></i>Jam Masuk
                                    </label>
                                    <input type="time" name="jam_masuk" id="jam_masuk" 
                                           class="form-control @error('jam_masuk') is-invalid @enderror" 
                                           value="{{ old('jam_masuk') }}">
                                    @error('jam_masuk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Kosongkan jika tidak masuk</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jam_keluar" class="form-label">
                                        <i class="fas fa-sign-out-alt me-1"></i>Jam Keluar
                                    </label>
                                    <input type="time" name="jam_keluar" id="jam_keluar" 
                                           class="form-control @error('jam_keluar') is-invalid @enderror" 
                                           value="{{ old('jam_keluar') }}">
                                    @error('jam_keluar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Kosongkan jika belum keluar</small>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">
                                <i class="fas fa-comment me-1"></i>Keterangan
                            </label>
                            <textarea name="keterangan" id="keterangan" rows="3" 
                                      class="form-control @error('keterangan') is-invalid @enderror" 
                                      placeholder="Masukkan keterangan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i>Simpan Absensi
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
    const statusSelect = document.getElementById('status');
    const jamMasukInput = document.getElementById('jam_masuk');
    const jamKeluarInput = document.getElementById('jam_keluar');
    
    statusSelect.addEventListener('change', function() {
        const status = this.value;
        
        // Reset fields
        jamMasukInput.removeAttribute('required');
        jamKeluarInput.removeAttribute('required');
        
        // Set default times based on status
        if (status === 'Hadir') {
            jamMasukInput.value = '08:00';
            jamKeluarInput.value = '17:00';
            jamMasukInput.setAttribute('required', 'required');
        } else if (status === 'Terlambat') {
            jamMasukInput.value = '08:30';
            jamKeluarInput.value = '17:00';
            jamMasukInput.setAttribute('required', 'required');
        } else {
            jamMasukInput.value = '';
            jamKeluarInput.value = '';
        }
    });
});
</script>

<style>
.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
}

.btn-success:hover {
    background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
    transform: translateY(-1px);
}
</style>
@endsection
