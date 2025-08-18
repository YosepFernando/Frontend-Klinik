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
                    $docStatus = $application->document_status ?? $application->status ?? 'pending';
                    $intStatus = $application->interview_status ?? $application->status ?? 'not_scheduled';
                    
                    // Mapping status final yang konsisten
                    $hasSelectionResult = isset($application->selection_result) && $application->selection_result;
                    if ($hasSelectionResult) {
                        $finalStatus = $application->selection_result['status'] ?? 'pending';
                    } else {
                        $finalStatus = $application->final_status ?? $application->status ?? 'pending';
                    }
                    
                    // PERBAIKAN: Jika final status sudah diterima, interview otomatis dianggap lulus
                    if (($finalStatus === 'diterima' || $finalStatus === 'accepted') && 
                        ($intStatus === 'scheduled' || $intStatus === 'terjadwal' || $intStatus === 'pending')) {
                        $intStatus = 'passed'; // Otomatis ubah ke passed jika final sudah diterima
                        if (config('app.debug')) {
                            \Log::info("Auto-corrected interview status to 'passed' for {$application->name} because final status is 'diterima'");
                        }
                    }
                    
                    // Filter logic berdasarkan stage
                    $shouldSkip = false;
                    
                    // Tab Seleksi Berkas: Sembunyikan yang sudah diterima (sudah lulus ke tahap selanjutnya)
                    if (isset($stage) && $stage === 'document') {
                        if ($docStatus === 'accepted' || $docStatus === 'diterima') {
                            $shouldSkip = true; // Jangan tampilkan yang sudah diterima di tab berkas
                        }
                    }
                    
                    // Tab Interview: Sembunyikan yang sudah lulus interview (sudah lulus ke tahap final)
                    if (isset($stage) && $stage === 'interview') {
                        // Jika interview sudah lulus (entah dari API atau karena final sudah diterima)
                        if ($intStatus === 'lulus' || $intStatus === 'passed' || 
                            (($finalStatus === 'diterima' || $finalStatus === 'accepted') && 
                             ($intStatus === 'scheduled' || $intStatus === 'terjadwal' || $intStatus === 'pending'))) {
                            $shouldSkip = true; // Jangan tampilkan yang sudah lulus interview di tab interview
                        }
                        // Juga jangan tampilkan yang dokumennya belum diterima
                        if ($docStatus !== 'accepted' && $docStatus !== 'diterima') {
                            $shouldSkip = true; // Hanya tampilkan yang dokumennya sudah diterima
                        }
                    }
                    
                    // Tab Final: HANYA tampilkan data yang berasal dari API Hasil Seleksi (bukan lamaran atau status lain)
                    if (isset($stage) && $stage === 'final') {
                        // Filter yang lebih permisif: Tampilkan jika ada salah satu dari indikator hasil seleksi
                        $isFromSelectionAPI = isset($application->data_source) && $application->data_source === 'hasil_seleksi_api';
                        $hasSelectionResult = isset($application->selection_result) && $application->selection_result;
                        $hasResultId = isset($application->hasil_seleksi_id) && $application->hasil_seleksi_id;
                        $hasFinalStatus = isset($application->final_status) && $application->final_status;
                        
                        // Debug output untuk melihat data aplikasi
                        if (config('app.debug')) {
                            \Log::info("Tab Final - Debug aplikasi ID {$application->id}:", [
                                'name' => $application->name ?? 'No name',
                                'data_source' => $application->data_source ?? 'No data_source',
                                'has_selection_result' => $hasSelectionResult,
                                'has_result_id' => $hasResultId,
                                'has_final_status' => $hasFinalStatus,
                                'final_status' => $application->final_status ?? 'No final_status'
                            ]);
                        }
                        
                        // Skip HANYA jika tidak ada satupun indikator hasil seleksi
                        if (!$isFromSelectionAPI && !$hasSelectionResult && !$hasResultId && !$hasFinalStatus) {
                            $shouldSkip = true; // Hanya tampilkan data yang memiliki indikator hasil seleksi
                            if (config('app.debug')) {
                                \Log::info("Tab Final - Skipping aplikasi ID {$application->id} karena tidak ada indikator hasil seleksi");
                            }
                        } else {
                            if (config('app.debug')) {
                                \Log::info("Tab Final - Menampilkan aplikasi ID {$application->id} dengan final_status: " . ($application->final_status ?? 'undefined'));
                            }
                        }
                    }
                @endphp
                
                @if(!$shouldSkip)
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
                            @php
                                // Untuk display, jika final sudah diterima dan interview masih terjadwal, tampilkan sebagai lulus
                                $displayIntStatus = $intStatus;
                                if (($finalStatus === 'diterima' || $finalStatus === 'accepted') && 
                                    ($intStatus === 'scheduled' || $intStatus === 'terjadwal' || $intStatus === 'pending')) {
                                    $displayIntStatus = 'passed';
                                }
                            @endphp
                            
                            @if($displayIntStatus === 'not_scheduled' || $displayIntStatus === 'belum_dijadwal')
                                <span class="badge bg-secondary">📅 Belum Dijadwal</span>
                            @elseif($displayIntStatus === 'scheduled' || $displayIntStatus === 'terjadwal' || $displayIntStatus === 'pending')
                                <span class="badge bg-info">⏰ Terjadwal</span>
                                {{-- Tampilkan detail hanya jika masih dijadwal/pending --}}
                                @if(isset($application->interview_date) && $application->interview_date)
                                    <br><small class="text-muted">📅 {{ \Carbon\Carbon::parse($application->interview_date)->format('d M Y H:i') }}</small>
                                @endif
                                @if(isset($application->interview_location) && $application->interview_location)
                                    <br><small class="text-muted">📍 {{ Str::limit($application->interview_location, 25) }}</small>
                                @endif
                            @elseif($displayIntStatus === 'lulus' || $displayIntStatus === 'passed')
                                <span class="badge bg-success">✅ Lulus</span>
                                {{-- Jika sudah lulus, tampilkan pesan sukses --}}
                                <br><small class="text-success"><i class="fas fa-check-circle"></i> 
                                    @if(($finalStatus === 'diterima' || $finalStatus === 'accepted') && 
                                        ($intStatus === 'scheduled' || $intStatus === 'terjadwal' || $intStatus === 'pending'))
                                        Lulus (final diterima)
                                    @else
                                        Interview berhasil
                                    @endif
                                </small>
                            @elseif($displayIntStatus === 'tidak_lulus' || $displayIntStatus === 'ditolak' || $displayIntStatus === 'failed')
                                <span class="badge bg-danger">❌ Tidak Lulus</span>
                            @else
                                <span class="badge bg-light text-dark">📋 Belum Ada Data</span>
                            @endif
                        </td>
                        
                        {{-- Status Final --}}
                        <td>
                            @if($finalStatus === 'pending' || $finalStatus === 'menunggu')
                                <span class="badge bg-warning">⏳ Menunggu</span>
                            @elseif($finalStatus === 'accepted' || $finalStatus === 'diterima')
                                <span class="badge bg-success">✅ Diterima</span>
                                @if(isset($application->start_date) && $application->start_date)
                                    <br><small class="text-muted">🕒 Mulai: {{ \Carbon\Carbon::parse($application->start_date)->format('d M Y') }}</small>
                                @endif
                            @elseif($finalStatus === 'rejected' || $finalStatus === 'ditolak')
                                <span class="badge bg-danger">❌ Ditolak</span>
                            @elseif($finalStatus === 'waiting_list')
                                <span class="badge bg-info">📋 Waiting List</span>
                            @else
                                <span class="badge bg-light text-dark">📋 Belum Ada Data</span>
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
                            @php
                                // Untuk display di tab interview, jika final sudah diterima dan interview masih terjadwal, tampilkan sebagai lulus
                                $displayIntStatus = $intStatus;
                                if (($finalStatus === 'diterima' || $finalStatus === 'accepted') && 
                                    ($intStatus === 'scheduled' || $intStatus === 'terjadwal' || $intStatus === 'pending')) {
                                    $displayIntStatus = 'passed';
                                }
                            @endphp
                            
                            @if($displayIntStatus === 'not_scheduled' || $displayIntStatus === 'belum_dijadwal')
                                <span class="badge bg-secondary">📅 Belum Dijadwal</span>
                            @elseif($displayIntStatus === 'scheduled' || $displayIntStatus === 'terjadwal' || $displayIntStatus === 'pending')
                                <span class="badge bg-info">⏰ Terjadwal</span>
                                {{-- Tampilkan detail hanya jika masih dijadwal/pending --}}
                                @if(isset($application->interview_date) && $application->interview_date)
                                    <br><small class="text-muted">📅 {{ \Carbon\Carbon::parse($application->interview_date)->format('d M Y H:i') }}</small>
                                @endif
                                @if(isset($application->interview_location) && $application->interview_location)
                                    <br><small class="text-muted">📍 {{ Str::limit($application->interview_location, 25) }}</small>
                                @endif
                            @elseif($displayIntStatus === 'lulus' || $displayIntStatus === 'passed')
                                <span class="badge bg-success">✅ Lulus</span>
                                {{-- Jika sudah lulus, tampilkan pesan sukses --}}
                                <br><small class="text-success"><i class="fas fa-check-circle"></i> 
                                    @if(($finalStatus === 'diterima' || $finalStatus === 'accepted') && 
                                        ($intStatus === 'scheduled' || $intStatus === 'terjadwal' || $intStatus === 'pending'))
                                        Lulus (final diterima)
                                    @else
                                        Interview berhasil
                                    @endif
                                </small>
                            @elseif($displayIntStatus === 'tidak_lulus' || $displayIntStatus === 'ditolak' || $displayIntStatus === 'failed')
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
                        {{-- Tab Hasil Seleksi: Hanya tampilkan status final dari API Hasil Seleksi --}}
                        <td>
                            @if($finalStatus === 'pending' || $finalStatus === 'menunggu')
                                <span class="badge bg-warning">⏳ Menunggu</span>
                            @elseif($finalStatus === 'accepted' || $finalStatus === 'diterima')
                                <span class="badge bg-success">✅ Diterima</span>
                                @if(isset($application->start_date) && $application->start_date)
                                    <br><small class="text-muted">🕒 Mulai: {{ \Carbon\Carbon::parse($application->start_date)->format('d M Y') }}</small>
                                @endif
                            @elseif($finalStatus === 'rejected' || $finalStatus === 'ditolak')
                                <span class="badge bg-danger">❌ Ditolak</span>
                            @elseif($finalStatus === 'waiting_list')
                                <span class="badge bg-info">📋 Waiting List</span>
                            @else
                                <span class="badge bg-light text-dark">📋 Belum Ada Data</span>
                            @endif
                            
                            {{-- Tampilkan informasi sumber data dan catatan khusus dari API hasil seleksi --}}
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
                            @elseif(isset($application->hasil_seleksi_id) && $application->hasil_seleksi_id)
                                {{-- Data dari API hasil seleksi (via ID) --}}
                                @if(isset($application->final_notes) && $application->final_notes)
                                    <br><small class="text-info">💬 {{ Str::limit($application->final_notes, 40) }}</small>
                                @endif
                            @else
                                {{-- Data bukan dari API hasil seleksi yang autentik --}}
                                <br><small class="text-warning">
                                    <i class="fas fa-exclamation-triangle"></i> 
                                    Belum tercatat di sistem hasil seleksi
                                </small>
                            @endif
                            
                            {{-- Tampilkan indikator sumber data yang jelas --}}
                            <br><small class="text-muted">
                                <i class="fas fa-database"></i> 
                                @if($hasSelectionResult || (isset($application->hasil_seleksi_id) && $application->hasil_seleksi_id))
                                    <span class="text-success">API Hasil Seleksi</span>
                                @else
                                    <span class="text-warning">Data Sementara</span>
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

                            @if(($intStatus === 'scheduled' || $intStatus === 'terjadwal' || $intStatus === 'pending') && 
                                (!isset($stage) || $stage === 'interview' || isset($showAll)))
                                <!-- Edit Interview Schedule Button -->
                                <button type="button" class="btn btn-sm btn-outline-warning btn-edit-interview mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#editInterviewModal" 
                                        data-application-id="{{ $application->id }}"
                                        data-application-name="{{ $application->name }}"
                                        data-user-id="{{ $application->user_id ?? '' }}"
                                        data-wawancara-id="{{ $application->interview_id ?? $application->wawancara_id ?? $application->id_wawancara ?? '' }}"
                                        data-current-date="{{ $application->interview_date ?? '' }}"
                                        data-current-location="{{ $application->interview_location ?? '' }}"
                                        data-current-notes="{{ $application->interview_notes ?? '' }}">
                                    <i class="fas fa-edit"></i> Edit Jadwal
                                </button>
                                
                                <!-- Input Interview Result Button -->
                                <button type="button" class="btn btn-sm btn-outline-success btn-interview-result mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#interviewResultModal" 
                                        data-application-id="{{ $application->id }}"
                                        data-application-name="{{ $application->name }}"
                                        data-user-id="{{ $application->user_id ?? '' }}"
                                        data-wawancara-id="{{ $application->interview_id ?? $application->wawancara_id ?? $application->id_wawancara ?? '' }}">
                                    <i class="fas fa-check"></i> Input Hasil
                                </button>
                            @endif

                            <!-- Final Decision Actions - Hanya tampil di tab final atau showAll -->
                            @if(($intStatus === 'lulus' || $intStatus === 'passed') && 
                                ($finalStatus === 'pending' || $finalStatus === 'menunggu') && 
                                (!isset($stage) || $stage === 'final' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-warning btn-final-decision mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#finalModal" 
                                        data-application-id="{{ $application->id }}"
                                        data-application-name="{{ $application->name }}"
                                        data-user-id="{{ $application->user_id ?? '' }}">
                                    <i class="fas fa-gavel"></i> Keputusan Final
                                </button>
                            @endif

                            <!-- Tombol untuk membuat hasil seleksi jika status sudah diterima tapi belum ada di API -->
                            @if((!isset($application->selection_result) || !$application->selection_result) && 
                                ($finalStatus === 'diterima' || $finalStatus === 'accepted') &&
                                (!isset($stage) || $stage === 'final' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-success btn-create-selection-result mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#finalModal" 
                                        data-application-id="{{ $application->id }}"
                                        data-application-name="{{ $application->name }}"
                                        data-user-id="{{ $application->user_id ?? '' }}"
                                        data-current-status="diterima"
                                        data-is-create="true"
                                        title="Buat catatan hasil seleksi">
                                    <i class="fas fa-plus"></i> Catat Hasil Seleksi
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
                                        data-status-seleksi="{{ 
                                            $hasSelectionResult ? 
                                            ($application->selection_result['status'] ?? 'Pending') : 
                                            ($application->status_seleksi ?? 'Menunggu review') 
                                        }}"
                                        data-created-at="{{ $application->created_at ? $application->created_at->format('d M Y H:i') : 'Tidak diketahui' }}"
                                        data-cv-path="{{ $application->cv_path ?? '' }}"
                                        data-cover-letter="{{ isset($application->cover_letter) ? htmlspecialchars($application->cover_letter, ENT_QUOTES, 'UTF-8') : '' }}"
                                        data-doc-status="{{ $docStatus }}"
                                        data-interview-status="{{ $intStatus }}"
                                        data-final-status="{{ $finalStatus }}"
                                        data-application-id="{{ $application->id }}"
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
                @endif {{-- End of $shouldSkip condition --}}
                @endforeach
            </tbody>
        </table>
    @else
        <div class="text-center py-4">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <p class="text-muted">
                @if(isset($stage))
                    @if($stage === 'document')
                        Tidak ada aplikasi yang perlu direview dokumennya.
                        <br><small>Aplikasi yang sudah diterima dokumennya akan pindah ke tab Interview.</small>
                    @elseif($stage === 'interview') 
                        Tidak ada aplikasi yang siap untuk tahap interview.
                        <br><small>Hanya aplikasi yang dokumennya sudah diterima dan belum lulus interview yang tampil di sini.</small>
                    @elseif($stage === 'final')
                        @if(config('app.debug'))
                            <!-- Debug Info Mode - Tampil hanya saat APP_DEBUG=true -->
                            <div class="alert alert-info mb-3">
                                <h6><i class="fas fa-bug"></i> Debug Mode - Tab Hasil Seleksi</h6>
                                <p><strong>Total aplikasi di controller:</strong> {{ $applications->count() }}</p>
                                @if($applications->count() > 0)
                                    <p><strong>Data aplikasi yang diterima:</strong></p>
                                    <ul class="small">
                                        @foreach($applications as $app)
                                            <li>{{ $app->name ?? 'No name' }} - 
                                                Data Source: {{ $app->data_source ?? 'undefined' }} - 
                                                Final Status: {{ $app->final_status ?? 'undefined' }} -
                                                Has Result ID: {{ isset($app->hasil_seleksi_id) ? 'Yes ('.$app->hasil_seleksi_id.')' : 'No' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endif
                        
                        Tidak ada data hasil seleksi yang dapat ditampilkan.
                        <br><small>Kemungkinan penyebab:</small>
                        <ul class="small text-muted mt-2">
                            <li>Belum ada hasil seleksi yang tercatat di sistem untuk lowongan ini</li>
                            <li>Data hasil seleksi belum memiliki struktur yang sesuai (memerlukan data_source, hasil_seleksi_id, atau final_status)</li>
                            <li>Filter tab terlalu ketat dan data tidak memenuhi kriteria</li>
                        </ul>
                        @if(config('app.debug'))
                            <div class="mt-2">
                                <small class="text-info">💡 Debug: Periksa Laravel log untuk detail filtering aplikasi di tab ini.</small>
                            </div>
                        @endif
                    @else
                        Tidak ada aplikasi untuk tahap ini.
                    @endif
                @else
                    Belum ada aplikasi untuk lowongan ini.
                @endif
            </p>
        </div>
    @endif
</div>
