@extends('layouts.app')

@section('content')
<div class="contain                                 @endif

                    <!-- Filters -->
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHRD()))
                        <div class="row mb-3">
                            <div class="col-md-12">` @endif

                    <!-- Filt                                        <div class="card-body">
                                                              <a href="{{ route('attendances.show', $attendance) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHRD()))
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('attendances.edit', $attendance) }}" class="btn btn-outline-warning btn-sm">`                        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHRD()))
                                                <!-- Employee Info -->
                                                <div class="d-flex align-items-center mb-3">`-->
                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHRD()))
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <form method="GET" action="{{ route('attendances.index') }}" class="row g-3">`  <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Daftar Absensi</h4>
                    <div class="d-flex gap-2">
                        @php
                            $user = auth()->user();
                            $today = \Carbon\Carbon::today();
                            $todayAttendance = \App\Models\Attendance::where('user_id', $user->id)
                                                                   ->whereDate('date', $today)
                                                                   ->first();
                        @endphp
                        
                        @if(!$todayAttendance)
                            <div class="dropdown">
                                <button class="btn btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-exclamation-triangle"></i> Lapor Ketidakhadiran
                                </button>
                                <div class="dropdown-menu">
                                    <form action="{{ route('attendances.submit-absence') }}" method="POST" class="px-3 py-2">
                                        @csrf
                                        <div class="mb-2">
                                            <select name="status" class="form-select form-select-sm" required>
                                                <option value="">Pilih Status</option>
                                                <option value="sick">Sakit</option>
                                                <option value="permission">Izin</option>
                                            </select>
                                        </div>
                                        <div class="mb-2">
                                            <textarea name="notes" class="form-control form-control-sm" placeholder="Alasan..." rows="2" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-sm btn-warning w-100">Kirim Laporan</button>
                                    </form>
                                </div>
                            </div>
                            <a href="{{ route('attendances.create') }}" class="btn btn-success">
                                <i class="fas fa-clock"></i> Check In
                            </a>
                        @elseif($todayAttendance && $todayAttendance->canCheckOut())
                            <button type="button" class="btn btn-danger" onclick="showCheckOutModal()">
                                <i class="fas fa-sign-out-alt"></i> Check Out
                            </button>
                        @endif
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

                    <!-- Filters -->
                    @if(auth()->user()->isAdmin() || auth()->user()->isHRD())
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <form method="GET" action="{{ route('attendances.index') }}" class="row g-3">
                                    <div class="col-md-3">
                                        <select name="user_id" class="form-select">
                                            <option value="">Semua Karyawan</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ ucfirst($user->role) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="fas fa-filter"></i> Filter
                                        </button>
                                        <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-undo"></i> Reset
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($attendances->count() > 0)
                        <!-- Attendance Cards Grid -->
                        <div class="row">
                            @foreach($attendances as $attendance)
                                <div class="col-lg-6 col-xl-4 mb-4">
                                    <div class="card h-100 shadow-sm attendance-card">
                                        <!-- Card Header with Date and Status -->
                                        <div class="card-header d-flex justify-content-between align-items-center
                                            {{ $attendance->status === 'present' ? 'bg-success text-white' : 
                                               ($attendance->status === 'late' ? 'bg-warning text-dark' :
                                               ($attendance->status === 'sick' ? 'bg-info text-white' :
                                               ($attendance->status === 'permission' ? 'bg-secondary text-white' : 'bg-danger text-white'))) }}">
                                            <div>
                                                <h6 class="mb-0">{{ $attendance->date->format('d M Y') }}</h6>
                                                <small class="opacity-75">{{ $attendance->date->format('l') }}</small>
                                            </div>
                                            <span class="badge bg-light text-dark">{{ $attendance->status_display }}</span>
                                        </div>

                                        <div class="card-body">
                                            @if(auth()->user()->isAdmin() || auth()->user()->isHRD())
                                                <!-- Employee Info -->
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar-circle me-3">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $attendance->user->name }}</strong>
                                                        <small class="text-muted d-block">{{ ucfirst($attendance->user->role) }}</small>
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Time Info -->
                                            <div class="row text-center mb-3">
                                                <div class="col-6">
                                                    <div class="border-end">
                                                        <div class="text-muted small">Check In</div>
                                                        <div class="fw-bold {{ $attendance->isLate() ? 'text-warning' : 'text-success' }}">
                                                            @if($attendance->check_in)
                                                                {{ $attendance->check_in->format('H:i') }}
                                                                @if($attendance->isLate())
                                                                    <small class="d-block">
                                                                        <i class="fas fa-clock"></i> Terlambat
                                                                    </small>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-muted small">Check Out</div>
                                                    <div class="fw-bold text-danger">
                                                        @if($attendance->check_out)
                                                            {{ $attendance->check_out->format('H:i') }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Work Duration -->
                                            @if($attendance->work_duration !== '-')
                                                <div class="text-center mb-3">
                                                    <div class="badge bg-primary fs-6">
                                                        <i class="fas fa-clock"></i> {{ $attendance->work_duration }}
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Notes -->
                                            @if($attendance->notes)
                                                <div class="mb-3">
                                                    <small class="text-muted">Catatan:</small>
                                                    <p class="small mb-0 p-2 bg-light rounded">{{ Str::limit($attendance->notes, 60) }}</p>
                                                </div>
                                            @endif

                                            <!-- Today's Attendance Special Actions -->
                                            @if($attendance->date->isToday() && $attendance->user_id === auth()->id())
                                                @if($attendance->canCheckOut())
                                                    <div class="alert alert-warning p-2 text-center">
                                                        <small>Anda sedang bekerja</small>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="card-footer bg-transparent">
                                            <div class="d-grid gap-2">
                                                <a href="{{ route('attendances.show', $attendance) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                @if(auth()->user()->isAdmin() || auth()->user()->isHRD())
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('attendances.edit', $attendance) }}" class="btn btn-outline-warning btn-sm">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <form action="{{ route('attendances.destroy', $attendance) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data absensi ini?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if(is_object($attendances) && method_exists($attendances, 'appends'))
                            <div class="d-flex justify-content-center">
                                {{ $attendances->appends(request()->query())->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-user-clock fa-3x text-muted mb-3"></i>
                            <h5>Belum ada data absensi</h5>
                            <p class="text-muted">Mulai dengan melakukan check-in untuk hari ini.</p>
                            @if(!$todayAttendance)
                                <a href="{{ route('attendances.create') }}" class="btn btn-success">
                                    <i class="fas fa-clock"></i> Check In Sekarang
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.attendance-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.attendance-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(45deg, #007bff, #0056b3);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}
</style>

<!-- Check Out Modal -->
<div class="modal fade" id="checkOutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Check Out</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="checkout-location-status" class="alert alert-info">
                    <i class="fas fa-spinner fa-spin"></i> Mengambil lokasi Anda...
                </div>
                
                <form id="checkout-form" action="{{ route('attendances.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" id="checkout-latitude" name="latitude">
                    <input type="hidden" id="checkout-longitude" name="longitude">
                    <input type="hidden" id="checkout-address" name="address">
                    
                    <p class="text-muted">Pastikan Anda berada di area kantor untuk check-out.</p>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirm-checkout-btn" disabled onclick="confirmCheckOut()">
                    <i class="fas fa-sign-out-alt"></i> Check Out
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showCheckOutModal() {
    const modal = new bootstrap.Modal(document.getElementById('checkOutModal'));
    modal.show();
    getCheckOutLocation();
}

function getCheckOutLocation() {
    const statusElement = document.getElementById('checkout-location-status');
    const confirmBtn = document.getElementById('confirm-checkout-btn');
    
    if (!navigator.geolocation) {
        statusElement.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Geolocation tidak didukung oleh browser ini.';
        statusElement.className = 'alert alert-danger';
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function(position) {
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;
            
            // Set hidden form fields
            document.getElementById('checkout-latitude').value = latitude;
            document.getElementById('checkout-longitude').value = longitude;
            
            // Get address from coordinates
            const address = `Lokasi: ${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
            document.getElementById('checkout-address').value = address;
            
            // Check if user is within office radius
            checkCheckOutLocationRadius(latitude, longitude);
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

function checkCheckOutLocationRadius(lat, lng) {
    // Office coordinates (Jakarta - sesuaikan dengan koordinat kantor sebenarnya)
    const officeLat = -8.781952;
    const officeLng = 115.179793;
    const allowedRadius = 100; // meters
    
    const distance = calculateCheckOutDistance(lat, lng, officeLat, officeLng);
    const statusElement = document.getElementById('checkout-location-status');
    const confirmBtn = document.getElementById('confirm-checkout-btn');
    
    if (distance <= allowedRadius) {
        statusElement.innerHTML = '<i class="fas fa-check-circle"></i> Anda berada di area kantor. Silakan check-out.';
        statusElement.className = 'alert alert-success';
        confirmBtn.disabled = false;
    } else {
        statusElement.innerHTML = '<i class="fas fa-map-marker-alt"></i> Anda berada ' + Math.round(distance) + ' meter dari kantor. Anda harus berada dalam radius ' + allowedRadius + ' meter untuk check-out.';
        statusElement.className = 'alert alert-warning';
        confirmBtn.disabled = true;
    }
}

function calculateCheckOutDistance(lat1, lon1, lat2, lon2) {
    const R = 6371000; // Earth radius in meters
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function confirmCheckOut() {
    if (confirm('Yakin ingin check out?')) {
        document.getElementById('checkout-form').submit();
    }
}
</script>
@endsection
