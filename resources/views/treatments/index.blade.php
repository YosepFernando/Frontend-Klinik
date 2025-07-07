@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Daftar Treatment</h4>
                    <a href="{{ route('treatments.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tambah Treatment
                    </a>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filter Section -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <form method="GET" action="{{ route('treatments.index') }}" class="row g-3">
                                        <div class="col-md-2">
                                            <label class="form-label">Tahun</label>
                                            <select name="year" class="form-select">
                                                <option value="">Semua Tahun</option>
                                                @if(isset($years))
                                                    @foreach($years as $year)
                                                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                                            {{ $year }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Bulan</label>
                                            <select name="month" class="form-select">
                                                <option value="">Semua Bulan</option>
                                                @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}" {{ request('month') == $i ? 'selected' : '' }}>
                                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Kategori</label>
                                            <select name="category" class="form-select">
                                                <option value="">Semua Kategori</option>
                                                @if(isset($categories))
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                                            {{ $category }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Status</label>
                                            <select name="status" class="form-select">
                                                <option value="">Semua Status</option>
                                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Non-Aktif</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Cari Treatment</label>
                                            <input type="text" name="search" class="form-control" placeholder="Nama treatment..." value="{{ request('search') }}">
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">&nbsp;</label>
                                            <div class="d-grid">
                                                <button type="submit" class="btn btn-outline-primary">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <div class="mt-2">
                                        <a href="{{ route('treatments.index') }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-undo"></i> Reset Filter
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($treatments->count() > 0)
                        <!-- Treatment Cards Grid -->
                        <div class="row">
                            @foreach($treatments as $treatment)
                                <div class="col-lg-4 col-md-6 mb-4">
                                    <div class="card h-100 shadow-sm hover-card">
                                        <!-- Treatment Image/Icon -->
                                        <div class="card-header bg-gradient-primary text-white text-center py-3">
                                            <div class="mb-2">
                                                @php
                                                    $icons = [
                                                        'facial' => 'fas fa-spa',
                                                        'body' => 'fas fa-user',
                                                        'medical' => 'fas fa-stethoscope',
                                                        'beauty' => 'fas fa-magic',
                                                        'wellness' => 'fas fa-heart'
                                                    ];
                                                    $categoryIcon = $icons[strtolower($treatment->category ?? 'beauty')] ?? 'fas fa-spa';
                                                @endphp
                                                <i class="{{ $categoryIcon }} fa-2x"></i>
                                            </div>
                                            <h5 class="card-title mb-0">{{ $treatment->name }}</h5>
                                            @if($treatment->category)
                                                <small class="opacity-75">{{ ucfirst($treatment->category) }}</small>
                                            @endif
                                        </div>
                                        
                                        <div class="card-body">
                                            <!-- Treatment Info -->
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-muted small">Harga</span>
                                                    <span class="fw-bold text-primary">
                                                        Rp {{ number_format($treatment->price, 0, ',', '.') }}
                                                    </span>
                                                </div>
                                                
                                                @if($treatment->duration_minutes ?? $treatment->duration)
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span class="text-muted small">Durasi</span>
                                                        <span class="badge bg-info">{{ $treatment->duration_minutes ?? $treatment->duration }} menit</span>
                                                    </div>
                                                @endif
                                                
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-muted small">Status</span>
                                                    <span class="badge {{ $treatment->is_active ? 'bg-success' : 'bg-danger' }}">
                                                        {{ $treatment->is_active ? 'Aktif' : 'Non-Aktif' }}
                                                    </span>
                                                </div>
                                                
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted small">Dibuat</span>
                                                    <span class="small">{{ $treatment->created_at->format('M Y') }}</span>
                                                </div>
                                            </div>

                                            <!-- Description -->
                                            @if($treatment->description)
                                                <p class="card-text text-muted small">
                                                    {{ Str::limit($treatment->description, 100) }}
                                                </p>
                                            @endif
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div class="card-footer bg-transparent">
                                            <div class="d-grid gap-2">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('treatments.show', $treatment) }}" class="btn btn-outline-info btn-sm">
                                                        <i class="fas fa-eye"></i> Detail
                                                    </a>
                                                    <a href="{{ route('treatments.edit', $treatment) }}" class="btn btn-outline-warning btn-sm">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <form action="{{ route('treatments.destroy', $treatment) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus treatment ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                                <a href="{{ route('appointments.create', ['treatment' => $treatment->id]) }}" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-calendar-plus"></i> Buat Appointment
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if(is_object($treatments) && method_exists($treatments, 'appends'))
                            <div class="d-flex justify-content-center">
                                {{ $treatments->appends(request()->query())->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-spa fa-4x text-muted"></i>
                            </div>
                            <h5>Tidak ada treatment ditemukan</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['year', 'month', 'category', 'status', 'search']))
                                    Coba ubah filter pencarian Anda.
                                @else
                                    Belum ada treatment yang tersedia.
                                @endif
                            </p>
                            @if(!request()->hasAny(['year', 'month', 'category', 'status', 'search']))
                                <a href="{{ route('treatments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Tambah Treatment Pertama
                                </a>
                            @else
                                <a href="{{ route('treatments.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-undo"></i> Reset Filter
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
.bg-gradient-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
}

.hover-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.card-header.bg-gradient-primary {
    border: none;
}

.btn-group .btn {
    flex: 1;
}
</style>
@endsection
