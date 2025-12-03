@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-gradient-danger text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-door-open me-2"></i>Check Out - Absensi Karyawan
                            </h4>
                            <small class="opacity-75">Silahkan melakukan check-out untuk mencatat waktu pulang Anda</small>
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

                    <form action="{{ route('absensi.checkout') }}" method="POST" id="checkoutForm">
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
                                                    
                                                    if (is_array($pegawaiData)) {
                                                        if (!empty($pegawaiData['nama_lengkap'])) {
                                                            $userName = $pegawaiData['nama_lengkap'];
                                                        }
                                                        if (isset($pegawaiData['user']) && is_array($pegawaiData['user'])) {
                                                            if (!empty($pegawaiData['user']['role'])) {
                                                                $userRole = $pegawaiData['user']['role'];
                                                            }
                                                        }
                                                    }
                                                    
                                                    if (empty($userName) || $userName === 'Pengguna') {
                                                        $userName = session('user_name', $userName);
                                                    }
                                                    if (empty($userRole) || $userRole === 'pegawai') {
                                                        $userRole = session('user_role', $userRole);
                                                    }
                                                @endphp
                                                <h6 class="mb-0">{{ $userName }}</h6>
                                                <small class="text-muted">{{ ucfirst($userRole) }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h5 class="card-title border-bottom pb-2 mb-3">
                                            <i class="fas fa-calendar-check me-2"></i>Waktu Check Out
                                        </h5>
                                        <div class="time-display text-center">
                                            <div class="current-time mb-2">
                                                <i class="fas fa-clock text-primary me-2"></i>
                                                <span id="currentTime" class="fs-3 fw-bold text-primary">--:--:--</span>
                                            </div>
                                            <div class="current-date">
                                                <i class="fas fa-calendar-alt text-muted me-2"></i>
                                                <span id="currentDate" class="text-muted">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location Status Section -->
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i>Status Lokasi
                            </h5>
                            <div id="locationSection" class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <div class="spinner-border spinner-border-sm me-3" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <div>
                                        <strong>Memverifikasi lokasi...</strong><br>
                                        <small class="text-muted">Mohon tunggu sebentar</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Optional Notes Section -->
                        <div class="mb-4">
                            <h5 class="mb-3">
                                <i class="fas fa-comment-dots me-2"></i>Keterangan (Opsional)
                            </h5>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="keterangan_keluar" class="form-label">Catatan Check Out</label>
                                        <textarea 
                                            class="form-control" 
                                            id="keterangan_keluar" 
                                            name="keterangan_keluar" 
                                            rows="3"
                                            placeholder="Contoh: Pulang lebih awal karena ada keperluan keluarga (opsional)"></textarea>
                                        <small class="text-muted">Kosongkan jika tidak ada catatan khusus</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden Fields -->
                        <input type="hidden" id="latitude" name="latitude" required>
                        <input type="hidden" id="longitude" name="longitude" required>
                        <input type="hidden" id="alamat_checkout" name="alamat_checkout">

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button 
                                type="submit" 
                                class="btn btn-danger btn-lg" 
                                id="submitBtn"
                                disabled>
                                <i class="fas fa-spinner fa-spin me-2"></i> Mengecek Lokasi...
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
    }

    .time-display {
        padding: 1rem;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 10px;
    }

    .current-time {
        font-family: 'Courier New', monospace;
    }

    .bg-gradient-danger {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .spinner-border {
        animation: pulse 1.5s ease-in-out infinite;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('submitBtn');
    const locationSection = document.getElementById('locationSection');
    
    // Variabel global untuk tracking lokasi
    let locationObtained = false;
    
    // Koordinat kantor dari backend (single source of truth)
    const OFFICE_LATITUDE = {{ $office_latitude ?? -8.796393374723333 }};
    const OFFICE_LONGITUDE = {{ $office_longitude ?? 115.17651823599097 }};
    const MAX_DISTANCE = {{ $office_radius ?? 100 }}; // Radius dalam meter

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
    
    // Fungsi untuk mendapatkan lokasi dengan GPS
    function getLocationWithTimeout() {
        console.log('🚀 Getting location for checkout...');
        
        updateLocationStatus('loading', 'Mendapatkan lokasi Anda...');
        
        // Set timeout
        const locationTimeout = setTimeout(() => {
            if (!locationObtained) {
                console.warn('⏰ Location timeout reached');
                handleLocationTimeout();
            }
        }, 8000); // 8 detik timeout
        
        const options = {
            enableHighAccuracy: true,
            timeout: 6000,
            maximumAge: 0
        };
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                clearTimeout(locationTimeout);
                if (!locationObtained) {
                    locationObtained = true;
                    console.log('✅ Location obtained:', position);
                    handleLocationSuccess(position);
                }
            },
            function(error) {
                clearTimeout(locationTimeout);
                if (!locationObtained) {
                    console.warn('❌ Location error:', error);
                    handleLocationError(error);
                }
            },
            options
        );
    }
    
    // Handle lokasi berhasil
    function handleLocationSuccess(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy;
        
        console.log(`📍 Your Location: ${lat}, ${lng} (accuracy: ${accuracy}m)`);
        console.log(`🏢 Office Location: ${OFFICE_LATITUDE}, ${OFFICE_LONGITUDE}`);
        
        // Set koordinat ke form
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        document.getElementById('alamat_checkout').value = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
        
        // Hitung jarak ke kantor
        const distance = calculateDistance(lat, lng, OFFICE_LATITUDE, OFFICE_LONGITUDE);
        console.log(`📏 Distance to office: ${distance.toFixed(2)}m`);
        
        // Check apakah dalam radius kantor
        if (distance <= MAX_DISTANCE) {
            updateLocationStatus('success', 
                `Lokasi terverifikasi dalam radius kantor (${distance.toFixed(0)}m dari kantor)`
            );
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-door-open me-2"></i> Check Out Sekarang';
        } else {
            updateLocationStatus('error', 
                `Anda berada ${distance.toFixed(0)}m dari kantor. Di luar radius ${MAX_DISTANCE}m yang diizinkan.`
            );
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-times me-2"></i> Di Luar Radius Kantor';
        }
    }
    
    // Handle error lokasi
    function handleLocationError(error) {
        console.error('Location error:', error);
        
        let message = 'Tidak dapat mengakses lokasi';
        
        switch(error.code) {
            case error.PERMISSION_DENIED:
                message = 'Akses lokasi ditolak. Aktifkan izin lokasi di browser Anda.';
                break;
            case error.POSITION_UNAVAILABLE:
                message = 'Informasi lokasi tidak tersedia.';
                break;
            case error.TIMEOUT:
                message = 'Request lokasi timeout.';
                break;
        }
        
        updateLocationStatus('error', message);
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-map-marker-alt me-2"></i> Lokasi Diperlukan';
    }
    
    // Handle timeout
    function handleLocationTimeout() {
        locationObtained = true;
        updateLocationStatus('error', 
            'Tidak dapat mendeteksi lokasi Anda. Pastikan GPS aktif dan izinkan akses lokasi.'
        );
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-map-marker-alt me-2"></i> Lokasi Diperlukan';
    }
    
    // Update status lokasi di UI
    function updateLocationStatus(type, message) {
        const iconMap = {
            loading: '<div class="spinner-border spinner-border-sm me-3" role="status"><span class="visually-hidden">Loading...</span></div>',
            success: '<i class="fas fa-check-circle me-2 fs-4 text-success"></i>',
            warning: '<i class="fas fa-exclamation-triangle me-2 fs-4 text-warning"></i>',
            error: '<i class="fas fa-times-circle me-2 fs-4 text-danger"></i>',
            info: '<i class="fas fa-info-circle me-2 fs-4 text-info"></i>'
        };
        
        const alertClass = {
            loading: 'alert-info',
            success: 'alert-success',
            warning: 'alert-warning',
            error: 'alert-danger',
            info: 'alert-info'
        };
        
        locationSection.className = `alert ${alertClass[type]}`;
        locationSection.innerHTML = `
            <div class="d-flex align-items-center">
                ${iconMap[type]}
                <div>
                    <strong>${message}</strong>
                </div>
            </div>`;
    }
    
    // Calculate distance between two coordinates using Haversine formula
    function calculateDistance(lat1, lon1, lat2, lon2) {
        lat1 = parseFloat(lat1);
        lon1 = parseFloat(lon1);
        lat2 = parseFloat(lat2);
        lon2 = parseFloat(lon2);
        
        if (isNaN(lat1) || isNaN(lon1) || isNaN(lat2) || isNaN(lon2)) {
            console.error('Invalid coordinates:', { lat1, lon1, lat2, lon2 });
            return 999999;
        }
        
        const R = 6371000; // Earth's radius in meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
        
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        const distance = R * c;
        
        return distance;
    }
    
    // Inisialisasi: langsung gunakan GPS sungguhan
    getLocationWithTimeout();
});
</script>
@endsection
