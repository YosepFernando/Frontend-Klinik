<div class="table-responsive mt-3">
    @if($applications->count() > 0)
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pelamar</th>
                    <th>Info Personal</th>
                    <th>Tanggal Apply</th>
                    <th>Status Dokumen</th>
                    <th>Status Seleksi</th>
                    <th>Status Interview</th>
                    <th>Status Final</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $index => $application)
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
                    <td>
                        @if(isset($application->document_status))
                            @if($application->document_status === 'pending')
                                <span class="badge bg-warning">Menunggu Review</span>
                            @elseif($application->document_status === 'accepted')
                                <span class="badge bg-success">Diterima</span>
                            @else
                                <span class="badge bg-danger">Ditolak</span>
                            @endif
                        @else
                            <span class="badge bg-secondary">Belum Ada Data</span>
                        @endif
                        
                        @if(isset($application->document_notes) && $application->document_notes)
                            <br><small class="text-muted">{{ $application->document_notes }}</small>
                        @endif
                        
                        @if(isset($stage) && $stage === 'document' && isset($application->data_source))
                            <br><small class="text-info">Source: {{ ucfirst(str_replace('_', ' ', $application->data_source)) }}</small>
                        @endif
                    </td>
                    <td>
                        @if(isset($application->status_seleksi) && $application->status_seleksi)
                            <span class="badge bg-info">{{ $application->status_seleksi }}</span>
                        @else
                            <span class="badge bg-secondary">Menunggu Review</span>
                        @endif
                    </td>
                    <td>
                        @if(isset($application->interview_status))
                            @if($application->interview_status === 'not_scheduled')
                                <span class="badge bg-secondary">Belum Dijadwal</span>
                            @elseif($application->interview_status === 'scheduled' || $application->interview_status === 'pending')
                                <span class="badge bg-info">Dijadwal</span>
                                @if(isset($application->interview_date) && $application->interview_date)
                                    <br><small class="text-muted">📅 {{ \Carbon\Carbon::parse($application->interview_date)->format('d M Y H:i') }}</small>
                                @endif
                                @if(isset($application->interview_location) && $application->interview_location)
                                    <br><small class="text-muted">📍 {{ Str::limit($application->interview_location, 30) }}</small>
                                @endif
                            @elseif($application->interview_status === 'passed')
                                <span class="badge bg-success">Lulus</span>
                                @if(isset($application->interview_score) && $application->interview_score)
                                    <br><small class="text-muted">Skor: {{ $application->interview_score }}</small>
                                @endif
                            @elseif($application->interview_status === 'failed')
                                <span class="badge bg-danger">Tidak Lulus</span>
                            @else
                                <span class="badge bg-secondary">Belum Dijadwal</span>
                            @endif
                        @else
                            <span class="badge bg-secondary">Belum Ada Data</span>
                        @endif
                        
                        @if(isset($stage) && $stage === 'interview')
                            @if(isset($application->interview_notes) && $application->interview_notes)
                                <br><small class="text-info">💬 {{ Str::limit($application->interview_notes, 50) }}</small>
                            @endif
                            @if(isset($application->data_source))
                                <br><small class="text-info">Source: {{ ucfirst(str_replace('_', ' ', $application->data_source)) }}</small>
                            @endif
                        @endif
                    </td>
                    <td>
                        @if(isset($application->final_status))
                            @if($application->final_status === 'pending')
                                <span class="badge bg-secondary">Menunggu</span>
                            @elseif($application->final_status === 'accepted')
                                <span class="badge bg-success">✅ Diterima</span>
                                @if(isset($application->start_date) && $application->start_date)
                                    <br><small class="text-muted">📅 Mulai: {{ \Carbon\Carbon::parse($application->start_date)->format('d M Y') }}</small>
                                @endif
                            @elseif($application->final_status === 'rejected')
                                <span class="badge bg-danger">❌ Ditolak</span>
                            @else
                                <span class="badge bg-warning">⏳ Waiting List</span>
                            @endif
                        @else
                            <span class="badge bg-secondary">Belum Ada Data</span>
                        @endif
                        
                        @if(isset($stage) && $stage === 'final')
                            @if(isset($application->final_notes) && $application->final_notes)
                                <br><small class="text-info">💬 {{ Str::limit($application->final_notes, 50) }}</small>
                            @endif
                            @if(isset($application->data_source))
                                <br><small class="text-info">Source: {{ ucfirst(str_replace('_', ' ', $application->data_source)) }}</small>
                            @endif
                        @endif
                    </td>
                    <td>
                        <div class="btn-group-vertical" role="group">
                            <!-- Document Actions - Hanya tampil di tab document atau showAll -->
                            @if(isset($application->document_status) && $application->document_status === 'pending' && 
                                (!isset($stage) || $stage === 'document' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-primary btn-document-review mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#documentModal" 
                                        data-application-id="{{ $application->id }}">
                                    <i class="fas fa-file-alt"></i> Review Dokumen
                                </button>
                            @endif

                            <!-- Interview Actions - Hanya tampil di tab interview atau showAll -->
                            @if(isset($application->document_status) && $application->document_status === 'accepted' && 
                                isset($application->interview_status) && $application->interview_status === 'not_scheduled' && 
                                (!isset($stage) || $stage === 'interview' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-info btn-schedule-interview mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#interviewModal" 
                                        data-application-id="{{ $application->id }}">
                                    <i class="fas fa-calendar"></i> Jadwal Interview
                                </button>
                            @endif

                            @if(isset($application->interview_status) && 
                                ($application->interview_status === 'scheduled' || $application->interview_status === 'pending') && 
                                (!isset($stage) || $stage === 'interview' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-success btn-interview-result mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#interviewResultModal" 
                                        data-application-id="{{ $application->id }}">
                                    <i class="fas fa-check"></i> Input Hasil
                                </button>
                            @endif

                            <!-- Final Decision Actions - Hanya tampil di tab final atau showAll -->
                            @if(isset($application->interview_status) && $application->interview_status === 'passed' && 
                                isset($application->final_status) && $application->final_status === 'pending' && 
                                (!isset($stage) || $stage === 'final' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-warning btn-final-decision mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#finalModal" 
                                        data-application-id="{{ $application->id }}">
                                    <i class="fas fa-gavel"></i> Keputusan Final
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
