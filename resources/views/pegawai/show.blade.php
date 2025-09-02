@extends('layouts.app')

@section('title', 'Detail Pegawai - ' . ($pegawai->nama_lengkap ?? 'Tidak Diketahui'))
@section('page-title', 'Detail Pegawai')

@section('page-actions')
<div class="d-flex gap-2">
    <a href="{{ route('pegawai.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
        <i class="fas fa-arrow-left me-2"></i> Kembali
    </a>
    <a href="{{ route('pegawai.edit', $pegawai->id ?? $pegawai->id_pegawai) }}" class="btn btn-warning rounded-pill px-4 shadow-sm">
        <i class="fas fa-edit me-2"></i> Edit
    </a>
    @if(auth()->check() && auth()->user()->isAdmin())
        <form action="{{ route('pegawai.destroy', $pegawai->id ?? $pegawai->id_pegawai) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm" 
                    onclick="return confirm('Yakin ingin menghapus data pegawai ini? Data absensi yang terkait juga akan terhapus.')">
                <i class="fas fa-trash me-2"></i> Hapus
            </button>
        </form>
    @endif
</div>
@endsection

@push('styles')
<style>
    /* Container utama dengan background gradient */
    .detail-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 2rem 0;
    }
    
    .detail-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 24px;
        box-shadow: 
            0 20px 60px rgba(0, 0, 0, 0.1),
            0 8px 32px rgba(31, 38, 135, 0.2);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        margin: 0 auto;
        max-width: 1200px;
    }
    
    .detail-card:hover {
        transform: translateY(-8px);
        box-shadow: 
            0 30px 80px rgba(0, 0, 0, 0.15),
            0 12px 40px rgba(31, 38, 135, 0.25);
    }
    
    .detail-header {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 2.5rem;
        margin: 0;
        border-radius: 24px 24px 0 0;
        position: relative;
        overflow: hidden;
    }
    
    .detail-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: float 6s ease-in-out infinite;
    }
    
    .detail-header h2 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .detail-header .subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        margin-bottom: 0;
    }
    
    @keyframes float {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(5deg); }
    }
    
    .section-divider {
        position: relative;
        margin: 2.5rem 0 1.5rem 0;
        text-align: center;
    }
    
    .section-divider span {
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        padding: 0.75rem 2rem;
        border-radius: 25px;
        color: #2e7d32;
        font-weight: 700;
        font-size: 1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border: 2px solid #e8f5e9;
    }
    
    .info-card {
        background: linear-gradient(135deg, #fafbfc 0%, #f8f9fa 100%);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 
            inset 0 2px 4px rgba(0,0,0,0.04),
            0 4px 15px rgba(0,0,0,0.05);
        border: 1px solid rgba(255, 255, 255, 0.5);
        transition: all 0.3s ease;
        margin-bottom: 2rem;
    }
    
    .info-card:hover {
        transform: translateY(-4px);
        box-shadow: 
            inset 0 2px 4px rgba(0,0,0,0.04),
            0 8px 25px rgba(0,0,0,0.1);
    }
    
    .info-card h5 {
        color: #2c3e50;
        font-weight: 700;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.2rem;
    }
    
    .info-card h5 i {
        font-size: 1.3rem;
        color: #28a745;
    }
    
    .info-table {
        margin: 0;
    }
    
    .info-table tr {
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .info-table tr:last-child {
        border-bottom: none;
    }
    
    .info-table td {
        padding: 1rem 0;
        vertical-align: top;
        border: none;
    }
    
    .info-table td:first-child {
        font-weight: 600;
        color: #495057;
        width: 40%;
    }
    
    .info-table td:last-child {
        color: #2c3e50;
        font-weight: 500;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-badge.active {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        border: 1px solid #28a745;
    }
    
    .status-badge.inactive {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border: 1px solid #dc3545;
    }
    
    .user-info-alert {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border: 1px solid #2196f3;
        border-radius: 16px;
        padding: 1.5rem;
        margin: 2rem 0;
    }
    
    .user-info-alert i {
        font-size: 1.5rem;
        color: #1976d2;
        margin-right: 1rem;
    }
    
    .attendance-card {
        background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 
            inset 0 2px 4px rgba(0,0,0,0.04),
            0 4px 15px rgba(0,0,0,0.05);
        border: 1px solid #ffc107;
        margin-top: 2rem;
    }
    
    .attendance-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-top: 1rem;
    }
    
    .attendance-table th {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 1rem;
        border: none;
        font-size: 0.875rem;
    }
    
    .attendance-table td {
        padding: 1rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        vertical-align: middle;
    }
    
    .attendance-table tbody tr:hover {
        background: rgba(40, 167, 69, 0.05);
    }
    
    .attendance-badge {
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .attendance-badge.present {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
    }
    
    .attendance-badge.late {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        color: #856404;
    }
    
    .attendance-badge.absent {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
    }
    
    .action-button {
        padding: 0.75rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .action-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    .action-button.btn-outline-primary {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border: 2px solid #2196f3;
        color: #1976d2;
    }
    
    .action-button.btn-outline-primary:hover {
        background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
        color: white;
    }
    
    .action-button.btn-outline-secondary {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px solid #6c757d;
        color: #495057;
    }
    
    .action-button.btn-outline-secondary:hover {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
        color: white;
    }
    
    .action-button.btn-outline-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border: 2px solid #ffc107;
        color: #856404;
    }
    
    .action-button.btn-outline-warning:hover {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        color: white;
    }
    
    .action-button.btn-outline-danger {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        border: 2px solid #dc3545;
        color: #721c24;
    }
    
    .action-button.btn-outline-danger:hover {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .detail-container {
            padding: 1rem;
        }
        
        .detail-card {
            border-radius: 20px;
            margin: 0 0.5rem;
        }
        
        .detail-header {
            padding: 2rem 1.5rem;
            border-radius: 20px 20px 0 0;
        }
        
        .detail-header h2 {
            font-size: 1.5rem;
        }
        
        .card-body {
            padding: 1.5rem !important;
        }
        
        .info-card {
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .section-divider {
            margin: 2rem 0 1rem 0;
        }
        
        .attendance-table {
            font-size: 0.875rem;
        }
        
        .attendance-table th,
        .attendance-table td {
            padding: 0.75rem 0.5rem;
        }
    }
    
    @media (max-width: 576px) {
        .info-table td:first-child {
            width: 35%;
            font-size: 0.875rem;
        }
        
        .info-table td:last-child {
            font-size: 0.875rem;
        }
        
        .action-button {
            padding: 0.5rem 1.5rem;
            font-size: 0.875rem;
        }
    }
</style>
@endpush

@section('content')
<div class="detail-container">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="detail-card">
                    <div class="detail-header">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h2 class="mb-1">
                                    <i class="fas fa-user-tie me-3"></i> 
                                    {{ $pegawai->nama_lengkap ?? 'Nama Tidak Diketahui' }}
                                </h2>
                                <p class="subtitle mb-0">
                                    {{ isset($pegawai->posisi->nama_posisi) ? $pegawai->posisi->nama_posisi : 'Posisi Tidak Diketahui' }}
                                    @if(isset($pegawai->posisi->departemen))
                                        - {{ $pegawai->posisi->departemen }}
                                    @endif
                                </p>
                            </div>
                            <div class="d-none d-lg-block">
                                <i class="fas fa-user-circle fa-4x opacity-25"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body p-5">
                        <!-- Personal Information Section -->
                        <div class="section-divider">
                            <span><i class="fas fa-user me-2"></i>Informasi Personal</span>
                        </div>
                        
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <div class="info-card">
                                    <h5><i class="fas fa-id-card"></i>Data Pribadi</h5>
                                    <table class="table info-table">
                                        <tr>
                                            <td><strong>Nama Lengkap</strong></td>
                                            <td>{{ $pegawai->nama_lengkap ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>NIK</strong></td>
                                            <td>{{ $pegawai->NIK ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tanggal Lahir</strong></td>
                                            <td>
                                                @if($pegawai->tanggal_lahir)
                                                    {{ $pegawai->tanggal_lahir->format('d F Y') }}
                                                    <small class="text-muted">({{ $pegawai->tanggal_lahir->age ?? 'N/A' }} tahun)</small>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Jenis Kelamin</strong></td>
                                            <td>
                                                @if($pegawai->jenis_kelamin == 'L')
                                                    <i class="fas fa-male text-primary me-2"></i>Laki-laki
                                                @elseif($pegawai->jenis_kelamin == 'P')
                                                    <i class="fas fa-female text-danger me-2"></i>Perempuan
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Agama</strong></td>
                                            <td>{{ $pegawai->agama ?? '-' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="info-card">
                                    <h5><i class="fas fa-address-book"></i>Kontak & Alamat</h5>
                                    <table class="table info-table">
                                        <tr>
                                            <td><strong>Email</strong></td>
                                            <td>
                                                @if($pegawai->email)
                                                    <a href="mailto:{{ $pegawai->email }}" class="text-decoration-none">
                                                        <i class="fas fa-envelope me-2"></i>{{ $pegawai->email }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Telepon</strong></td>
                                            <td>
                                                @if($pegawai->telepon)
                                                    <a href="tel:{{ $pegawai->telepon }}" class="text-decoration-none">
                                                        <i class="fas fa-phone me-2"></i>{{ $pegawai->telepon }}
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Alamat</strong></td>
                                            <td>
                                                @if($pegawai->alamat)
                                                    <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                                                    {{ $pegawai->alamat }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Employment Information Section -->
                        <div class="section-divider">
                            <span><i class="fas fa-briefcase me-2"></i>Informasi Kepegawaian</span>
                        </div>
                        
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <div class="info-card">
                                    <h5><i class="fas fa-user-tie"></i>Posisi & Status</h5>
                                    <table class="table info-table">
                                        <tr>
                                            <td><strong>Posisi</strong></td>
                                            <td>
                                                @if(isset($pegawai->posisi->nama_posisi))
                                                    <span class="badge bg-primary px-3 py-2 fs-6">
                                                        {{ $pegawai->posisi->nama_posisi }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        @if(isset($pegawai->posisi->departemen))
                                        <tr>
                                            <td><strong>Departemen</strong></td>
                                            <td>
                                                <i class="fas fa-building me-2 text-muted"></i>
                                                {{ $pegawai->posisi->departemen }}
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Tanggal Masuk</strong></td>
                                            <td>
                                                @if($pegawai->tanggal_masuk)
                                                    <i class="fas fa-calendar-plus me-2 text-success"></i>
                                                    {{ $pegawai->tanggal_masuk->format('d F Y') }}
                                                    <small class="text-muted">
                                                        ({{ $pegawai->tanggal_masuk->diffForHumans() }})
                                                    </small>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Status Pegawai</strong></td>
                                            <td>
                                                @if($pegawai->tanggal_keluar)
                                                    <span class="status-badge inactive">
                                                        <i class="fas fa-user-times me-2"></i>Tidak Aktif
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">
                                                        Keluar: {{ $pegawai->tanggal_keluar->format('d F Y') }}
                                                    </small>
                                                @else
                                                    <span class="status-badge active">
                                                        <i class="fas fa-user-check me-2"></i>Aktif
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="info-card">
                                    <h5><i class="fas fa-chart-line"></i>Statistik Kerja</h5>
                                    <table class="table info-table">
                                        <tr>
                                            <td><strong>Masa Kerja</strong></td>
                                            <td>
                                                @if($pegawai->tanggal_masuk)
                                                    @php
                                                        $endDate = $pegawai->tanggal_keluar ?: now();
                                                        $workPeriod = $pegawai->tanggal_masuk->diffInMonths($endDate);
                                                        $years = floor($workPeriod / 12);
                                                        $months = $workPeriod % 12;
                                                    @endphp
                                                    <strong class="text-primary">
                                                        @if($years > 0)
                                                            {{ $years }} tahun
                                                        @endif
                                                        @if($months > 0)
                                                            {{ $months }} bulan
                                                        @endif
                                                        @if($years == 0 && $months == 0)
                                                            < 1 bulan
                                                        @endif
                                                    </strong>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- User Account Information Section -->
                        @if(isset($pegawai->user) && $pegawai->user)
                        <div class="section-divider">
                            <span><i class="fas fa-user-circle me-2"></i>Akun User Terkait</span>
                        </div>
                        
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="user-info-alert">
                                    <div class="d-flex align-items-start">
                                        <i class="fas fa-user-circle fa-2x me-3 text-primary"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2 text-primary fw-bold">
                                                <i class="fas fa-link me-2"></i>Terhubung dengan Akun User
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1">
                                                        <strong>Nama:</strong> {{ $pegawai->user->name ?? 'Tidak diketahui' }}
                                                    </p>
                                                    <p class="mb-1">
                                                        <strong>Email:</strong> 
                                                        @if($pegawai->user->email)
                                                            <a href="mailto:{{ $pegawai->user->email }}" class="text-decoration-none">
                                                                {{ $pegawai->user->email }}
                                                            </a>
                                                        @else
                                                            Tidak diketahui
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1">
                                                        <strong>Role:</strong> 
                                                        <span class="badge bg-info px-3 py-2">
                                                            {{ ucfirst($pegawai->user->role ?? 'user') }}
                                                        </span>
                                                    </p>
                                                    <p class="mb-0">
                                                        <strong>Status:</strong> 
                                                        <span class="badge bg-success px-3 py-2">
                                                            <i class="fas fa-check-circle me-1"></i>Aktif
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Recent Attendance Section -->
                        @if(isset($pegawai->absensi) && ($pegawai->absensi instanceof \Illuminate\Support\Collection ? $pegawai->absensi->count() > 0 : (is_array($pegawai->absensi) && count($pegawai->absensi) > 0)))
                        <div class="section-divider">
                            <span><i class="fas fa-clock me-2"></i>Absensi Terbaru</span>
                        </div>
                        
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="attendance-card">
                                    <h5><i class="fas fa-calendar-check"></i>5 Hari Terakhir</h5>
                                    <div class="attendance-table">
                                        <table class="table mb-0">
                                            <thead>
                                                <tr>
                                                    <th><i class="fas fa-calendar me-2"></i>Tanggal</th>
                                                    <th><i class="fas fa-clock me-2"></i>Jam Masuk</th>
                                                    <th><i class="fas fa-clock me-2"></i>Jam Keluar</th>
                                                    <th><i class="fas fa-info-circle me-2"></i>Status</th>
                                                    <th><i class="fas fa-hourglass-half me-2"></i>Durasi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $absensiData = $pegawai->absensi instanceof \Illuminate\Support\Collection 
                                                        ? $pegawai->absensi->sortByDesc('tanggal')->take(5) 
                                                        : collect($pegawai->absensi)->sortByDesc('tanggal')->take(5);
                                                @endphp
                                                @foreach($absensiData as $absen)
                                                    @php
                                                        $absenObj = is_array($absen) ? (object) $absen : $absen;
                                                    @endphp
                                                    <tr>
                                                        <td>
                                                            <strong>
                                                                {{ isset($absenObj->tanggal) ? (is_string($absenObj->tanggal) ? \Carbon\Carbon::parse($absenObj->tanggal)->format('d/m/Y') : $absenObj->tanggal->format('d/m/Y')) : '-' }}
                                                            </strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                {{ isset($absenObj->tanggal) ? (is_string($absenObj->tanggal) ? \Carbon\Carbon::parse($absenObj->tanggal)->format('l') : $absenObj->tanggal->format('l')) : '' }}
                                                            </small>
                                                        </td>
                                                        <td>
                                                            @if(isset($absenObj->jam_masuk))
                                                                <span class="fw-bold text-success">
                                                                    {{ is_string($absenObj->jam_masuk) ? \Carbon\Carbon::parse($absenObj->jam_masuk)->format('H:i') : $absenObj->jam_masuk->format('H:i') }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if(isset($absenObj->jam_keluar))
                                                                <span class="fw-bold text-danger">
                                                                    {{ is_string($absenObj->jam_keluar) ? \Carbon\Carbon::parse($absenObj->jam_keluar)->format('H:i') : $absenObj->jam_keluar->format('H:i') }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @php
                                                                $status = $absenObj->status ?? 'Tidak Diketahui';
                                                                $badgeClass = $status === 'Hadir' ? 'present' : 
                                                                             ($status === 'Terlambat' ? 'late' : 'absent');
                                                            @endphp
                                                            <span class="attendance-badge {{ $badgeClass }}">
                                                                @if($status === 'Hadir')
                                                                    <i class="fas fa-check-circle me-1"></i>
                                                                @elseif($status === 'Terlambat')
                                                                    <i class="fas fa-clock me-1"></i>
                                                                @else
                                                                    <i class="fas fa-times-circle me-1"></i>
                                                                @endif
                                                                {{ $status }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if(isset($absenObj->durasi_kerja) && $absenObj->durasi_kerja !== '-')
                                                                <span class="fw-bold text-primary">
                                                                    {{ $absenObj->durasi_kerja }}
                                                                </span>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-center mt-3">
                                        <a href="{{ route('absensi.index', ['user_id' => $pegawai->user->id ?? '']) }}" class="action-button btn-outline-primary">
                                            <i class="fas fa-chart-line me-2"></i>Lihat Semua Absensi
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="section-divider">
                            <span><i class="fas fa-cogs me-2"></i>Aksi</span>
                        </div>
                        
                        <div class="row g-4">
                            <div class="col-12">
                                <div class="d-flex flex-wrap justify-content-center gap-3">
                                    <a href="{{ route('pegawai.index') }}" class="action-button btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
                                    </a>
                                    
                                    <a href="{{ route('pegawai.edit', $pegawai->id ?? $pegawai->id_pegawai) }}" class="action-button btn-outline-warning">
                                        <i class="fas fa-edit me-2"></i>Edit Data
                                    </a>
                                    
                                    @if(auth()->check() && auth()->user()->isAdmin())
                                        <form action="{{ route('pegawai.destroy', $pegawai->id ?? $pegawai->id_pegawai) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="action-button btn-outline-danger" 
                                                    onclick="return confirm('Yakin ingin menghapus data pegawai ini? Data absensi yang terkait juga akan terhapus.')">
                                                <i class="fas fa-trash me-2"></i>Hapus Data
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
