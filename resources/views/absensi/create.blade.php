@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-gradient-success text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-clock me-2"></i>Check In - Absensi Karyawan
                            </h4>
                            <small class="opacity-75">Silahkan melakukan check-in untuk mencatat kehadiran Anda</small>
                        </div>
                        <a href="{{ route('absensi.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('absensi.store') }}" method="POST" id="absensiForm">
                        @csrf
                        
                        <!-- Employee & Time Info Cards -->
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h5 class="card-title border-bottom pb-2 mb-3">
                                            <i class="fas fa-user-tie me-2"></i>Informasi Karyawan
                                        </h5>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="avatar-circle me-3">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                @php
                                                    $userName = 'Pengguna';
                                                    $userRole = 'pegawai';
                                                    $pegawaiData = session('pegawai_data');
                                                    $apiUser = session('api_user');
                                                    
                                                    // Debug - Tampilkan informasi yang tersedia
                                                    \Log::debug('Data User di View', [
                                                        'auth_user' => auth()->user(),
                                                        'session_api_user' => $apiUser,
                                                        'session_user_name' => session('user_name'),
                                                        'session_user_role' => session('user_role'),
                                                        'session_pegawai_data' => $pegawaiData
                                                    ]);
                                                    
                                                    // Prioritas 1: Dari session pegawai_data (paling akurat)
                                                    if (is_array($pegawaiData)) {
                                                        if (!empty($pegawaiData['nama_lengkap'])) {
                                                            $userName = $pegawaiData['nama_lengkap'];
                                                        }
                                                        if (isset($pegawaiData['user']) && is_array($pegawaiData['user'])) {
                                                            if (!empty($pegawaiData['user']['role'])) {
                                                                $userRole = $pegawaiData['user']['role'];
                                                            }
                                                            if (empty($userName) || $userName === 'Pengguna') {
                                                                $userName = $pegawaiData['user']['nama_user'] ?? $pegawaiData['user']['name'] ?? $userName;
                                                            }
                                                        }
                                                    }
                                                    
                                                    // Prioritas 2: Dari session user (reliable)
                                                    if (empty($userName) || $userName === 'Pengguna') {
                                                        $userName = session('user_name', $userName);
                                                    }
                                                    if (empty($userRole) || $userRole === 'pegawai') {
                                                        $userRole = session('user_role', $userRole);
                                                    }
                                                    
                                                    // Prioritas 3: Dari auth()->user() jika tersedia
                                                    if (auth()->check() && auth()->user()) {
                                                        if ((empty($userName) || $userName === 'Pengguna') && !empty(auth()->user()->name)) {
                                                            $userName = auth()->user()->name;
                                                        }
                                                        if ((empty($userRole) || $userRole === 'pegawai') && !empty(auth()->user()->role)) {
                                                            $userRole = auth()->user()->role;
                                                        }
                                                    }
                                                    
                                                    // Prioritas 4: Dari api_user session (fallback)
                                                    if (is_array($apiUser)) {
                                                        if ((empty($userName) || $userName === 'Pengguna')) {
                                                            if (!empty($apiUser['nama_user'])) {
                                                                $userName = $apiUser['nama_user'];
                                                            } elseif (!empty($apiUser['name'])) {
                                                                $userName = $apiUser['name'];
                                                            }
                                                        }
                                                        
                                                        if ((empty($userRole) || $userRole === 'pegawai') && !empty($apiUser['role'])) {
                                                            $userRole = $apiUser['role'];
                                                        }
                                                    }
                                                @endphp
                                                <h5 class="mb-0">{{ $userName }}</h5>
                                                <span class="badge bg-primary">{{ ucfirst($userRole) }}</span>
                                                @php
                                                    $posisiName = '';
                                                    if (is_array($pegawaiData) && isset($pegawaiData['posisi']) && is_array($pegawaiData['posisi'])) {
                                                        $posisiName = $pegawaiData['posisi']['nama_posisi'] ?? '';
                                                    }
                                                @endphp
                                                @if(!empty($posisiName))
                                                    <span class="badge bg-secondary">{{ $posisiName }}</span>
                                                @elseif(auth()->user() && isset(auth()->user()->pegawai) && isset(auth()->user()->pegawai->posisi))
                                                    <span class="badge bg-secondary">{{ auth()->user()->pegawai->posisi->nama_posisi }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        @php
                                            $pegawaiId = null;
                                            $departemen = 'Umum';
                                            $pegawaiData = session('pegawai_data');
                                            $apiUser = session('api_user');
                                            
                                            // Prioritas 1: Dari session pegawai_data (paling akurat)
                                            if (is_array($pegawaiData)) {
                                                $pegawaiId = $pegawaiData['id_pegawai'] ?? null;
                                                $departemen = $pegawaiData['departemen'] ?? $departemen;
                                                
                                                // Jika departemen kosong, coba dari posisi
                                                if (($departemen === 'Umum' || empty($departemen)) && isset($pegawaiData['posisi']) && is_array($pegawaiData['posisi'])) {
                                                    $departemen = $pegawaiData['posisi']['nama_posisi'] ?? $departemen;
                                                }
                                            }
                                            
                                            // Prioritas 2: Dari session pegawai_id langsung
                                            if (!$pegawaiId) {
                                                $pegawaiId = session('pegawai_id');
                                            }
                                            
                                            // Prioritas 3: Dari auth()->user()->pegawai
                                            if (!$pegawaiId && auth()->check() && auth()->user() && isset(auth()->user()->pegawai)) {
                                                $pegawaiDataAuth = auth()->user()->pegawai;
                                                $pegawaiId = $pegawaiDataAuth->id_pegawai ?? $pegawaiDataAuth->id ?? null;
                                                if ($departemen === 'Umum') {
                                                    $departemen = $pegawaiDataAuth->departemen ?? $departemen;
                                                }
                                            }
                                            
                                            // Prioritas 4: Dari id_pegawai langsung di auth()->user()
                                            if (!$pegawaiId && auth()->check() && auth()->user() && isset(auth()->user()->id_pegawai)) {
                                                $pegawaiId = auth()->user()->id_pegawai;
                                            }
                                            
                                            // Prioritas 5: Dari api_user session
                                            if (!$pegawaiId && is_array($apiUser)) {
                                                // Langsung dari api_user
                                                if (isset($apiUser['id_pegawai'])) {
                                                    $pegawaiId = $apiUser['id_pegawai'];
                                                    if ($departemen === 'Umum') {
                                                        $departemen = $apiUser['departemen'] ?? $departemen;
                                                    }
                                                }
                                                
                                                // Dari api_user.pegawai
                                                if (!$pegawaiId && isset($apiUser['pegawai'])) {
                                                    if (is_array($apiUser['pegawai'])) {
                                                        $pegawaiId = $apiUser['pegawai']['id_pegawai'] ?? $apiUser['pegawai']['id'] ?? null;
                                                        if ($departemen === 'Umum') {
                                                            $departemen = $apiUser['pegawai']['departemen'] ?? $departemen;
                                                        }
                                                    } elseif (is_object($apiUser['pegawai'])) {
                                                        $pegawaiId = $apiUser['pegawai']->id_pegawai ?? $apiUser['pegawai']->id ?? null;
                                                        if ($departemen === 'Umum') {
                                                            $departemen = $apiUser['pegawai']->departemen ?? $departemen;
                                                        }
                                                    }
                                                }
                                            }
                                            
                                            // Prioritas 6: Fallback ke user_id
                                            if (!$pegawaiId) {
                                                $pegawaiId = session('user_id') ?? 'N/A';
                                            }
                                            
                                            // Debug - Tampilkan informasi untuk debugging
                                            \Log::debug('ID Pegawai di View', [
                                                'pegawaiId' => $pegawaiId,
                                                'departemen' => $departemen,
                                                'pegawai_data_available' => !empty($pegawaiData),
                                                'pegawai_data_keys' => is_array($pegawaiData) ? array_keys($pegawaiData) : null,
                                                'auth_user_pegawai' => auth()->check() && auth()->user() ? isset(auth()->user()->pegawai) : null,
                                                'api_user_keys' => is_array($apiUser) ? array_keys($apiUser) : null,
                                                'api_user_pegawai' => is_array($apiUser) && isset($apiUser['pegawai']) ? 'ada' : 'tidak ada',
                                            ]);
                                        @endphp
                                        
                                        <div class="row text-center mt-4">
                                            <div class="col-6">
                                                <div class="border-end">
                                                    <div class="text-muted small">ID Karyawan</div>
                                                    <div class="fw-bold">{{ $pegawaiId }}</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-muted small">Departemen/Posisi</div>
                                                <div class="fw-bold">{{ $departemen }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h5 class="card-title border-bottom pb-2 mb-3">
                                            <i class="fas fa-calendar-check me-2"></i>Waktu Check In
                                        </h5>
                                        <div class="text-center">
                                            <div id="currentTime" class="display-4 fw-bold text-success mb-2"></div>
                                            <div id="currentDate" class="fs-5 text-muted"></div>
                                            <div class="mt-3 badge bg-light text-dark p-2">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Waktu absensi secara realtime
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Lokasi dengan Feedback Visual -->
                        <div class="mb-4">
                            <div id="locationStatus" class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <div>
                                        <strong>Persiapan Check-in</strong><br>
                                        Pilih status kehadiran Anda di bawah ini
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Info -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-clipboard-check me-2"></i>Status Kehadiran
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="status" class="form-label fw-bold">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                        <option value="Hadir" selected>Hadir</option>
                                        <option value="Sakit">Sakit</option>
                                        <option value="Izin">Izin</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-map-marker-alt me-2"></i>Informasi Lokasi
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="alamat_absen" class="form-label fw-bold">Alamat Absensi</label>
                                            <textarea class="form-control @error('alamat_absen') is-invalid @enderror" 
                                                    id="alamat_absen" 
                                                    name="alamat_absen" 
                                                    rows="3" 
                                                    readonly
                                                    required>{{ old('alamat_absen') }}</textarea>
                                            @error('alamat_absen')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="keterangan" class="form-label fw-bold">Keterangan (Opsional)</label>
                                            <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                                    id="keterangan" 
                                                    name="keterangan" 
                                                    rows="3" 
                                                    placeholder="Tambahan keterangan...">{{ old('keterangan') }}</textarea>
                                            @error('keterangan')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <!-- Hidden Location Fields -->
                        <input type="hidden" id="latitude" name="latitude" required>
                        <input type="hidden" id="longitude" name="longitude" required>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" id="submitBtn" class="btn btn-success btn-lg px-5 py-3 rounded-pill shadow" disabled>
                                <i class="fas fa-clock me-2"></i> Check In Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.avatar-circle {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.card {
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.card-header {
    border-bottom: none;
    padding: 1.25rem;
}

.card-title {
    font-weight: 600;
    color: #555;
}

.shadow-lg {
    box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important;
}

.shadow-sm {
    box-shadow: 0 .125rem .25rem rgba(0,0,0,.075) !important;
}

.rounded-pill {
    border-radius: 50rem !important;
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border: none;
}

.btn-success:hover {
    background: linear-gradient(135deg, #218838 0%, #1e9c7c 100%);
    transform: translateY(-2px);
}

.btn-success:active {
    transform: translateY(0);
}

#currentTime {
    font-family: 'Roboto Mono', monospace;
    letter-spacing: 1px;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffeeba;
    color: #856404;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.form-label {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

/* Loading states */
.location-loading {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.btn-retry {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
}

/* Progress indicator */
.location-progress {
    height: 3px;
    background: linear-gradient(90deg, #28a745, #20c997);
    border-radius: 2px;
    margin-top: 0.5rem;
    animation: progress 2s ease-in-out;
}

@keyframes progress {
    0% { width: 0%; }
    50% { width: 70%; }
    100% { width: 100%; }
}

@media (max-width: 768px) {
    .card-body {
        padding: 1rem;
    }
    
    .btn-lg {
        padding: 0.5rem 1.5rem;
        font-size: 1rem;
    }
    
    /* Enhanced location feedback styles */
    .location-loading {
        animation: pulse 1.5s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }
    
    .alert {
        transition: all 0.3s ease;
        border: none;
        border-radius: 10px;
    }
    
    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border-left: 4px solid #28a745;
    }
    
    .alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border-left: 4px solid #ffc107;
    }
    
    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        border-left: 4px solid #17a2b8;
    }
    
    .progress {
        background-color: rgba(255,255,255,0.2);
        border-radius: 10px;
        overflow: hidden;
    }
    
    .progress-bar {
        background: linear-gradient(45deg, #007bff, #0056b3);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const submitBtn = document.getElementById('submitBtn');
    const locationSection = document.getElementById('locationStatus');
    
    // Variabel global untuk tracking lokasi
    let locationObtained = false;
    let locationAttempts = 0;
    const MAX_ATTEMPTS = 2;
    let lastKnownLocation = localStorage.getItem('lastKnownLocation');
    
    // Update current time
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        const dateString = now.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        document.getElementById('currentTime').textContent = timeString;
        document.getElementById('currentDate').textContent = dateString;
    }
    
    // Update time every second
    updateTime();
    setInterval(updateTime, 1000);
    
    // Preload lokasi jika ada data sebelumnya
    function preloadLastLocation() {
        if (lastKnownLocation && statusSelect.value === 'Hadir') {
            try {
                const cached = JSON.parse(lastKnownLocation);
                const cacheAge = Date.now() - cached.timestamp;
                
                // Gunakan cache jika masih fresh (< 30 menit)
                if (cacheAge < 30 * 60 * 1000) {
                    console.log('Menggunakan lokasi cache:', cached);
                    
                    locationSection.className = 'alert alert-info';
                    locationSection.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-history me-2 fs-4"></i>
                            <div>
                                <strong>Menggunakan Lokasi Sebelumnya</strong><br>
                                <small class="text-muted">Cache dari ${new Date(cached.timestamp).toLocaleTimeString('id-ID')}</small>
                            </div>
                        </div>`;
                    
                    // Set nilai dari cache
                    document.getElementById('latitude').value = cached.lat;
                    document.getElementById('longitude').value = cached.lon;
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-clock me-1"></i> Check In (Cache)';
                    
                    // Tetap coba update lokasi di background
                    setTimeout(() => {
                        getLocationOptimized(true); // Background update
                    }, 1000);
                    
                    return true;
                }
            } catch (e) {
                console.log('Error parsing cached location:', e);
                localStorage.removeItem('lastKnownLocation');
            }
        }
        return false;
    }
    
    // Handle status change
    statusSelect.addEventListener('change', function() {
        if (this.value === 'Sakit' || this.value === 'Izin') {
            // Untuk sakit/izin, tidak perlu lokasi
            submitBtn.disabled = false;
            locationSection.className = 'alert alert-info';
            locationSection.innerHTML = '<div class="d-flex align-items-center"><i class="fas fa-info-circle me-2 fs-4"></i><div><strong>Status: ' + this.value + '</strong><br>Lokasi tidak diperlukan untuk status ini.</div></div>';
            
            // Reset location values untuk status sakit/izin
            document.getElementById('latitude').value = '';
            document.getElementById('longitude').value = '';
        } else {
            // Untuk status hadir, perlu lokasi
            submitBtn.disabled = true;
            getLocationOptimized();
        }
    });
    
    // Fungsi optimized untuk mendapatkan lokasi dengan cepat
    function getLocationOptimized(isBackgroundUpdate = false) {
        if (!navigator.geolocation) {
            handleLocationError('Browser tidak mendukung geolocation');
            return;
        }

        // Cek cache terlebih dahulu
        if (!isBackgroundUpdate && checkLocationCache()) {
            console.log('✅ Menggunakan lokasi dari cache');
            return;
        }

        if (!isBackgroundUpdate) {
            locationSection.className = 'alert alert-info';
            locationSection.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                    <div>
                        <strong>Mendapatkan Lokasi...</strong><br>
                        <small class="text-muted">Mohon tunggu sebentar...</small>
                    </div>
                </div>`;
        }

        locationAttempts = 0;
        locationObtained = false;

        // Gunakan Promise.race untuk mendapatkan lokasi tercepat
        console.log('🚀 Memulai deteksi lokasi...');
        
        // 1. Coba GPS cepat dulu
        const quickGps = getCurrentPosition({
            enableHighAccuracy: false,
            timeout: 3000,
            maximumAge: 30000
        }).then(position => ({...position, source: 'quick_gps'}));

        // 2. Fallback ke GPS akurat jika yang cepat gagal
        const accurateGps = getCurrentPosition({
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        }).then(position => ({...position, source: 'accurate_gps'}));

        // Jalankan secara berurutan dengan timeout
        Promise.race([
            quickGps,
            new Promise((_, reject) => setTimeout(() => reject('timeout'), 3000))
        ])
        .then(position => {
            locationObtained = true;
            handleLocationSuccess(position, isBackgroundUpdate);
        })
        .catch(() => {
            // Jika GPS cepat gagal, coba GPS akurat
            if (!locationObtained) {
                accurateGps
                .then(position => {
                    locationObtained = true;
                    handleLocationSuccess(position, isBackgroundUpdate);
                })
                .catch(error => {
                    console.warn('GPS location failed:', error);
                    handleLocationError('Lokasi tidak dapat dideteksi', true);
                });
            }
        });
        
        // Fallback final setelah 5 detik
        setTimeout(() => {
            if (!locationObtained && !isBackgroundUpdate) {
                console.log('⏰ Location timeout - enabling manual mode');
                handleLocationError('Lokasi tidak dapat dideteksi secara akurat, Anda tetap bisa check-in', true);
                
                // Set default location jika diperlukan
                document.getElementById('latitude').value = {{ $office_latitude }};
                document.getElementById('longitude').value = {{ $office_longitude }};
            }
        }, 5000);
    }
                clearTimeout(finalTimer);
                locationObtained = true;
                console.log('Lokasi GPS berhasil didapat super cepat:', position);
                handleLocationSuccess(position, isBackgroundUpdate);
            },
            function(error) {
                console.log('GPS gagal, akan coba IP location:', error);
                // Biarkan fallback timer yang handle
            },
            ultraFastOptions
        );
    }
    
    // Fallback menggunakan IP-based location (sangat cepat)
    function tryIPLocationFallback() {
        if (locationObtained) return;
        
        console.log('Mencoba IP-based location...');
        
        // Gunakan service IP location yang cepat dengan timeout
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 1000);
        
        fetch('https://ipapi.co/json/', {
            method: 'GET',
            signal: controller.signal
        })
        .then(response => {
            clearTimeout(timeoutId);
            return response.json();
        })
        .then(data => {
            if (data.latitude && data.longitude) {
                locationObtained = true;
                console.log('IP location berhasil:', data);
                
                const position = {
                    coords: {
                        latitude: parseFloat(data.latitude),
                        longitude: parseFloat(data.longitude),
                        accuracy: 10000 // IP location kurang akurat
                    }
                };
                
                handleLocationSuccess(position, false);
            } else {
                console.log('IP location gagal, data tidak lengkap');
                tryBrowserLocationFallback();
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            console.log('IP location error:', error);
            tryBrowserLocationFallback();
        });
    }
    
    // Fallback terakhir dengan browser geolocation biasa
    function tryBrowserLocationFallback() {
        if (locationObtained) return;
        
        console.log('Mencoba browser location fallback...');
        
        const basicOptions = {
            enableHighAccuracy: true,
            timeout: 1000,
            maximumAge: 60000
        };
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                locationObtained = true;
                console.log('Browser fallback berhasil:', position);
                handleLocationSuccess(position);
            },
            function(error) {
                console.log('Semua metode lokasi gagal:', error);
                handleLocationError('Tidak dapat mengakses lokasi', true);
            },
            basicOptions
        );
    }
    
    // Handle error atau fallback dengan UX yang lebih baik
    function handleLocationError(message, isFallback = false) {
        if (isFallback) {
            // Fallback: izinkan check-in tanpa lokasi dengan feedback positif
            locationSection.className = 'alert alert-info';
            locationSection.innerHTML = `
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-2 fs-4 text-info"></i>
                        <div>
                            <strong>Proses Dilanjutkan</strong><br>
                            <small class="text-muted">${message}. Check-in tetap dapat dilakukan.</small>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" onclick="retryLocation()">
                        <i class="fas fa-redo"></i> Retry
                    </button>
                </div>`;
            // alamatAbsenField.value = 'Lokasi tidak tersedia - Check-in manual';
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-clock me-1"></i> Check In Manual';
        } else {
            locationSection.className = 'alert alert-warning';
            locationSection.innerHTML = `
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-triangle me-2 fs-4 text-warning"></i>
                        <div>
                            <strong>Lokasi Tidak Dapat Diakses</strong><br>
                            <small class="text-muted">${message}</small>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-primary" onclick="retryLocation()">
                        <i class="fas fa-redo"></i> Coba Lagi
                    </button>
                </div>`;
            // alamatAbsenField.value = 'Error mendapatkan lokasi';
            submitBtn.disabled = false; // Tetap izinkan check-in
            submitBtn.innerHTML = '<i class="fas fa-clock me-1"></i> Check In Tanpa Lokasi';
        }
    }
    
    // Fungsi untuk retry pengambilan lokasi
    window.retryLocation = function() {
        locationObtained = false;
        if (statusSelect.value === 'Hadir') {
            getLocationOptimized();
        }
    };
    
    // Inisialisasi: coba preload atau langsung ambil lokasi jika status default adalah "Hadir"
    if (statusSelect.value === 'Hadir') {
        // Coba gunakan cache dulu, jika tidak ada baru ambil lokasi fresh
        if (!preloadLastLocation()) {
            getLocationOptimized();
        }
    }
});

// Calculate distance between two coordinates
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // Earth's radius in meters
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// Fungsi untuk mengecek cache lokasi
function checkLocationCache() {
    if (lastKnownLocation) {
        try {
            const cached = JSON.parse(lastKnownLocation);
            const cacheAge = Date.now() - cached.timestamp;
            
            // Gunakan cache jika < 15 menit
            if (cacheAge < 15 * 60 * 1000) {
                const position = {
                    coords: {
                        latitude: cached.coords.latitude,
                        longitude: cached.coords.longitude,
                        accuracy: cached.coords.accuracy
                    },
                    source: 'cache',
                    timestamp: cached.timestamp
                };
                handleLocationSuccess(position, false);
                return true;
            }
        } catch (e) {
            console.warn('Error parsing cache:', e);
            localStorage.removeItem('lastKnownLocation');
        }
    }
    return false;
}

// Fungsi untuk mendapatkan lokasi dengan Promise
function getCurrentPosition(options) {
    return new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(resolve, reject, options);
    });
}

// STRATEGI MULTIPLE PARALLEL APPROACH
</script>
@endsection
