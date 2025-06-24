@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-user-clock me-2"></i>Sistem Absensi Karyawan
                            </h4>
                            <small class="opacity-75">Management absensi dan kehadiran karyawan</small>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @php
                                $user = auth()->user();
                                $today = \Carbon\Carbon::today();
                                $pegawai = $user->pegawai;
                                $todayAbsensi = null;
                                if ($pegawai) {
                                    $todayAbsensi = \App\Models\Absensi::where('id_pegawai', $pegawai->id_pegawai)
                                                                       ->whereDate('tanggal', $today)
                                                                       ->first();
                                }
                            @endphp
                            
                            @if(!$todayAbsensi && $pegawai)
                                <div class="dropdown">
                                    <button class="btn btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-exclamation-triangle"></i> Lapor Ketidakhadiran
                                    </button>
                                    <div class="dropdown-menu">
                                        <form action="{{ route('absensi.submit-absence') }}" method="POST" class="px-3 py-2">
                                            @csrf
                                            <div class="mb-2">
                                                <select name="status" class="form-select form-select-sm" required>
                                                    <option value="">Pilih Status</option>
                                                    <option value="sakit">Sakit</option>
                                                    <option value="izin">Izin</option>
                                                </select>
                                            </div>
                                            <div class="mb-2">
                                                <textarea name="keterangan" class="form-control form-control-sm" placeholder="Alasan..." rows="2" required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-warning w-100">Kirim Laporan</button>
                                        </form>
                                    </div>
                                </div>
                                <a href="{{ route('absensi.create') }}" class="btn btn-success">
                                    <i class="fas fa-clock"></i> Check In
                                </a>
                            @elseif($todayAbsensi && !$todayAbsensi->jam_keluar && $todayAbsensi->jam_masuk)
                                <button type="button" class="btn btn-danger" onclick="showCheckOutModal()">
                                    <i class="fas fa-sign-out-alt"></i> Check Out
                                </button>
                            @endif
                            
                            @if(auth()->user()->isAdmin() || auth()->user()->isHRD())
                                <a href="{{ route('pegawai.index') }}" class="btn btn-info">
                                    <i class="fas fa-users"></i> Kelola Pegawai
                                </a>
                                @if(auth()->user()->isAdmin())
                                <a href="{{ route('absensi.dashboard') }}" class="btn btn-secondary">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                                @endif
                                <a href="{{ route('absensi.admin-create') }}" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Tambah Absensi
                                </a>
                                <a href="{{ route('absensi.report') }}" class="btn btn-primary">
                                    <i class="fas fa-chart-bar"></i> Laporan
                                </a>
                            @endif
                        </div>
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
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fas fa-filter me-2"></i>Filter & Pencarian Data
                                </h6>
                            </div>
                            <div class="card-body">
                                <form method="GET" action="{{ route('absensi.index') }}" class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label small">Karyawan</label>
                                        <select name="user_id" class="form-select">
                                            <option value="">Semua Karyawan</option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ ucfirst($user->role) }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="">Semua Status</option>
                                            <option value="Hadir" {{ request('status') == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                            <option value="Terlambat" {{ request('status') == 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                                            <option value="Sakit" {{ request('status') == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                            <option value="Izin" {{ request('status') == 'Izin' ? 'selected' : '' }}>Izin</option>
                                            <option value="Tidak Hadir" {{ request('status') == 'Tidak Hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Tanggal</label>
                                        <input type="date" name="tanggal" class="form-control" value="{{ request('tanggal') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Bulan</label>
                                        <select name="bulan" class="form-select">
                                            <option value="">Bulan</option>
                                            @for($i = 1; $i <= 12; $i++)
                                                <option value="{{ $i }}" {{ request('bulan') == $i ? 'selected' : '' }}>
                                                    {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small">Tahun</label>
                                        <select name="tahun" class="form-select">
                                            <option value="">Tahun</option>
                                            @for($i = date('Y'); $i >= date('Y') - 5; $i--)
                                                <option value="{{ $i }}" {{ request('tahun') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <div class="btn-group w-100">
                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <a href="{{ route('absensi.index') }}" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-undo"></i>
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($absensi->count() > 0)
                        <!-- Summary Stats for Admin/HRD -->
                        @if(auth()->user()->isAdmin() || auth()->user()->isHRD())
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-title">Hadir</h6>
                                                    <h4 class="mb-0">{{ $absensi->where('status', 'Hadir')->count() }}</h4>
                                                </div>
                                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-title">Terlambat</h6>
                                                    <h4 class="mb-0">{{ $absensi->where('status', 'Terlambat')->count() }}</h4>
                                                </div>
                                                <i class="fas fa-clock fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-title">Izin/Sakit</h6>
                                                    <h4 class="mb-0">{{ $absensi->whereIn('status', ['Sakit', 'Izin'])->count() }}</h4>
                                                </div>
                                                <i class="fas fa-user-md fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h6 class="card-title">Tidak Hadir</h6>
                                                    <h4 class="mb-0">{{ $absensi->where('status', 'Tidak Hadir')->count() }}</h4>
                                                </div>
                                                <i class="fas fa-times-circle fa-2x opacity-75"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Absensi Cards Grid -->
                        <div class="row">
                            @foreach($absensi as $item)
                                <div class="col-lg-6 col-xl-4 mb-4">
                                    <div class="card h-100 shadow-sm absensi-card">
                                        <!-- Card Header with Date and Status -->
                                        <div class="card-header d-flex justify-content-between align-items-center
                                            {{ $item->status === 'Hadir' ? 'bg-success text-white' : 
                                               ($item->status === 'Terlambat' ? 'bg-warning text-dark' :
                                               ($item->status === 'Sakit' ? 'bg-info text-white' :
                                               ($item->status === 'Izin' ? 'bg-secondary text-white' :
                                               ($item->status === 'Tidak Hadir' ? 'bg-danger text-white' : 'bg-light text-dark')))) }}">
                                            <div>
                                                <h6 class="mb-0">{{ $item->tanggal->format('d M Y') }}</h6>
                                                <small class="opacity-75">{{ $item->tanggal->format('l') }}</small>
                                            </div>
                                            <span class="badge bg-white text-dark">{{ $item->status }}</span>
                                        </div>

                                        <div class="card-body">
                                            @if(auth()->user()->isAdmin() || auth()->user()->isHRD())
                                                <!-- Employee Info -->
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar-circle me-3">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $item->pegawai->user->name }}</strong>
                                                        <small class="text-muted d-block">{{ ucfirst($item->pegawai->user->role) }}</small>
                                                        @if($item->pegawai && $item->pegawai->posisi)
                                                            <small class="text-muted d-block">{{ $item->pegawai->posisi->nama_posisi }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif

                                            <!-- Time Info -->
                                            <div class="row text-center mb-3">
                                                <div class="col-6">
                                                    <div class="border-end">
                                                        <div class="text-muted small">Masuk</div>
                                                        <div class="fw-bold {{ $item->status === 'Terlambat' ? 'text-warning' : 'text-success' }}">
                                                            @if($item->jam_masuk)
                                                                {{ $item->jam_masuk->format('H:i') }}
                                                                @if($item->status === 'Terlambat')
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
                                                    <div class="text-muted small">Keluar</div>
                                                    <div class="fw-bold text-danger">
                                                        @if($item->jam_keluar)
                                                            {{ $item->jam_keluar->format('H:i') }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Location Info -->
                                            @if($item->alamat_masuk)
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        {{ Str::limit($item->alamat_masuk, 50) }}
                                                    </small>
                                                </div>
                                            @endif

                                            <!-- Work Duration -->
                                            @if($item->jam_masuk && $item->jam_keluar)
                                                <div class="mb-2">
                                                    <small class="text-info">
                                                        <i class="fas fa-hourglass-half"></i>
                                                        Durasi: {{ $item->durasi_kerja }}
                                                    </small>
                                                </div>
                                            @endif

                                            <!-- Notes -->
                                            @if($item->catatan)
                                                <div class="mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-comment"></i>
                                                        {{ $item->catatan }}
                                                    </small>
                                                </div>
                                            @endif

                                            <!-- Actions -->
                                            <div class="d-flex gap-1 mt-3">
                                                <a href="{{ route('absensi.show', $item) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                                    <i class="fas fa-eye"></i> Detail
                                                </a>
                                                @if(auth()->user()->isAdmin() || auth()->user()->isHRD())
                                                    <a href="{{ route('absensi.admin-edit', $item) }}" class="btn btn-sm btn-outline-warning" title="Edit Absensi">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if(auth()->user()->isAdmin())
                                                        <form action="{{ route('absensi.destroy', $item) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                    title="Hapus Absensi"
                                                                    onclick="return confirm('Yakin ingin menghapus data absensi ini?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $absensi->appends(request()->query())->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Belum ada data absensi</h5>
                            <p class="text-muted">Silakan lakukan absensi untuk melihat riwayat.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Check Out Modal -->
<div class="modal fade" id="checkOutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Check Out</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('absensi.checkout') }}" method="POST" id="checkOutForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="alamat_checkout" class="form-label">Alamat Check Out</label>
                        <input type="text" class="form-control" id="alamat_checkout" name="alamat_checkout" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan_keluar" class="form-label">Keterangan (Opsional)</label>
                        <textarea class="form-control" id="keterangan_keluar" name="keterangan_keluar" rows="3"></textarea>
                    </div>
                    <input type="hidden" id="latitude_checkout" name="latitude">
                    <input type="hidden" id="longitude_checkout" name="longitude">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Check Out
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.absensi-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 12px;
    overflow: hidden;
}

.absensi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

.btn-outline-primary:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
}

.btn-outline-warning:hover {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    border-color: transparent;
}

.btn-outline-danger:hover {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    border-color: transparent;
}

.form-label.small {
    font-size: 0.875rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.shadow-lg {
    box-shadow: 0 1rem 3rem rgba(0,0,0,0.175) !important;
}

@media (max-width: 768px) {
    .container-fluid {
        padding: 0.5rem;
    }
    
    .absensi-card {
        margin-bottom: 1rem;
    }
    
    .d-flex.gap-2.flex-wrap > * {
        margin-bottom: 0.5rem;
    }
}
</style>

<script>
function showCheckOutModal() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            
            document.getElementById('latitude_checkout').value = lat;
            document.getElementById('longitude_checkout').value = lon;
            
            // Get address from coordinates using reverse geocoding
            fetch(`https://api.opencagedata.com/geocode/v1/json?q=${lat}+${lon}&key=YOUR_API_KEY`)
                .then(response => response.json())
                .then(data => {
                    if (data.results && data.results.length > 0) {
                        document.getElementById('alamat_checkout').value = data.results[0].formatted;
                    } else {
                        document.getElementById('alamat_checkout').value = `Lat: ${lat}, Lon: ${lon}`;
                    }
                })
                .catch(error => {
                    console.error('Error getting address:', error);
                    document.getElementById('alamat_checkout').value = `Lat: ${lat}, Lon: ${lon}`;
                });
            
            const modal = new bootstrap.Modal(document.getElementById('checkOutModal'));
            modal.show();
        }, function(error) {
            alert('Error getting location: ' + error.message);
        });
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}
</script>
@endsection
