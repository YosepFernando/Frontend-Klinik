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
                                @php
                                    $userName = session('user_name', 'Pengguna');
                                    if (auth()->check() && auth()->user()) {
                                        $userName = auth()->user()->name ?? $userName;
                                    }
                                @endphp
                                {{ substr($userName, 0, 1) }}
                            </div>
                            <div>
                                <strong>{{ $userName }}</strong>
                                <small class="d-block text-muted">
                                    @php
                                        $userRole = session('user_role', 'pegawai');
                                        if (auth()->check() && auth()->user()) {
                                            $userRole = auth()->user()->role ?? $userRole;
                                        }
                                    @endphp
                                    {{ ucfirst($userRole) }}
                                </small>
                                <small class="d-block">
                                    <i class="fas fa-calendar me-1"></i>
                                    @php
                                        $tanggalFormatted = 'Tidak diketahui';
                                        if (is_object($absensi) && isset($absensi->tanggal_absensi)) {
                                            if (is_string($absensi->tanggal_absensi)) {
                                                $tanggalFormatted = \Carbon\Carbon::parse($absensi->tanggal_absensi)->format('d F Y');
                                            } elseif (method_exists($absensi->tanggal_absensi, 'format')) {
                                                $tanggalFormatted = $absensi->tanggal_absensi->format('d F Y');
                                            }
                                        } elseif (is_array($absensi) && isset($absensi['tanggal_absensi'])) {
                                            $tanggalFormatted = \Carbon\Carbon::parse($absensi['tanggal_absensi'])->format('d F Y');
                                        }
                                    @endphp
                                    {{ $tanggalFormatted }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('absensi.update', $absensi) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Tanggal Absensi -->
                        <div class="mb-3">
                            <label for="tanggal_absensi" class="form-label">
                                <i class="fas fa-calendar me-1"></i>Tanggal Absensi <span class="text-danger">*</span>
                            </label>
                            @php
                                $tanggalValue = '';
                                if (is_object($absensi) && isset($absensi->tanggal_absensi)) {
                                    if (is_string($absensi->tanggal_absensi)) {
                                        $tanggalValue = \Carbon\Carbon::parse($absensi->tanggal_absensi)->format('Y-m-d');
                                    } elseif (method_exists($absensi->tanggal_absensi, 'format')) {
                                        $tanggalValue = $absensi->tanggal_absensi->format('Y-m-d');
                                    }
                                } elseif (is_array($absensi) && isset($absensi['tanggal_absensi'])) {
                                    $tanggalValue = \Carbon\Carbon::parse($absensi['tanggal_absensi'])->format('Y-m-d');
                                }
                            @endphp
                            <input type="date" 
                                   class="form-control @error('tanggal_absensi') is-invalid @enderror" 
                                   id="tanggal_absensi" 
                                   name="tanggal_absensi" 
                                   value="{{ old('tanggal_absensi', $tanggalValue) }}" 
                                   required>
                            @error('tanggal_absensi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">
                                <i class="fas fa-info-circle me-1"></i>Status <span class="text-danger">*</span>
                            </label>
                            @php
                                $currentStatus = 'Hadir';
                                if (is_object($absensi) && isset($absensi->status)) {
                                    $currentStatus = $absensi->status;
                                } elseif (is_array($absensi) && isset($absensi['status'])) {
                                    $currentStatus = $absensi['status'];
                                }
                            @endphp
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" 
                                    name="status" 
                                    required>
                                <option value="">Pilih Status</option>
                                <option value="Hadir" {{ old('status', $currentStatus) == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="Sakit" {{ old('status', $currentStatus) == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="Izin" {{ old('status', $currentStatus) == 'Izin' ? 'selected' : '' }}>Izin</option>
                                <option value="Alfa" {{ old('status', $currentStatus) == 'Alfa' ? 'selected' : '' }}>Alfa</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Time inputs -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jam_masuk" class="form-label">
                                        <i class="fas fa-clock me-1"></i>Jam Masuk <span class="text-danger">*</span>
                                    </label>
                                    @php
                                        $jamMasukValue = '';
                                        if (is_object($absensi) && isset($absensi->jam_masuk)) {
                                            if (is_string($absensi->jam_masuk)) {
                                                $jamMasukValue = \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i');
                                            } elseif (method_exists($absensi->jam_masuk, 'format')) {
                                                $jamMasukValue = $absensi->jam_masuk->format('H:i');
                                            }
                                        } elseif (is_array($absensi) && isset($absensi['jam_masuk']) && !empty($absensi['jam_masuk'])) {
                                            $jamMasukValue = \Carbon\Carbon::parse($absensi['jam_masuk'])->format('H:i');
                                        }
                                    @endphp
                                    <input type="time" 
                                           class="form-control @error('jam_masuk') is-invalid @enderror" 
                                           id="jam_masuk" 
                                           name="jam_masuk" 
                                           value="{{ old('jam_masuk', $jamMasukValue) }}" 
                                           required>
                                    @error('jam_masuk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jam_keluar" class="form-label">
                                        <i class="fas fa-sign-out-alt me-1"></i>Jam Keluar
                                    </label>
                                    @php
                                        $jamKeluarValue = '';
                                        if (is_object($absensi) && isset($absensi->jam_keluar)) {
                                            if (is_string($absensi->jam_keluar)) {
                                                $jamKeluarValue = \Carbon\Carbon::parse($absensi->jam_keluar)->format('H:i');
                                            } elseif (method_exists($absensi->jam_keluar, 'format')) {
                                                $jamKeluarValue = $absensi->jam_keluar->format('H:i');
                                            }
                                        } elseif (is_array($absensi) && isset($absensi['jam_keluar']) && !empty($absensi['jam_keluar'])) {
                                            $jamKeluarValue = \Carbon\Carbon::parse($absensi['jam_keluar'])->format('H:i');
                                        }
                                    @endphp
                                    <input type="time" 
                                           class="form-control @error('jam_keluar') is-invalid @enderror" 
                                           id="jam_keluar" 
                                           name="jam_keluar" 
                                           value="{{ old('jam_keluar', $jamKeluarValue) }}">
                                    @error('jam_keluar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan -->
                        <div class="mb-3">
                            <label for="keterangan" class="form-label">
                                <i class="fas fa-comment me-1"></i>Keterangan
                            </label>
                            @php
                                $keteranganValue = '';
                                if (is_object($absensi) && isset($absensi->keterangan)) {
                                    $keteranganValue = $absensi->keterangan;
                                } elseif (is_array($absensi) && isset($absensi['keterangan'])) {
                                    $keteranganValue = $absensi['keterangan'];
                                }
                            @endphp
                            <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                      id="keterangan" 
                                      name="keterangan" 
                                      rows="3" 
                                      placeholder="Tambahkan keterangan (opsional)">{{ old('keterangan', $keteranganValue) }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <div>
                                @php
                                    $absensiId = null;
                                    if (is_object($absensi)) {
                                        $absensiId = $absensi->id_absensi ?? $absensi->id ?? null;
                                    } elseif (is_array($absensi)) {
                                        $absensiId = $absensi['id_absensi'] ?? $absensi['id'] ?? null;
                                    }
                                @endphp
                                
                                @if($absensiId)
                                <a href="{{ route('absensi.show', $absensiId) }}" class="btn btn-info me-2">
                                    <i class="fas fa-eye me-1"></i>Lihat Detail
                                </a>
                                @endif
                                
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
