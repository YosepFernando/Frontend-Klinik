<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\ReligiousStudyController;
use App\Services\ApiService;

// API Status Check Route
Route::get('/api-status', function (ApiService $apiService) {
    $status = $apiService->testConnection();
    return response()->json([
        'status' => $status ? 'success' : 'error',
        'message' => $status ? 'API Server tersedia' : 'API Server tidak dapat diakses',
        'api_url' => 'http://localhost:8002/api'
    ]);
})->name('api.status');

// Authentication Routes
Auth::routes();

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
});

// Dashboard Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [DashboardController::class, 'index'])->name('home');
    
    // HRD Dashboard (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/hrd-dashboard', [DashboardController::class, 'hrdDashboard'])->name('hrd.dashboard');
    });
});

// Protected Routes with Role-based Access Control
Route::middleware(['auth'])->group(function () {
    
    // Treatment Management (Admin, HRD, Front Office, Kasir, Dokter, Beautician)
    Route::middleware(['role:admin,hrd,front_office,kasir,dokter,beautician'])->group(function () {
        Route::resource('treatments', TreatmentController::class);
    });
    
    // Appointment Management - All roles can access
    Route::resource('appointments', AppointmentController::class);
    
    // Attendance Management (Admin, HRD, Front Office, Kasir, Dokter, Beautician)
    Route::middleware(['role:admin,hrd,front_office,kasir,dokter,beautician'])->group(function () {
        Route::resource('attendances', AttendanceController::class);
        Route::post('attendances/checkout', [AttendanceController::class, 'checkOut'])->name('attendances.checkout');
        Route::post('attendances/submit-absence', [AttendanceController::class, 'submitAbsence'])->name('attendances.submit-absence');
    });
    
    // New Absensi Management (using tb_absensi table)
    Route::middleware(['role:admin,hrd,front_office,kasir,dokter,beautician'])->group(function () {
        Route::resource('absensi', AbsensiController::class);
        Route::post('absensi/checkout', [AbsensiController::class, 'checkOut'])->name('absensi.checkout');
        Route::post('absensi/submit-absence', [AbsensiController::class, 'submitAbsence'])->name('absensi.submit-absence');
        Route::get('absensi/report', [AbsensiController::class, 'report'])->name('absensi.report');
    });
    
    // Admin-only Absensi Dashboard
    Route::middleware(['role:admin'])->group(function () {
        Route::get('absensi/dashboard', [AbsensiController::class, 'dashboard'])->name('absensi.dashboard');
    });
    
    // Admin/HRD Absensi Management
    Route::middleware(['role:admin,hrd'])->group(function () {
        Route::get('absensi/admin/create', [AbsensiController::class, 'adminCreate'])->name('absensi.admin-create');
        Route::post('absensi/admin/store', [AbsensiController::class, 'adminStore'])->name('absensi.admin-store');
        Route::get('absensi/{absensi}/admin/edit', [AbsensiController::class, 'adminEdit'])->name('absensi.admin-edit');
        Route::put('absensi/{absensi}/admin/update', [AbsensiController::class, 'adminUpdate'])->name('absensi.admin-update');
    });
    
    // Pegawai Management (Admin, HRD only)
    Route::middleware(['role:admin,hrd'])->group(function () {
        Route::resource('pegawai', PegawaiController::class);
    });
    
    // Recruitment Management - Different access for different roles
    // Recruitment Management (Admin, HRD only) - Must come BEFORE show routes
    Route::middleware(['role:admin,hrd'])->group(function () {
        Route::get('recruitments/create', [RecruitmentController::class, 'create'])->name('recruitments.create');
        Route::post('recruitments', [RecruitmentController::class, 'store'])->name('recruitments.store');
        Route::get('recruitments/{recruitment}/edit', [RecruitmentController::class, 'edit'])->name('recruitments.edit');
        Route::put('recruitments/{recruitment}', [RecruitmentController::class, 'update'])->name('recruitments.update');
        Route::delete('recruitments/{recruitment}', [RecruitmentController::class, 'destroy'])->name('recruitments.destroy');
        
        // Application Management
        Route::get('recruitments/{recruitment}/manage-applications', [RecruitmentController::class, 'manageApplications'])->name('recruitments.manage-applications');
        Route::patch('applications/{application}/document-status', [RecruitmentController::class, 'updateDocumentStatus'])->name('applications.update-document-status');
        Route::patch('applications/{application}/schedule-interview', [RecruitmentController::class, 'scheduleInterview'])->name('applications.schedule-interview');
        Route::patch('applications/{application}/interview-result', [RecruitmentController::class, 'updateInterviewResult'])->name('applications.update-interview-result');
        Route::patch('applications/{application}/final-decision', [RecruitmentController::class, 'updateFinalDecision'])->name('applications.update-final-decision');
    });
    
    // Public recruitment access
    Route::get('recruitments', [RecruitmentController::class, 'index'])->name('recruitments.index');
    Route::get('recruitments/{recruitment}', [RecruitmentController::class, 'show'])->name('recruitments.show');
    
    // Recruitment Apply (for Pelanggan only)
    Route::middleware(['role:pelanggan'])->group(function () {
        Route::get('recruitments/{recruitment}/apply', [RecruitmentController::class, 'showApplyForm'])->name('recruitments.apply.form');
        Route::post('recruitments/{recruitment}/apply', [RecruitmentController::class, 'apply'])->name('recruitments.apply');
        Route::get('recruitments/{recruitment}/application-status', [RecruitmentController::class, 'applicationStatus'])->name('recruitments.application-status');
        Route::get('my-applications', [RecruitmentController::class, 'myApplications'])->name('recruitments.my-applications');
    });
    
    // Training Management - Different access levels
    // Training CRUD (Admin, HRD only) - Must come BEFORE show routes
    Route::middleware(['role:admin,hrd'])->group(function () {
        Route::get('trainings/create', [TrainingController::class, 'create'])->name('trainings.create');
        Route::post('trainings', [TrainingController::class, 'store'])->name('trainings.store');
        Route::get('trainings/{training}/edit', [TrainingController::class, 'edit'])->name('trainings.edit');
        Route::put('trainings/{training}', [TrainingController::class, 'update'])->name('trainings.update');
        Route::delete('trainings/{training}', [TrainingController::class, 'destroy'])->name('trainings.destroy');
    });
    
    // View Training (Admin, HRD, Front Office, Kasir, Dokter, Beautician, Pelanggan)
    Route::middleware(['role:admin,hrd,front_office,kasir,dokter,beautician,pelanggan'])->group(function () {
        Route::get('trainings', [TrainingController::class, 'index'])->name('trainings.index');
        Route::get('trainings/{training}', [TrainingController::class, 'show'])->name('trainings.show');
    });
    
    // Religious Study Management - Different access levels
    // View and Join Religious Study (Admin, HRD, Front Office, Kasir, Dokter, Beautician)
    Route::middleware(['role:admin,hrd,front_office,kasir,dokter,beautician'])->group(function () {
        Route::get('religious-studies', [ReligiousStudyController::class, 'index'])->name('religious-studies.index');
        Route::get('religious-studies/{religious_study}', [ReligiousStudyController::class, 'show'])->name('religious-studies.show');
        Route::post('religious-studies/{religious_study}/join', [ReligiousStudyController::class, 'join'])->name('religious-studies.join');
        Route::delete('religious-studies/{religious_study}/leave', [ReligiousStudyController::class, 'leave'])->name('religious-studies.leave');
    });
    
    // Religious Study Update Only (Admin, HRD only) - No Create/Delete
    Route::middleware(['role:admin,hrd'])->group(function () {
        Route::get('religious-studies/{religious_study}/edit', [ReligiousStudyController::class, 'edit'])->name('religious-studies.edit');
        Route::put('religious-studies/{religious_study}', [ReligiousStudyController::class, 'update'])->name('religious-studies.update');
    });
    
    // User Management (Admin, HRD only)
    Route::middleware(['role:admin,hrd'])->group(function () {
        Route::get('users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');
        Route::get('users/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');
        Route::post('users', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
        Route::get('users/{user}', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/toggle-status', [App\Http\Controllers\UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::delete('users/{user}', [App\Http\Controllers\UserController::class, 'destroy'])->name('users.destroy');
    });
});

// Temporary debug route
Route::get('/debug-role', function() {
    if (!auth()->check()) {
        return 'Not logged in';
    }
    
    $user = auth()->user();
    return [
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'isHRD' => $user->isHRD(),
        'isAdmin' => $user->isAdmin(),
    ];
})->name('debug.role');

// Temporary test login route for HRD
Route::get('/test-login-hrd', function() {
    $user = \App\Models\User::where('role', 'hrd')->first();
    if ($user) {
        auth()->login($user);
        return [
            'message' => 'Logged in as HRD',
            'user' => $user->name,
            'role' => $user->role,
            'recruitments_create_url' => route('recruitments.create'),
            'trainings_create_url' => route('trainings.create'),
        ];
    }
    return 'HRD user not found';
})->name('test.login.hrd');

// Debug routes untuk troubleshooting
Route::get('/debug-auth', function() {
    if (!auth()->check()) {
        return ['status' => 'not_logged_in'];
    }
    
    $user = auth()->user();
    return [
        'status' => 'logged_in',
        'user' => [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ],
        'role_checks' => [
            'isAdmin' => $user->isAdmin(),
            'isHRD' => $user->isHRD(),
            'role_matches_admin' => $user->role === 'admin',
            'role_matches_hrd' => $user->role === 'hrd',
        ],
        'can_access' => [
            'recruitment_create' => in_array($user->role, ['admin', 'hrd']),
            'training_create' => in_array($user->role, ['admin', 'hrd']),
        ]
    ];
});

Route::get('/test-recruitment-create', function() {
    return 'Recruitment create page accessed successfully!';
})->middleware(['auth', 'role:admin,hrd']);

Route::get('/test-training-create', function() {
    return 'Training create page accessed successfully!';
})->middleware(['auth', 'role:admin,hrd']);

// Debug routes khusus untuk troubleshooting masalah akses
Route::get('/debug-middleware', function() {
    if (!auth()->check()) {
        return ['status' => 'not_logged_in', 'message' => 'Please login first'];
    }
    
    $user = auth()->user();
    
    // Test manual middleware logic
    $allowedRoles = ['admin', 'hrd'];
    $userHasRole = in_array($user->role, $allowedRoles);
    
    return [
        'status' => 'logged_in',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ],
        'middleware_check' => [
            'allowed_roles' => $allowedRoles,
            'user_role' => $user->role,
            'user_has_role' => $userHasRole,
            'should_pass' => $userHasRole,
        ],
        'test_urls' => [
            'recruitment_create' => route('recruitments.create'),
            'training_create' => route('trainings.create'),
        ]
    ];
});

Route::get('/direct-recruitment-create', function() {
    return view('recruitments.create');
})->middleware(['auth']);

Route::get('/direct-training-create', function() {
    return view('trainings.create');
})->middleware(['auth']);

// Simple login test route
Route::get('/easy-test', function() {
    // Login as HRD
    $hrdUser = \App\Models\User::where('role', 'hrd')->first();
    if (!$hrdUser) {
        return 'HRD user not found';
    }
    
    auth()->login($hrdUser);
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <title>Test HRD Access</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <h2>Test HRD Access</h2>
            <p>Logged in as: <strong>' . auth()->user()->name . '</strong> (Role: ' . auth()->user()->role . ')</p>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Test Recruitment Create</div>
                        <div class="card-body">
                            <a href="' . route('recruitments.create') . '" class="btn btn-primary" target="_blank">
                                Buka Tambah Lowongan
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Test Training Create</div>
                        <div class="card-body">
                            <a href="' . route('trainings.create') . '" class="btn btn-success" target="_blank">
                                Buka Tambah Pelatihan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-3">
                <a href="/dashboard" class="btn btn-secondary">Dashboard</a>
                <a href="/debug-middleware" class="btn btn-info">Debug Middleware</a>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
});
