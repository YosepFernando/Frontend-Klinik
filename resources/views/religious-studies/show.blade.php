@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-cash-register me-2"></i>Detail Penggajian</h2>
                <div>
                    @if(Auth::user()->isAdmin() || Auth::user()->isHRD())
                    <a href="{{ route('religious-studies.edit', $religiousStudy) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    @endif
                    <a href="{{ route('religious-studies.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Religious Study Info -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ $religiousStudy->title }}</h5>
                            <span class="badge bg-{{ $religiousStudy->status == 'scheduled' ? 'info' : ($religiousStudy->status == 'ongoing' ? 'warning' : ($religiousStudy->status == 'completed' ? 'success' : 'danger')) }} fs-6">
                                {{ ucfirst($religiousStudy->status) }}
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="text-muted">Informasi Dasar</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Pemateri:</strong></td>
                                            <td>{{ $religiousStudy->leader->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Lokasi:</strong></td>
                                            <td>{{ $religiousStudy->location }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Kapasitas:</strong></td>
                                            <td>{{ $religiousStudy->participants->count() }}/{{ $religiousStudy->max_participants }} peserta</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Dibuat:</strong></td>
                                            <td>{{ $religiousStudy->created_at->format('d M Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted">Jadwal</h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="40%"><strong>Tanggal:</strong></td>
                                            <td>{{ $religiousStudy->study_date->format('d M Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Waktu:</strong></td>
                                            <td>{{ $religiousStudy->start_time }} - {{ $religiousStudy->end_time }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Hari:</strong></td>
                                            <td>{{ $religiousStudy->study_date->locale('id')->dayName }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-muted">Deskripsi</h6>
                                <p class="text-justify">{{ $religiousStudy->description }}</p>
                            </div>

                            @if($religiousStudy->materials)
                            <div class="mb-4">
                                <h6 class="text-muted">Materi Pengajian</h6>
                                <div class="bg-light p-3 rounded">
                                    {!! nl2br(e($religiousStudy->materials)) !!}
                                </div>
                            </div>
                            @endif

                            <!-- Progress Bar -->
                            <div class="mb-4">
                                <h6 class="text-muted">Kapasitas Peserta</h6>
                                @php
                                    $percentage = $religiousStudy->max_participants > 0 ? ($religiousStudy->participants->count() / $religiousStudy->max_participants) * 100 : 0;
                                @endphp
                                <div class="progress mb-2" style="height: 25px;">
                                    <div class="progress-bar bg-{{ $percentage >= 100 ? 'danger' : ($percentage >= 80 ? 'warning' : 'success') }}" 
                                         style="width: {{ min($percentage, 100) }}%">
                                        {{ $religiousStudy->participants->count() }}/{{ $religiousStudy->max_participants }}
                                    </div>
                                </div>
                                <small class="text-muted">{{ number_format($percentage, 1) }}% kapasitas terisi</small>
                            </div>

                            <!-- Join/Leave Religious Study -->
                            @if($religiousStudy->status == 'scheduled')
                                @if(!$religiousStudy->participants->contains('user_id', auth()->id()))
                                    @if($religiousStudy->participants->count() < $religiousStudy->max_participants)
                                    <form action="{{ route('religious-studies.join', $religiousStudy) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-plus me-2"></i>Daftar Pengajian
                                        </button>
                                    </form>
                                    @else
                                    <button class="btn btn-danger" disabled>
                                        <i class="fas fa-times me-2"></i>Kapasitas Penuh
                                    </button>
                                    @endif
                                @else
                                <form action="{{ route('religious-studies.leave', $religiousStudy) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" 
                                            onclick="return confirm('Yakin ingin keluar dari pengajian ini?')">
                                        <i class="fas fa-minus me-2"></i>Keluar dari Pengajian
                                    </button>
                                </form>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Participants List -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-users me-2"></i>Daftar Peserta 
                                <span class="badge bg-primary">{{ $religiousStudy->participants->count() }}</span>
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($religiousStudy->participants->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($religiousStudy->participants as $participant)
                                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <div>
                                        <strong>{{ $participant->user->name }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-user-tag me-1"></i>{{ ucfirst($participant->user->role) }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>Daftar: {{ $participant->created_at->format('d M Y') }}
                                        </small>
                                    </div>
                                    @if($participant->user->id == auth()->id())
                                    <span class="badge bg-success">Anda</span>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="text-center py-3">
                                <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Belum ada peserta terdaftar</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Leader Info -->
                    @if($religiousStudy->leader)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="fas fa-user-tie me-2"></i>Informasi Pemateri
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <h5>{{ $religiousStudy->leader->name }}</h5>
                                <p class="text-muted mb-2">{{ ucfirst($religiousStudy->leader->role) }}</p>
                                @if($religiousStudy->leader->email)
                                <p class="small mb-2">
                                    <i class="fas fa-envelope me-1"></i>{{ $religiousStudy->leader->email }}
                                </p>
                                @endif
                                @if($religiousStudy->leader->phone)
                                <p class="small mb-0">
                                    <i class="fas fa-phone me-1"></i>{{ $religiousStudy->leader->phone }}
                                </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
