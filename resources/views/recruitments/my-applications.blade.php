@extends('layouts.app')

@section('page-title', 'My Job Applications')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">My Job Applications</h6>
                    <a href="{{ route('recruitments.index') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Browse Jobs
                    </a>
                </div>
                <div class="card-body">
                    @if($applications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered" id="applicationsTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Job Position</th>
                                        <th>Applied Date</th>
                                        <th>Status</th>
                                        <th>Stage</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($applications as $application)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong>{{ $application->recruitment->title ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">
                                                    {{ $application->recruitment->posisi->nama_posisi ?? 'Position not available' }}
                                                </small>
                                                @if($application->recruitment->salary_min || $application->recruitment->salary_max)
                                                <br><small class="text-info">
                                                    <i class="bi bi-currency-dollar"></i>
                                                    @if($application->recruitment->salary_min && $application->recruitment->salary_max)
                                                        Rp {{ number_format($application->recruitment->salary_min, 0, ',', '.') }} - Rp {{ number_format($application->recruitment->salary_max, 0, ',', '.') }}
                                                    @elseif($application->recruitment->salary_min)
                                                        Rp {{ number_format($application->recruitment->salary_min, 0, ',', '.') }}+
                                                    @else
                                                        Up to Rp {{ number_format($application->recruitment->salary_max, 0, ',', '.') }}
                                                    @endif
                                                </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $application->created_at->format('d M Y, H:i') }}</td>
                                        <td>
                                            <span class="badge {{ $application->getStatusBadgeClass() }}">
                                                {{ $application->getStatusLabel() }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ ucfirst($application->getCurrentStage()) }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('recruitments.show', $application->recruitment) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="View Job Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('recruitments.application-status', $application->recruitment) }}" 
                                                   class="btn btn-sm btn-outline-info" title="View Application Status">
                                                    <i class="bi bi-info-circle"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $applications->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-briefcase text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">No Job Applications Yet</h5>
                            <p class="text-muted">You haven't applied to any jobs yet. Start exploring available positions!</p>
                            <a href="{{ route('recruitments.index') }}" class="btn btn-primary mt-3">
                                <i class="bi bi-briefcase"></i> Browse Available Jobs
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Application Status Details Modal (if needed) -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel">Application Status Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="statusDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable if you have many applications
        if ($('#applicationsTable tbody tr').length > 10) {
            $('#applicationsTable').DataTable({
                "pageLength": 10,
                "order": [[ 1, "desc" ]], // Order by applied date descending
                "columnDefs": [
                    { "orderable": false, "targets": 4 } // Disable ordering on Actions column
                ]
            });
        }
    });
</script>
@endsection
