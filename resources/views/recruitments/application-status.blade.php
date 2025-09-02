@extends('layouts.app')@section('title', 'Status Lamaran')@section('page-title', 'Status Lamaran: ' . $recruitment->position)@section('page-actions')<a href="{{ route('recruitments.index') }}" class="btn btn-secondary">    <i class="bi bi-arrow-left"></i> Kembali ke Daftar Lowongan</a>@endsection@section('content')<div class="container-fluid">    <div class="row">        <div class="col-12">            <!-- Informasi Lowongan Pekerjaan -->            <div class="card mb-4">                <div class="card-body">                    <h5 class="card-title">{{ $recruitment->position }}</h5>                    <p class="text-muted mb-2">                        <i class="bi bi-building"></i> {{ $recruitment->employment_type_display }}                        @if($recruitment->salary_range)                            | <i class="bi bi-currency-dollar"></i> {{ $recruitment->salary_range }}                        @endif                    </p>                    <span class="badge {{ $application->getStatusBadgeClass() }} fs-6">                        {{ $application->getStatusLabel() }}                    </span>                                        @if($application->final_status === 'accepted')                        <div class="alert alert-success mt-3">                            <h6><i class="bi bi-check-circle"></i> Selamat! Lamaran Anda Diterima</h6>                            <p class="mb-1">Anda telah resmi diterima sebagai <strong>{{ $recruitment->position }}</strong>.</p>                            <small class="text-muted">                                Role akun Anda akan diperbarui sesuai dengan posisi pekerjaan yang Anda lamar.                            </small>                        </div>                    @elseif($application->final_status === 'rejected')                        <div class="alert alert-danger mt-3">                            <h6><i class="bi bi-x-circle"></i> Mohon Maaf</h6>                            <p class="mb-0">Lamaran Anda untuk posisi <strong>{{ $recruitment->position }}</strong> tidak dapat kami terima pada kesempatan ini.</p>                        </div>                    @endif                </div>            </div>            <!-- Progres Seleksi -->            <div class="card">                <div class="card-header">                    <h5 class="mb-0">                        <i class="bi bi-list-check"></i> Progres Seleksi Lamaran Anda                    </h5>                </div>                <div class="card-body">                    <!-- Tahapan Seleksi -->                    <div class="row">                        <div class="col-12">                            <div class="progress-container mb-4">                                <div class="row text-center">                                    <!-- Tahap 1: Seleksi Berkas -->                                    <div class="col-md-4">                                        <div class="step {{ $application->document_status !== 'pending' ? 'completed' : 'active' }}">                                            <div class="step-icon {{ $application->document_status === 'accepted' ? 'bg-success' : ($application->document_status === 'rejected' ? 'bg-danger' : 'bg-primary') }}">                                                <i class="bi bi-file-earmark-text text-white"></i>                                            </div>                                            <h6 class="mt-2">Seleksi Berkas</h6>                                            <p class="small text-muted">                                                Status:                                                 <span class="badge bg-{{ $application->document_status === 'accepted' ? 'success' : ($application->document_status === 'rejected' ? 'danger' : 'warning') }}">                                                    {{ $application->document_status === 'accepted' ? 'Diterima' : ($application->document_status === 'rejected' ? 'Ditolak' : 'Menunggu') }}                                                </span>                                            </p>                                                                                        {{-- Tampilkan detail hanya jika status masih pending atau rejected --}}                                            @if($application->document_status !== 'accepted')                                                @if($application->document_reviewed_at)                                                    <small class="text-muted">                                                        Ditinjau: {{ $application->document_reviewed_at->format('d M Y, H:i') }}                                                    </small>                                                @endif                                                @if($application->document_notes)                                                    <div class="alert alert-info alert-sm mt-2">                                                        <small>{{ $application->document_notes }}</small>                                                    </div>                                                @endif                                            @else                                                <small class="text-success">                                                    <i class="bi bi-check-circle"></i> Berkas Anda telah diterima                                                </small>                                            @endif                                        </div>                                    </div>                                    <!-- Tahap 2: Wawancara -->                                    <div class="col-md-4">                                        <div class="step {{ $application->canAccessInterviewStage() ? ($application->interview_status !== 'pending' ? 'completed' : 'active') : 'disabled' }}">                                            <div class="step-icon {{ $application->interview_status === 'accepted' ? 'bg-success' : ($application->interview_status === 'rejected' ? 'bg-danger' : ($application->canAccessInterviewStage() ? 'bg-primary' : 'bg-secondary')) }}">                                                <i class="bi bi-person-video2 text-white"></i>                                            </div>                                            <h6 class="mt-2">Wawancara</h6>                                            @if($application->canAccessInterviewStage())                                                <p class="small text-muted">                                                    Status:                                                     <span class="badge bg-{{ $application->interview_status === 'accepted' ? 'success' : ($application->interview_status === 'rejected' ? 'danger' : 'warning') }}">                                                        {{ $application->interview_status === 'accepted' ? 'Lulus' : ($application->interview_status === 'rejected' ? 'Tidak Lulus' : 'Menunggu') }}                                                    </span>                                                </p>                                                                                                {{-- Tampilkan detail hanya jika status masih pending atau rejected --}}                                                @if($application->interview_status !== 'accepted')                                                    @if($application->interview_scheduled_at)                                                        <div class="alert alert-warning alert-sm mt-2">                                                            <small>                                                                <strong>Jadwal:</strong><br>                                                                {{ $application->interview_scheduled_at->format('d M Y, H:i') }}<br>                                                                <strong>Lokasi:</strong> {{ $application->interview_location }}                                                            </small>                                                        </div>                                                    @endif                                                    @if($application->interview_notes)                                                        <div class="alert alert-info alert-sm mt-2">                                                            <small>{{ $application->interview_notes }}</small>
                                                        </div>
                                                    @endif
                                                @else
                                                    <small class="text-success">
                                                        <i class="bi bi-check-circle"></i> Selamat! Anda lulus wawancara
                                                    </small>
                                                @endif
                                            @else
                                                <p class="small text-muted">Menunggu hasil seleksi berkas</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Tahap 3: Keputusan Akhir -->
                                    <div class="col-md-4">
                                        <div class="step {{ $application->canAccessFinalStage() ? ($application->final_status !== 'pending' ? 'completed' : 'active') : 'disabled' }}">
                                            <div class="step-icon {{ $application->final_status === 'accepted' ? 'bg-success' : ($application->final_status === 'rejected' ? 'bg-danger' : ($application->canAccessFinalStage() ? 'bg-primary' : 'bg-secondary')) }}">
                                                <i class="bi bi-trophy text-white"></i>
                                            </div>
                                            <h6 class="mt-2">Keputusan Akhir</h6>
                                            @if($application->canAccessFinalStage())
                                                <p class="small text-muted">
                                                    Status: 
                                                    <span class="badge bg-{{ $application->final_status === 'accepted' ? 'success' : ($application->final_status === 'rejected' ? 'danger' : 'warning') }}">
                                                        {{ $application->final_status === 'accepted' ? 'Diterima' : ($application->final_status === 'rejected' : 'Ditolak' : 'Menunggu') }}
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
                                                @if($application->final_status === 'accepted')
                                                    <div class="alert alert-success alert-sm mt-2">
                                                        <small><i class="bi bi-check-circle"></i> Role akun Anda akan diperbarui sesuai dengan posisi pekerjaan.</small>
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

                    <!-- Detail Lamaran -->
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

/* Animation for successful stages */
.text-success {
    animation: pulse-success 2s infinite;
}

@keyframes pulse-success {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
    100% {
        opacity: 1;
    }
}

/* Clean completed stages styling */
.step.completed .text-success {
    font-weight: 600;
}

/* Highlight completed steps */
.step.completed {
    background: rgba(40, 167, 69, 0.05);
    border-radius: 10px;
    margin: 0 5px;
}

/* Clean up spacing for successful messages */
.step.completed .text-success {
    display: block;
    margin-top: 10px;
    padding: 5px 10px;
    background: rgba(40, 167, 69, 0.1);
    border-radius: 5px;
    border: 1px solid rgba(40, 167, 69, 0.2);
}
</style>
@endsection
