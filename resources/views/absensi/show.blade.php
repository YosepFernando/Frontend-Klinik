@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="                        </a>
                        
                        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHRD()))
                            <a href="{{ route('absensi.edit', $absensi) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            
                            @if(auth()->check() && auth()->user()->isAdmin())
                                <form action="{{ route('absensi.destroy', $absensi) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')`">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-eye"></i> Detail Absensi
                    </h4>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Employee Information -->
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Informasi Karyawan</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Nama</strong></td>
                                    <td>: {{ $absensi->pegawai->user->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Role</strong></td>
                                    <td>: {{ ucfirst($absensi->pegawai->user->role) }}</td>
                                </tr>
                                @if($absensi->pegawai->posisi)
                                <tr>
                                    <td><strong>Posisi</strong></td>
                                    <td>: {{ $absensi->pegawai->posisi->nama_posisi }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Email</strong></td>
                                    <td>: {{ $absensi->pegawai->user->email }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Attendance Information -->
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Detail Absensi</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Tanggal</strong></td>
                                    <td>: {{ $absensi->tanggal->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Hari</strong></td>
                                    <td>: {{ $absensi->tanggal->format('l') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td>: 
                                        <span class="badge {{ $absensi->status === 'Hadir' ? 'bg-success' : 
                                                              ($absensi->status === 'Terlambat' ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $absensi->status }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <!-- Time Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Waktu Kehadiran</h5>
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title text-success">Jam Masuk</h6>
                                            <h4 class="text-success">
                                                {{ $absensi->jam_masuk ? $absensi->jam_masuk->format('H:i:s') : '-' }}
                                            </h4>
                                            @if($absensi->jam_masuk)
                                                <small class="text-muted">
                                                    {{ $absensi->jam_masuk->format('d M Y') }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title text-danger">Jam Keluar</h6>
                                            <h4 class="text-danger">
                                                {{ $absensi->jam_keluar ? $absensi->jam_keluar->format('H:i:s') : '-' }}
                                            </h4>
                                            @if($absensi->jam_keluar)
                                                <small class="text-muted">
                                                    {{ $absensi->jam_keluar->format('d M Y') }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($absensi->jam_masuk && $absensi->jam_keluar)
                                <div class="text-center mt-3">
                                    <div class="alert alert-info">
                                        <i class="fas fa-hourglass-half"></i>
                                        <strong>Durasi Kerja: {{ $absensi->durasi_kerja }}</strong>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Location Information -->
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Informasi Lokasi</h5>
                            
                            @if($absensi->alamat_masuk)
                                <div class="card mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> Lokasi Check In</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1">{{ $absensi->alamat_masuk }}</p>
                                        @if($absensi->latitude_masuk && $absensi->longitude_masuk)
                                            <small class="text-muted">
                                                Koordinat: {{ $absensi->latitude_masuk }}, {{ $absensi->longitude_masuk }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if($absensi->alamat_keluar)
                                <div class="card">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> Lokasi Check Out</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1">{{ $absensi->alamat_keluar }}</p>
                                        @if($absensi->latitude_keluar && $absensi->longitude_keluar)
                                            <small class="text-muted">
                                                Koordinat: {{ $absensi->latitude_keluar }}, {{ $absensi->longitude_keluar }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($absensi->catatan)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-muted mb-3">Catatan</h5>
                                <div class="alert alert-secondary">
                                    <i class="fas fa-comment"></i>
                                    {{ $absensi->catatan }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="text-center mt-4">
                        <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        
                        @if(auth()->user()->isAdmin() || auth()->user()->isHRD())
                            <a href="{{ route('absensi.edit', $absensi) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            
                            @if(auth()->user()->isAdmin())
                                <form action="{{ route('absensi.destroy', $absensi) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('Yakin ingin menghapus data absensi ini?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
