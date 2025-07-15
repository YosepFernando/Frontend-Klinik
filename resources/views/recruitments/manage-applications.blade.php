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
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6><strong>Posisi:</strong> {{ $recruitment->title }}</h6>
                            <p><strong>Kuota:</strong> {{ $recruitment->quota }} orang</p>
                            <p><strong>Batas Waktu:</strong> {{ $recruitment->deadline ? \Carbon\Carbon::parse($recruitment->deadline)->format('d M Y') : 'Tidak ada batas' }}</p>
                            {{-- Data lowongan ID: {{ $recruitment->id }} untuk debugging --}}
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6>Statistik Aplikasi</h6>
                                    {{-- Statistik ini diambil dari data yang sudah difilter berdasarkan id_lowongan_pekerjaan di controller --}}
                                    <div class="row text-center">
                                        <div class="col-3">
                                            <div class="text-primary">
                                                <strong>{{ $allApplications->count() }}</strong><br>
                                                <small>Total</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="text-warning">
                                                <strong>{{ $documentApplications->count() }}</strong><br>
                                                <small>Seleksi Berkas</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="text-info">
                                                <strong>{{ $interviewApplications->count() }}</strong><br>
                                                <small>Interview</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="text-success">
                                                <strong>{{ $finalApplications->count() }}</strong><br>
                                                <small>Hasil Seleksi</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($allApplications->count() > 0)
                        <!-- Filter Tabs -->
                        <ul class="nav nav-tabs" id="applicationTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                                    Semua <span class="badge bg-secondary">{{ $allApplications->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="document-tab" data-bs-toggle="tab" data-bs-target="#document" type="button" role="tab">
                                    Seleksi Berkas <span class="badge bg-warning">{{ $documentApplications->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="interview-tab" data-bs-toggle="tab" data-bs-target="#interview" type="button" role="tab">
                                    Interview <span class="badge bg-info">{{ $interviewApplications->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="final-tab" data-bs-toggle="tab" data-bs-target="#final" type="button" role="tab">
                                    Hasil Seleksi <span class="badge bg-primary">{{ $finalApplications->count() }}</span>
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="applicationTabsContent">
                            <!-- All Applications -->
                            {{-- Tab Semua: Menampilkan gabungan semua data yang sudah difilter berdasarkan id_lowongan_pekerjaan --}}
                            <div class="tab-pane fade show active" id="all" role="tabpanel">
                                @include('recruitments.partials.applications-table', ['applications' => $allApplications, 'showAll' => true])
                            </div>

                            <!-- Document Review - Data dari API Lamaran -->
                            {{-- Tab Seleksi Berkas: Data dari API Lamaran dengan filter id_lowongan_pekerjaan --}}
                            <div class="tab-pane fade" id="document" role="tabpanel">
                                @include('recruitments.partials.applications-table', ['applications' => $documentApplications, 'stage' => 'document'])
                            </div>

                            <!-- Interview - Data dari API Wawancara -->
                            {{-- Tab Interview: Data dari API Wawancara dengan filter id_lowongan_pekerjaan --}}
                            <div class="tab-pane fade" id="interview" role="tabpanel">
                                @if($interviewApplications->count() > 0)
                                    <!-- Statistics khusus untuk Interview -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6><i class="fas fa-chart-bar"></i> Statistik Interview</h6>
                                                    <div class="row text-center">
                                                        <div class="col-3">
                                                            <div class="text-info">
                                                                <strong>{{ $interviewApplications->where('interview_status', 'pending')->count() }}</strong><br>
                                                                <small>Dijadwal</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="text-success">
                                                                <strong>{{ $interviewApplications->where('interview_status', 'passed')->count() }}</strong><br>
                                                                <small>Lulus</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="text-danger">
                                                                <strong>{{ $interviewApplications->where('interview_status', 'failed')->count() }}</strong><br>
                                                                <small>Tidak Lulus</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="text-primary">
                                                                <strong>{{ $interviewApplications->count() }}</strong><br>
                                                                <small>Total</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                @include('recruitments.partials.applications-table', ['applications' => $interviewApplications, 'stage' => 'interview'])
                            </div>

                            <!-- Final Decision - Data dari API Hasil Seleksi -->
                            {{-- Tab Hasil Seleksi: Data dari API Hasil Seleksi dengan filter id_lowongan_pekerjaan --}}
                            <div class="tab-pane fade" id="final" role="tabpanel">
                                @if($finalApplications->count() > 0)
                                    <!-- Statistics khusus untuk Final Decision -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="card bg-light">
                                                <div class="card-body">
                                                    <h6><i class="fas fa-trophy"></i> Statistik Hasil Seleksi</h6>
                                                    <div class="row text-center">
                                                        <div class="col-3">
                                                            <div class="text-success">
                                                                <strong>{{ $finalApplications->where('final_status', 'accepted')->count() }}</strong><br>
                                                                <small>Diterima</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="text-danger">
                                                                <strong>{{ $finalApplications->where('final_status', 'rejected')->count() }}</strong><br>
                                                                <small>Ditolak</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="text-warning">
                                                                <strong>{{ $finalApplications->where('final_status', 'pending')->count() }}</strong><br>
                                                                <small>Menunggu</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="text-primary">
                                                                <strong>{{ $finalApplications->count() }}</strong><br>
                                                                <small>Total</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                @include('recruitments.partials.applications-table', ['applications' => $finalApplications, 'stage' => 'final'])
                            </div>
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-5">
                            <div class="empty-state-icon mb-4">
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                                    <i class="fas fa-file-alt fa-3x text-muted"></i>
                                </div>
                            </div>
                            <h4 class="text-muted mb-3">Belum Ada Lamaran</h4>
                            <p class="text-muted mb-4 lead">
                                Belum ada pelamar yang mendaftar untuk lowongan ini.<br>
                                Silakan tunggu atau promosikan lowongan lebih luas.
                            </p>
                        </div>
                    @endif
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
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Jika diterima, jadwal wawancara akan otomatis dibuat 3 hari dari sekarang
                        </small>
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
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Jika lulus, data akan otomatis masuk ke tahap hasil seleksi
                        </small>
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
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Jika diterima, data pegawai akan otomatis dibuat dan role user diperbarui
                        </small>
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

<!-- Applicant Detail Modal -->
<div class="modal fade" id="applicantDetailModal" tabindex="-1" aria-labelledby="applicantDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="applicantDetailModalLabel">
                    <i class="fas fa-user"></i> Detail Pelamar
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-user"></i> Informasi Personal</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Nama Lengkap:</strong></td>
                                <td id="detail-name">-</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td id="detail-email">-</td>
                            </tr>
                            <tr>
                                <td><strong>Telepon:</strong></td>
                                <td id="detail-phone">-</td>
                            </tr>
                            <tr>
                                <td><strong>NIK:</strong></td>
                                <td id="detail-nik">-</td>
                            </tr>
                            <tr>
                                <td><strong>Alamat:</strong></td>
                                <td id="detail-alamat">-</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-graduation-cap"></i> Informasi Pendidikan & Status</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td width="40%"><strong>Pendidikan Terakhir:</strong></td>
                                <td id="detail-pendidikan">-</td>
                            </tr>
                            <tr>
                                <td><strong>Status Seleksi:</strong></td>
                                <td id="detail-status-seleksi">-</td>
                            </tr>
                            <tr>
                                <td><strong>Tanggal Apply:</strong></td>
                                <td id="detail-created-at">-</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if Bootstrap is loaded
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap JavaScript is not loaded!');
        alert('Bootstrap JavaScript tidak ter-load. Refresh halaman atau periksa koneksi internet.');
        return;
    }
    
    console.log('Bootstrap loaded successfully');
    
    // Initialize all dropdowns manually if needed
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl);
    });
    
    console.log('Initialized', dropdownList.length, 'dropdowns');

    // Document review modal
    document.querySelectorAll('.btn-document-review').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            const form = document.getElementById('documentForm');
            form.action = `{{ url('/recruitments') }}/{{ $recruitment->id }}/applications/${applicationId}/document-status`;
            console.log('Document review modal opened for application:', applicationId);
        });
    });

    // Interview schedule modal
    document.querySelectorAll('.btn-schedule-interview').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            const form = document.getElementById('interviewForm');
            form.action = `{{ url('/recruitments') }}/{{ $recruitment->id }}/applications/${applicationId}/schedule-interview`;
            console.log('Interview schedule modal opened for application:', applicationId);
        });
    });

    // Interview result modal
    document.querySelectorAll('.btn-interview-result').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            const form = document.getElementById('interviewResultForm');
            form.action = `{{ url('/recruitments') }}/{{ $recruitment->id }}/applications/${applicationId}/interview-result`;
            console.log('Interview result modal opened for application:', applicationId);
        });
    });

    // Final decision modal
    document.querySelectorAll('.btn-final-decision').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            const form = document.getElementById('finalForm');
            form.action = `{{ url('/recruitments') }}/{{ $recruitment->id }}/applications/${applicationId}/final-decision`;
            console.log('Final decision modal opened for application:', applicationId);
        });
    });
    
    // Cover letter button handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.btn-cover-letter')) {
            const button = e.target.closest('.btn-cover-letter');
            const coverLetter = button.dataset.coverLetter;
            if (coverLetter) {
                showCoverLetter(coverLetter);
            } else {
                console.error('No cover letter data found');
                alert('Cover letter tidak ditemukan.');
            }
        }
    });
    
    // Detail applicant modal handler
    document.querySelectorAll('.btn-detail-applicant').forEach(button => {
        button.addEventListener('click', function() {
            const data = this.dataset;
            document.getElementById('detail-name').textContent = data.name || '-';
            document.getElementById('detail-email').textContent = data.email || '-';
            document.getElementById('detail-phone').textContent = data.phone || '-';
            document.getElementById('detail-nik').textContent = data.nik || '-';
            document.getElementById('detail-alamat').textContent = data.alamat || '-';
            document.getElementById('detail-pendidikan').textContent = data.pendidikan || '-';
            document.getElementById('detail-status-seleksi').textContent = data.statusSeleksi || '-';
            document.getElementById('detail-created-at').textContent = data.createdAt || '-';
            console.log('Detail applicant modal opened for:', data.name);
        });
    });
    
    // Debug dropdown clicks
    document.querySelectorAll('.dropdown-toggle').forEach(button => {
        button.addEventListener('click', function(e) {
            console.log('Dropdown clicked:', this.id, this);
            // Force show dropdown if Bootstrap fails
            if (!this.getAttribute('aria-expanded') || this.getAttribute('aria-expanded') === 'false') {
                console.log('Manually showing dropdown');
                const dropdown = bootstrap.Dropdown.getOrCreateInstance(this);
                dropdown.show();
            }
        });
    });
});

