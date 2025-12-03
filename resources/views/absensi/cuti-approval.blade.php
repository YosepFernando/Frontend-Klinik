@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header bg-gradient-warning text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0">
                                <i class="fas fa-calendar-check me-2"></i>Persetujuan Cuti Karyawan
                            </h4>
                            <small class="opacity-75">Kelola permohonan cuti yang menunggu persetujuan</small>
                        </div>
                        <a href="{{ route('absensi.index') }}" class="btn btn-light">
                            <i class="fas fa-arrow-left me-1"></i> Kembali ke Absensi
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Summary Stats -->
                    @if(isset($stats))
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">Menunggu Persetujuan</h6>
                                            <h2 class="mb-0">{{ $stats['pending'] ?? 0 }}</h2>
                                        </div>
                                        <i class="fas fa-hourglass-half fa-3x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">Disetujui Bulan Ini</h6>
                                            <h2 class="mb-0">{{ $stats['approved'] ?? 0 }}</h2>
                                        </div>
                                        <i class="fas fa-check-circle fa-3x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">Ditolak Bulan Ini</h6>
                                            <h2 class="mb-0">{{ $stats['rejected'] ?? 0 }}</h2>
                                        </div>
                                        <i class="fas fa-times-circle fa-3x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ ($filterStatus ?? 'pending') === 'pending' ? 'active' : '' }}" 
                               href="{{ route('absensi.cuti.approval', ['status' => 'pending']) }}">
                                <i class="fas fa-clock me-1"></i>Menunggu 
                                <span class="badge bg-warning ms-1">{{ $stats['pending'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ ($filterStatus ?? '') === 'approved' ? 'active' : '' }}" 
                               href="{{ route('absensi.cuti.approval', ['status' => 'approved']) }}">
                                <i class="fas fa-check me-1"></i>Disetujui 
                                <span class="badge bg-success ms-1">{{ $stats['approved'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ ($filterStatus ?? '') === 'rejected' ? 'active' : '' }}" 
                               href="{{ route('absensi.cuti.approval', ['status' => 'rejected']) }}">
                                <i class="fas fa-times me-1"></i>Ditolak 
                                <span class="badge bg-danger ms-1">{{ $stats['rejected'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link {{ ($filterStatus ?? '') === 'all' ? 'active' : '' }}" 
                               href="{{ route('absensi.cuti.approval', ['status' => 'all']) }}">
                                <i class="fas fa-list me-1"></i>Semua
                            </a>
                        </li>
                    </ul>


                    @if(isset($cutiRequests) && $cutiRequests->count() > 0)
                        @php
                            // Group consecutive cuti by employee and date range
                            $cutiGroups = [];
                            $currentGroup = null;
                            
                            // First, sort cuti by employee, created_at, and date to ensure proper grouping
                            $sortedCuti = $cutiRequests->sortBy(function($cuti) {
                                $pegawai = is_array($cuti) ? ($cuti['pegawai'] ?? null) : ($cuti->pegawai ?? null);
                                $idPegawai = is_array($pegawai) ? ($pegawai['id_pegawai'] ?? 0) : ($pegawai->id_pegawai ?? 0);
                                $tanggalCuti = is_array($cuti) ? ($cuti['tanggal_absensi'] ?? '') : ($cuti->tanggal_absensi ?? '');
                                $createdAt = is_array($cuti) ? ($cuti['created_at'] ?? '') : ($cuti->created_at ?? '');
                                
                                return $idPegawai . '_' . $createdAt . '_' . $tanggalCuti;
                            });
                            
                            foreach ($sortedCuti as $cuti) {
                                $pegawai = is_array($cuti) ? ($cuti['pegawai'] ?? null) : ($cuti->pegawai ?? null);
                                $idPegawai = is_array($pegawai) ? ($pegawai['id_pegawai'] ?? 0) : ($pegawai->id_pegawai ?? 0);
                                $tanggalCuti = is_array($cuti) ? ($cuti['tanggal_absensi'] ?? '') : ($cuti->tanggal_absensi ?? '');
                                $cutiId = is_array($cuti) ? ($cuti['id_absensi'] ?? 0) : ($cuti->id_absensi ?? 0);
                                $cutiReason = is_array($cuti) ? ($cuti['cuti_reason'] ?? '') : ($cuti->cuti_reason ?? '');
                                $approvalStatus = is_array($cuti) ? ($cuti['approval_status'] ?? '') : ($cuti->approval_status ?? '');
                                $createdAt = is_array($cuti) ? ($cuti['created_at'] ?? '') : ($cuti->created_at ?? '');
                                
                                // Only group pending cuti
                                if ($approvalStatus !== 'pending') {
                                    // Add ungrouped item
                                    $cutiGroups[] = [
                                        'is_group' => false,
                                        'items' => [$cuti]
                                    ];
                                    continue;
                                }
                                
                                $tanggalCarbon = \Carbon\Carbon::parse($tanggalCuti)->startOfDay();
                                
                                // Check if this cuti can be added to current group
                                if ($currentGroup && 
                                    $currentGroup['id_pegawai'] == $idPegawai && 
                                    $currentGroup['cuti_reason'] == $cutiReason &&
                                    $currentGroup['created_at'] == $createdAt) {
                                    
                                    $lastDate = \Carbon\Carbon::parse($currentGroup['end_date'])->startOfDay();
                                    
                                    // Check if this date is exactly 1 day after the last date (consecutive)
                                    $daysDiff = $tanggalCarbon->diffInDays($lastDate, false);
                                    
                                    // For ascending dates: daysDiff should be -1 (tomorrow)
                                    // For same date or consecutive: abs should be 0 or 1
                                    if (abs($daysDiff) <= 1 && $tanggalCarbon->greaterThanOrEqualTo($lastDate)) {
                                        // Add to current group
                                        $currentGroup['items'][] = $cuti;
                                        $currentGroup['cuti_ids'][] = $cutiId;
                                        
                                        // Update end_date only if this date is later
                                        if ($tanggalCarbon->greaterThan($lastDate)) {
                                            $currentGroup['end_date'] = $tanggalCuti;
                                        }
                                        
                                        $currentGroup['total_days']++;
                                        continue;
                                    }
                                }
                                
                                // Save current group if exists
                                if ($currentGroup && count($currentGroup['items']) > 0) {
                                    $cutiGroups[] = [
                                        'is_group' => count($currentGroup['items']) > 1,
                                        'items' => $currentGroup['items'],
                                        'cuti_ids' => $currentGroup['cuti_ids'],
                                        'start_date' => $currentGroup['start_date'],
                                        'end_date' => $currentGroup['end_date'],
                                        'total_days' => $currentGroup['total_days']
                                    ];
                                }
                                
                                // Start new group
                                $currentGroup = [
                                    'id_pegawai' => $idPegawai,
                                    'cuti_reason' => $cutiReason,
                                    'created_at' => $createdAt,
                                    'items' => [$cuti],
                                    'cuti_ids' => [$cutiId],
                                    'start_date' => $tanggalCuti,
                                    'end_date' => $tanggalCuti,
                                    'total_days' => 1
                                ];
                            }
                            
                            // Add last group
                            if ($currentGroup && count($currentGroup['items']) > 0) {
                                $cutiGroups[] = [
                                    'is_group' => count($currentGroup['items']) > 1,
                                    'items' => $currentGroup['items'],
                                    'cuti_ids' => $currentGroup['cuti_ids'] ?? [],
                                    'start_date' => $currentGroup['start_date'],
                                    'end_date' => $currentGroup['end_date'],
                                    'total_days' => $currentGroup['total_days']
                                ];
                            }
                        @endphp
                        
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Tanggal Pengajuan</th>
                                        <th>Karyawan</th>
                                        <th>Posisi</th>
                                        <th>Tanggal Cuti</th>
                                        <th>Alasan Cuti</th>
                                        <th>Status</th>
                                        <th>Status Kuota</th>
                                        @if(($filterStatus ?? 'pending') === 'pending')
                                        <th class="text-center">Aksi</th>
                                        @elseif(($filterStatus ?? '') === 'approved')
                                        <th>Disetujui Oleh</th>
                                        <th>Tanggal Disetujui</th>
                                        @elseif(($filterStatus ?? '') === 'rejected')
                                        <th>Alasan Penolakan</th>
                                        <th>Ditolak Oleh</th>
                                        @else
                                        <th class="text-center">Aksi/Info</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cutiGroups as $group)
                                        @php
                                            $firstCuti = $group['items'][0];
                                            $pegawai = is_array($firstCuti) ? ($firstCuti['pegawai'] ?? null) : ($firstCuti->pegawai ?? null);
                                            $namaPegawai = 'N/A';
                                            $posisi = 'N/A';
                                            
                                            if ($pegawai) {
                                                if (is_array($pegawai)) {
                                                    $namaPegawai = $pegawai['nama_lengkap'] ?? 'N/A';
                                                    $posisi = $pegawai['posisi']['nama_posisi'] ?? 'N/A';
                                                } else {
                                                    $namaPegawai = $pegawai->nama_lengkap ?? 'N/A';
                                                    $posisi = $pegawai->posisi->nama_posisi ?? 'N/A';
                                                }
                                            }
                                            
                                            $tanggalCuti = is_array($firstCuti) ? ($firstCuti['tanggal_absensi'] ?? '') : ($firstCuti->tanggal_absensi ?? '');
                                            $cutiReason = is_array($firstCuti) ? ($firstCuti['cuti_reason'] ?? '') : ($firstCuti->cuti_reason ?? '');
                                            $createdAt = is_array($firstCuti) ? ($firstCuti['created_at'] ?? '') : ($firstCuti->created_at ?? '');
                                            $cutiId = is_array($firstCuti) ? ($firstCuti['id_absensi'] ?? 0) : ($firstCuti->id_absensi ?? 0);
                                            $idPegawai = is_array($pegawai) ? ($pegawai['id_pegawai'] ?? 0) : ($pegawai->id_pegawai ?? 0);
                                            $approvalStatus = is_array($firstCuti) ? ($firstCuti['approval_status'] ?? '') : ($firstCuti->approval_status ?? '');
                                            $approvedBy = is_array($firstCuti) ? ($firstCuti['approved_by'] ?? null) : ($firstCuti->approved_by ?? null);
                                            $approvedAt = is_array($firstCuti) ? ($firstCuti['approved_at'] ?? '') : ($firstCuti->approved_at ?? '');
                                            $rejectionReason = is_array($firstCuti) ? ($firstCuti['rejection_reason'] ?? '') : ($firstCuti->rejection_reason ?? '');
                                            
                                            $isGroup = $group['is_group'] ?? false;
                                            $totalDays = $group['total_days'] ?? 1;
                                        @endphp
                                        <tr class="{{ $isGroup ? 'table-info' : '' }}">
                                            <td>
                                                <small class="text-muted">
                                                    {{ \Carbon\Carbon::parse($createdAt)->format('d M Y H:i') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle-sm me-2">
                                                        {{ strtoupper(substr($namaPegawai, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <strong>{{ $namaPegawai }}</strong>
                                                        @if($isGroup)
                                                            <span class="badge bg-primary ms-2">
                                                                <i class="fas fa-layer-group"></i> {{ $totalDays }} hari berurutan
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $posisi }}</span>
                                            </td>
                                            <td>
                                                @if($isGroup)
                                                    <strong>{{ \Carbon\Carbon::parse($group['start_date'])->format('d M Y') }}</strong>
                                                    <br>
                                                    <span class="text-muted">s/d</span>
                                                    <br>
                                                    <strong>{{ \Carbon\Carbon::parse($group['end_date'])->format('d M Y') }}</strong>
                                                    <br>
                                                    <small class="badge bg-primary">{{ $totalDays }} hari</small>
                                                @else
                                                    <strong>{{ \Carbon\Carbon::parse($tanggalCuti)->format('d M Y') }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($tanggalCuti)->format('l') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div style="max-width: 250px;">
                                                    {{ $cutiReason }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($approvalStatus === 'pending')
                                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                                @elseif($approvalStatus === 'approved')
                                                    <span class="badge bg-success">Disetujui</span>
                                                @elseif($approvalStatus === 'rejected')
                                                    <span class="badge bg-danger">Ditolak</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($approvalStatus) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if(isset($cutiQuotas[$idPegawai]))
                                                    @php
                                                        $quota = $cutiQuotas[$idPegawai];
                                                        $used = $quota['used_cuti'] ?? 0;
                                                        $remaining = $quota['remaining_quota'] ?? 0;
                                                        $canApprove = $remaining >= $totalDays;
                                                    @endphp
                                                    <div class="text-center">
                                                        <span class="badge {{ $canApprove ? 'bg-success' : 'bg-danger' }}">
                                                            {{ $used }}/12 Terpakai
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">Sisa: {{ $remaining }} hari</small>
                                                        @if(!$canApprove)
                                                            <br>
                                                            <small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Kuota tidak cukup</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary">Belum ada data</span>
                                                @endif
                                            </td>
                                            
                                            @if(($filterStatus ?? 'pending') === 'pending')
                                            <td class="text-center">
                                                @if($isGroup)
                                                    <!-- Batch approval buttons -->
                                                    <div class="btn-group-vertical" role="group">
                                                        <button type="button" 
                                                                class="btn btn-sm btn-success mb-1" 
                                                                onclick="batchApproveCuti({{ json_encode($group['cuti_ids']) }}, '{{ $namaPegawai }}', {{ $totalDays }}, {{ $canApprove ?? true ? 'true' : 'false' }})"
                                                                {{ isset($canApprove) && !$canApprove ? 'disabled' : '' }}>
                                                            <i class="fas fa-check-double"></i> Setujui Semua ({{ $totalDays }} hari)
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-danger" 
                                                                onclick="showBatchRejectModal({{ json_encode($group['cuti_ids']) }}, '{{ $namaPegawai }}', {{ $totalDays }})">
                                                            <i class="fas fa-times"></i> Tolak Semua
                                                        </button>
                                                    </div>
                                                @else
                                                    <!-- Single approval buttons -->
                                                    <div class="btn-group" role="group">
                                                        <button type="button" 
                                                                class="btn btn-sm btn-success" 
                                                                onclick="approveCuti({{ $cutiId }}, '{{ $namaPegawai }}', {{ $canApprove ?? true ? 'true' : 'false' }})"
                                                                {{ isset($canApprove) && !$canApprove ? 'disabled' : '' }}>
                                                            <i class="fas fa-check"></i> Setuju
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-danger" 
                                                                onclick="showRejectModal({{ $cutiId }}, '{{ $namaPegawai }}')">
                                                            <i class="fas fa-times"></i> Tolak
                                                        </button>
                                                    </div>
                                                @endif
                                            </td>
                                            @elseif(($filterStatus ?? '') === 'approved')
                                            <td>
                                                @if($approvedBy)
                                                    <span class="badge bg-primary">{{ is_array($approvedBy) ? ($approvedBy['nama_lengkap'] ?? 'Admin') : ($approvedBy->nama_lengkap ?? 'Admin') }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($approvedAt)
                                                    {{ \Carbon\Carbon::parse($approvedAt)->format('d M Y H:i') }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            @elseif(($filterStatus ?? '') === 'rejected')
                                            <td>
                                                <div style="max-width: 250px;">
                                                    {{ $rejectionReason ?: '-' }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($approvedBy)
                                                    <span class="badge bg-primary">{{ is_array($approvedBy) ? ($approvedBy['nama_lengkap'] ?? 'Admin') : ($approvedBy->nama_lengkap ?? 'Admin') }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            @else
                                            <td class="text-center">
                                                @if($approvalStatus === 'pending')
                                                    <div class="btn-group" role="group">
                                                        <button type="button" 
                                                                class="btn btn-sm btn-success" 
                                                                onclick="approveCuti({{ $cutiId }}, '{{ $namaPegawai }}', {{ $canApprove ?? true ? 'true' : 'false' }})"
                                                                {{ isset($canApprove) && !$canApprove ? 'disabled' : '' }}>
                                                            <i class="fas fa-check"></i> Setuju
                                                        </button>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-danger" 
                                                                onclick="showRejectModal({{ $cutiId }}, '{{ $namaPegawai }}')">
                                                            <i class="fas fa-times"></i> Tolak
                                                        </button>
                                                    </div>
                                                @elseif($approvalStatus === 'approved')
                                                    <span class="text-success">
                                                        <i class="fas fa-check-circle"></i> Disetujui<br>
                                                        <small>{{ $approvedAt ? \Carbon\Carbon::parse($approvedAt)->format('d M Y') : '' }}</small>
                                                    </span>
                                                @elseif($approvalStatus === 'rejected')
                                                    <span class="text-danger">
                                                        <i class="fas fa-times-circle"></i> Ditolak<br>
                                                        <small>{{ $approvedAt ? \Carbon\Carbon::parse($approvedAt)->format('d M Y') : '' }}</small>
                                                    </span>
                                                @endif
                                            </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if(method_exists($cutiRequests, 'links'))
                            <div class="d-flex justify-content-center mt-4">
                                {{ $cutiRequests->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-check fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">
                                @if(($filterStatus ?? 'pending') === 'pending')
                                    Tidak Ada Permohonan Cuti Menunggu Persetujuan
                                @elseif(($filterStatus ?? '') === 'approved')
                                    Tidak Ada Cuti yang Disetujui
                                @elseif(($filterStatus ?? '') === 'rejected')
                                    Tidak Ada Cuti yang Ditolak
                                @else
                                    Tidak Ada Data Cuti
                                @endif
                            </h5>
                            <p class="text-muted">
                                @if(($filterStatus ?? 'pending') === 'pending')
                                    Semua permohonan cuti telah diproses atau belum ada pengajuan cuti baru.
                                @else
                                    Belum ada data untuk status ini.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle me-2"></i>Tolak Permohonan Cuti
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Anda akan menolak permohonan cuti dari <strong id="rejectEmployeeName"></strong>
                    </div>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">
                            Alasan Penolakan <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" 
                                  id="rejection_reason" 
                                  name="rejection_reason" 
                                  rows="4" 
                                  placeholder="Jelaskan alasan penolakan cuti..."
                                  required></textarea>
                        <small class="text-muted">
                            Alasan ini akan diberitahukan kepada karyawan
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-1"></i>Tolak Cuti
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Batch Reject Modal -->
<div class="modal fade" id="batchRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle me-2"></i>Tolak Beberapa Permohonan Cuti
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="batchRejectForm" action="{{ route('absensi.cuti.batch-reject') }}" method="POST">
                @csrf
                <input type="hidden" id="batch_reject_cuti_ids" name="cuti_ids">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Anda akan menolak <strong id="batchRejectCount"></strong> permohonan cuti dari <strong id="batchRejectEmployeeName"></strong>
                    </div>
                    <div class="mb-3">
                        <label for="batch_rejection_reason" class="form-label">
                            Alasan Penolakan <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" 
                                  id="batch_rejection_reason" 
                                  name="rejection_reason" 
                                  rows="4" 
                                  placeholder="Jelaskan alasan penolakan cuti..."
                                  required></textarea>
                        <small class="text-muted">
                            Alasan ini akan diberitahukan kepada karyawan untuk semua permohonan
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-1"></i>Tolak Semua Cuti
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
}

.avatar-circle-sm {
    width: 35px;
    height: 35px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.9rem;
    font-weight: bold;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: rgba(255, 193, 7, 0.1);
}

.table tbody tr.table-info {
    background-color: rgba(13, 202, 240, 0.1) !important;
    border-left: 4px solid #0dcaf0;
}

.table tbody tr.table-info:hover {
    background-color: rgba(13, 202, 240, 0.2) !important;
}

.card {
    border: none;
    border-radius: 12px;
}

.card-header {
    border-radius: 12px 12px 0 0 !important;
}

.btn-group-vertical .btn {
    min-width: 200px;
}
</style>

<script>
function approveCuti(cutiId, employeeName, canApprove) {
    if (!canApprove) {
        Swal.fire({
            icon: 'error',
            title: 'Tidak Dapat Menyetujui',
            text: 'Kuota cuti karyawan sudah habis (12 hari per tahun).',
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    Swal.fire({
        title: 'Konfirmasi Persetujuan',
        html: `Apakah Anda yakin ingin menyetujui cuti untuk <strong>${employeeName}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Setujui',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memproses...',
                text: 'Sedang memproses persetujuan cuti',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/absensi/cuti/${cutiId}/approve`;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function batchApproveCuti(cutiIds, employeeName, totalDays, canApprove) {
    if (!canApprove) {
        Swal.fire({
            icon: 'error',
            title: 'Tidak Dapat Menyetujui',
            text: `Kuota cuti karyawan tidak mencukupi untuk ${totalDays} hari.`,
            confirmButtonColor: '#dc3545'
        });
        return;
    }
    
    Swal.fire({
        title: 'Konfirmasi Batch Persetujuan',
        html: `
            <p>Apakah Anda yakin ingin menyetujui <strong>${totalDays} hari</strong> cuti sekaligus untuk <strong>${employeeName}</strong>?</p>
            <p class="text-muted mt-2">Ini akan menyetujui semua permohonan cuti yang berurutan.</p>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: `Ya, Setujui ${totalDays} Hari`,
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memproses...',
                text: `Sedang memproses persetujuan ${totalDays} hari cuti`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("absensi.cuti.batch-approve") }}';
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = '{{ csrf_token() }}';
            form.appendChild(csrfInput);
            
            // Add cuti IDs as array
            cutiIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'cuti_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function showRejectModal(cutiId, employeeName) {
    document.getElementById('rejectEmployeeName').textContent = employeeName;
    document.getElementById('rejectForm').action = `/absensi/cuti/${cutiId}/reject`;
    
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

function showBatchRejectModal(cutiIds, employeeName, totalDays) {
    document.getElementById('batchRejectEmployeeName').textContent = employeeName;
    document.getElementById('batchRejectCount').textContent = totalDays + ' hari';
    document.getElementById('batch_reject_cuti_ids').value = JSON.stringify(cutiIds);
    
    const modal = new bootstrap.Modal(document.getElementById('batchRejectModal'));
    modal.show();
}

// Handle form submission with SweetAlert loading
document.getElementById('rejectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    Swal.fire({
        title: 'Memproses...',
        text: 'Sedang memproses penolakan cuti',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Submit form after showing loading
    setTimeout(() => {
        this.submit();
    }, 100);
});

// Handle batch reject form submission
document.getElementById('batchRejectForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Parse cuti_ids from JSON
    const cutiIdsJson = document.getElementById('batch_reject_cuti_ids').value;
    const cutiIds = JSON.parse(cutiIdsJson);
    
    // Remove the JSON input
    document.getElementById('batch_reject_cuti_ids').remove();
    
    // Add individual inputs for each ID
    cutiIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'cuti_ids[]';
        input.value = id;
        this.appendChild(input);
    });
    
    Swal.fire({
        title: 'Memproses...',
        text: 'Sedang memproses penolakan batch',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Submit form after showing loading
    setTimeout(() => {
        this.submit();
    }, 100);
});

</script>
@endsection
