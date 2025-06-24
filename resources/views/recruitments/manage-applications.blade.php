@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Kelola Aplikasi - {{ $recruitment->title }}</h4>
                    <a href="{{ route('recruitments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><strong>Posisi:</strong> {{ $recruitment->title }}</h6>
                            <p><strong>Kuota:</strong> {{ $recruitment->quota }} orang</p>
                            <p><strong>Batas Waktu:</strong> {{ $recruitment->deadline ? \Carbon\Carbon::parse($recruitment->deadline)->format('d M Y') : 'Tidak ada batas' }}</p>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Statistik Aplikasi</h6>
                                    <div class="row text-center">
                                        <div class="col-3">
                                            <div class="text-primary">
                                                <strong>{{ $applications->count() }}</strong><br>
                                                <small>Total</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="text-warning">
                                                <strong>{{ $applications->where('document_status', 'pending')->count() }}</strong><br>
                                                <small>Review</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="text-info">
                                                <strong>{{ $applications->where('interview_status', 'scheduled')->count() }}</strong><br>
                                                <small>Interview</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="text-success">
                                                <strong>{{ $applications->where('final_status', 'accepted')->count() }}</strong><br>
                                                <small>Diterima</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Tabs -->
                    <ul class="nav nav-tabs" id="applicationTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                                Semua <span class="badge bg-secondary">{{ $applications->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="document-tab" data-bs-toggle="tab" data-bs-target="#document" type="button" role="tab">
                                Review Dokumen <span class="badge bg-warning">{{ $applications->where('document_status', 'pending')->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="interview-tab" data-bs-toggle="tab" data-bs-target="#interview" type="button" role="tab">
                                Interview <span class="badge bg-info">{{ $applications->where('interview_status', 'scheduled')->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="final-tab" data-bs-toggle="tab" data-bs-target="#final" type="button" role="tab">
                                Keputusan Final <span class="badge bg-primary">{{ $applications->where('interview_status', 'passed')->where('final_status', 'pending')->count() }}</span>
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="applicationTabsContent">
                        <!-- All Applications -->
                        <div class="tab-pane fade show active" id="all" role="tabpanel">
                            @include('recruitments.partials.applications-table', ['applications' => $applications, 'showAll' => true])
                        </div>

                        <!-- Document Review -->
                        <div class="tab-pane fade" id="document" role="tabpanel">
                            @include('recruitments.partials.applications-table', ['applications' => $applications->where('document_status', 'pending'), 'stage' => 'document'])
                        </div>

                        <!-- Interview -->
                        <div class="tab-pane fade" id="interview" role="tabpanel">
                            @include('recruitments.partials.applications-table', ['applications' => $applications->where('document_status', 'accepted'), 'stage' => 'interview'])
                        </div>

                        <!-- Final Decision -->
                        <div class="tab-pane fade" id="final" role="tabpanel">
                            @include('recruitments.partials.applications-table', ['applications' => $applications->where('interview_status', 'passed'), 'stage' => 'final'])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Review Modal -->
<div class="modal fade" id="documentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Dokumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="documentForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status Dokumen</label>
                        <select class="form-select" name="document_status" required>
                            <option value="">Pilih Status</option>
                            <option value="accepted">Diterima</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" name="document_notes" rows="3" placeholder="Catatan untuk pelamar (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Interview Schedule Modal -->
<div class="modal fade" id="interviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Jadwal Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="interviewForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Interview</label>
                        <input type="datetime-local" class="form-control" name="interview_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi/Platform</label>
                        <input type="text" class="form-control" name="interview_location" placeholder="Ruang Meeting / Zoom / Google Meet" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" name="interview_notes" rows="3" placeholder="Instruksi atau catatan untuk pelamar"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Jadwalkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Interview Result Modal -->
<div class="modal fade" id="interviewResultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hasil Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="interviewResultForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Hasil Interview</label>
                        <select class="form-select" name="interview_status" required>
                            <option value="">Pilih Hasil</option>
                            <option value="passed">Lulus</option>
                            <option value="failed">Tidak Lulus</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nilai/Skor (1-100)</label>
                        <input type="number" class="form-control" name="interview_score" min="1" max="100">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" name="interview_notes" rows="3" placeholder="Catatan hasil interview"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Hasil</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Final Decision Modal -->
<div class="modal fade" id="finalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Keputusan Final</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="finalForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Keputusan</label>
                        <select class="form-select" name="final_status" required>
                            <option value="">Pilih Keputusan</option>
                            <option value="accepted">Diterima</option>
                            <option value="rejected">Ditolak</option>
                            <option value="waiting_list">Waiting List</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Mulai Kerja</label>
                        <input type="date" class="form-control" name="start_date">
                        <small class="form-text text-muted">Hanya untuk pelamar yang diterima</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" name="final_notes" rows="3" placeholder="Catatan keputusan final"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Keputusan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Document review modal
    document.querySelectorAll('.btn-document-review').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            const form = document.getElementById('documentForm');
            form.action = `/applications/${applicationId}/document-status`;
        });
    });

    // Interview schedule modal
    document.querySelectorAll('.btn-schedule-interview').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            const form = document.getElementById('interviewForm');
            form.action = `/applications/${applicationId}/schedule-interview`;
        });
    });

    // Interview result modal
    document.querySelectorAll('.btn-interview-result').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            const form = document.getElementById('interviewResultForm');
            form.action = `/applications/${applicationId}/interview-result`;
        });
    });

    // Final decision modal
    document.querySelectorAll('.btn-final-decision').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            const form = document.getElementById('finalForm');
            form.action = `/applications/${applicationId}/final-decision`;
        });
    });
});
</script>
@endpush
