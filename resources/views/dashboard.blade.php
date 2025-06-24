@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Stats Cards -->
        @if($user->isAdmin() || $user->isHRD())
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Users
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers ?? 0 }}</div>
                                <small class="text-muted">{{ $totalActiveUsers ?? 0 }} aktif</small>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-people fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Today's Appointments
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayAppointments ?? 0 }}</div>
                                <small class="text-muted">{{ $pendingAppointments ?? 0 }} pending</small>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-calendar-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Treatments
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalTreatments ?? 0 }}</div>
                                <small class="text-muted">{{ $completedAppointments ?? 0 }} completed</small>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-heart-pulse fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="col-xl-6 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Job Applications
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalApplications ?? 0 }}</div>
                                <small class="text-muted">{{ $acceptedApplications ?? 0 }} accepted</small>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-briefcase fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Your Role
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-person-badge fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions -->
        @if(!$user->isPelanggan())
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($user->isAdmin() || $user->isFrontOffice())
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('treatments.create') }}" class="btn btn-success btn-block">
                                    <i class="bi bi-plus-circle"></i> Add Treatment
                                </a>
                            </div>
                        @endif

                        @if($user->isHRD() || $user->isAdmin())
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('trainings.create') }}" class="btn btn-info btn-block">
                                    <i class="bi bi-plus-circle"></i> Create Training
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('religious-studies.index') }}" class="btn btn-warning btn-block">
                                    <i class="bi bi-eye"></i> View Pengajian
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('users.index') }}" class="btn btn-secondary btn-block">
                                    <i class="bi bi-people"></i> Manage Users
                                </a>
                            </div>
                            <div class="col-md-6 mb-2">
                                <a href="{{ route('users.create') }}" class="btn btn-dark btn-block">
                                    <i class="bi bi-person-plus"></i> Add User
                                </a>
                            </div>
                        @endif

                        @if(!$user->isPelanggan())
                            <div class="col-md-6 mb-2">
                                <button onclick="checkIn()" class="btn btn-outline-success btn-block">
                                    <i class="bi bi-clock-history"></i> Check In
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button onclick="checkOut()" class="btn btn-outline-danger btn-block">
                                    <i class="bi bi-clock"></i> Check Out
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Upcoming Schedule -->
        @if(!$user->isPelanggan())
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upcoming Schedule</h6>
                </div>
                <div class="card-body">
                    @if($upcomingAppointments->count() > 0)
                        @foreach($upcomingAppointments as $appointment)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>{{ $appointment->treatment->name }}</strong><br>
                                <small class="text-muted">
                                    {{ $appointment->appointment_date->format('d M Y, H:i') }}
                                    - {{ $appointment->patient->name }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $appointment->status == 'confirmed' ? 'success' : 'warning' }}">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted">No upcoming schedule</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($user->isPelanggan())
        <!-- My Job Applications for Pelanggan -->
        <div class="col-lg-12 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">My Job Applications</h6>
                    <a href="{{ route('recruitments.index') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> Apply Job
                    </a>
                </div>
                <div class="card-body">
                    @if(isset($myApplications) && $myApplications->count() > 0)
                        @foreach($myApplications as $application)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                            <div class="flex-grow-1">
                                <strong>{{ $application->recruitment->title ?? 'N/A' }}</strong><br>
                                <small class="text-muted">
                                    {{ $application->recruitment->posisi->nama_posisi ?? 'Position not available' }} - 
                                    Applied: {{ $application->created_at->format('d M Y') }}
                                </small>
                                @if($application->interview_date)
                                <br><small class="text-info">
                                    <i class="bi bi-calendar"></i> Interview: {{ $application->interview_date->format('d M Y, H:i') }}
                                </small>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="badge {{ $application->getStatusBadgeClass() }} mb-2">
                                    {{ $application->getStatusLabel() }}
                                </span>
                                @if($application->overall_status == 'accepted')
                                <br><small class="text-success">
                                    <i class="bi bi-check-circle"></i> Congratulations!
                                </small>
                                @endif
                                <br>
                                <div class="btn-group mt-2" role="group">
                                    <a href="{{ route('recruitments.show', $application->recruitment) }}" 
                                       class="btn btn-sm btn-outline-primary" title="View Job Details">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('recruitments.application-status', $application->recruitment) }}" 
                                       class="btn btn-sm btn-outline-info" title="View Application Status">
                                        <i class="bi bi-info-circle"></i> Status
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @if($myApplications->count() >= 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('recruitments.my-applications') }}" class="btn btn-outline-primary">
                                    View All Applications
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-briefcase text-muted" style="font-size: 3rem;"></i>
                            <h5 class="mt-3 text-muted">No Job Applications Yet</h5>
                            <p class="text-muted">You haven't applied to any jobs yet. Start exploring available positions!</p>
                            <a href="{{ route('recruitments.index') }}" class="btn btn-primary mt-3">
                                <i class="bi bi-briefcase"></i> Browse Jobs
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>

    @if($user->isHRD() || $user->isAdmin())
    <div class="row">
        <!-- Recent Trainings -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Trainings</h6>
                </div>
                <div class="card-body">
                    @if($upcomingTrainings->count() > 0)
                        @foreach($upcomingTrainings as $training)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>{{ $training->judul }}</strong><br>
                                <small class="text-muted">
                                    {{ $training->jenis_display }} - {{ $training->durasi_display }}
                                </small>
                            </div>
                            <span class="{{ $training->status_badge_class }}">{{ $training->status_display }}</span>
                        </div>
                        @endforeach
                        <div class="text-center mt-3">
                            <a href="{{ route('trainings.index') }}" class="btn btn-primary btn-sm">View All Trainings</a>
                        </div>
                    @else
                        <p class="text-muted">No trainings available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Religious Studies -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Upcoming Pengajian</h6>
                </div>
                <div class="card-body">
                    @if($upcomingReligiousStudies->count() > 0)
                        @foreach($upcomingReligiousStudies as $study)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong>{{ $study->title }}</strong><br>
                                <small class="text-muted">
                                    {{ $study->scheduled_date->format('d M Y, H:i') }}
                                </small>
                            </div>
                            <span class="badge bg-warning">{{ $study->status }}</span>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted">No upcoming pengajian</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function checkIn() {
        // Redirect to the check-in page instead of trying to do it via AJAX
        window.location.href = '{{ route("attendances.create") }}';
    }

    function checkOut() {
        if (confirm('Yakin ingin check out?')) {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("attendances.checkout") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>
@endsection
