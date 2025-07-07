@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Daftar Appointment</h4>
                    @if(!auth()->user()->isPelanggan() || auth()->user()->isPelanggan())
                        <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Buat Appointment
                        </a>
                    @endif
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($appointments->count() > 0)
                        <!-- Appointments Grid -->
                        <div class="row">
                            @foreach($appointments as $appointment)
                                <div class="col-lg-6 col-xl-4 mb-4">
                                    <div class="card h-100 shadow-sm appointment-card">
                                        <!-- Card Header with Status -->
                                        <div class="card-header d-flex justify-content-between align-items-center 
                                            {{ $appointment->status === 'completed' ? 'bg-success text-white' : 
                                               ($appointment->status === 'cancelled' ? 'bg-danger text-white' :
                                               ($appointment->status === 'in_progress' ? 'bg-warning text-dark' :
                                               ($appointment->status === 'confirmed' ? 'bg-info text-white' : 'bg-light'))) }}">
                                            <div>
                                                <h6 class="mb-0">Appointment #{{ $appointment->id }}</h6>
                                                <small class="opacity-75">{{ $appointment->appointment_date->format('d M Y, H:i') }}</small>
                                            </div>
                                            <span class="badge {{ match($appointment->status) {
                                                'scheduled' => 'bg-primary',
                                                'confirmed' => 'bg-success', 
                                                'in_progress' => 'bg-warning text-dark',
                                                'completed' => 'bg-light text-dark',
                                                'cancelled' => 'bg-light text-dark',
                                                default => 'bg-secondary'
                                            } }}">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </div>

                                        <div class="card-body">
                                            <!-- Patient & Treatment Info -->
                                            <div class="mb-3">
                                                @if(!auth()->user()->isPelanggan())
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fas fa-user text-muted me-2"></i>
                                                        <div>
                                                            <strong>{{ $appointment->patient->name }}</strong>
                                                            <small class="text-muted d-block">{{ $appointment->patient->email }}</small>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-spa text-muted me-2"></i>
                                                    <div>
                                                        <strong>{{ $appointment->treatment->name }}</strong>
                                                        @if($appointment->treatment->category)
                                                            <small class="text-muted d-block">{{ ucfirst($appointment->treatment->category) }}</small>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if(!auth()->user()->isDokter() && !auth()->user()->isBeautician() && $appointment->staff)
                                                    <div class="d-flex align-items-center mb-2">
                                                        <i class="fas fa-user-md text-muted me-2"></i>
                                                        <div>
                                                            <strong>{{ $appointment->staff->name }}</strong>
                                                            <small class="text-muted d-block">{{ ucfirst($appointment->staff->role) }}</small>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Payment & Price Info -->
                                            <div class="row text-center mb-3">
                                                <div class="col-6">
                                                    <div class="border-end">
                                                        <div class="text-muted small">Total Harga</div>
                                                        <div class="fw-bold text-primary">Rp {{ number_format($appointment->total_price, 0, ',', '.') }}</div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="text-muted small">Payment</div>
                                                    <span class="badge {{ match($appointment->payment_status) {
                                                        'pending' => 'bg-warning text-dark',
                                                        'paid' => 'bg-success',
                                                        'cancelled' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    } }}">
                                                        {{ ucfirst($appointment->payment_status) }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Notes -->
                                            @if($appointment->notes)
                                                <div class="mb-3">
                                                    <small class="text-muted">Catatan:</small>
                                                    <p class="small mb-0">{{ Str::limit($appointment->notes, 80) }}</p>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="card-footer bg-transparent">
                                            <div class="d-grid gap-2">
                                                <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i> Lihat Detail
                                                </a>
                                                @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isHRD() || auth()->user()->isFrontOffice() || auth()->user()->isKasir()))
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-outline-warning btn-sm">
                                                            <i class="fas fa-edit"></i> Edit
                                                        </a>
                                                        <form action="{{ route('appointments.destroy', $appointment) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus appointment ini?')">
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

                        <div class="d-flex justify-content-center">
                            {{ $appointments->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5>Belum ada appointment</h5>
                            <p class="text-muted">Silakan buat appointment baru untuk memulai.</p>
                            @if(!auth()->user()->isPelanggan() || auth()->user()->isPelanggan())
                                <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Buat Appointment Pertama
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
.appointment-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.appointment-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.appointment-card .card-header {
    border-radius: 0.375rem 0.375rem 0 0;
}
</style>
@endsection
