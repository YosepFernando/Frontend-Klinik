@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-chart-bar me-2"></i>Laporan Absensi</h2>
                <div>
                    <a href="{{ route('absensi.admin-create') }}" class="btn btn-success me-2">
                        <i class="fas fa-plus me-1"></i>Tambah Absensi
                    </a>
                    @if(auth()->check() && auth()->user()->isAdmin())
                    <a href="{{ route('absensi.dashboard') }}" class="btn btn-info me-2">
                        <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                    </a>
                    @endif
                    <a href="{{ route('absensi.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('absensi.report') }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Karyawan</label>
                            <select name="user_id" class="form-select">
                                <option value="">Semua Karyawan</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ ucfirst($user->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select" required>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" class="form-select" required>
                                @for($i = date('Y'); $i >= date('Y') - 3; $i--)
                                    <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Generate
                            </button>
                            <a href="{{ route('absensi.report') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-refresh me-1"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Export PDF Card -->
            @if(is_admin() || is_hrd())
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-file-pdf me-2"></i>Export Laporan PDF Bulanan
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('absensi.export-monthly-pdf') }}" class="row g-3">
                        @csrf
                        <div class="col-md-4">
                            <label class="form-label">Bulan <span class="text-danger">*</span></label>
                            <select name="bulan" class="form-select" required>
                                <option value="">Pilih Bulan</option>
                                @for($i = 1; $i <= 12; $i++)
                                    @php
                                        $namaBulan = [
                                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                        ];
                                    @endphp
                                    <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                                        {{ $namaBulan[$i] }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tahun <span class="text-danger">*</span></label>
                            <select name="tahun" class="form-select" required>
                                <option value="">Pilih Tahun</option>
                                @for($i = date('Y'); $i >= date('Y') - 3; $i--)
                                    <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-download me-1"></i>Download Rekap PDF
                            </button>
                        </div>
                    </form>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        File PDF akan berisi rekap lengkap absensi semua karyawan untuk bulan dan tahun yang dipilih.
                    </small>
                </div>
            </div>
            @endif

            <!-- Statistics Cards -->
            @if(!$user_id)
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Karyawan</h6>
                                    <h3 class="mb-0">{{ $users->count() }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Hari Kerja</h6>
                                    <h3 class="mb-0">{{ $workDays }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-calendar-check fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Absensi</h6>
                                    <h3 class="mb-0">{{ $absensi->count() }}</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Rata-rata Kehadiran</h6>
                                    <h3 class="mb-0">{{ number_format(collect($stats)->avg('percentage'), 1) }}%</h3>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Ringkasan Kehadiran {{ DateTime::createFromFormat('!m', $bulan)->format('F') }} {{ $tahun }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Posisi</th>
                                    <th class="text-center">Hadir</th>
                                    <th class="text-center">Terlambat</th>
                                    <th class="text-center">Sakit</th>
                                    <th class="text-center">Izin</th>
                                    <th class="text-center">Tidak Hadir</th>
                                    <th class="text-center">Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats as $stat)
                                <tr>
                                    <td>{{ $stat['name'] }}</td>
                                    <td>{{ ucfirst($stat['role']) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success">{{ $stat['total_hadir'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning">{{ $stat['total_terlambat'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $stat['total_sakit'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $stat['total_izin'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">{{ $stat['total_tidak_hadir'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $stat['percentage'] >= 80 ? 'bg-success' : ($stat['percentage'] >= 60 ? 'bg-warning' : 'bg-danger') }}" 
                                                 style="width: {{ $stat['percentage'] }}%">
                                                {{ $stat['percentage'] }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Detailed Attendance Records -->
            @if($absensi->count() > 0)
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Absensi</h5>
                    <small class="text-muted">{{ $absensi->count() }} record ditemukan</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama</th>
                                    <th>Posisi</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Keluar</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($absensi as $item)
                                <tr>
                                    <td>{{ $item->tanggal_absensi->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2 bg-primary text-white">
                                                {{ substr($item->pegawai->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <strong>{{ $item->pegawai->user->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ ucfirst($item->pegawai->user->role) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->pegawai && $item->pegawai->posisi)
                                            {{ $item->pegawai->posisi->nama_posisi }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->jam_masuk)
                                            <span class="text-success">{{ $item->jam_masuk->format('H:i') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->jam_keluar)
                                            <span class="text-danger">{{ $item->jam_keluar->format('H:i') }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge 
                                            {{ $item->status === 'Hadir' ? 'bg-success' : 
                                               ($item->status === 'Terlambat' ? 'bg-warning' :
                                               ($item->status === 'Sakit' ? 'bg-info' :
                                               ($item->status === 'Izin' ? 'bg-secondary' : 'bg-danger'))) }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->keterangan)
                                            <span class="text-muted">{{ Str::limit($item->keterangan, 30) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('absensi.show', $item) }}" class="btn btn-outline-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('absensi.admin-edit', $item) }}" class="btn btn-outline-warning btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak ada data absensi</h5>
                    <p class="text-muted">Belum ada data absensi untuk periode yang dipilih.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: bold;
}
</style>
@endsection
