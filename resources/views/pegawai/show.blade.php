@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user"></i> Detail Pegawai: {{ $pegawai->nama_lengkap }}
                    </h4>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Left Column - Personal Info -->
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Informasi Personal</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Nama Lengkap</strong></td>
                                    <td>: {{ $pegawai->nama_lengkap }}</td>
                                </tr>
                                <tr>
                                    <td><strong>NIK</strong></td>
                                    <td>: {{ $pegawai->NIK ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Lahir</strong></td>
                                    <td>: {{ $pegawai->tanggal_lahir ? $pegawai->tanggal_lahir->format('d F Y') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Kelamin</strong></td>
                                    <td>: {{ $pegawai->jenis_kelamin == 'L' ? 'Laki-laki' : ($pegawai->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Agama</strong></td>
                                    <td>: {{ $pegawai->agama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat</strong></td>
                                    <td>: {{ $pegawai->alamat ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Right Column - Contact & Employment Info -->
                        <div class="col-md-6">
                            <h5 class="text-muted mb-3">Informasi Kontak & Kepegawaian</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="40%"><strong>Email</strong></td>
                                    <td>: {{ $pegawai->email ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Telepon</strong></td>
                                    <td>: {{ $pegawai->telepon ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Posisi</strong></td>
                                    <td>: {{ $pegawai->posisi->nama_posisi ?? '-' }}</td>
                                </tr>
                                @if($pegawai->posisi)
                                <tr>
                                    <td><strong>Departemen</strong></td>
                                    <td>: {{ $pegawai->posisi->departemen ?? '-' }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Tanggal Masuk</strong></td>
                                    <td>: {{ $pegawai->tanggal_masuk ? $pegawai->tanggal_masuk->format('d F Y') : '-' }}</td>
                                </tr>
                                @if($pegawai->tanggal_keluar)
                                <tr>
                                    <td><strong>Tanggal Keluar</strong></td>
                                    <td>: {{ $pegawai->tanggal_keluar->format('d F Y') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($pegawai->user)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-muted mb-3">Informasi Akun User</h5>
                                <div class="alert alert-info">
                                    <i class="fas fa-user-circle"></i>
                                    <strong>Terhubung dengan akun:</strong> {{ $pegawai->user->name }} ({{ ucfirst($pegawai->user->role) }})
                                    <br>
                                    <small>Email: {{ $pegawai->user->email }}</small>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Recent Attendance -->
                    @if($pegawai->absensi && $pegawai->absensi->count() > 0)
                        <hr>
                        <div class="row">
                            <div class="col-12">
                                <h5 class="text-muted mb-3">Absensi Terbaru (5 Hari Terakhir)</h5>
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Jam Masuk</th>
                                                <th>Jam Keluar</th>
                                                <th>Status</th>
                                                <th>Durasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($pegawai->absensi->sortByDesc('tanggal')->take(5) as $absen)
                                                <tr>
                                                    <td>{{ $absen->tanggal->format('d/m/Y') }}</td>
                                                    <td>{{ $absen->jam_masuk ? $absen->jam_masuk->format('H:i') : '-' }}</td>
                                                    <td>{{ $absen->jam_keluar ? $absen->jam_keluar->format('H:i') : '-' }}</td>
                                                    <td>
                                                        <span class="badge {{ $absen->status === 'Hadir' ? 'bg-success' : 
                                                                              ($absen->status === 'Terlambat' ? 'bg-warning' : 'bg-danger') }}">
                                                            {{ $absen->status }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $absen->durasi_kerja ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center">
                                    <a href="{{ route('absensi.index', ['user_id' => $pegawai->user?->id]) }}" class="btn btn-sm btn-outline-primary">
                                        Lihat Semua Absensi
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="text-center mt-4">
                        <a href="{{ route('pegawai.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        
                        <a href="{{ route('pegawai.edit', $pegawai) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        
                        @if(auth()->user()->isAdmin())
                            <form action="{{ route('pegawai.destroy', $pegawai) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Yakin ingin menghapus data pegawai ini? Data absensi yang terkait juga akan terhapus.')">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
