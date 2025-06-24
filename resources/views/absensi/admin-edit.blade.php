@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Absensi
                    </h4>
                    <div>
                        <a href="{{ route('absensi.report') }}" class="btn btn-outline-light">
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

                    <!-- Employee Info (Read Only) -->
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3 bg-primary text-white">
                                {{ substr($absensi->pegawai->user->name, 0, 1) }}
                            </div>
                            <div>
                                <strong>{{ $absensi->pegawai->user->name }}</strong>
                                <small class="d-block text-muted">
                                    {{ ucfirst($absensi->pegawai->user->role) }}
                                    @if($absensi->pegawai && $absensi->pegawai->posisi)
                                        - {{ $absensi->pegawai->posisi->nama_posisi }}
                                    @endif
                                </small>
                                <small class="d-block">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $absensi->tanggal->format('d F Y') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('absensi.admin-update', $absensi) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">
                                <i class="fas fa-info-circle me-1"></i>Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="">Pilih Status</option>
                                <option value="Hadir" {{ old('status', $absensi->status) == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="Terlambat" {{ old('status', $absensi->status) == 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                                <option value="Sakit" {{ old('status', $absensi->status) == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="Izin" {{ old('status', $absensi->status) == 'Izin' ? 'selected' : '' }}>Izin</option>
                                <option value="Tidak Hadir" {{ old('status', $absensi->status) == 'Tidak Hadir' ? 'selected' : '' }}>Tidak Hadir</option>
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
                                           value="{{ old('jam_masuk', $absensi->jam_masuk ? $absensi->jam_masuk->format('H:i') : '') }}">
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
                                           value="{{ old('jam_keluar', $absensi->jam_keluar ? $absensi->jam_keluar->format('H:i') : '') }}">
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
                                      placeholder="Masukkan keterangan tambahan (opsional)">{{ old('keterangan', $absensi->keterangan) }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Additional Info -->
                        @if($absensi->alamat_masuk)
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Lokasi Check-in
                            </label>
                            <div class="form-control-plaintext bg-light p-2 rounded">
                                {{ $absensi->alamat_masuk }}
                            </div>
                        </div>
                        @endif

                        @if($absensi->jam_masuk && $absensi->jam_keluar)
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-hourglass-half me-1"></i>Durasi Kerja
                            </label>
                            <div class="form-control-plaintext bg-light p-2 rounded">
                                {{ $absensi->durasi_kerja }}
                            </div>
                        </div>
                        @endif

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('absensi.report') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <div>
                                <a href="{{ route('absensi.show', $absensi) }}" class="btn btn-info me-2">
                                    <i class="fas fa-eye me-1"></i>Lihat Detail
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-1"></i>Update Absensi
                                </button>
                            </div>
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
        
        // Remove required attributes
        jamMasukInput.removeAttribute('required');
        jamKeluarInput.removeAttribute('required');
        
        // Set requirements based on status
        if (status === 'Hadir' || status === 'Terlambat') {
            jamMasukInput.setAttribute('required', 'required');
            
            // Set default times if empty
            if (!jamMasukInput.value) {
                jamMasukInput.value = status === 'Hadir' ? '08:00' : '08:30';
            }
            if (!jamKeluarInput.value && status === 'Hadir') {
                jamKeluarInput.value = '17:00';
            }
        } else if (status === 'Sakit' || status === 'Izin' || status === 'Tidak Hadir') {
            // Clear times for absence statuses
            jamMasukInput.value = '';
            jamKeluarInput.value = '';
        }
    });
});
</script>

<style>
.card-header {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    color: white;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.btn-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    border: none;
    color: white;
}

.btn-warning:hover {
    background: linear-gradient(135deg, #ff8c00 0%, #ffc107 100%);
    transform: translateY(-1px);
    color: white;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: bold;
}

.form-control-plaintext {
    border: 1px solid #dee2e6;
}
</style>
@endsection
