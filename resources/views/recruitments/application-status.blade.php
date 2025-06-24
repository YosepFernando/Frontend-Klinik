@extends('layouts.app')

@section('title', 'Status Lamaran')
@section('page-title', 'Status Lamaran: ' . $recruitment->position)

@section('page-actions')
<a href="{{ route('recruitments.index') }}" class="btn btn-secondary">
    <i class="bi bi-arrow-left"></i> Kembali ke Lowongan
</a>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Recruitment Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">{{ $recruitment->position }}</h5>
                    <p class="text-muted mb-2">
                        <i class="bi bi-building"></i> {{ $recruitment->employment_type_display }}
                        @if($recruitment->salary_range)
                            | <i class="bi bi-currency-dollar"></i> {{ $recruitment->salary_range }}
                        @endif
                    </p>
                    <span class="badge {{ $application->getStatusBadgeClass() }} fs-6">
                        {{ $application->getStatusLabel() }}
                    </span>
                </div>
            </div>

            <!-- Application Progress -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-list-check"></i> Progress Lamaran Anda
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Progress Steps -->
                    <div class="row">
                        <div class="col-12">
                            <div class="progress-container mb-4">
                                <div class="row text-center">
                                    <!-- Stage 1: Document Review -->
                                    <div class="col-md-4">
                                        <div class="step {{ $application->document_status !== 'pending' ? 'completed' : 'active' }}">
                                            <div class="step-icon {{ $application->document_status === 'accepted' ? 'bg-success' : ($application->document_status === 'rejected' ? 'bg-danger' : 'bg-primary') }}">
                                                <i class="bi bi-file-earmark-text text-white"></i>
                                            </div>
                                            <h6 class="mt-2">Seleksi Berkas</h6>
                                            <p class="small text-muted">
                                                Status: 
                                                <span class="badge bg-{{ $application->document_status === 'accepted' ? 'success' : ($application->document_status === 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($application->document_status) }}
                                                </span>
                                            </p>
                                            @if($application->document_reviewed_at)
                                                <small class="text-muted">
                                                    Ditinjau: {{ $application->document_reviewed_at->format('d M Y, H:i') }}
                                                </small>
                                            @endif
                                            @if($application->document_notes)
                                                <div class="alert alert-info alert-sm mt-2">
                                                    <small>{{ $application->document_notes }}</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Stage 2: Interview -->
                                    <div class="col-md-4">
                                        <div class="step {{ $application->canAccessInterviewStage() ? ($application->interview_status !== 'pending' ? 'completed' : 'active') : 'disabled' }}">
                                            <div class="step-icon {{ $application->interview_status === 'accepted' ? 'bg-success' : ($application->interview_status === 'rejected' ? 'bg-danger' : ($application->canAccessInterviewStage() ? 'bg-primary' : 'bg-secondary')) }}">
                                                <i class="bi bi-person-video2 text-white"></i>
                                            </div>
                                            <h6 class="mt-2">Wawancara</h6>
                                            @if($application->canAccessInterviewStage())
                                                <p class="small text-muted">
                                                    Status: 
                                                    <span class="badge bg-{{ $application->interview_status === 'accepted' ? 'success' : ($application->interview_status === 'rejected' ? 'danger' : 'warning') }}">
                                                        {{ ucfirst($application->interview_status) }}
                                                    </span>
                                                </p>
                                                @if($application->interview_scheduled_at)
                                                    <div class="alert alert-warning alert-sm mt-2">
                                                        <small>
                                                            <strong>Jadwal:</strong><br>
                                                            {{ $application->interview_scheduled_at->format('d M Y, H:i') }}<br>
                                                            <strong>Lokasi:</strong> {{ $application->interview_location }}
                                                        </small>
                                                    </div>
                                                @endif
                                                @if($application->interview_notes)
                                                    <div class="alert alert-info alert-sm mt-2">
                                                        <small>{{ $application->interview_notes }}</small>
                                                    </div>
                                                @endif
                                            @else
                                                <p class="small text-muted">Menunggu hasil seleksi berkas</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Stage 3: Final Result -->
                                    <div class="col-md-4">
                                        <div class="step {{ $application->canAccessFinalStage() ? ($application->final_status !== 'pending' ? 'completed' : 'active') : 'disabled' }}">
                                            <div class="step-icon {{ $application->final_status === 'accepted' ? 'bg-success' : ($application->final_status === 'rejected' ? 'bg-danger' : ($application->canAccessFinalStage() ? 'bg-primary' : 'bg-secondary')) }}">
                                                <i class="bi bi-trophy text-white"></i>
                                            </div>
                                            <h6 class="mt-2">Hasil Akhir</h6>
                                            @if($application->canAccessFinalStage())
                                                <p class="small text-muted">
                                                    Status: 
                                                    <span class="badge bg-{{ $application->final_status === 'accepted' ? 'success' : ($application->final_status === 'rejected' ? 'danger' : 'warning') }}">
                                                        {{ ucfirst($application->final_status) }}
                                                    </span>
                                                </p>
                                                @if($application->final_decided_at)
                                                    <small class="text-muted">
                                                        Diputuskan: {{ $application->final_decided_at->format('d M Y, H:i') }}
                                                    </small>
                                                @endif
                                                @if($application->final_notes)
                                                    <div class="alert alert-info alert-sm mt-2">
                                                        <small>{{ $application->final_notes }}</small>
                                                    </div>
                                                @endif
                                            @else
                                                <p class="small text-muted">Menunggu hasil wawancara</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Application Details -->
                    <hr>
                    <h6>Detail Lamaran Anda</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Tanggal Melamar:</strong> {{ $application->created_at->format('d M Y, H:i') }}</p>
                            <p><strong>CV:</strong> 
                                <a href="{{ Storage::url($application->cv_file) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i> Download CV
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            @if($application->additional_documents)
                                <p><strong>Dokumen Tambahan:</strong></p>
                                <ul class="list-unstyled">
                                    @foreach($application->additional_documents as $doc)
                                        <li>
                                            <a href="{{ Storage::url($doc) }}" target="_blank" class="btn btn-sm btn-outline-secondary mb-1">
                                                <i class="bi bi-download"></i> Download Dokumen
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>

                    @if($application->cover_letter)
                    <div class="mt-3">
                        <h6>Surat Lamaran</h6>
                        <div class="bg-light p-3 rounded">
                            {{ $application->cover_letter }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.progress-container .step {
    position: relative;
    padding: 20px 0;
}

.progress-container .step-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 1.5rem;
    transition: all 0.3s ease;
}

.progress-container .step.disabled .step-icon {
    opacity: 0.5;
}

.progress-container .step.active .step-icon {
    box-shadow: 0 0 20px rgba(0,123,255,0.5);
}

.progress-container .step.completed .step-icon {
    transform: scale(1.1);
}

.alert-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}
</style>
@endsection