// Function to show cover letter in modal
function showCoverLetter(coverLetter) {
    // Create modal if it doesn't exist
    let modal = document.getElementById('coverLetterModal');
    if (!modal) {
        const modalHtml = `
            <div class="modal fade" id="coverLetterModal" tabindex="-1" aria-labelledby="coverLetterModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="coverLetterModalLabel">Cover Letter</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="coverLetterContent"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        modal = document.getElementById('coverLetterModal');
    }
    
    // Set content and show modal
    document.getElementById('coverLetterContent').innerHTML = '<pre>' + coverLetter + '</pre>';
    const bootstrapModal = new bootstrap.Modal(modal);
    bootstrapModal.show();
}
</script>
@endpush

@push('styles')
<style>
/* Fallback CSS untuk dropdown yang tidak berfungsi dengan JavaScript */
.dropdown:hover .dropdown-menu {
    display: block !important;
}

.dropdown-menu {
    transition: all 0.3s ease;
}

/* Pastikan dropdown button bisa diklik */
.dropdown-toggle {
    cursor: pointer !important;
    pointer-events: auto !important;
}

/* Debug style untuk melihat area klik */
.dropdown-toggle:hover {
    background-color: #e9ecef !important;
    border-color: #dee2e6 !important;
}

/* Perbaiki z-index untuk dropdown */
.dropdown-menu {
    z-index: 1050 !important;
}

/* Pastikan button group tidak menghalangi */
.btn-group-vertical .dropdown {
    position: relative;
    z-index: 1;
}

/* Style untuk debugging - bisa dihapus nanti */
.debug-dropdown {
    border: 2px solid red !important;
    background-color: yellow !important;
}
</style>
@endpush
