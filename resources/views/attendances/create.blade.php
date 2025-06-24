@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">Check In</h4>
                    <p class="text-muted mb-0">{{ now()->format('l, d F Y') }}</p>
                </div>

                <div class="card-body text-center">
                    <div class="mb-4">
                        <div id="current-time" class="display-4 text-primary mb-2"></div>
                        <p class="text-muted">Waktu saat ini</p>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-center">
                            <div class="text-center mx-3">
                                <div class="text-success">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                                <small class="text-muted">Jam Masuk Normal</small>
                                <div class="fw-bold">08:00</div>
                            </div>
                            <div class="text-center mx-3">
                                <div class="text-danger">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                                <small class="text-muted">Terlambat Setelah</small>
                                <div class="fw-bold">08:01</div>
                            </div>
                        </div>
                    </div>

                    <!-- Location Status -->
                    <div class="mb-4">
                        <div id="location-status" class="alert alert-info">
                            <i class="fas fa-spinner fa-spin"></i> Mengambil lokasi Anda...
                        </div>
                    </div>

                    <form method="POST" action="{{ route('attendances.store') }}" id="attendance-form">
                        @csrf
                        
                        <!-- Hidden fields for location -->
                        <input type="hidden" id="latitude" name="latitude">
                        <input type="hidden" id="longitude" name="longitude">
                        <input type="hidden" id="address" name="address">
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" 
                                      name="notes" 
                                      rows="3" 
                                      placeholder="Tambahkan catatan jika diperlukan...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('latitude')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @error('longitude')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @error('address')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            @php
                                $currentTime = now();
                                $workStartTime = \Carbon\Carbon::createFromTime(8, 0, 0);
                                $isLate = $currentTime->format('H:i') > $workStartTime->format('H:i');
                            @endphp
                            
                            @if($isLate)
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Perhatian:</strong> Anda akan tercatat terlambat karena check-in setelah jam 08:00.
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Tepat Waktu:</strong> Anda check-in pada waktu yang tepat.
                                </div>
                            @endif
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn {{ $isLate ? 'btn-warning' : 'btn-success' }} btn-lg" id="check-in-btn" disabled>
                                <i class="fas fa-clock"></i> 
                                Check In Sekarang
                            </button>
                            <a href="{{ route('attendances.index') }}" class="btn btn-secondary">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Quick Absence Report -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Tidak bisa hadir hari ini?</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('attendances.submit-absence') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <select name="status" class="form-select" required>
                                    <option value="">Pilih Alasan</option>
                                    <option value="sick">Sakit</option>
                                    <option value="permission">Izin</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button type="submit" class="btn btn-outline-warning w-100">
                                    <i class="fas fa-paper-plane"></i> Kirim Laporan
                                </button>
                            </div>
                        </div>
                        <div class="mb-2">
                            <textarea name="notes" class="form-control" placeholder="Jelaskan alasan Anda..." rows="2" required></textarea>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    document.getElementById('current-time').textContent = timeString;
}

// Update clock immediately and then every second
updateClock();
setInterval(updateClock, 1000);

// Geolocation functionality
let userLocation = null;

function getLocation() {
    const statusElement = document.getElementById('location-status');
    const checkInBtn = document.getElementById('check-in-btn');
    
    if (!navigator.geolocation) {
        statusElement.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Geolocation tidak didukung oleh browser ini.';
        statusElement.className = 'alert alert-danger';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            
            userLocation = { latitude, longitude };
            
            // Set hidden form fields
            document.getElementById('latitude').value = latitude;
            document.getElementById('longitude').value = longitude;
            
            // Get address from coordinates
            getAddressFromCoordinates(latitude, longitude);
            
            // Check if user is within office radius
            checkLocationRadius(latitude, longitude);
        },
        function(error) {
            let errorMessage = '';
            switch(error.code) {
                case error.PERMISSION_DENIED:
                    errorMessage = 'Izin lokasi ditolak. Silakan aktifkan lokasi di browser Anda.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    errorMessage = 'Informasi lokasi tidak tersedia.';
                    break;
                case error.TIMEOUT:
                    errorMessage = 'Permintaan lokasi timeout.';
                    break;
                default:
                    errorMessage = 'Terjadi kesalahan saat mengambil lokasi.';
                    break;
            }
            statusElement.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + errorMessage;
            statusElement.className = 'alert alert-danger';
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 300000 // 5 minutes
        }
    );
}

function getAddressFromCoordinates(lat, lng) {
    // Simple address format - in production, you might want to use a reverse geocoding service
    const address = `Lokasi: ${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    document.getElementById('address').value = address;
}

function checkLocationRadius(lat, lng) {
    // Office coordinates (Jakarta - sesuaikan dengan koordinat kantor sebenarnya)
    const officeLat = -8.781952;
    const officeLng = 115.179793;
    const allowedRadius = 100; // meters
    
    const distance = calculateDistance(lat, lng, officeLat, officeLng);
    const statusElement = document.getElementById('location-status');
    const checkInBtn = document.getElementById('check-in-btn');
    
    if (distance <= allowedRadius) {
        statusElement.innerHTML = '<i class="fas fa-check-circle"></i> Anda berada di area kantor. Silakan check-in.';
        statusElement.className = 'alert alert-success';
        checkInBtn.disabled = false;
    } else {
        statusElement.innerHTML = '<i class="fas fa-map-marker-alt"></i> Anda berada ' + Math.round(distance) + ' meter dari kantor. Anda harus berada dalam radius ' + allowedRadius + ' meter untuk check-in.';
        statusElement.className = 'alert alert-warning';
        checkInBtn.disabled = true;
    }
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // Earth radius in meters
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

// Get location when page loads
document.addEventListener('DOMContentLoaded', function() {
    getLocation();
});
</script>
@endsection
