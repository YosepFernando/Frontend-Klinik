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

<!-- Edit Interview Schedule Modal -->
<div class="modal fade" id="editInterviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Jadwal Interview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editInterviewForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i>
                        <strong>Edit Jadwal Interview</strong><br>
                        Perubahan jadwal akan dikirimkan sebagai notifikasi kepada pelamar.
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tanggal Interview <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" name="tanggal_wawancara" id="edit_tanggal_wawancara" required>
                        <small class="form-text text-muted">
                            <i class="fas fa-calendar"></i> Pastikan tanggal dan waktu sesuai dengan ketersediaan pelamar
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Lokasi/Platform <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="lokasi" id="edit_lokasi" 
                               placeholder="Ruang Meeting / Zoom / Google Meet / Microsoft Teams" required>
                        <small class="form-text text-muted">
                            <i class="fas fa-map-marker-alt"></i> Sertakan detail akses jika menggunakan platform online
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Catatan/Instruksi</label>
                        <textarea class="form-control" name="catatan" id="edit_catatan" rows="4" 
                                  placeholder="Tambahkan instruksi khusus, persiapan yang diperlukan, atau informasi penting lainnya..."></textarea>
                        <small class="form-text text-muted">
                            <i class="fas fa-sticky-note"></i> Catatan ini akan dikirimkan kepada pelamar
                        </small>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sendNotification" name="send_notification" checked>
                            <label class="form-check-label" for="sendNotification">
                                <i class="fas fa-bell"></i> Kirim notifikasi perubahan jadwal kepada pelamar
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Jadwal
                    </button>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="applicantDetailModalLabel">
                    <i class="fas fa-user-circle me-2"></i> Detail Pelamar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <!-- Profile Header -->
                <div class="bg-gradient-primary text-white p-4 mb-4">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <div class="avatar-circle bg-white bg-opacity-20 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%;">
                                <i class="fas fa-user fa-2x text-white"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h4 class="mb-1" id="detail-name-header">-</h4>
                            <p class="mb-1"><i class="fas fa-envelope me-2"></i><span id="detail-email-header">-</span></p>
                            <p class="mb-0"><i class="fas fa-phone me-2"></i><span id="detail-phone-header">-</span></p>
                        </div>
                        <div class="col-auto">
                            <div class="text-end">
                                <div class="badge bg-white text-primary fs-6 px-3 py-2" id="detail-status-badge">
                                    <i class="fas fa-clock me-1"></i> Status Loading...
                                </div>
                                <div class="small mt-2 text-white-50">
                                    <i class="fas fa-calendar-alt me-1"></i> Mendaftar: <span id="detail-apply-date">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-4 pb-4">
                    <!-- Info Cards -->
                    <div class="row g-4 mb-4">
                        <!-- Personal Information Card -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0 text-primary">
                                        <i class="fas fa-id-card me-2"></i>Informasi Personal
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="info-item mb-3">
                                        <label class="form-label small text-muted mb-1">Nama Lengkap</label>
                                        <div class="fw-medium" id="detail-name">-</div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="form-label small text-muted mb-1">Email</label>
                                        <div id="detail-email">
                                            <i class="fas fa-envelope text-muted me-1"></i>-
                                        </div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="form-label small text-muted mb-1">Nomor Telepon</label>
                                        <div id="detail-phone">
                                            <i class="fas fa-phone text-muted me-1"></i>-
                                        </div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="form-label small text-muted mb-1">NIK</label>
                                        <div id="detail-nik">
                                            <i class="fas fa-id-card text-muted me-1"></i>-
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <label class="form-label small text-muted mb-1">Alamat</label>
                                        <div id="detail-alamat">
                                            <i class="fas fa-map-marker-alt text-muted me-1"></i>-
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Education & Status Card -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-light border-0">
                                    <h6 class="mb-0 text-success">
                                        <i class="fas fa-graduation-cap me-2"></i>Pendidikan & Status
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="info-item mb-3">
                                        <label class="form-label small text-muted mb-1">Pendidikan Terakhir</label>
                                        <div class="fw-medium" id="detail-pendidikan">
                                            <i class="fas fa-university text-muted me-1"></i>-
                                        </div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="form-label small text-muted mb-1">Status Seleksi</label>
                                        <div id="detail-status-seleksi">
                                            <span class="badge bg-secondary">-</span>
                                        </div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="form-label small text-muted mb-1">Tanggal Apply</label>
                                        <div id="detail-created-at">
                                            <i class="fas fa-calendar text-muted me-1"></i>-
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <label class="form-label small text-muted mb-1">Progress Rekrutmen</label>
                                        <div id="detail-progress">
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" 
                                                     role="progressbar" style="width: 25%" 
                                                     aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" 
                                                     id="progress-bar">
                                                </div>
                                            </div>
                                            <small class="text-muted mt-1 d-block" id="progress-text">Tahap Seleksi Berkas</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Application Timeline -->
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-light border-0">
                            <h6 class="mb-0 text-info">
                                <i class="fas fa-history me-2"></i>Timeline Aplikasi
                            </h6>
                        </div>
                        <div class="card-body" id="application-timeline">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Aplikasi Diterima</h6>
                                        <p class="text-muted small mb-0">Pelamar telah mengirimkan lamaran</p>
                                        <small class="text-muted" id="timeline-apply-date">-</small>
                                    </div>
                                </div>
                                <div class="timeline-item" id="timeline-document" style="display: none;">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Review Dokumen</h6>
                                        <p class="text-muted small mb-0">Status: <span id="timeline-doc-status">-</span></p>
                                        <small class="text-muted" id="timeline-doc-date">-</small>
                                    </div>
                                </div>
                                <div class="timeline-item" id="timeline-interview" style="display: none;">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Interview</h6>
                                        <p class="text-muted small mb-0">Status: <span id="timeline-int-status">-</span></p>
                                        <small class="text-muted" id="timeline-int-date">-</small>
                                    </div>
                                </div>
                                <div class="timeline-item" id="timeline-final" style="display: none;">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Hasil Akhir</h6>
                                        <p class="text-muted small mb-0">Status: <span id="timeline-final-status">-</span></p>
                                        <small class="text-muted" id="timeline-final-date">-</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <div class="d-flex justify-content-between w-100">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btn-view-cv" style="display: none;">
                            <i class="fas fa-file-pdf me-1"></i> Lihat CV
                        </button>
                        <button type="button" class="btn btn-outline-info btn-sm" id="btn-view-cover-letter" style="display: none;">
                            <i class="fas fa-file-alt me-1"></i> Cover Letter
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-send-email" style="display: none;">
                            <i class="fas fa-envelope me-1"></i> Kirim Email
                        </button>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Tutup
                    </button>
                </div>
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

    // Edit interview schedule modal
    document.querySelectorAll('.btn-edit-interview').forEach(button => {
        button.addEventListener('click', function() {
            const applicationId = this.dataset.applicationId;
            const applicationName = this.dataset.applicationName;
            const userId = this.dataset.userId;
            const wawancaraId = this.dataset.wawancaraId;
            const currentDate = this.dataset.currentDate;
            const currentLocation = this.dataset.currentLocation;
            const currentNotes = this.dataset.currentNotes;
            const form = document.getElementById('editInterviewForm');
            
            // Update modal title to show applicant name
            const modalTitle = document.querySelector('#editInterviewModal .modal-title');
            modalTitle.innerHTML = `<i class="fas fa-edit"></i> Edit Jadwal Interview - ${applicationName}`;
            
            // Set form data
            form.dataset.applicationId = applicationId;
            form.dataset.userId = userId;
            form.dataset.wawancaraId = wawancaraId;
            
            // Pre-fill form with current values
            if (currentDate) {
                // Convert to datetime-local format if needed
                const formattedDate = new Date(currentDate).toISOString().slice(0, 16);
                document.getElementById('edit_tanggal_wawancara').value = formattedDate;
            }
            
            if (currentLocation) {
                document.getElementById('edit_lokasi').value = currentLocation;
            }
            
            if (currentNotes) {
                document.getElementById('edit_catatan').value = currentNotes;
            }
            
            console.log('Edit interview modal opened for wawancara:', wawancaraId);
            console.log('Current data:', { currentDate, currentLocation, currentNotes });
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

    // Handle edit interview form submission - UPDATE WAWANCARA schedule
    document.getElementById('editInterviewForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const wawancaraId = this.dataset.wawancaraId;
        const applicationId = this.dataset.applicationId;
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        // Debug logging
        console.log('Edit interview form submitted');
        console.log('WawancaraId:', wawancaraId);
        console.log('ApplicationId:', applicationId);
        
        // Validasi data yang diperlukan
        if (!wawancaraId) {
            alert('Error: Wawancara ID tidak ditemukan. Silakan refresh halaman dan coba lagi.');
            return;
        }
        
        if (!formData.get('tanggal_wawancara') || !formData.get('lokasi')) {
            alert('Tanggal dan lokasi interview wajib diisi.');
            return;
        }
        
        // Show loading state
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengupdate...';
        submitButton.disabled = true;
        
        // Update wawancara schedule
        console.log('Sending PUT request to update interview schedule...');
        fetch(`{{ config('app.api_url') }}/public/wawancara/${wawancaraId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                tanggal_wawancara: formData.get('tanggal_wawancara'),
                lokasi: formData.get('lokasi'),
                catatan: formData.get('catatan') || null,
                // Keep existing status, only update schedule details
                // status: 'terjadwal' // Optional: update status to 'scheduled' if needed
            })
        })
        .then(response => {
            console.log('API Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('API Response data:', data);
            if (data.status === 'success') {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editInterviewModal'));
                modal.hide();
                
                // Show success message with notification option
                const sendNotification = formData.get('send_notification');
                const message = sendNotification ? 
                    'Jadwal interview berhasil diupdate dan notifikasi akan dikirim kepada pelamar!' : 
                    'Jadwal interview berhasil diupdate!';
                
                alert(message);
                
                // TODO: Implement notification sending if checkbox checked
                if (sendNotification) {
                    console.log('Sending notification to applicant...');
                    // Implement notification logic here
                }
                
                // Reload page to update data
                window.location.reload();
            } else {
                throw new Error(data.message || 'Gagal mengupdate jadwal interview');
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
        console.log('Form data received:', {
            final_status: formData.get('final_status'),
            final_notes: formData.get('final_notes'),
            start_date: formData.get('start_date')
        });
        
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
        console.log('Mapped status - Frontend:', finalStatus, '-> API:', hasilStatus);
        
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
                            const newRole = employeeData.data?.new_role || 'pegawai';
                            const positionName = employeeData.data?.position_name || 'posisi yang dilamar';
                            successMessage += ` Data pegawai berhasil dibuat untuk posisi "${positionName}" dan role user diperbarui ke "${newRole}".`;
                        } else if (employeeData.status === 'error') {
                            // Jika error karena sudah ada pegawai, beri pesan yang ramah
                            if (employeeData.message && (
                                employeeData.message.includes('sudah terdaftar sebagai pegawai') ||
                                employeeData.message.includes('Validation error') ||
                                employeeData.message.includes('unique constraint')
                            )) {
                                successMessage += ' Catatan: User ini sudah terdaftar sebagai pegawai atau data sudah ada.';
                            } else {
                                successMessage += ' Namun terjadi masalah: ' + employeeData.message;
                                console.warn('Employee creation issue:', employeeData.message);
                                console.warn('Full response:', employeeData);
                            }
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
            
            // Handle specific error untuk duplikasi
            let errorMessage = 'Error: ' + error.message;
            
            if (error.message && (
                error.message.includes('already exists') ||
                error.message.includes('sudah ada') ||
                error.message.includes('duplicate') ||
                error.message.includes('Hasil seleksi untuk user dan lamaran ini sudah ada')
            )) {
                errorMessage = 'Hasil seleksi sudah ada untuk lamaran ini. Data berhasil diperbarui dengan keputusan baru.';
                
                // Tutup modal dan reload halaman untuk menampilkan data terbaru
                const modal = bootstrap.Modal.getInstance(document.getElementById('finalModal'));
                modal.hide();
                
                alert(errorMessage);
                window.location.reload();
                return;
            }
            
            alert(errorMessage);
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
            
            // Fill header section
            document.getElementById('detail-name-header').textContent = data.name || '-';
            document.getElementById('detail-email-header').textContent = data.email || '-';
            document.getElementById('detail-phone-header').textContent = data.phone || 'Tidak tersedia';
            document.getElementById('detail-apply-date').textContent = data.createdAt || '-';
            
            // Fill main content
            document.getElementById('detail-name').textContent = data.name || '-';
            document.getElementById('detail-email').innerHTML = '<i class="fas fa-envelope text-muted me-1"></i>' + (data.email || '-');
            document.getElementById('detail-phone').innerHTML = '<i class="fas fa-phone text-muted me-1"></i>' + (data.phone || 'Tidak tersedia');
            document.getElementById('detail-nik').innerHTML = '<i class="fas fa-id-card text-muted me-1"></i>' + (data.nik || 'Tidak tersedia');
            document.getElementById('detail-alamat').innerHTML = '<i class="fas fa-map-marker-alt text-muted me-1"></i>' + (data.alamat || 'Tidak tersedia');
            document.getElementById('detail-pendidikan').innerHTML = '<i class="fas fa-university text-muted me-1"></i>' + (data.pendidikan || 'Tidak tersedia');
            document.getElementById('detail-created-at').innerHTML = '<i class="fas fa-calendar text-muted me-1"></i>' + (data.createdAt || '-');
            
            // Determine overall status and progress based on individual statuses
            const docStatus = data.docStatus || 'pending';
            const interviewStatus = data.interviewStatus || 'not_scheduled';
            const finalStatus = data.finalStatus || 'pending';
            
            let overallStatus = 'Menunggu Review Dokumen';
            let progressValue = 25;
            let progressStage = 'Tahap Seleksi Berkas';
            let badgeClass = 'bg-warning text-dark';
            let badgeIcon = 'fas fa-clock';
            let progressClass = 'bg-primary';
            
            // Determine status and progress based on current stage
            if (finalStatus === 'diterima' || finalStatus === 'accepted') {
                overallStatus = 'Diterima';
                progressValue = 100;
                progressStage = 'Selesai - Diterima';
                badgeClass = 'bg-success text-white';
                badgeIcon = 'fas fa-check';
                progressClass = 'bg-success';
            } else if (finalStatus === 'ditolak' || finalStatus === 'rejected') {
                overallStatus = 'Ditolak';
                progressValue = 100;
                progressStage = 'Selesai - Ditolak';
                badgeClass = 'bg-danger text-white';
                badgeIcon = 'fas fa-times';
                progressClass = 'bg-danger';
            } else if (interviewStatus === 'lulus' || interviewStatus === 'passed') {
                overallStatus = 'Lulus Interview - Menunggu Hasil Final';
                progressValue = 75;
                progressStage = 'Tahap Hasil Seleksi';
                badgeClass = 'bg-info text-white';
                badgeIcon = 'fas fa-hourglass-half';
                progressClass = 'bg-info';
            } else if (interviewStatus === 'tidak_lulus' || interviewStatus === 'failed') {
                overallStatus = 'Tidak Lulus Interview';
                progressValue = 50;
                progressStage = 'Tahap Interview - Tidak Lulus';
                badgeClass = 'bg-danger text-white';
                badgeIcon = 'fas fa-times';
                progressClass = 'bg-danger';
            } else if (interviewStatus === 'scheduled' || interviewStatus === 'pending' || interviewStatus === 'terjadwal') {
                overallStatus = 'Interview Dijadwalkan';
                progressValue = 50;
                progressStage = 'Tahap Interview';
                badgeClass = 'bg-info text-white';
                badgeIcon = 'fas fa-calendar-check';
                progressClass = 'bg-info';
            } else if (docStatus === 'accepted' || docStatus === 'diterima') {
                overallStatus = 'Dokumen Diterima - Menunggu Interview';
                progressValue = 35;
                progressStage = 'Persiapan Interview';
                badgeClass = 'bg-primary text-white';
                badgeIcon = 'fas fa-check-circle';
                progressClass = 'bg-primary';
            } else if (docStatus === 'rejected' || docStatus === 'ditolak') {
                overallStatus = 'Dokumen Ditolak';
                progressValue = 25;
                progressStage = 'Tahap Seleksi Berkas - Ditolak';
                badgeClass = 'bg-danger text-white';
                badgeIcon = 'fas fa-times';
                progressClass = 'bg-danger';
            }
            
            // Update status badge and elements
            const statusBadge = document.getElementById('detail-status-badge');
            const statusElement = document.getElementById('detail-status-seleksi');
            
            statusBadge.className = `badge fs-6 px-3 py-2 ${badgeClass}`;
            statusBadge.innerHTML = `<i class="${badgeIcon} me-1"></i> ${overallStatus}`;
            statusElement.innerHTML = `<span class="badge ${badgeClass}">${overallStatus}</span>`;
            
            // Update progress bar
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            
            progressBar.style.width = progressValue + '%';
            progressBar.className = `progress-bar progress-bar-striped progress-bar-animated ${progressClass}`;
            progressBar.setAttribute('aria-valuenow', progressValue);
            progressText.textContent = progressStage;
            
            // Update timeline
            document.getElementById('timeline-apply-date').textContent = data.createdAt || '-';
            
            // Show/hide timeline items based on status
            const timelineDocument = document.getElementById('timeline-document');
            const timelineInterview = document.getElementById('timeline-interview');
            const timelineFinal = document.getElementById('timeline-final');
            
            // Reset timeline visibility
            timelineDocument.style.display = 'none';
            timelineInterview.style.display = 'none';
            timelineFinal.style.display = 'none';
            
            // Show timeline items based on progress
            if (progressValue >= 25) {
                timelineDocument.style.display = 'block';
                let docStatusText = 'Dalam Review';
                if (docStatus === 'accepted' || docStatus === 'diterima') {
                    docStatusText = 'Diterima';
                } else if (docStatus === 'rejected' || docStatus === 'ditolak') {
                    docStatusText = 'Ditolak';
                }
                document.getElementById('timeline-doc-status').textContent = docStatusText;
                document.getElementById('timeline-doc-date').textContent = data.createdAt || '-';
            }
            
            if (progressValue >= 35 && (docStatus === 'accepted' || docStatus === 'diterima')) {
                timelineInterview.style.display = 'block';
                let intStatusText = 'Belum Dijadwalkan';
                if (interviewStatus === 'scheduled' || interviewStatus === 'pending' || interviewStatus === 'terjadwal') {
                    intStatusText = 'Dijadwalkan';
                } else if (interviewStatus === 'lulus' || interviewStatus === 'passed') {
                    intStatusText = 'Lulus';
                } else if (interviewStatus === 'tidak_lulus' || interviewStatus === 'failed') {
                    intStatusText = 'Tidak Lulus';
                }
                document.getElementById('timeline-int-status').textContent = intStatusText;
                document.getElementById('timeline-int-date').textContent = '-';
            }
            
            if (progressValue >= 75) {
                timelineFinal.style.display = 'block';
                let finalStatusText = 'Menunggu Keputusan';
                if (finalStatus === 'diterima' || finalStatus === 'accepted') {
                    finalStatusText = 'Diterima';
                } else if (finalStatus === 'ditolak' || finalStatus === 'rejected') {
                    finalStatusText = 'Ditolak';
                }
                document.getElementById('timeline-final-status').textContent = finalStatusText;
                document.getElementById('timeline-final-date').textContent = '-';
            }
            
            // Setup action buttons
            const cvButton = document.getElementById('btn-view-cv');
            const coverLetterButton = document.getElementById('btn-view-cover-letter');
            const emailButton = document.getElementById('btn-send-email');
            
            // Show email button
            if (data.email && data.email !== '-') {
                emailButton.style.display = 'inline-block';
                emailButton.onclick = function() {
                    window.location.href = `mailto:${data.email}`;
                };
            } else {
                emailButton.style.display = 'none';
            }
            
            // Check for CV and cover letter
            if (data.cvPath && data.cvPath !== '') {
                cvButton.style.display = 'inline-block';
                cvButton.onclick = function() {
                    window.open(data.cvPath, '_blank');
                };
            } else {
                cvButton.style.display = 'none';
            }
            
            if (data.coverLetter && data.coverLetter !== '') {
                coverLetterButton.style.display = 'inline-block';
                coverLetterButton.onclick = function() {
                    showCoverLetter(data.coverLetter);
                };
            } else {
                coverLetterButton.style.display = 'none';
            }
            
            console.log('Enhanced detail applicant modal opened for:', data.name);
            console.log('Status info:', {
                doc: docStatus,
                interview: interviewStatus,
                final: finalStatus,
                overall: overallStatus,
                progress: progressValue
            });
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
/* Enhanced Modal Styles */
.bg-gradient-primary {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.avatar-circle {
    transition: all 0.3s ease;
}

.avatar-circle:hover {
    transform: scale(1.05);
}

/* Info Cards */
.info-item {
    transition: all 0.2s ease;
}

.info-item:hover {
    background-color: #f8f9fa;
    padding: 8px;
    border-radius: 6px;
}

/* Timeline Styles */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
    z-index: 1;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.timeline-content:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.timeline-content h6 {
    color: #495057;
    margin-bottom: 5px;
}

/* Progress Bar Animation */
.progress {
    background-color: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.6s ease;
}

/* Card Hover Effects */
.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

/* Badge Styles */
.badge.fs-6 {
    font-weight: 500;
    letter-spacing: 0.5px;
}

/* Button Group Styling */
.btn-group .btn {
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
}

/* Modal Header Gradient */
.modal-header.bg-primary {
    border-bottom: none;
}

/* Edit Interview Modal Styling */
#editInterviewModal .alert-info {
    border-left: 4px solid #17a2b8;
    background-color: #d1ecf1;
    border-color: #bee5eb;
}

#editInterviewModal .form-label {
    font-weight: 600;
    color: #495057;
}

#editInterviewModal .form-control:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

#editInterviewModal .btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    transition: all 0.3s ease;
}

#editInterviewModal .btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
}

/* Form Check Styling */
.form-check-input:checked {
    background-color: #28a745;
    border-color: #28a745;
}

.form-check-label {
    font-size: 0.9rem;
    color: #6c757d;
}

/* Alert Styling */
.alert {
    border-radius: 8px;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .timeline {
        padding-left: 20px;
    }
    
    .timeline::before {
        left: 10px;
    }
    
    .timeline-marker {
        left: -17px;
        width: 10px;
        height: 10px;
    }
    
    .avatar-circle {
        width: 60px !important;
        height: 60px !important;
    }
    
    .avatar-circle i {
        font-size: 1.5rem !important;
    }
}

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
