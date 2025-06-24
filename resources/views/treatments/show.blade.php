@extends('layouts.app')

@section('page-title', 'Detail Treatment')

@section('page-actions')
<div class="btn-group">
    @if(Auth::user()->isAdmin() || Auth::user()->isHRD())
    <a href="{{ route('treatments.edit', $treatment) }}" class="btn btn-warning">
        <i class="bi bi-pencil"></i> Edit
    </a>
    @endif
    <a href="{{ route('treatments.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Kembali
    </a>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">{{ $treatment->name }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Kategori</h6>
                            <span class="badge bg-{{ $treatment->category == 'medical' ? 'danger' : ($treatment->category == 'beauty' ? 'success' : 'info') }} fs-6 mb-3">
                                {{ ucfirst($treatment->category) }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Status</h6>
                            <span class="badge bg-{{ $treatment->is_active ? 'success' : 'secondary' }} fs-6 mb-3">
                                {{ $treatment->is_active ? 'Aktif' : 'Non-aktif' }}
                            </span>
                        </div>
                    </div>
                    
                    <h6 class="text-muted">Deskripsi</h6>
                    <p class="mb-4">{{ $treatment->description }}</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Harga</h6>
                            <h4 class="text-primary">Rp {{ number_format($treatment->price, 0, ',', '.') }}</h4>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Durasi</h6>
                            <h4 class="text-info">{{ $treatment->duration_minutes }} Menit</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Informasi Treatment</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bi bi-calendar-plus text-primary"></i>
                            <strong>Dibuat:</strong> {{ $treatment->created_at->format('d M Y, H:i') }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-calendar-check text-info"></i>
                            <strong>Diperbarui:</strong> {{ $treatment->updated_at->format('d M Y, H:i') }}
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-clock text-warning"></i>
                            <strong>Estimasi:</strong> {{ $treatment->duration_minutes }} menit
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-cash text-success"></i>
                            <strong>Tarif:</strong> Rp {{ number_format($treatment->price, 0, ',', '.') }}
                        </li>
                    </ul>
                </div>
            </div>
            
            @if(Auth::user()->isPelanggan())
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Booking Treatment</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">Tertarik dengan treatment ini?</p>
                    <a href="{{ route('appointments.create', ['treatment' => $treatment->id]) }}" class="btn btn-primary w-100">
                        <i class="bi bi-calendar-plus"></i> Book Sekarang
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
    
    @if($treatment->appointments()->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Riwayat Appointment ({{ $treatment->appointments()->count() }})</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Pasien</th>
                                    <th>Staff</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($treatment->appointments()->latest()->limit(10)->get() as $appointment)
                                <tr>
                                    <td>{{ $appointment->appointment_date->format('d M Y, H:i') }}</td>
                                    <td>{{ $appointment->patient->name }}</td>
                                    <td>{{ $appointment->staff->name ?? 'Belum ditentukan' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $appointment->status == 'completed' ? 'success' : ($appointment->status == 'confirmed' ? 'info' : 'warning') }}">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
