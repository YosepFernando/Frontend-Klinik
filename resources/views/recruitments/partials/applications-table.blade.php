<div class="table-responsive mt-3">
    @if($applications->count() > 0)
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pelamar</th>
                    <th>Info Personal</th>
                    <th>Tanggal Apply</th>

                    @if(!isset($stage) || $stage === 'all' || isset($showAll))
                        {{-- Tab Semua: Tampilkan semua kolom status --}}
                        <th>Status Dokumen</th>
                        <th>Status Interview</th>
                        <th>Status Final</th>
                    @elseif($stage === 'document')
                        {{-- Tab Seleksi Berkas: Hanya tampilkan status dokumen --}}
                        <th>Status Dokumen</th>
                    @elseif($stage === 'interview')
                        {{-- Tab Interview: Hanya tampilkan status interview --}}
                        <th>Status Interview</th>
                    @elseif($stage === 'final')
                        {{-- Tab Hasil Seleksi: Hanya tampilkan status final --}}
                        <th>Status Final</th>
                    @endif

                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $index => $application)
                @php
                    // Define status variables globally for use in conditions
                    $docStatus = $application->document_status ?? 'pending';

                    // Interview status - check multiple possible field names untuk status interview
                    $intStatus = $application->interview_status ??
                                 $application->status ??
                                 $application->wawancara_status ??
                                 $application->status_wawancara ??
                                 'not_scheduled';

                    // Final status - PERBAIKAN: Hanya tampilkan status final jika benar-benar ada data hasil seleksi
                    $finalStatus = null;
                    $hasSelectionResult = false;

                    // Prioritas pengecekan status final HANYA dari field hasil seleksi yang valid:
                    // 1. Dari nested object selection_result (data API hasil seleksi)
                    if (isset($application->selection_result) && $application->selection_result) {
                        $hasSelectionResult = true;
                        if (is_array($application->selection_result)) {
                            $finalStatus = $application->selection_result['status'] ?? null;
                        } elseif (is_object($application->selection_result)) {
                            $finalStatus = $application->selection_result->status ?? null;
                        }
                    }
                    // 2. Dari field final_status HANYA jika ada indikasi data hasil seleksi
                    elseif (isset($application->final_status) && !empty($application->final_status) &&
                            (isset($application->hasil_seleksi_id) || isset($application->selection_result_id))) {
                        $finalStatus = $application->final_status;
                        $hasSelectionResult = true;
                    }
                    // 3. Dari field hasil_status atau status_hasil_seleksi (data API hasil seleksi)
                    elseif (isset($application->hasil_status) && !empty($application->hasil_status)) {
                        $finalStatus = $application->hasil_status;
                        $hasSelectionResult = true;
                    }
                    elseif (isset($application->status_hasil_seleksi) && !empty($application->status_hasil_seleksi)) {
                        $finalStatus = $application->status_hasil_seleksi;
                        $hasSelectionResult = true;
                    }
                    // 4. Dari field status_final atau hasil_seleksi_status (data API hasil seleksi)
                    elseif (isset($application->status_final) && !empty($application->status_final) &&
                            isset($application->hasil_seleksi_created_at)) {
                        $finalStatus = $application->status_final;
                        $hasSelectionResult = true;
                    }
                    elseif (isset($application->hasil_seleksi_status) && !empty($application->hasil_seleksi_status)) {
                        $finalStatus = $application->hasil_seleksi_status;
                        $hasSelectionResult = true;
                    }

                    // TIDAK ada fallback ke field status umum - hindari false positive
                    // Field status umum dari lamaran TIDAK dianggap sebagai status final

                    // Debug information (uncomment untuk troubleshooting)
                    // if ($application->id === 'TARGET_ID') { // Ganti dengan ID yang bermasalah
                    //     echo "<!-- DEBUG: Application ID: " . ($application->id ?? 'N/A') . " -->";
                    //     echo "<!-- Final Status: " . ($finalStatus ?? 'NULL') . " -->";
                    //     echo "<!-- Has Selection Result: " . ($hasSelectionResult ? 'true' : 'false') . " -->";
                    //     echo "<!-- Available Fields: " . json_encode(array_keys((array)$application)) . " -->";
                    // }
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <div>
                            <strong>{{ $application->name }}</strong><br>
                            <small class="text-muted">{{ $application->email }}</small>
                        </div>
                    </td>
                    <td>
                        <div class="small">
                            @if(isset($application->phone) && $application->phone && $application->phone !== 'Tidak diketahui')
                                <div><i class="fas fa-phone"></i> {{ $application->phone }}</div>
                            @endif
                            @if(isset($application->nik) && $application->nik)
                                <div><i class="fas fa-id-card"></i> NIK: {{ $application->nik }}</div>
                            @endif
                            @if(isset($application->pendidikan) && $application->pendidikan)
                                <div><i class="fas fa-graduation-cap"></i> {{ $application->pendidikan }}</div>
                            @endif
                        </div>
                    </td>
                    <td>{{ $application->created_at ? $application->created_at->format('d M Y H:i') : 'Tidak diketahui' }}</td>

                    @if(!isset($stage) || $stage === 'all' || isset($showAll))
                        {{-- Tab Semua: Tampilkan semua kolom status --}}
                        {{-- Status Dokumen --}}
                        <td>
                            @if($docStatus === 'pending' || $docStatus === 'menunggu')
                                <span class="badge bg-warning">⏳ Menunggu Review</span>
                                {{-- Tampilkan detail hanya jika masih pending --}}
                                @if(isset($application->document_notes) && $application->document_notes)
                                    <br><small class="text-muted">💬 {{ Str::limit($application->document_notes, 40) }}</small>
                                @endif
                            @elseif($docStatus === 'accepted' || $docStatus === 'diterima')
                                <span class="badge bg-success">✅ Diterima</span>
                                {{-- Jika sudah diterima, hanya tampilkan pesan sukses singkat --}}
                                <br><small class="text-success"><i class="fas fa-check-circle"></i> Berkas telah diterima</small>
                            @elseif($docStatus === 'rejected' || $docStatus === 'ditolak')
                                <span class="badge bg-danger">❌ Ditolak</span>
                                {{-- Tampilkan catatan untuk yang ditolak --}}
                                @if(isset($application->document_notes) && $application->document_notes)
                                    <br><small class="text-muted">💬 {{ Str::limit($application->document_notes, 40) }}</small>
                                @endif
                            @else
                                <span class="badge bg-secondary">📄 Belum Review</span>
                            @endif
                        </td>

                        {{-- Status Interview --}}
                        <td>
                            @if($docStatus !== 'accepted' && $docStatus !== 'diterima')
                                {{-- Jika dokumen belum diterima, interview belum bisa dijadwal --}}
                                <span class="badge bg-secondary">📄 Menunggu Review Dokumen</span>
                                <br><small class="text-muted">Interview dapat dijadwalkan setelah dokumen diterima</small>
                            @elseif($intStatus === 'not_scheduled' || $intStatus === 'belum_dijadwal')
                                <span class="badge bg-warning">📅 Belum Dijadwal</span>
                                <br><small class="text-muted">Dokumen telah diterima, siap untuk dijadwal interview</small>
                            @elseif($intStatus === 'scheduled' || $intStatus === 'terjadwal' || $intStatus === 'dijadwalkan' || $intStatus === 'pending')
                                <span class="badge bg-info">⏰ Terjadwal</span>
                                {{-- Tampilkan detail hanya jika masih dijadwal/pending --}}
                                @if(isset($application->interview_date) && $application->interview_date)
                                    <br><small class="text-muted">📅 {{ \Carbon\Carbon::parse($application->interview_date)->format('d M Y H:i') }}</small>
                                @endif
                                @if(isset($application->interview_location) && $application->interview_location)
                                    <br><small class="text-muted">📍 {{ Str::limit($application->interview_location, 25) }}</small>
                                @endif
                            @elseif($intStatus === 'lulus' || $intStatus === 'passed' || $intStatus === 'diterima')
                                <span class="badge bg-success">✅ Lulus</span>
                                {{-- Jika sudah lulus, hanya tampilkan pesan sukses singkat --}}
                                <br><small class="text-success"><i class="fas fa-check-circle"></i> Interview berhasil</small>
                            @elseif($intStatus === 'tidak_lulus' || $intStatus === 'tidak lulus' || $intStatus === 'ditolak' || $intStatus === 'failed')
                                <span class="badge bg-danger">❌ Tidak Lulus</span>
                            @else
                                <span class="badge bg-light text-dark">📋 Belum Ada Data</span>
                            @endif
                        </td>

                        {{-- Status Final --}}
                        <td>
                            @if($finalStatus && (strtolower($finalStatus) === 'pending' || strtolower($finalStatus) === 'menunggu'))
                                <span class="badge bg-warning">⏳ Menunggu</span>
                            @elseif($finalStatus && (strtolower($finalStatus) === 'accepted' || strtolower($finalStatus) === 'diterima'))
                                <span class="badge bg-success">✅ Diterima</span>
                                @if(isset($application->start_date) && $application->start_date)
                                    <br><small class="text-muted">🕒 Mulai: {{ \Carbon\Carbon::parse($application->start_date)->format('d M Y') }}</small>
                                @endif
                            @elseif($finalStatus && (strtolower($finalStatus) === 'rejected' || strtolower($finalStatus) === 'ditolak'))
                                <span class="badge bg-danger">❌ Ditolak</span>
                            @elseif($finalStatus && (strtolower($finalStatus) === 'waiting_list' || strtolower($finalStatus) === 'pending_final'))
                                <span class="badge bg-info">📋 Waiting List</span>
                            @elseif($finalStatus)
                                {{-- Jika ada status final tapi belum dikenali, tampilkan status aslinya --}}
                                <span class="badge bg-warning">⚠️ {{ ucfirst($finalStatus) }}</span>
                                <br><small class="text-muted">Status: {{ $finalStatus }}</small>
                            @else
                                <span class="badge bg-light text-dark">📋 Belum Diproses</span>
                            @endif
                        </td>

                    @elseif($stage === 'document')
                        {{-- Tab Seleksi Berkas: Hanya tampilkan status dokumen --}}
                        <td>
                            @if($docStatus === 'pending' || $docStatus === 'menunggu')
                                <span class="badge bg-warning">⏳ Menunggu Review</span>
                                {{-- Tampilkan detail hanya jika masih pending --}}
                                @if(isset($application->document_notes) && $application->document_notes)
                                    <br><small class="text-muted">💬 {{ Str::limit($application->document_notes, 40) }}</small>
                                @endif
                            @elseif($docStatus === 'accepted' || $docStatus === 'diterima')
                                <span class="badge bg-success">✅ Diterima</span>
                                {{-- Jika sudah diterima, hanya tampilkan pesan sukses singkat --}}
                                <br><small class="text-success"><i class="fas fa-check-circle"></i> Berkas telah diterima</small>
                            @elseif($docStatus === 'rejected' || $docStatus === 'ditolak')
                                <span class="badge bg-danger">❌ Ditolak</span>
                                {{-- Tampilkan catatan untuk yang ditolak --}}
                                @if(isset($application->document_notes) && $application->document_notes)
                                    <br><small class="text-muted">💬 {{ Str::limit($application->document_notes, 40) }}</small>
                                @endif
                            @else
                                <span class="badge bg-secondary">📄 Belum Review</span>
                            @endif

                            {{-- Tampilkan sumber data untuk debugging --}}
                            @if(isset($application->data_source))
                                <br><small class="text-info">📊 {{ ucfirst(str_replace('_', ' ', $application->data_source)) }}</small>
                            @endif
                        </td>

                    @elseif($stage === 'interview')
                        {{-- Tab Interview: Hanya tampilkan status interview --}}
                        <td>
                            @if($docStatus !== 'accepted' && $docStatus !== 'diterima')
                                {{-- Jika dokumen belum diterima, interview belum bisa dijadwal --}}
                                <span class="badge bg-secondary">📄 Menunggu Review Dokumen</span>
                                <br><small class="text-muted">Interview dapat dijadwalkan setelah dokumen diterima</small>
                            @elseif($intStatus === 'not_scheduled' || $intStatus === 'belum_dijadwal')
                                <span class="badge bg-warning">📅 Belum Dijadwal</span>
                                <br><small class="text-muted">Dokumen telah diterima, siap untuk dijadwal interview</small>
                            @elseif($intStatus === 'scheduled' || $intStatus === 'terjadwal' || $intStatus === 'dijadwalkan' || $intStatus === 'pending')
                                <span class="badge bg-info">⏰ Terjadwal</span>
                                {{-- Tampilkan detail hanya jika masih dijadwal/pending --}}
                                @if(isset($application->interview_date) && $application->interview_date)
                                    <br><small class="text-muted">📅 {{ \Carbon\Carbon::parse($application->interview_date)->format('d M Y H:i') }}</small>
                                @endif
                                @if(isset($application->interview_location) && $application->interview_location)
                                    <br><small class="text-muted">📍 {{ Str::limit($application->interview_location, 25) }}</small>
                                @endif
                            @elseif($intStatus === 'lulus' || $intStatus === 'passed')
                                <span class="badge bg-success">✅ Lulus</span>
                                {{-- Jika sudah lulus, hanya tampilkan pesan sukses singkat --}}
                                <br><small class="text-success"><i class="fas fa-check-circle"></i> Interview berhasil</small>
                            @elseif($intStatus === 'tidak_lulus' || $intStatus === 'ditolak' || $intStatus === 'failed')
                                <span class="badge bg-danger">❌ Tidak Lulus</span>
                                {{-- Tampilkan catatan untuk yang tidak lulus --}}
                                @if(isset($application->interview_notes) && $application->interview_notes)
                                    <br><small class="text-muted">💬 {{ Str::limit($application->interview_notes, 40) }}</small>
                                @endif
                            @else
                                <span class="badge bg-light text-dark">📋 Belum Ada Data</span>
                            @endif
                        </td>

                    @elseif($stage === 'final')
                        {{-- Tab Hasil Seleksi: Hanya tampilkan status final --}}
                        <td>
                            @if($finalStatus && (strtolower($finalStatus) === 'pending' || strtolower($finalStatus) === 'menunggu'))
                                <span class="badge bg-warning">⏳ Menunggu</span>
                            @elseif($finalStatus && (strtolower($finalStatus) === 'accepted' || strtolower($finalStatus) === 'diterima'))
                                <span class="badge bg-success">✅ Diterima</span>
                                @if(isset($application->start_date) && $application->start_date)
                                    <br><small class="text-muted">🕒 Mulai: {{ \Carbon\Carbon::parse($application->start_date)->format('d M Y') }}</small>
                                @endif
                            @elseif($finalStatus && (strtolower($finalStatus) === 'rejected' || strtolower($finalStatus) === 'ditolak'))
                                <span class="badge bg-danger">❌ Ditolak</span>
                            @elseif($finalStatus && (strtolower($finalStatus) === 'waiting_list' || strtolower($finalStatus) === 'pending_final'))
                                <span class="badge bg-info">📋 Waiting List</span>
                            @elseif($finalStatus)
                                {{-- Jika ada status final tapi belum dikenali, tampilkan status aslinya --}}
                                <span class="badge bg-warning">⚠️ {{ ucfirst($finalStatus) }}</span>
                                <br><small class="text-muted">Status: {{ $finalStatus }}</small>
                            @else
                                <span class="badge bg-light text-dark">📋 Belum Diproses</span>
                            @endif

                            {{-- Tampilkan informasi sumber data dan catatan --}}
                            @if($hasSelectionResult)
                                {{-- Data dari API hasil seleksi --}}
                                @if(isset($application->selection_result['catatan']) && $application->selection_result['catatan'])
                                    <br><small class="text-info">💬 {{ Str::limit($application->selection_result['catatan'], 40) }}</small>
                                @endif
                                @if(isset($application->selection_result['updated_at']))
                                    <br><small class="text-muted">
                                        <i class="fas fa-calendar-alt"></i>
                                        {{ \Carbon\Carbon::parse($application->selection_result['updated_at'])->format('d M Y H:i') }}
                                    </small>
                                @endif
                            @elseif($finalStatus)
                                {{-- Ada final status tapi tidak ada hasil seleksi yang valid --}}
                                <br><small class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                    Status tidak konsisten - hasil belum dicatat di sistem seleksi
                                </small>
                                @if(isset($application->final_notes) && $application->final_notes)
                                    <br><small class="text-info">💬 {{ Str::limit($application->final_notes, 40) }}</small>
                                @endif
                            @endif

                            {{-- Tampilkan info sumber data untuk admin --}}
                            <br><small class="text-muted">
                                <i class="fas fa-database"></i>
                                @if($hasSelectionResult)
                                    Hasil Seleksi API
                                @else
                                    Data Lamaran
                                @endif
                            </small>
                        </td>
                    @endif

                    <td>
                        <div class="btn-group-vertical" role="group">
                            <!-- Document Actions - Hanya tampil di tab document atau showAll -->
                            @if(($docStatus === 'pending' || $docStatus === 'menunggu') &&
                                (!isset($stage) || $stage === 'document' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-primary btn-document-review mb-1"
                                        data-bs-toggle="modal" data-bs-target="#documentModal"
                                        data-application-id="{{ $application->id }}"
                                        data-application-name="{{ $application->name }}"
                                        data-user-id="{{ $application->user_id ?? '' }}">
                                    <i class="fas fa-file-alt"></i> Review Dokumen
                                </button>
                            @endif

                            <!-- CV Actions - Available for Admin/HRD -->
                            @if(isset($application->id))
                                <div class="btn-group mb-1" role="group">
                                    @if(isset($application->cv_info) && $application->cv_info && $application->cv_info['has_cv'])
                                        <a href="{{ config('app.api_url') }}/lamaran/{{ $application->id }}/download-cv"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-success"
                                           title="Download CV ({{ $application->cv_info['cv_size_formatted'] ?? '' }})">
                                            <i class="fas fa-download"></i> Download CV
                                        </a>
                                        <a href="{{ config('app.api_url') }}/lamaran/{{ $application->id }}/view-cv"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-info"
                                           title="Lihat CV">
                                            <i class="fas fa-eye"></i> Lihat CV
                                        </a>
                                    @else
                                        <span class="btn btn-sm btn-outline-secondary disabled" title="CV tidak tersedia">
                                            <i class="fas fa-file-excel"></i> Tidak ada CV
                                        </span>
                                    @endif
                                </div>
                            @endif

                            <!-- Interview Actions - Hanya tampil di tab interview atau showAll -->
                            @if(($docStatus === 'accepted' || $docStatus === 'diterima') &&
                                ($intStatus === 'not_scheduled' || $intStatus === 'belum_dijadwal') &&
                                (!isset($stage) || $stage === 'interview' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-info btn-schedule-interview mb-1"
                                        data-bs-toggle="modal" data-bs-target="#interviewModal"
                                        data-application-id="{{ $application->id }}"
                                        data-application-name="{{ $application->name }}"
                                        data-user-id="{{ $application->user_id ?? '' }}">
                                    <i class="fas fa-calendar"></i> Jadwal Interview
                                </button>
                            @endif

                            @if(($docStatus === 'accepted' || $docStatus === 'diterima') &&
                                ($intStatus === 'scheduled' || $intStatus === 'terjadwal' || $intStatus === 'dijadwalkan' || $intStatus === 'pending') &&
                                (!isset($stage) || $stage === 'interview' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-success btn-interview-result mb-1"
                                        data-bs-toggle="modal" data-bs-target="#interviewResultModal"
                                        data-application-id="{{ $application->id }}"
                                        data-application-name="{{ $application->name }}"
                                        data-user-id="{{ $application->user_id ?? '' }}"
                                        data-wawancara-id="{{ $application->interview_id ?? $application->wawancara_id ?? $application->id_wawancara ?? '' }}">
                                    <i class="fas fa-check"></i> Input Hasil
                                </button>

                                <!-- Button Edit Jadwal Wawancara -->
                                <button type="button" class="btn btn-sm btn-outline-warning btn-edit-interview mb-1"
                                        data-bs-toggle="modal" data-bs-target="#editInterviewModal"
                                        data-application-name="{{ $application->name }}"
                                        data-wawancara-id="{{ $application->interview_id ?? $application->wawancara_id ?? $application->id_wawancara ?? '' }}"
                                        data-current-date="{{ $application->tanggal_wawancara ?? $application->interview_date ?? '' }}"
                                        data-current-location="{{ $application->lokasi ?? $application->location ?? '' }}"
                                        data-current-notes="{{ $application->catatan ?? $application->notes ?? '' }}">
                                    <i class="fas fa-edit"></i> Edit Jadwal
                                </button>
                            @endif

                            <!-- Final Decision Actions - Hanya tampil di tab final atau showAll -->
                            @if(($intStatus === 'lulus' || $intStatus === 'passed') &&
                                (!$finalStatus || $finalStatus === 'pending' || $finalStatus === 'menunggu') &&
                                (!isset($stage) || $stage === 'final' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-warning btn-final-decision mb-1"
                                        data-bs-toggle="modal" data-bs-target="#finalModal"
                                        data-application-id="{{ $application->id }}"
                                        data-application-name="{{ $application->name }}"
                                        data-user-id="{{ $application->user_id ?? '' }}">
                                    <i class="fas fa-gavel"></i> Keputusan Final
                                </button>
                            @endif

                            <!-- Tombol untuk membuat hasil seleksi hanya jika ada data inconsistent yang perlu diperbaiki -->
                            @if($finalStatus && !$hasSelectionResult &&
                                ($finalStatus === 'diterima' || $finalStatus === 'accepted' || $finalStatus === 'ditolak' || $finalStatus === 'rejected') &&
                                (!isset($stage) || $stage === 'final' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-warning btn-create-selection-result mb-1"
                                        data-bs-toggle="modal" data-bs-target="#finalModal"
                                        data-application-id="{{ $application->id }}"
                                        data-application-name="{{ $application->name }}"
                                        data-user-id="{{ $application->user_id ?? '' }}"
                                        data-current-status="{{ $finalStatus }}"
                                        title="Data tidak konsisten - catat ulang hasil seleksi">
                                    <i class="fas fa-exclamation-triangle"></i> Perbaiki Data
                                </button>
                            @endif

                            <!-- Edit hasil seleksi jika sudah ada di API -->
                            @if(isset($application->selection_result) && $application->selection_result &&
                                (!isset($stage) || $stage === 'final' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-primary btn-edit-selection-result mb-1"
                                        data-bs-toggle="modal" data-bs-target="#finalModal"
                                        data-application-id="{{ $application->id }}"
                                        data-application-name="{{ $application->name }}"
                                        data-user-id="{{ $application->user_id ?? '' }}"
                                        data-hasil-seleksi-id="{{ $application->selection_result['id'] ?? '' }}"
                                        data-current-status="{{ $application->selection_result['status'] ?? 'pending' }}"
                                        data-current-notes="{{ $application->selection_result['catatan'] ?? '' }}"
                                        data-is-edit="true"
                                        title="Edit hasil seleksi">
                                    <i class="fas fa-edit"></i> Edit Hasil Seleksi
                                </button>
                            @endif

                            <!-- Informasi Stage untuk tab tertentu -->
                            @if(isset($stage) && isset($application->stage))
                                <small class="text-muted mt-1">
                                    <i class="fas fa-layer-group"></i>
                                    Stage: {{ ucfirst($application->stage) }}
                                </small>
                            @endif

                            <!-- View Details - Selalu tampil -->
                            <div class="btn-group mt-1" role="group">
                                <!-- Detail Pelamar Button -->
                                <button type="button"
                                        class="btn btn-sm btn-primary btn-detail-applicant"
                                        data-bs-toggle="modal"
                                        data-bs-target="#applicantDetailModal"
                                        data-name="{{ $application->name }}"
                                        data-email="{{ $application->email }}"
                                        data-phone="{{ $application->phone ?? 'Tidak tersedia' }}"
                                        data-nik="{{ $application->nik ?? 'Tidak tersedia' }}"
                                        data-alamat="{{ $application->alamat ?? 'Tidak tersedia' }}"
                                        data-pendidikan="{{ $application->pendidikan ?? 'Tidak tersedia' }}"
                                        data-status-seleksi="{{ $application->status_seleksi ?? 'Menunggu review' }}"
                                        data-created-at="{{ $application->created_at ? $application->created_at->format('d M Y H:i') : 'Tidak diketahui' }}"
                                        title="Detail Pelamar">
                                    <i class="fas fa-user"></i> Detail
                                </button>

                                @if(isset($application->cv_path) && $application->cv_path)
                                    <a href="{{ $application->cv_path }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Lihat CV">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                @endif
                                @if(isset($application->cover_letter) && $application->cover_letter)
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary btn-cover-letter"
                                            data-cover-letter="{{ htmlspecialchars($application->cover_letter, ENT_QUOTES, 'UTF-8') }}"
                                            title="Lihat Cover Letter">
                                        <i class="fas fa-file-alt"></i>
                                    </button>
                                @endif
                            </div>

                            <!-- Dropdown version (sebagai backup) -->
                            <div class="dropdown mt-1" style="display: none;">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        type="button"
                                        id="dropdownMenuButton{{ $application->id }}"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton{{ $application->id }}">
                                    @if(isset($application->cv_path) && $application->cv_path)
                                        <li><a class="dropdown-item" href="{{ $application->cv_path }}" target="_blank">
                                            <i class="fas fa-file-pdf"></i> Lihat CV
                                        </a></li>
                                    @endif
                                    @if(isset($application->cover_letter) && $application->cover_letter)
                                        <li><a class="dropdown-item btn-cover-letter"
                                               href="#"
                                               data-cover-letter="{{ htmlspecialchars($application->cover_letter, ENT_QUOTES, 'UTF-8') }}">
                                            <i class="fas fa-file-alt"></i> Lihat Cover Letter
                                        </a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="mailto:{{ $application->email }}">
                                        <i class="fas fa-envelope"></i> Kirim Email
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="text-center py-4">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">
                @if(isset($stage))
                    Tidak ada aplikasi untuk tahap ini.
                @else
                    Belum ada aplikasi untuk lowongan ini.
                @endif
            </p>
        </div>
    @endif
</div>
