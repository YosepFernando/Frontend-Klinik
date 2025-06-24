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
                    <th>Status Interview</th>
                    <th>Status Final</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $index => $application)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div>
                            <strong>{{ $application->full_name ?? $application->user->name }}</strong><br>
                            <small class="text-muted">{{ $application->email ?? $application->user->email }}</small>
                        </div>
                    </td>
                    <td>
                        <div class="small">
                            @if($application->phone)
                                <div><i class="fas fa-phone"></i> {{ $application->phone }}</div>
                            @endif
                            @if($application->education)
                                <div><i class="fas fa-graduation-cap"></i> {{ $application->education_display }}</div>
                            @endif
                            @if($application->nik)
                                <div><i class="fas fa-id-card"></i> {{ substr($application->nik, 0, 6) . '****' . substr($application->nik, -4) }}</div>
                            @endif
                        </div>
                    </td>
                    <td>{{ $application->created_at->format('d M Y H:i') }}</td>
                    <td>
                        @if($application->document_status === 'pending')
                            <span class="badge bg-warning">Menunggu Review</span>
                        @elseif($application->document_status === 'accepted')
                            <span class="badge bg-success">Diterima</span>
                        @else
                            <span class="badge bg-danger">Ditolak</span>
                        @endif
                        
                        @if($application->document_notes)
                            <br><small class="text-muted">{{ $application->document_notes }}</small>
                        @endif
                    </td>
                    <td>
                        @if($application->interview_status === 'pending')
                            <span class="badge bg-secondary">Belum Dijadwal</span>
                        @elseif($application->interview_status === 'scheduled')
                            <span class="badge bg-info">Dijadwal</span>
                            @if($application->interview_date)
                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($application->interview_date)->format('d M Y H:i') }}</small>
                            @endif
                        @elseif($application->interview_status === 'passed')
                            <span class="badge bg-success">Lulus</span>
                            @if($application->interview_score)
                                <br><small class="text-muted">Skor: {{ $application->interview_score }}</small>
                            @endif
                        @else
                            <span class="badge bg-danger">Tidak Lulus</span>
                        @endif
                    </td>
                    <td>
                        @if($application->final_status === 'pending')
                            <span class="badge bg-secondary">Menunggu</span>
                        @elseif($application->final_status === 'accepted')
                            <span class="badge bg-success">Diterima</span>
                            @if($application->start_date)
                                <br><small class="text-muted">Mulai: {{ \Carbon\Carbon::parse($application->start_date)->format('d M Y') }}</small>
                            @endif
                        @elseif($application->final_status === 'rejected')
                            <span class="badge bg-danger">Ditolak</span>
                        @else
                            <span class="badge bg-warning">Waiting List</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group-vertical" role="group">
                            <!-- Document Actions -->
                            @if($application->document_status === 'pending' && (!isset($stage) || $stage === 'document' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-primary btn-document-review mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#documentModal" 
                                        data-application-id="{{ $application->id }}">
                                    <i class="fas fa-file-alt"></i> Review Dokumen
                                </button>
                            @endif

                            <!-- Interview Actions -->
                            @if($application->document_status === 'accepted' && $application->interview_status === 'pending' && (!isset($stage) || $stage === 'interview' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-info btn-schedule-interview mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#interviewModal" 
                                        data-application-id="{{ $application->id }}">
                                    <i class="fas fa-calendar"></i> Jadwal Interview
                                </button>
                            @endif

                            @if($application->interview_status === 'scheduled' && (!isset($stage) || $stage === 'interview' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-success btn-interview-result mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#interviewResultModal" 
                                        data-application-id="{{ $application->id }}">
                                    <i class="fas fa-check"></i> Input Hasil
                                </button>
                            @endif

                            <!-- Final Decision Actions -->
                            @if($application->interview_status === 'passed' && $application->final_status === 'pending' && (!isset($stage) || $stage === 'final' || isset($showAll)))
                                <button type="button" class="btn btn-sm btn-outline-warning btn-final-decision mb-1" 
                                        data-bs-toggle="modal" data-bs-target="#finalModal" 
                                        data-application-id="{{ $application->id }}">
                                    <i class="fas fa-gavel"></i> Keputusan Final
                                </button>
                            @endif

                            <!-- View Details -->
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-eye"></i> Detail
                                </button>
                                <ul class="dropdown-menu">
                                    @if($application->cv_path)
                                        <li><a class="dropdown-item" href="{{ Storage::url($application->cv_path) }}" target="_blank">
                                            <i class="fas fa-file-pdf"></i> Lihat CV
                                        </a></li>
                                    @endif
                                    @if($application->cover_letter_path)
                                        <li><a class="dropdown-item" href="{{ Storage::url($application->cover_letter_path) }}" target="_blank">
                                            <i class="fas fa-file-alt"></i> Lihat Cover Letter
                                        </a></li>
                                    @endif
                                    @if($application->additional_documents_path)
                                        <li><a class="dropdown-item" href="{{ Storage::url($application->additional_documents_path) }}" target="_blank">
                                            <i class="fas fa-folder"></i> Dokumen Tambahan
                                        </a></li>
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="mailto:{{ $application->user->email }}">
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
