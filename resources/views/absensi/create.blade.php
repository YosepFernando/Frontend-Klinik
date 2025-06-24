@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-clock"></i> Check In - Absensi Karyawan
                    </h4>
                </div>
                
                <div class="card-body">
                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('absensi.store') }}" method="POST" id="absensiForm">
                        @csrf
                        
                        <!-- User Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="text-muted">Informasi Karyawan</h5>
                                <p><strong>Nama:</strong> {{ auth()->user()->name }}</p>
                                <p><strong>Role:</strong> {{ ucfirst(auth()->user()->role) }}</p>
                                @if(auth()->user()->pegawai)
                                    <p><strong>Posisi:</strong> {{ auth()->user()->pegawai->posisi->nama_posisi ?? 'Belum ditentukan' }}</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-muted">Waktu Check In</h5>
                                <p id="currentTime" class="h4 text-primary"></p>
                                <p id="currentDate" class="text-muted"></p>
                            </div>
                        </div>

                        <!-- Location Info -->
                        <div class="mb-4">
                            <h5 class="text-muted">Informasi Lokasi</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="alamat_absen" class="form-label">Alamat Absensi</label>
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
                                        <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
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

                        <!-- Hidden Location Fields -->
                        <input type="hidden" id="latitude" name="latitude" required>
                        <input type="hidden" id="longitude" name="longitude" required>

                        <!-- Location Status -->
                        <div class="mb-4">
                            <div id="locationStatus" class="alert alert-info">
                                <i class="fas fa-spinner fa-spin"></i> Mengambil lokasi Anda...
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" id="submitBtn" class="btn btn-success btn-lg" disabled>
                                <i class="fas fa-clock"></i> Check In Sekarang
                            </button>
                            <a href="{{ route('absensi.index') }}" class="btn btn-secondary btn-lg ms-2">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.location-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    padding: 20px;
    color: white;
    margin-bottom: 20px;
}

.time-display {
    font-family: 'Courier New', monospace;
    font-weight: bold;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
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
    
    // Get user location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lon;
                
                // Check if within office radius
                const officeDistance = calculateDistance(lat, lon, {{ App\Http\Controllers\AbsensiController::OFFICE_LATITUDE }}, {{ App\Http\Controllers\AbsensiController::OFFICE_LONGITUDE }});
                
                if (officeDistance <= {{ App\Http\Controllers\AbsensiController::OFFICE_RADIUS }}) {
                    document.getElementById('locationStatus').className = 'alert alert-success';
                    document.getElementById('locationStatus').innerHTML = '<i class="fas fa-check-circle"></i> Anda berada dalam radius kantor (' + Math.round(officeDistance) + 'm dari kantor)';
                    document.getElementById('submitBtn').disabled = false;
                } else {
                    document.getElementById('locationStatus').className = 'alert alert-warning';
                    document.getElementById('locationStatus').innerHTML = '<i class="fas fa-exclamation-triangle"></i> Anda berada di luar radius kantor (' + Math.round(officeDistance) + 'm dari kantor). Hubungi HRD untuk izin absen dari luar kantor.';
                }
                
                // Get address from coordinates
                fetch(`https://api.opencagedata.com/geocode/v1/json?q=${lat}+${lon}&key=YOUR_API_KEY`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.results && data.results.length > 0) {
                            document.getElementById('alamat_absen').value = data.results[0].formatted;
                        } else {
                            document.getElementById('alamat_absen').value = `Koordinat: ${lat}, ${lon}`;
                        }
                    })
                    .catch(error => {
                        console.error('Error getting address:', error);
                        document.getElementById('alamat_absen').value = `Koordinat: ${lat}, ${lon}`;
                    });
            },
            function(error) {
                document.getElementById('locationStatus').className = 'alert alert-danger';
                document.getElementById('locationStatus').innerHTML = '<i class="fas fa-times-circle"></i> Error mengambil lokasi: ' + error.message;
                console.error('Error getting location:', error);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        document.getElementById('locationStatus').className = 'alert alert-danger';
        document.getElementById('locationStatus').innerHTML = '<i class="fas fa-times-circle"></i> Browser Anda tidak mendukung geolocation.';
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
</script>
@endsection
