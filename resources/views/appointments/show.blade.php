@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Detail Appointment #{{ $appointment->id }}</h4>
                    <div>
                        @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHRD() || auth()->user()->isFrontOffice() || auth()->user()->isKasir()))
                            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif
                        <a href="{{ route('appointments.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Informasi Pasien</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 40%;">Nama:</td>
                                    <td>{{ $appointment->patient->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Email:</td>
                                    <td>{{ $appointment->patient->email }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Telepon:</td>
                                    <td>{{ $appointment->patient->phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Jenis Kelamin:</td>
                                    <td>{{ $appointment->patient->gender ? ucfirst($appointment->patient->gender) : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Informasi Staff</h6>
                            @if($appointment->staff)
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-bold" style="width: 40%;">Nama:</td>
                                        <td>{{ $appointment->staff->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Role:</td>
                                        <td>{{ ucfirst($appointment->staff->role) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email:</td>
                                        <td>{{ $appointment->staff->email }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Telepon:</td>
                                        <td>{{ $appointment->staff->phone ?? '-' }}</td>
                                    </tr>
                                </table>
                            @else
                                <p class="text-muted">Staff belum ditentukan</p>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Detail Treatment</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 40%;">Treatment:</td>
                                    <td>{{ $appointment->treatment->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Kategori:</td>
                                    <td>{{ $appointment->treatment->category ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Durasi:</td>
                                    <td>{{ $appointment->treatment->duration ?? '-' }} menit</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Deskripsi:</td>
                                    <td>{{ $appointment->treatment->description ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Detail Appointment</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 40%;">Tanggal:</td>
                                    <td>{{ $appointment->appointment_date->format('d F Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Waktu:</td>
                                    <td>{{ $appointment->appointment_date->format('H:i') }} WIB</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Status:</td>
                                    <td>
                                        @php
                                            $statusClass = match($appointment->status) {
                                                'scheduled' => 'badge bg-info',
                                                'confirmed' => 'badge bg-primary',
                                                'in_progress' => 'badge bg-warning',
                                                'completed' => 'badge bg-success',
                                                'cancelled' => 'badge bg-danger',
                                                default => 'badge bg-secondary'
                                            };
                                        @endphp
                                        <span class="{{ $statusClass }}">{{ ucfirst($appointment->status) }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Payment Status:</td>
                                    <td>
                                        @php
                                            $paymentClass = match($appointment->payment_status) {
                                                'pending' => 'badge bg-warning',
                                                'paid' => 'badge bg-success',
                                                'cancelled' => 'badge bg-danger',
                                                default => 'badge bg-secondary'
                                            };
                                        @endphp
                                        <span class="{{ $paymentClass }}">{{ ucfirst($appointment->payment_status) }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Catatan</h6>
                            @if($appointment->notes)
                                <p>{{ $appointment->notes }}</p>
                            @else
                                <p class="text-muted">Tidak ada catatan</p>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="border-bottom pb-2 mb-3">Informasi Pembayaran</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 40%;">Harga Treatment:</td>
                                    <td>Rp {{ number_format($appointment->total_price, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Total Bayar:</td>
                                    <td class="fs-5 fw-bold text-primary">Rp {{ number_format($appointment->total_price, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-12">
                            <h6 class="border-bottom pb-2 mb-3">Riwayat</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold" style="width: 20%;">Dibuat:</td>
                                    <td>{{ $appointment->created_at->format('d F Y H:i') }} WIB</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Terakhir Diupdate:</td>
                                    <td>{{ $appointment->updated_at->format('d F Y H:i') }} WIB</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHRD() || auth()->user()->isFrontOffice() || auth()->user()->isKasir()))
                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Appointment
                            </a>
                            <form action="{{ route('appointments.destroy', $appointment) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus appointment ini?')">
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
