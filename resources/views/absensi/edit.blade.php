@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Absensi Saya
                    </h4>
                    <div>
                        <a href="{{ route('absensi.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Kembali
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

                    <!-- Info Card -->
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3 bg-primary text-white">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div>
                                <strong>{{ auth()->user()->name }}</strong>
                                <small class="d-block text-muted">
                                    {{ ucfirst(auth()->user()->role) }}
                                </small>
                                <small class="d-block">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $absensi->tanggal->format('d F Y') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('absensi.update', $absensi) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Show current status (read-only) -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-info-circle me-1"></i>Status Saat Ini
                            </label>
                            <div class="form-control-plaintext bg-light p-2 rounded">
                                <span class="badge bg-{{ $absensi->status === 'Hadir' ? 'success' : ($absensi->status === 'Terlambat' ? 'warning' : 'secondary') }}">
                                    {{ $absensi->status }}
                                </span>
                            </div>
                        </div>

                        <!-- Show time info (read-only) -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-clock me-1"></i>Jam Masuk
                                    </label>
                                    <div class="form-control-plaintext bg-light p-2 rounded">
                                        @if($absensi->jam_masuk)
                                            {{ $absensi->jam_masuk->format('H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-sign-out-alt me-1"></i>Jam Keluar
                                    </label>
                                    <div class="form-control-plaintext bg-light p-2 rounded">
                                        @if($absensi->jam_keluar)
                                            {{ $absensi->jam_keluar->format('H:i') }}
                                        @else
                                            <span class="text-muted">Belum checkout</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Editable Notes -->
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">
                                <i class="fas fa-comment me-1"></i>Keterangan Tambahan
                            </label>
                            <textarea name="keterangan" id="keterangan" rows="3" 
                                      class="form-control @error('keterangan') is-invalid @enderror" 
                                      placeholder="Tambahkan keterangan jika diperlukan...">{{ old('keterangan', $absensi->keterangan) }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Anda hanya dapat mengedit keterangan absensi Anda sendiri.</small>
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

                        <!-- Notice -->
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Perhatian:</strong> Anda hanya dapat mengedit keterangan absensi. 
                            Untuk perubahan jam masuk/keluar atau status, hubungi HRD atau Admin.
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <div>
                                <a href="{{ route('absensi.show', $absensi) }}" class="btn btn-info me-2">
                                    <i class="fas fa-eye me-1"></i>Lihat Detail
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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

.card-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
}

.btn-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    border: none;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3 0%, #007bff 100%);
    transform: translateY(-1px);
}
</style>
@endsection
