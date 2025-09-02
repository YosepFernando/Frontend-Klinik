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

                    <!-- Informasi Karyawan (Read Only) -->
                    <!-- Data diambil dari API dengan struktur: pegawai.user.nama_user dan pegawai.posisi.nama_posisi -->
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle me-3 bg-primary text-white">
                                @php
                                    $userName = '';
                                    if (is_object($absensi) && isset($absensi->pegawai) && isset($absensi->pegawai->user)) {
                                        $userName = $absensi->pegawai->user->nama_user ?? '';
                                    } elseif (is_array($absensi) && isset($absensi['pegawai']['user']['nama_user'])) {
                                        $userName = $absensi['pegawai']['user']['nama_user'];
                                    }
                                @endphp
                                {{ $userName ? substr($userName, 0, 1) : '?' }}
                            </div>
                            <div>
                                @php
                                    $employeeName = 'Tidak diketahui';
                                    $employeeRole = '';
                                    $positionName = '';
                                    
                                    if (is_object($absensi)) {
                                        if (isset($absensi->pegawai) && isset($absensi->pegawai->user)) {
                                            $employeeName = $absensi->pegawai->user->nama_user ?? 'Tidak diketahui';
                                            $employeeRole = $absensi->pegawai->user->role ?? '';
                                        }
                                        if (isset($absensi->pegawai) && isset($absensi->pegawai->posisi)) {
                                            $positionName = $absensi->pegawai->posisi->nama_posisi ?? '';
                                        }
                                    } elseif (is_array($absensi)) {
                                        if (isset($absensi['pegawai']['user']['nama_user'])) {
                                            $employeeName = $absensi['pegawai']['user']['nama_user'];
                                        }
                                        if (isset($absensi['pegawai']['user']['role'])) {
                                            $employeeRole = $absensi['pegawai']['user']['role'];
                                        }
                                        if (isset($absensi['pegawai']['posisi']['nama_posisi'])) {
                                            $positionName = $absensi['pegawai']['posisi']['nama_posisi'];
                                        }
                                    }
                                @endphp
                                <strong>{{ $employeeName }}</strong>
                                <small class="d-block text-muted">
                                    {{ $employeeRole ? ucfirst($employeeRole) : '' }}
                                    @if($positionName)
                                        - {{ $positionName }}
                                    @endif
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

                    @php
                        $absensiId = null;
                        $debugInfo = [];
                        
                        // Debug: Cek apakah $absensi tersedia
                        if (!isset($absensi)) {
                            $debugInfo[] = 'Variable $absensi tidak tersedia';
                        } elseif (empty($absensi)) {
                            $debugInfo[] = 'Variable $absensi kosong';
                        } elseif (is_object($absensi)) {
                            $debugInfo[] = 'Data adalah object';
                            $absensiId = $absensi->id_absensi ?? $absensi->id ?? null;
                            if (!$absensiId) {
                                $availableProps = array_keys(get_object_vars($absensi));
                                $debugInfo[] = 'Field id_absensi dan id tidak ditemukan di object';
                                $debugInfo[] = 'Available properties: ' . implode(', ', $availableProps);
                            }
                        } elseif (is_array($absensi)) {
                            $debugInfo[] = 'Data adalah array';
                            $absensiId = $absensi['id_absensi'] ?? $absensi['id'] ?? null;
                            if (!$absensiId) {
                                $debugInfo[] = 'Field id_absensi dan id tidak ditemukan di array';
                                $debugInfo[] = 'Available keys: ' . implode(', ', array_keys($absensi));
                            }
                        } else {
                            $debugInfo[] = 'Data bukan object atau array, tipe: ' . gettype($absensi);
                        }
                        
                        // Log debug info untuk development
                        if (config('app.debug') && !empty($debugInfo)) {
                            \Log::info('Absensi Edit Debug Info', [
                                'debug_info' => $debugInfo,
                                'absensi_data' => $absensi ?? 'null',
                                'absensi_id' => $absensiId
                            ]);
                        }
                    @endphp

                    @if($absensiId)
                    <!-- Peringatan: Beberapa field mungkin tidak tersedia dari API -->
                    @if(!isset($absensi->status) && !isset($absensi['status']))
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Perhatian:</strong> Data absensi ini belum memiliki status, jam masuk/keluar, dan keterangan. 
                            Silakan lengkapi data berikut untuk melengkapi informasi absensi.
                        </div>
                    @endif
                    
                    <form action="{{ route('absensi.admin-update', $absensiId) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Hidden field for tanggal_absensi -->
                        @php
                            $currentTanggal = '';
                            if (is_object($absensi) && isset($absensi->tanggal_absensi)) {
                                if (is_string($absensi->tanggal_absensi)) {
                                    $currentTanggal = \Carbon\Carbon::parse($absensi->tanggal_absensi)->format('Y-m-d');
                                } elseif (method_exists($absensi->tanggal_absensi, 'format')) {
                                    $currentTanggal = $absensi->tanggal_absensi->format('Y-m-d');
                                }
                            } elseif (is_array($absensi) && isset($absensi['tanggal_absensi'])) {
                                $currentTanggal = \Carbon\Carbon::parse($absensi['tanggal_absensi'])->format('Y-m-d');
                            }
                        @endphp
                        <input type="hidden" name="tanggal_absensi" value="{{ old('tanggal_absensi', $currentTanggal ?: date('Y-m-d')) }}">

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">
                                <i class="fas fa-info-circle me-1"></i>Status <span class="text-danger">*</span>
                            </label>
                            @php
                                $currentStatus = '';
                                if (is_object($absensi) && isset($absensi->status)) {
                                    $currentStatus = $absensi->status;
                                } elseif (is_array($absensi) && isset($absensi['status'])) {
                                    $currentStatus = $absensi['status'];
                                }
                                
                                // Map frontend status to API status
                                $statusMapping = [
                                    'Terlambat' => 'Hadir',
                                    'Tidak Hadir' => 'Alpa'
                                ];
                                $currentStatus = $statusMapping[$currentStatus] ?? $currentStatus;
                            @endphp
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="">Pilih Status</option>
                                <option value="Hadir" {{ old('status', $currentStatus) == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="Sakit" {{ old('status', $currentStatus) == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="Izin" {{ old('status', $currentStatus) == 'Izin' ? 'selected' : '' }}>Izin</option>
                                <option value="Alpa" {{ old('status', $currentStatus) == 'Alpa' ? 'selected' : '' }}>Tidak Hadir</option>
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
                                        <i class="fas fa-clock me-1"></i>Jam Masuk <span class="text-danger">*</span>
                                    </label>
                                    @php
                                        $jamMasukValue = '';
                                        if (is_object($absensi) && isset($absensi->jam_masuk)) {
                                            if (is_string($absensi->jam_masuk)) {
                                                $jamMasukValue = \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i:s');
                                            } elseif (method_exists($absensi->jam_masuk, 'format')) {
                                                $jamMasukValue = $absensi->jam_masuk->format('H:i:s');
                                            }
                                        } elseif (is_array($absensi) && isset($absensi['jam_masuk'])) {
                                            $jamMasukValue = \Carbon\Carbon::parse($absensi['jam_masuk'])->format('H:i');
                                        }
                                    @endphp
                                    <input type="time" name="jam_masuk" id="jam_masuk" step="1"
                                           class="form-control @error('jam_masuk') is-invalid @enderror" 
                                           value="{{ old('jam_masuk', $jamMasukValue) }}" required>
                                    @error('jam_masuk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Jam masuk wajib diisi</small>
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
                                        } elseif (is_array($absensi) && isset($absensi['jam_keluar'])) {
                                            $jamKeluarValue = \Carbon\Carbon::parse($absensi['jam_keluar'])->format('H:i');
                                        }
                                    @endphp
                                    <input type="time" name="jam_keluar" id="jam_keluar" step="1" 
                                           class="form-control @error('jam_keluar') is-invalid @enderror" 
                                           value="{{ old('jam_keluar', $jamKeluarValue) }}">
                                    @error('jam_keluar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Kosongkan jika belum keluar</small>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        @php
                            $alamatMasuk = '';
                            if (is_object($absensi) && isset($absensi->alamat_masuk)) {
                                $alamatMasuk = $absensi->alamat_masuk;
                            } elseif (is_array($absensi) && isset($absensi['alamat_masuk'])) {
                                $alamatMasuk = $absensi['alamat_masuk'];
                            }
                        @endphp
                        
                        @if(!empty($alamatMasuk))
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Lokasi Check-in
                            </label>
                            <div class="form-control-plaintext bg-light p-2 rounded">
                                {{ $alamatMasuk }}
                            </div>
                        </div>
                        @endif

                        @php
                            $hasJamMasuk = false;
                            $hasJamKeluar = false;
                            if (is_object($absensi)) {
                                $hasJamMasuk = isset($absensi->jam_masuk) && !empty($absensi->jam_masuk);
                                $hasJamKeluar = isset($absensi->jam_keluar) && !empty($absensi->jam_keluar);
                            } elseif (is_array($absensi)) {
                                $hasJamMasuk = isset($absensi['jam_masuk']) && !empty($absensi['jam_masuk']);
                                $hasJamKeluar = isset($absensi['jam_keluar']) && !empty($absensi['jam_keluar']);
                            }
                        @endphp
                        
                        @if($hasJamMasuk && $hasJamKeluar)
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-hourglass-half me-1"></i>Durasi Kerja
                            </label>
                            @php
                                $durasiKerja = '';
                                if (is_object($absensi) && isset($absensi->durasi_kerja)) {
                                    $durasiKerja = $absensi->durasi_kerja;
                                } elseif (is_array($absensi) && isset($absensi['durasi_kerja'])) {
                                    $durasiKerja = $absensi['durasi_kerja'];
                                }
                            @endphp
                            <div class="form-control-plaintext bg-light p-2 rounded">
                                {{ $durasiKerja }}
                            </div>
                        </div>
                        @endif

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Batal
                            </a>
                            <div>
                                <a href="{{ route('absensi.show', $absensiId) }}" class="btn btn-info me-2">
                                    <i class="fas fa-eye me-1"></i>Lihat Detail
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save me-1"></i>Update Absensi
                                </button>
                            </div>
                        </div>
                    </form>
                    @else
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle me-2"></i>Data absensi tidak dapat dimuat</h5>
                            <p><strong>ID absensi tidak valid atau tidak ditemukan.</strong></p>
                            
                            @if(config('app.debug') && !empty($debugInfo))
                                <hr>
                                <h6>Informasi Debug:</h6>
                                <ul class="mb-0">
                                    @foreach($debugInfo as $info)
                                        <li>{{ $info }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            
                            <hr>
                            <p class="mb-3">Kemungkinan penyebab:</p>
                            <ul>
                                <li>ID absensi yang diminta tidak ada di database</li>
                                <li>Koneksi ke API bermasalah</li>
                                <li>Anda tidak memiliki akses ke data absensi ini</li>
                                <li>Data absensi telah dihapus</li>
                            </ul>
                            
                            <div class="d-flex gap-2">
                                <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar Absensi
                                </a>
                                <a href="{{ route('absensi.report') }}" class="btn btn-primary">
                                    <i class="fas fa-chart-bar me-1"></i>Lihat Laporan
                                </a>
                                <button onclick="window.location.reload()" class="btn btn-outline-info">
                                    <i class="fas fa-sync-alt me-1"></i>Muat Ulang
                                </button>
                            </div>
                        </div>
                    @endif
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
        
        // jam_masuk is always required by API
        jamMasukInput.setAttribute('required', 'required');
        jamKeluarInput.removeAttribute('required');
        
        // Set requirements based on status
        if (status === 'Hadir') {
            // Set default times if empty
            if (!jamMasukInput.value) {
                jamMasukInput.value = '08:00';
            }
            if (!jamKeluarInput.value) {
                jamKeluarInput.value = '17:00';
            }
        } else if (status === 'Sakit' || status === 'Izin' || status === 'Alpa') {
            // Keep jam_masuk required but set default time for non-attendance
            if (!jamMasukInput.value) {
                jamMasukInput.value = '00:00';
            }
            // Clear jam_keluar for absence statuses
            jamKeluarInput.value = '';
        }
    });
    
    // Convert time format from HH:MM to HH:MM:SS before form submission
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        // Convert jam_masuk to HH:MM:SS format
        if (jamMasukInput.value && jamMasukInput.value.length === 5) {
            jamMasukInput.value = jamMasukInput.value + ':00';
        }
        
        // Convert jam_keluar to HH:MM:SS format if not empty
        if (jamKeluarInput.value && jamKeluarInput.value.length === 5) {
            jamKeluarInput.value = jamKeluarInput.value + ':00';
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
