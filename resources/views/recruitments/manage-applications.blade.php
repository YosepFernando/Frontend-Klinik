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
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-primary mb-3">Statistik Lamaran</h6>
                                    {{-- Statistik ini diambil dari data yang sudah difilter berdasarkan id_lowongan_pekerjaan di controller --}}
                                    <div class="row text-center">
                                        <div class="col-3">
                                            <div class="p-2">
                                                <div class="text-primary h4 mb-1">{{ $allApplications->count() }}</div>
                                                <small class="text-muted">Total</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="p-2">
                                                <div class="text-warning h4 mb-1">{{ $documentApplications->where('document_status', '!=', 'accepted')->count() }}</div>
                                                <small class="text-muted">Seleksi Berkas</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="p-2">
                                                <div class="text-info h4 mb-1">{{ $interviewApplications->filter(function($app) {
                                    $status = $app->interview_status ?? $app->status ?? $app->wawancara_status ?? $app->status_wawancara ?? 'not_scheduled';
                                    return in_array($status, ['pending', 'scheduled', 'terjadwal', 'dijadwalkan', 'belum_dijadwal', 'not_scheduled', 'tidak_lulus', 'tidak lulus', 'ditolak', 'failed']);
                                })->count() }}</div>
                                                <small class="text-muted">Wawancara</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="p-2">
                                                <div class="text-success h4 mb-1">{{ $finalApplications->count() }}</div>
                                                <small class="text-muted">Hasil Akhir</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($allApplications->count() > 0)
                        <!-- Tabs Filter -->
                        <ul class="nav nav-tabs nav-tabs-simple" id="applicationTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                                    Semua Lamaran <span class="badge bg-secondary ms-1">{{ $allApplications->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="document-tab" data-bs-toggle="tab" data-bs-target="#document" type="button" role="tab">
                                    Seleksi Berkas <span class="badge bg-warning ms-1">{{ $documentApplications->where('document_status', '!=', 'accepted')->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="interview-tab" data-bs-toggle="tab" data-bs-target="#interview" type="button" role="tab">
                                    Wawancara <span class="badge bg-info ms-1">{{ $interviewApplications->filter(function($app) {
                                        $status = $app->interview_status ?? $app->status ?? $app->wawancara_status ?? $app->status_wawancara ?? 'not_scheduled';
                                        return in_array($status, ['pending', 'scheduled', 'terjadwal', 'dijadwalkan', 'belum_dijadwal', 'not_scheduled', 'tidak_lulus', 'tidak lulus', 'ditolak', 'failed']);
                                    })->count() }}</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="final-tab" data-bs-toggle="tab" data-bs-target="#final" type="button" role="tab">
                                    Hasil Akhir <span class="badge bg-primary ms-1">{{ $finalApplications->count() }}</span>
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
                            {{-- Tab Seleksi Berkas: Data dari API Lamaran dengan filter id_lowongan_pekerjaan, exclude yang sudah diterima --}}
                            <div class="tab-pane fade" id="document" role="tabpanel">
                                @php
                                    $filteredDocumentApplications = $documentApplications->where('document_status', '!=', 'accepted');
                                @endphp

                                @if($documentApplications->where('document_status', 'accepted')->count() > 0)
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Catatan:</strong> {{ $documentApplications->where('document_status', 'accepted')->count() }} lamaran yang sudah diterima pada seleksi berkas telah otomatis dipindahkan ke tahap wawancara.
                                    </div>
                                @endif

                                @include('recruitments.partials.applications-table', ['applications' => $filteredDocumentApplications, 'stage' => 'document'])
                            </div>

                            <!-- Wawancara - Data dari API Wawancara -->
                            {{-- Tab Wawancara: Data dari API Wawancara dengan filter id_lowongan_pekerjaan, exclude yang sudah lulus --}}
                            <div class="tab-pane fade" id="interview" role="tabpanel">
                                @php
                                    // Filter hanya menampilkan wawancara yang belum lulus (termasuk semua status kecuali lulus/diterima)
                                    $filteredInterviewApplications = $interviewApplications->filter(function($application) {
                                        $status = $application->interview_status ??
                                                 $application->status ??
                                                 $application->wawancara_status ??
                                                 $application->status_wawancara ??
                                                 'not_scheduled';

                                        return in_array($status, [
                                            'pending',
                                            'scheduled',
                                            'terjadwal',
                                            'dijadwalkan',
                                            'belum_dijadwal',
                                            'not_scheduled',
                                            'tidak_lulus',
                                            'tidak lulus',
                                            'ditolak',
                                            'failed'
                                        ]);
                                    });
                                @endphp

                                @if($interviewApplications->where('status', 'lulus')->count() > 0)
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i>
                                        <strong>Catatan:</strong> {{ $interviewApplications->where('status', 'lulus')->count() }} pelamar yang lulus wawancara telah otomatis dipindahkan ke tahap hasil seleksi.
                                    </div>
                                @endif

                                @if($filteredInterviewApplications->count() == 0 && $interviewApplications->count() > 0)
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Info:</strong> Semua pelamar pada tahap wawancara sudah lulus dan dipindahkan ke tahap hasil seleksi.
                                    </div>
                                @endif

                                @include('recruitments.partials.applications-table', ['applications' => $filteredInterviewApplications, 'stage' => 'interview'])
                            </div>

                            <!-- Hasil Akhir - Data dari API Hasil Seleksi -->
                            {{-- Tab Hasil Akhir: Data dari API Hasil Seleksi dengan filter id_lowongan_pekerjaan --}}
                            <div class="tab-pane fade" id="final" role="tabpanel">
                                @if($finalApplications->count() > 0)
                                    <!-- Statistik khusus untuk Hasil Akhir -->
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <div class="card border-0 bg-light">
                                                <div class="card-body py-3">
                                                    <h6 class="mb-3">Statistik Hasil Seleksi</h6>
                                                    <div class="row text-center">
                                                        <div class="col-3">
                                                            <div class="p-2">
                                                                <div class="text-success h5 mb-1">{{ $finalApplications->where('final_status', 'accepted')->count() }}</div>
                                                                <small class="text-muted">Diterima</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="p-2">
                                                                <div class="text-danger h5 mb-1">{{ $finalApplications->where('final_status', 'rejected')->count() }}</div>
                                                                <small class="text-muted">Ditolak</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="p-2">
                                                                <div class="text-warning h5 mb-1">{{ $finalApplications->where('final_status', 'pending')->count() }}</div>
                                                                <small class="text-muted">Menunggu</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="p-2">
                                                                <div class="text-primary h5 mb-1">{{ $finalApplications->count() }}</div>
                                                                <small class="text-muted">Total</small>
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
                        <!-- Belum Ada Lamaran -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                                    <i class="fas fa-file-alt fa-2x text-muted"></i>
                                </div>
                            </div>
                            <h4 class="text-muted mb-3">Belum Ada Lamaran</h4>
                            <p class="text-muted lead">
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
                            Jika diterima, gunakan tombol "Jadwal Interview" untuk membuat jadwal wawancara
                        </small>
                    </div>

                    <!-- Interview Schedule Fields - Hidden by default (PREVIEW ONLY - NOT SUBMITTED) -->
                    <div id="interviewScheduleFields" style="display: none;">
                        <hr>
                        <h6 class="text-primary">
                            <i class="fas fa-calendar"></i> Preview Jadwal Interview
                        </h6>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <small>Field di bawah ini hanya untuk preview. Jadwal interview akan dibuat menggunakan tombol "Jadwal Interview" setelah dokumen diterima.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tanggal Interview</label>
                            <input type="datetime-local" class="form-control" id="tanggal_wawancara" readonly>
                            <small class="form-text text-muted">Preview tanggal interview yang akan dijadwalkan</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lokasi/Platform Interview</label>
                            <input type="text" class="form-control" id="lokasi_wawancara" placeholder="Ruang Meeting / Zoom / Google Meet" readonly>
                            <small class="form-text text-muted">Preview lokasi atau platform interview</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan Interview</label>
                            <textarea class="form-control" id="catatan_wawancara" rows="2" placeholder="Instruksi atau catatan untuk pelamar" readonly></textarea>
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

<!-- Edit Interview Schedule Modal -->
<div class="modal fade" id="editInterviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Jadwal Wawancara</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editInterviewForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tanggal & Waktu Wawancara</label>
                        <input type="datetime-local" class="form-control" name="tanggal_wawancara" id="edit_tanggal_wawancara" required>
                        <small class="form-text text-muted">Pastikan jadwal tidak bentrok dengan agenda lain</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi/Platform Wawancara</label>
                        <input type="text" class="form-control" name="lokasi" id="edit_lokasi_wawancara" placeholder="Ruang Meeting / Zoom / Google Meet" required>
                        <small class="form-text text-muted">Lokasi fisik atau platform online untuk wawancara</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan Tambahan</label>
                        <textarea class="form-control" name="catatan" id="edit_catatan_wawancara" rows="3" placeholder="Instruksi atau catatan untuk pelamar (opsional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Jadwal
                    </button>
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
                            Jika <strong>diterima</strong>, data pegawai akan otomatis dibuat, role user akan diperbarui sesuai posisi yang dilamar, dan tanggal mulai kerja wajib diisi.
                        </small>
                    </div>

                    <!-- Field tanggal mulai kerja - hanya muncul jika diterima -->
                    <div class="mb-3" id="startDateField" style="display: none;">
                        <label class="form-label">Tanggal Mulai Kerja <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="start_date" id="start_date">
                        <small class="form-text text-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Wajib diisi untuk pelamar yang diterima.</strong> Data pegawai akan otomatis dibuat dengan posisi sesuai lowongan yang dilamar dan role user akan diperbarui.
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

<!-- Detail Pelamar Modal -->
<div class="modal fade" id="applicantDetailModal" tabindex="-1" aria-labelledby="applicantDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="applicantDetailModalLabel">
                    Detail Pelamar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Informasi Personal -->
                <div class="mb-4">
                    <h6 class="text-primary border-bottom pb-2 mb-3">Informasi Personal</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Nama Lengkap</label>
                            <div class="fw-bold" id="detail-name">-</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Email</label>
                            <div id="detail-email">-</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Nomor Telepon</label>
                            <div id="detail-phone">-</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">NIK</label>
                            <div id="detail-nik">-</div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label text-muted small">Alamat</label>
                            <div id="detail-alamat">-</div>
                        </div>
                    </div>
                </div>

                <!-- Informasi Pendidikan & Status -->
                <div class="mb-4">
                    <h6 class="text-primary border-bottom pb-2 mb-3">Informasi Pendidikan & Status</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Pendidikan Terakhir</label>
                            <div id="detail-pendidikan">-</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Status Seleksi</label>
                            <div id="detail-status-seleksi">-</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Tanggal Melamar</label>
                            <div id="detail-created-at">-</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
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

    // Handle document status change to show/hide interview preview fields
    document.getElementById('document_status').addEventListener('change', function() {
        const interviewFields = document.getElementById('interviewScheduleFields');
        const tanggalField = document.getElementById('tanggal_wawancara');
        const lokasiField = document.getElementById('lokasi_wawancara');

        if (this.value === 'accepted') {
            interviewFields.style.display = 'block';
            // Set default datetime to 3 days from now (PREVIEW ONLY)
            const defaultDate = new Date();
            defaultDate.setDate(defaultDate.getDate() + 3);
            defaultDate.setHours(10, 0); // Set to 10:00 AM
            tanggalField.value = defaultDate.toISOString().slice(0, 16);
            lokasiField.value = 'Ruang Meeting Klinik (akan dikonfirmasi)';
        } else {
            interviewFields.style.display = 'none';
            // Clear preview fields when hidden
            tanggalField.value = '';
            lokasiField.value = '';
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

    // Edit interview schedule modal handler
    document.querySelectorAll('.btn-edit-interview').forEach(button => {
        button.addEventListener('click', function() {
            const wawancaraId = this.dataset.wawancaraId;
            const applicationName = this.dataset.applicationName || 'Pelamar';
            const currentDate = this.dataset.currentDate;
            const currentLocation = this.dataset.currentLocation;
            const currentNotes = this.dataset.currentNotes || '';
            const form = document.getElementById('editInterviewForm');

            // Debug logging
            console.log('Edit interview button clicked');
            console.log('Button data:', {
                wawancaraId,
                applicationName,
                currentDate,
                currentLocation,
                currentNotes
            });

            // Update modal title
            const modalTitle = document.querySelector('#editInterviewModal .modal-title');
            modalTitle.innerHTML = `<i class="fas fa-edit"></i> Edit Jadwal Wawancara - ${applicationName}`;

            // Set form data
            form.dataset.wawancaraId = wawancaraId;

            // Pre-fill form dengan data saat ini
            const tanggalField = document.getElementById('edit_tanggal_wawancara');
            const lokasiField = document.getElementById('edit_lokasi_wawancara');
            const catatanField = document.getElementById('edit_catatan_wawancara');

            // Convert currentDate to datetime-local format jika ada
            if (currentDate) {
                // Assuming currentDate is in format like "2024-01-15 10:30:00"
                const dateObj = new Date(currentDate);
                if (!isNaN(dateObj.getTime())) {
                    // Convert to datetime-local format (YYYY-MM-DDTHH:MM)
                    const year = dateObj.getFullYear();
                    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                    const day = String(dateObj.getDate()).padStart(2, '0');
                    const hours = String(dateObj.getHours()).padStart(2, '0');
                    const minutes = String(dateObj.getMinutes()).padStart(2, '0');
                    tanggalField.value = `${year}-${month}-${day}T${hours}:${minutes}`;
                }
            }

            if (currentLocation) lokasiField.value = currentLocation;
            if (currentNotes) catatanField.value = currentNotes;

            console.log('Edit interview modal opened for wawancara:', wawancaraId);
        });
    });

    // Handle edit interview form submission
    document.getElementById('editInterviewForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const wawancaraId = this.dataset.wawancaraId;
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;

        console.log('Edit interview form submitted for wawancara:', wawancaraId);

        if (!wawancaraId) {
            alert('Error: Wawancara ID tidak ditemukan. Silakan refresh halaman dan coba lagi.');
            return;
        }

        // Show loading state
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';
        submitButton.disabled = true;

        // Send PUT request to update interview schedule
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
                catatan: formData.get('catatan') || null
            })
        })
        .then(response => {
            console.log('Edit interview API response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Edit interview API response data:', data);
            if (data.status === 'success') {
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('editInterviewModal'));
                modal.hide();

                // Show success message
                alert('Jadwal wawancara berhasil diperbarui!');

                // Reload page to update data
                window.location.reload();
            } else {
                throw new Error(data.message || 'Gagal memperbarui jadwal wawancara');
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

                // Jika keputusan final adalah "accepted", buat data pegawai (wajib ada tanggal mulai kerja)
                if (finalStatus === 'accepted') {
                    const startDate = formData.get('start_date');

                    if (!startDate) {
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('finalModal'));
                        modal.hide();

                        alert('Tanggal mulai kerja wajib diisi untuk pelamar yang diterima!');
                        window.location.reload();
                        return;
                    }

                    console.log('Creating employee for accepted application...');

                    // Call API untuk membuat data pegawai dan update role user
                    fetch(`{{ url('/api/recruitments/applications/') }}/${applicationId}/create-employee`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            final_status: 'accepted',
                            start_date: startDate,
                            user_id: userId,
                            application_id: applicationId,
                            recruitment_id: {{ $recruitment->id }}
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
                            const employeeNip = employeeData.data?.nip || '';
                            successMessage += ` Pelamar berhasil diterima sebagai pegawai pada posisi "${positionName}"`;
                            if (employeeNip) successMessage += ` dengan NIP: ${employeeNip}`;
                            successMessage += `. Role user telah diperbarui menjadi "${newRole}".`;
                        } else if (employeeData.status === 'error') {
                            // Jika error karena sudah ada pegawai, beri pesan yang ramah
                            if (employeeData.message && (
                                employeeData.message.includes('sudah terdaftar sebagai pegawai') ||
                                employeeData.message.includes('Validation error') ||
                                employeeData.message.includes('unique constraint') ||
                                employeeData.message.includes('already exists')
                            )) {
                                successMessage += ' Catatan: User ini sudah terdaftar sebagai pegawai sebelumnya.';
                            } else {
                                successMessage += ' Namun terjadi masalah saat membuat data pegawai: ' + employeeData.message;
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
/* Style untuk tampilan admin/HRD yang simpel */
.nav-tabs-simple .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    background: none;
    color: #6c757d;
    font-weight: 500;
}

.nav-tabs-simple .nav-link.active {
    border-bottom-color: #0d6efd;
    color: #0d6efd;
    background: none;
}

.nav-tabs-simple .nav-link:hover {
    border-bottom-color: #0d6efd;
    color: #0d6efd;
}

.card {
    border-radius: 8px;
}

/* Mengurangi efek animasi */
.modal {
    --bs-modal-transition: none;
}

.tab-content {
    animation: none;
}

/* Style yang lebih profesional untuk dropdown */
.dropdown-menu {
    border: 1px solid #dee2e6;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    transition: none;
}

.dropdown-toggle {
    transition: none;
}

.dropdown-toggle:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

/* Mengurangi shadow yang berlebihan */
.shadow {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

.shadow-sm {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.05) !important;
}

/* Style untuk form yang lebih clean */
.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.25rem;
}

.form-control, .form-select {
    border: 1px solid #ced4da;
    border-radius: 4px;
}

.form-control:focus, .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>
@endpush
