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
                        <select class="form-select" name="document_status" id="document_status" required>
                            <option value="">Pilih Status</option>
                            <option value="accepted">Diterima</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Jika diterima, jadwal wawancara akan otomatis dibuat
                        </small>
                    </div>
                    
                    <!-- Interview Schedule Fields - Hidden by default -->
                    <div id="interviewScheduleFields" style="display: none;">
                        <hr>
                        <h6 class="text-primary">
                            <i class="fas fa-calendar"></i> Jadwal Interview
                        </h6>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Interview</label>
                            <input type="datetime-local" class="form-control" name="tanggal_wawancara" id="tanggal_wawancara">
                            <small class="form-text text-muted">Atur jadwal interview untuk pelamar</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lokasi/Platform Interview</label>
                            <input type="text" class="form-control" name="lokasi_wawancara" id="lokasi_wawancara" placeholder="Ruang Meeting / Zoom / Google Meet">
                            <small class="form-text text-muted">Lokasi atau platform untuk interview</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan Interview</label>
                            <textarea class="form-control" name="catatan_wawancara" id="catatan_wawancara" rows="2" placeholder="Instruksi atau catatan untuk pelamar"></textarea>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan Review Dokumen</label>
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
                        <input type="datetime-local" class="form-control" name="tanggal_wawancara" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi/Platform</label>
                        <input type="text" class="form-control" name="lokasi" placeholder="Ruang Meeting / Zoom / Google Meet" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea class="form-control" name="catatan" rows="3" placeholder="Instruksi atau catatan untuk pelamar"></textarea>
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
                        <select class="form-select" name="status" required>
                            <option value="">Pilih Hasil</option>
                            <option value="lulus">✅ Lulus Interview</option>
                            <option value="tidak_lulus">❌ Tidak Lulus Interview</option>
                        </select>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> 
                            Jika lulus, data akan otomatis ditambahkan ke hasil seleksi
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Interview</label>
                        <textarea class="form-control" name="catatan" rows="3" placeholder="Catatan hasil interview"></textarea>
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
                        <select class="form-select" name="final_status" id="final_status" required>
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
                    
                    <!-- Field tanggal mulai kerja - hanya muncul jika diterima -->
                    <div class="mb-3" id="startDateField" style="display: none;">
                        <label class="form-label">Tanggal Mulai Kerja <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="start_date" id="start_date">
                        <small class="form-text text-success">
                            <i class="fas fa-check-circle"></i> 
                            Wajib diisi untuk pelamar yang diterima. Data pegawai akan otomatis dibuat.
                        </small>
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
            const applicationName = this.dataset.applicationName || 'Pelamar';
            const form = document.getElementById('documentForm');
            
            // Update modal title
            const modalTitle = document.querySelector('#documentModal .modal-title');
            modalTitle.innerHTML = `<i class="fas fa-file-alt"></i> Review Dokumen - ${applicationName}`;
            
            // Set form action
            form.action = `/recruitments/{{ $recruitment->id }}/applications/${applicationId}/document-status`;
            form.dataset.applicationId = applicationId;
            
            // Reset form
            form.reset();
            document.getElementById('interviewScheduleFields').style.display = 'none';
            
            console.log('Document review modal opened for application:', applicationId);
            console.log('Form action set to:', form.action);
        });
    });

    // Handle document status change to show/hide interview fields
    document.getElementById('document_status').addEventListener('change', function() {
        const interviewFields = document.getElementById('interviewScheduleFields');
        const tanggalField = document.getElementById('tanggal_wawancara');
        const lokasiField = document.getElementById('lokasi_wawancara');
        
        if (this.value === 'accepted') {
            interviewFields.style.display = 'block';
            // Set default datetime to 3 days from now
            const defaultDate = new Date();
            defaultDate.setDate(defaultDate.getDate() + 3);
            defaultDate.setHours(10, 0); // Set to 10:00 AM
            tanggalField.value = defaultDate.toISOString().slice(0, 16);
            lokasiField.value = 'Ruang Meeting Klinik (akan dikonfirmasi)';
            
            // Make fields required when visible
            tanggalField.required = true;
            lokasiField.required = true;
        } else {
            interviewFields.style.display = 'none';
            // Clear and make fields not required when hidden
            tanggalField.value = '';
            lokasiField.value = '';
            tanggalField.required = false;
            lokasiField.required = false;
        }
    });

    // Handle final status change to show/hide start date field
    document.getElementById('final_status').addEventListener('change', function() {
        const startDateField = document.getElementById('startDateField');
        const startDateInput = document.getElementById('start_date');
        
        if (this.value === 'accepted') {
            startDateField.style.display = 'block';
            // Set default date to 7 days from now (working days)
            const defaultDate = new Date();
            defaultDate.setDate(defaultDate.getDate() + 7);
            startDateInput.value = defaultDate.toISOString().split('T')[0];
            startDateInput.required = true;
        } else {
            startDateField.style.display = 'none';
            startDateInput.value = '';
            startDateInput.required = false;
        }
    });

    // Handle document form submission dengan integrasi jadwal interview
    document.getElementById('documentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        const applicationId = this.dataset.applicationId;
        const documentStatus = formData.get('document_status');
        
        // Show loading state
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
        submitButton.disabled = true;
        
        console.log('Document form submitted for application:', applicationId);
        
        // Submit via standard form submission for now
        this.submit();
    });

    // Interview schedule modal
    document.querySelectorAll('.btn-schedule-interview').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            const applicationName = this.dataset.applicationName;
            const userId = this.dataset.userId;
            const form = document.getElementById('interviewForm');
            
            // Update modal title to show applicant name
            const modalTitle = document.querySelector('#interviewModal .modal-title');
            modalTitle.innerHTML = `<i class="fas fa-calendar"></i> Jadwal Interview - ${applicationName}`;
            
            // Set form data
            form.dataset.applicationId = applicationId;
            form.dataset.userId = userId;
            
            console.log('Interview schedule modal opened for application:', applicationId);
        });
    });

    // Interview result modal
    document.querySelectorAll('.btn-interview-result').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            const wawancaraId = this.dataset.wawancaraId;
            const userId = this.dataset.userId;
            const applicationName = this.dataset.applicationName;
            const form = document.getElementById('interviewResultForm');
            
            // Debug logging
            console.log('Interview result button clicked');
            console.log('Button data attributes:', {
                applicationId,
                wawancaraId,
                userId,
                applicationName
            });
            
            // Update modal title
            const modalTitle = document.querySelector('#interviewResultModal .modal-title');
            modalTitle.innerHTML = `<i class="fas fa-check"></i> Hasil Interview - ${applicationName}`;
            
            // Set form data
            form.dataset.applicationId = applicationId;
            form.dataset.wawancaraId = wawancaraId;
            form.dataset.userId = userId;
            
            // Verify data was set
            console.log('Form dataset after setting:', {
                applicationId: form.dataset.applicationId,
                wawancaraId: form.dataset.wawancaraId,
                userId: form.dataset.userId
            });
            
            console.log('Interview result modal opened for wawancara:', wawancaraId);
        });
    });

    // Handle interview form submission - CREATE WAWANCARA via API
    document.getElementById('interviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const applicationId = this.dataset.applicationId;
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        // Show loading state
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menjadwalkan...';
        submitButton.disabled = true;
        
        // Send to Wawancara API to create new interview
        fetch(`{{ config('app.api_url') }}/public/wawancara`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                id_lamaran_pekerjaan: applicationId,
                id_user: this.dataset.userId, // Will be set from button
                tanggal_wawancara: formData.get('tanggal_wawancara'),
                lokasi: formData.get('lokasi'),
                catatan: formData.get('catatan') || null,
                status: 'pending'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('interviewModal'));
                modal.hide();
                
                // Show success message
                alert('Interview berhasil dijadwalkan!');
                
                // Reload page to update data
                window.location.reload();
            } else {
                throw new Error(data.message || 'Gagal menjadwalkan interview');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        })
        .finally(() => {
            // Reset button
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        });
    });

    // Handle interview result form submission - UPDATE WAWANCARA status
    document.getElementById('interviewResultForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const wawancaraId = this.dataset.wawancaraId; // Will be set from button
        const applicationId = this.dataset.applicationId;
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        const interviewStatus = formData.get('status');
        
        // Debug logging
        console.log('Interview result form submitted');
        console.log('WawancaraId:', wawancaraId);
        console.log('ApplicationId:', applicationId);
        console.log('InterviewStatus:', interviewStatus);
        console.log('UserId:', this.dataset.userId);
        
        // Validasi data yang diperlukan
        if (!wawancaraId) {
            alert('Error: Wawancara ID tidak ditemukan. Silakan refresh halaman dan coba lagi.');
            return;
        }
        
        if (!interviewStatus) {
            alert('Silakan pilih hasil interview terlebih dahulu.');
            return;
        }
        
        // Show loading state
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
        submitButton.disabled = true;
        
        // Update wawancara status
        console.log('Sending PUT request to API...');
        fetch(`{{ config('app.api_url') }}/public/wawancara/${wawancaraId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: interviewStatus,
                catatan: formData.get('catatan') || null
            })
        })
        .then(response => {
            console.log('API Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('API Response data:', data);
            if (data.status === 'success') {
                // If interview passed, create hasil seleksi
                if (interviewStatus === 'lulus') {
                    console.log('Interview passed, creating hasil seleksi...');
                    return createHasilSeleksi(applicationId, this.dataset.userId);
                }
                return Promise.resolve();
            } else {
                throw new Error(data.message || 'Gagal memperbarui hasil interview');
            }
        })
        .then(() => {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('interviewResultModal'));
            modal.hide();
            
            // Show success message
            const message = interviewStatus === 'lulus' 
                ? 'Hasil interview berhasil disimpan dan data hasil seleksi telah dibuat!' 
                : 'Hasil interview berhasil disimpan!';
            alert(message);
            
            // Reload page to update data
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        })
        .finally(() => {
            // Reset button
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        });
    });

    // Function to create hasil seleksi when interview passed
    function createHasilSeleksi(applicationId, userId) {
        return fetch(`{{ config('app.api_url') }}/public/hasil-seleksi`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                id_lamaran_pekerjaan: applicationId,
                id_user: userId,
                status: 'pending',
                catatan: 'Otomatis dibuat setelah lulus wawancara'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'success') {
                console.warn('Gagal membuat hasil seleksi:', data.message);
            }
            return data;
        })
        .catch(error => {
            console.error('Error creating hasil seleksi:', error);
            return null;
        });
    };

    // Final decision modal
    document.querySelectorAll('.btn-final-decision').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            const applicationName = this.dataset.applicationName || 'Pelamar';
            const userId = this.dataset.userId;
            const form = document.getElementById('finalForm');
            
            // Update modal title
            const modalTitle = document.querySelector('#finalModal .modal-title');
            modalTitle.innerHTML = `<i class="fas fa-gavel"></i> Keputusan Final - ${applicationName}`;
            
            // Set form data for regular final decision
            form.dataset.applicationId = applicationId;
            form.dataset.userId = userId;
            form.dataset.isCreate = 'false';
            form.dataset.isEdit = 'false';
            
            // Clear any previous data
            form.reset();
            
            console.log('Final decision modal opened for application:', applicationId);
        });
    });

    // Create selection result modal handler
    document.querySelectorAll('.btn-create-selection-result').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            const applicationName = this.dataset.applicationName || 'Pelamar';
            const userId = this.dataset.userId;
            const currentStatus = this.dataset.currentStatus;
            const form = document.getElementById('finalForm');
            
            // Update modal title
            const modalTitle = document.querySelector('#finalModal .modal-title');
            modalTitle.innerHTML = `<i class="fas fa-plus"></i> Catat Hasil Seleksi - ${applicationName}`;
            
            // Set form data for creating new selection result
            form.dataset.applicationId = applicationId;
            form.dataset.userId = userId;
            form.dataset.isCreate = 'true';
            form.dataset.isEdit = 'false';
            
            // Pre-fill with current status
            const statusSelect = form.querySelector('select[name="final_status"]');
            if (currentStatus === 'diterima') {
                statusSelect.value = 'accepted';
            }
            
            // Add helper text
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.innerHTML = '<i class="fas fa-save"></i> Simpan Hasil Seleksi';
            
            console.log('Create selection result modal opened for application:', applicationId);
        });
    });

    // Edit selection result modal handler
    document.querySelectorAll('.btn-edit-selection-result').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            const applicationName = this.dataset.applicationName || 'Pelamar';
            const userId = this.dataset.userId;
            const hasilSeleksiId = this.dataset.hasilSeleksiId;
            const currentStatus = this.dataset.currentStatus;
            const currentNotes = this.dataset.currentNotes;
            const form = document.getElementById('finalForm');
            
            // Update modal title
            const modalTitle = document.querySelector('#finalModal .modal-title');
            modalTitle.innerHTML = `<i class="fas fa-edit"></i> Edit Hasil Seleksi - ${applicationName}`;
            
            // Set form data for editing
            form.dataset.applicationId = applicationId;
            form.dataset.userId = userId;
            form.dataset.hasilSeleksiId = hasilSeleksiId;
            form.dataset.isCreate = 'false';
            form.dataset.isEdit = 'true';
            
            // Pre-fill form with current values
            const statusSelect = form.querySelector('select[name="final_status"]');
            const notesTextarea = form.querySelector('textarea[name="final_notes"]');
            
            // Map hasil seleksi status to form values
            if (currentStatus === 'diterima') statusSelect.value = 'accepted';
            else if (currentStatus === 'ditolak') statusSelect.value = 'rejected';
            else if (currentStatus === 'pending') statusSelect.value = 'waiting_list';
            
            if (currentNotes) notesTextarea.value = currentNotes;
            
            // Update submit button text
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.innerHTML = '<i class="fas fa-save"></i> Update Hasil Seleksi';
            
            console.log('Edit selection result modal opened for application:', applicationId);
            console.log('Current status:', currentStatus, 'Mapped to:', statusSelect.value);
        });
    });

    // Handle final form submission (untuk hasil seleksi)
    document.getElementById('finalForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        const applicationId = this.dataset.applicationId;
        const userId = this.dataset.userId;
        const hasilSeleksiId = this.dataset.hasilSeleksiId;
        const isCreate = this.dataset.isCreate === 'true';
        const isEdit = this.dataset.isEdit === 'true';
        const finalStatus = formData.get('final_status');
        
        if (!finalStatus) {
            alert('Pilih keputusan terlebih dahulu!');
            return;
        }
        
        // Show loading state
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';
        submitButton.disabled = true;
        
        console.log('Final form submitted');
        console.log('Data:', { applicationId, userId, hasilSeleksiId, finalStatus, isCreate, isEdit });
        
        // Map final_status to hasil seleksi status
        let hasilStatus = 'pending';
        if (finalStatus === 'accepted') hasilStatus = 'diterima';
        else if (finalStatus === 'rejected') hasilStatus = 'ditolak';
        else if (finalStatus === 'waiting_list') hasilStatus = 'pending';
        
        // Tentukan URL dan method berdasarkan action
        let apiUrl, method;
        if (isEdit && hasilSeleksiId) {
            // Edit existing hasil seleksi
            apiUrl = `{{ config('app.api_url') }}/public/hasil-seleksi/${hasilSeleksiId}`;
            method = 'PUT';
        } else if (isCreate || !hasilSeleksiId) {
            // Create new hasil seleksi
            apiUrl = `{{ config('app.api_url') }}/public/hasil-seleksi`;
            method = 'POST';
        } else {
            // Fallback to legacy form submission
            this.submit();
            return;
        }
        
        const requestBody = method === 'POST' ? {
            id_lamaran_pekerjaan: applicationId,
            id_user: userId,
            status: hasilStatus,
            catatan: formData.get('final_notes') || `Keputusan: ${finalStatus === 'accepted' ? 'Diterima' : finalStatus === 'rejected' ? 'Ditolak' : 'Waiting List'}${formData.get('start_date') ? '. Mulai kerja: ' + formData.get('start_date') : ''}`
        } : {
            status: hasilStatus,
            catatan: formData.get('final_notes') || `Keputusan: ${finalStatus === 'accepted' ? 'Diterima' : finalStatus === 'rejected' ? 'Ditolak' : 'Waiting List'}${formData.get('start_date') ? '. Mulai kerja: ' + formData.get('start_date') : ''}`
        };
        
        console.log('Sending request to:', apiUrl);
        console.log('Method:', method);
        console.log('Request body:', requestBody);
        
        fetch(apiUrl, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(requestBody)
        })
        .then(response => {
            console.log('API Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('API Response data:', data);
            if (data.status === 'success') {
                
                // Jika keputusan final adalah "accepted" dan ada tanggal mulai kerja, buat data pegawai
                if (finalStatus === 'accepted' && formData.get('start_date')) {
                    console.log('Creating employee for accepted application...');
                    
                    // Call API untuk membuat data pegawai
                    fetch(`{{ url('/api/recruitments/applications/') }}/${applicationId}/create-employee`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            final_status: 'accepted',
                            start_date: formData.get('start_date')
                        })
                    })
                    .then(employeeResponse => {
                        console.log('Employee API Response status:', employeeResponse.status);
                        return employeeResponse.json();
                    })
                    .then(employeeData => {
                        console.log('Employee API Response data:', employeeData);
                        
                        let successMessage = 'Hasil seleksi berhasil dicatat!';
                        
                        if (employeeData.status === 'success') {
                            successMessage += ' Data pegawai berhasil dibuat dan role user diperbarui ke "' + 
                                           (employeeData.data?.new_role || 'pegawai') + '".';
                        } else if (employeeData.status === 'error') {
                            successMessage += ' Namun, ' + employeeData.message;
                        }
                        
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('finalModal'));
                        modal.hide();
                        
                        // Show success message
                        alert(successMessage);
                        
                        // Reload page to update data
                        window.location.reload();
                    })
                    .catch(employeeError => {
                        console.error('Employee creation error:', employeeError);
                        
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('finalModal'));
                        modal.hide();
                        
                        // Show partial success message
                        alert('Hasil seleksi berhasil dicatat, tetapi terjadi masalah saat membuat data pegawai: ' + employeeError.message);
                        
                        // Reload page to update data
                        window.location.reload();
                    });
                } else {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('finalModal'));
                    modal.hide();
                    
                    // Show success message
                    const actionText = isCreate ? 'dicatat' : isEdit ? 'diperbarui' : 'disimpan';
                    alert(`Hasil seleksi berhasil ${actionText}!`);
                    
                    // Reload page to update data
                    window.location.reload();
                }
            } else {
                throw new Error(data.message || 'Gagal menyimpan keputusan final');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        })
        .finally(() => {
            // Reset button
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
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
