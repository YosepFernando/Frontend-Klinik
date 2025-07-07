@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Detail Absensi</h4>
                    <div>
                        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHRD()))
                            <a href="{{ route('attendances.edit', $attendance) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                        <a href="{{ route('attendances.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Informasi Karyawan</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 40%;">Nama:</td>
                                    <td>{{ $attendance->user->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Email:</td>
                                    <td>{{ $attendance->user->email }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Role:</td>
                                    <td>{{ ucfirst($attendance->user->role) }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Telepon:</td>
                                    <td>{{ $attendance->user->phone ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Detail Absensi</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 40%;">Tanggal:</td>
                                    <td>{{ $attendance->date->format('l, d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Status:</td>
                                    <td>
                                        <span class="{{ $attendance->status_badge_class }}">
                                            {{ $attendance->status_display }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Check In:</td>
                                    <td>
                                        @if($attendance->check_in)
                                            {{ $attendance->check_in->format('H:i:s') }}
                                            @if($attendance->isLate())
                                                <small class="text-warning">
                                                    <i class="fas fa-clock"></i> (Terlambat)
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Check Out:</td>
                                    <td>
                                        @if($attendance->check_out)
                                            {{ $attendance->check_out->format('H:i:s') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Waktu Kerja</h6>
                            <div class="card bg-light">
                                <div class="card-body">
                                    @if($attendance->check_in && $attendance->check_out)
                                        <div class="text-center">
                                            <div class="display-6 text-primary mb-2">
                                                {{ $attendance->work_duration }}
                                            </div>
                                            <p class="text-muted mb-0">Total Durasi Kerja</p>
                                        </div>
                                        
                                        <hr class="my-3">
                                        
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="text-success">
                                                    <i class="fas fa-sign-in-alt fa-2x"></i>
                                                </div>
                                                <small class="text-muted">Masuk</small>
                                                <div class="fw-bold">{{ $attendance->check_in->format('H:i') }}</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-danger">
                                                    <i class="fas fa-sign-out-alt fa-2x"></i>
                                                </div>
                                                <small class="text-muted">Keluar</small>
                                                <div class="fw-bold">{{ $attendance->check_out->format('H:i') }}</div>
                                            </div>
                                        </div>
                                    @elseif($attendance->check_in)
                                        <div class="text-center">
                                            <div class="text-warning">
                                                <i class="fas fa-clock fa-3x"></i>
                                            </div>
                                            <h6 class="mt-2">Sedang Bekerja</h6>
                                            <p class="text-muted">Check in: {{ $attendance->check_in->format('H:i') }}</p>
                                            @if($attendance->canCheckOut())
                                                <form action="{{ route('attendances.checkout') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin check out?')">
                                                        <i class="fas fa-sign-out-alt"></i> Check Out
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-center text-muted">
                                            <i class="fas fa-minus-circle fa-3x"></i>
                                            <h6 class="mt-2">Tidak Ada Data Waktu</h6>
                                            <p>{{ $attendance->status_display }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Catatan</h6>
                            @if($attendance->notes)
                                <div class="card">
                                    <div class="card-body">
                                        <p class="mb-0">{{ $attendance->notes }}</p>
                                    </div>
                                </div>
                            @else
                                <p class="text-muted">Tidak ada catatan untuk absensi ini.</p>
                            @endif

                            <h6 class="border-bottom pb-2 mb-3 mt-4">Informasi Sistem</h6>
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-bold" style="width: 40%;">Jam Kerja Normal:</td>
                                    <td>08:00 - 17:00</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Batas Terlambat:</td>
                                    <td>Setelah 08:00</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Dicatat Pada:</td>
                                    <td>{{ $attendance->created_at->format('d F Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Terakhir Update:</td>
                                    <td>{{ $attendance->updated_at->format('d F Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHRD()))
                        <hr>
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('attendances.edit', $attendance) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Absensi
                            </a>
                            <form action="{{ route('attendances.destroy', $attendance) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data absensi ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
