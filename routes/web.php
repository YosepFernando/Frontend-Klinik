<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\RecruitmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\ReligiousStudyController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\MasterGajiController;
use App\Services\GajiService;

// API Status Check Route
Route::get('/api-status', function (ApiService $apiService) {
    $status = $apiService->testConnection();
    return response()->json([
        'status' => $status ? 'success' : 'error',
        'message' => $status ? 'API Server tersedia' : 'API Server tidak dapat diakses',
        'api_url' => 'http://localhost:8002/api'
    ]);
})->name('api.status');

// Debug Session Route
Route::get('/debug-session', function () {
    $apiToken = session('api_token');
    
    $data = [
        'session_status' => [
            'session_id' => session_id(),
            'session_active' => session_status() == PHP_SESSION_ACTIVE,
            'session_file_path' => session_save_path()
        ],
        'authentication' => [
            'authenticated' => session('authenticated'),
            'user_id' => session('user_id'),
            'user_email' => session('user_email'),
            'user_name' => session('user_name'),
            'user_role' => session('user_role')
        ],
        'api_token' => [
            'exists' => !empty($apiToken),
            'length' => $apiToken ? strlen($apiToken) : 0,
            'preview' => $apiToken ? substr($apiToken, 0, 20) . '...' : null,
            'format_valid' => $apiToken ? preg_match('/^[a-zA-Z0-9_\-\.]+$/', $apiToken) : false
        ],
        'full_session' => session()->all()
    ];
    
    // Test API connection if token exists
    if ($apiToken) {
        try {
            $apiService = app(\App\Services\ApiService::class);
            $response = $apiService->withToken($apiToken)->get('auth/profile');
            $data['api_test'] = [
                'status' => 'success',
                'response' => $response
            ];
        } catch (Exception $e) {
            $data['api_test'] = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    } else {
        $data['api_test'] = [
            'status' => 'skipped',
            'message' => 'No token available'
        ];
    }
    
    return response()->json($data, 200, [], JSON_PRETTY_PRINT);
})->name('debug.session');

// Authentication Routes
Auth::routes();

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
});

// Dashboard Routes
Route::middleware(['api.auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/home', [DashboardController::class, 'index'])->name('home');
    
    // HRD Dashboard (Admin only)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/hrd-dashboard', [DashboardController::class, 'hrdDashboard'])->name('hrd.dashboard');
    });
});

// Protected Routes with Role-based Access Control
Route::middleware(['api.auth'])->group(function () {
    
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
        Route::match(['GET', 'POST'], 'absensi/export-pdf', [AbsensiController::class, 'exportPdf'])->name('absensi.export-pdf');
        Route::post('absensi/export-monthly-pdf', [AbsensiController::class, 'exportMonthlyPdf'])->name('absensi.export-monthly-pdf');
        Route::get('absensi/debug-api', [AbsensiController::class, 'debugApiData'])->name('absensi.debug-api');
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
        Route::post('absensi/export-monthly-pdf', [AbsensiController::class, 'exportMonthlyPdf'])->name('absensi.export-monthly-pdf');
    });
    
    // Pegawai Management (Admin, HRD only)
    Route::middleware(['role:admin,hrd'])->group(function () {
        Route::resource('pegawai', PegawaiController::class);
        Route::get('pegawai-export-pdf', [PegawaiController::class, 'exportPdf'])->name('pegawai.export-pdf');
    });
    
    // Recruitment Management - Different access for different roles
    // Recruitment Management (Admin, HRD only) - Must come BEFORE show routes
    Route::middleware(['role:admin,hrd'])->group(function () {
        Route::get('recruitments/create', [RecruitmentController::class, 'create'])->name('recruitments.create');
        Route::post('recruitments', [RecruitmentController::class, 'store'])->name('recruitments.store');
        Route::get('recruitments/{recruitment}/edit', [RecruitmentController::class, 'edit'])->name('recruitments.edit');
        Route::put('recruitments/{recruitment}', [RecruitmentController::class, 'update'])->name('recruitments.update');
        Route::delete('recruitments/{recruitment}', [RecruitmentController::class, 'destroy'])->name('recruitments.destroy');
        Route::delete('recruitments/{recruitment}/force', [RecruitmentController::class, 'forceDestroy'])->name('recruitments.force-destroy');
        Route::delete('recruitments/bulk-delete', [RecruitmentController::class, 'bulkDestroy'])->name('recruitments.bulk-destroy');
        
        // Application Management
        Route::get('recruitments/{id}/manage-applications', [RecruitmentController::class, 'manageApplications'])->name('recruitments.manage-applications');
        
        // Application Status Updates - Include recruitment context
        Route::patch('recruitments/{recruitmentId}/applications/{applicationId}/document-status', function(Request $request, $recruitmentId, $applicationId) {
            return app(RecruitmentController::class)->updateDocumentStatusWithContext($request, $recruitmentId, $applicationId);
        })->name('recruitments.applications.update-document-status');
        
        Route::patch('recruitments/{recruitmentId}/applications/{applicationId}/schedule-interview', function(Request $request, $recruitmentId, $applicationId) {
            return app(RecruitmentController::class)->scheduleInterviewWithContext($request, $recruitmentId, $applicationId);
        })->name('recruitments.applications.schedule-interview');
        
        Route::patch('recruitments/{recruitmentId}/applications/{applicationId}/interview-result', function(Request $request, $recruitmentId, $applicationId) {
            return app(RecruitmentController::class)->updateInterviewResultWithContext($request, $recruitmentId, $applicationId);
        })->name('recruitments.applications.update-interview-result');
        
        Route::patch('recruitments/{recruitmentId}/applications/{applicationId}/final-decision', function(Request $request, $recruitmentId, $applicationId) {
            return app(RecruitmentController::class)->updateFinalDecisionWithContext($request, $recruitmentId, $applicationId);
        })->name('recruitments.applications.update-final-decision');
        
        // API endpoint for creating employee when final decision is accepted
        Route::post('api/recruitments/applications/{applicationId}/create-employee', [RecruitmentController::class, 'createEmployeeFromApplication'])->name('api.applications.create-employee');
        
        // Legacy routes (for backward compatibility)
        Route::patch('applications/{applicationId}/document-status', [RecruitmentController::class, 'updateDocumentStatus'])->name('applications.update-document-status');
        Route::patch('applications/{applicationId}/schedule-interview', [RecruitmentController::class, 'scheduleInterview'])->name('applications.schedule-interview');
        Route::patch('applications/{applicationId}/interview-result', [RecruitmentController::class, 'updateInterviewResult'])->name('applications.update-interview-result');
        Route::patch('applications/{applicationId}/final-decision', [RecruitmentController::class, 'updateFinalDecision'])->name('applications.update-final-decision');
    });
    
    // Public recruitment access
    Route::get('recruitments', [RecruitmentController::class, 'index'])->name('recruitments.index');
    Route::get('recruitments/{recruitment}', [RecruitmentController::class, 'show'])->name('recruitments.show');

    // Recruitment Apply (for Pelanggan only) - Within api.auth middleware group
    Route::middleware(['role:pelanggan'])->group(function () {
        Route::get('recruitments/{id}/apply', [RecruitmentController::class, 'showApplyForm'])->name('recruitments.apply.form');
        Route::post('recruitments/{id}/apply', [RecruitmentController::class, 'apply'])->name('recruitments.apply');
        Route::get('recruitments/{id}/application-status', [RecruitmentController::class, 'applicationStatus'])->name('recruitments.application-status');
        Route::get('my-applications', [RecruitmentController::class, 'myApplications'])->name('recruitments.my-applications');
    });
    
    // Training Management - Different access levels
    // Training CRUD (Admin, HRD only) - Must come BEFORE show routes
    Route::middleware(['api.auth', 'role:admin,hrd'])->group(function () {
        Route::get('trainings/create', [TrainingController::class, 'create'])->name('trainings.create');
        Route::post('trainings', [TrainingController::class, 'store'])->name('trainings.store');
        Route::get('trainings/{id}/edit', [TrainingController::class, 'edit'])->name('trainings.edit');
        Route::put('trainings/{id}', [TrainingController::class, 'update'])->name('trainings.update');
    });
    
    // View and Delete Training (All authenticated users)
    Route::middleware(['api.auth', 'role:admin,hrd,front_office,kasir,dokter,beautician,pelanggan'])->group(function () {
        Route::get('trainings', [TrainingController::class, 'index'])->name('trainings.index');
        Route::get('trainings/{id}', [TrainingController::class, 'show'])->name('trainings.show');
        Route::delete('trainings/{id}', [TrainingController::class, 'destroy'])->name('trainings.destroy');
    });
    
    // Payroll Management - Semua pegawai bisa melihat gaji mereka
    // View Payroll (Semua role yang valid)
    Route::middleware(['api.auth', 'role:admin,hrd,front_office,kasir,dokter,beautician,pegawai', 'api.check'])->group(function () {
        Route::get('payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::get('payroll/export-pdf', [PayrollController::class, 'exportPdf'])->name('payroll.export-pdf');
        Route::get('payroll/{payroll}/slip', [PayrollController::class, 'exportSlip'])->name('payroll.export-slip');
        Route::get('payroll/test-slip/{id}', [PayrollController::class, 'exportSlipTest'])->name('payroll.test-slip'); // TEST ONLY
        Route::get('payroll/debug-pdf/{id}', [PayrollController::class, 'debugPdf'])->name('payroll.debug-pdf'); // DEBUG ONLY
        Route::get('payroll/{payroll}', [PayrollController::class, 'show'])->name('payroll.show');
        Route::get('payroll/employee/{pegawai}', [PayrollController::class, 'getByEmployee'])->name('payroll.employee');
    });
    
    // Simple PDF test without auth (for debugging only)
    Route::get('payroll/test-simple-pdf', [PayrollController::class, 'testSimplePdf'])->name('payroll.test-simple-pdf');
    
    // Payroll Management (Admin, HRD only) - Full CRUD
    Route::middleware(['api.auth', 'role:admin,hrd', 'api.check'])->group(function () {
        Route::get('payroll/create', [PayrollController::class, 'create'])->name('payroll.create');
        Route::post('payroll', [PayrollController::class, 'store'])->name('payroll.store');
        Route::get('payroll/{payroll}/edit', [PayrollController::class, 'edit'])->name('payroll.edit');
        Route::put('payroll/{payroll}', [PayrollController::class, 'update'])->name('payroll.update');
        Route::delete('payroll/{payroll}', [PayrollController::class, 'destroy'])->name('payroll.destroy');
        Route::get('payroll/generate/form', [PayrollController::class, 'showGenerateForm'])->name('payroll.generate.form');
        Route::post('payroll/generate', [PayrollController::class, 'generatePayroll'])->name('payroll.generate');
        Route::put('payroll/{payroll}/payment-status', [PayrollController::class, 'updatePaymentStatus'])
            ->name('payroll.payment-status')
            ->middleware('session.valid');
        
        // Master Gaji routes
        Route::post('master-gaji', [MasterGajiController::class, 'store'])->name('master-gaji.store');
        Route::get('api/pegawai/all', [MasterGajiController::class, 'getPegawai'])->name('api.pegawai.all');
    });
    
    // User Management (Admin, HRD only)
    Route::middleware(['role:admin'])->group(function () {
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

// TEST ONLY - Route untuk test slip gaji tanpa auth
Route::get('/test-slip/{id}', [App\Http\Controllers\PayrollController::class, 'exportSlipTest'])->name('test.slip');

// Temporary debug route
Route::get('/debug-role', function() {
    $user = auth_user();
    $role = user_role();
    
    return response()->json([
        'auth_user' => $user,
        'user_role' => $role,
        'session_data' => [
            'authenticated' => session('authenticated'),
            'user_id' => session('user_id'),
            'user_name' => session('user_name'),
            'user_email' => session('user_email'),
            'user_role' => session('user_role'),
            'has_api_token' => session()->has('api_token'),
            'has_api_user' => session()->has('api_user'),
        ],
        'role_checks' => [
            'is_admin' => is_admin(),
            'is_hrd' => is_hrd(),
            'is_dokter' => is_dokter(),
            'is_beautician' => is_beautician(),
            'is_front_office' => is_front_office(),
            'is_kasir' => is_kasir(),
            'is_pelanggan' => is_pelanggan(),
        ]
    ]);
})->middleware('api.auth')->name('debug.role');

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

// Test login route untuk debugging
Route::get('/test-login', function (\App\Services\AuthService $authService) {
    try {
        $loginResponse = $authService->login('admin@klinik.com', 'admin123');
        
        if (isset($loginResponse['status']) && $loginResponse['status'] === 'success') {
            // Set session data seperti di LoginController
            $apiUser = $loginResponse['data']['user'];
            $token = $loginResponse['data']['token'];
            
            session([
                'api_token' => $token,
                'api_user' => $apiUser,
                'user_role' => $apiUser['role'],
                'authenticated' => true,
                'user_id' => $apiUser['id_user'],
                'user_email' => $apiUser['email'],
                'user_name' => $apiUser['nama_user']
            ]);
            
            return redirect()->route('payroll.index')
                ->with('success', 'Login berhasil! Token: ' . substr($token, 0, 20) . '...');
        } else {
            return redirect()->route('login')
                ->with('error', 'Login gagal: ' . ($loginResponse['message'] ?? 'Unknown error'));
        }
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
})->name('test.login');

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

// Debug route for testing gaji API
Route::get('/debug-gaji', function (GajiService $gajiService, \App\Services\AuthService $authService) {
    try {
        // First try to login with admin credentials to get a token
        if (!session('api_token')) {
            $loginResponse = $authService->login('admin@klinik.com', 'admin123');
            if (isset($loginResponse['status']) && $loginResponse['status'] === 'success') {
                $token = $loginResponse['data']['token'] ?? null;
                if ($token) {
                    session(['api_token' => $token]);
                }
            } else {
                return response()->json([
                    'error' => 'Failed to login',
                    'login_response' => $loginResponse
                ], 401);
            }
        }
        
        $response = $gajiService->getAll();
        
        return response()->json([
            'debug_info' => [
                'status' => $response['status'] ?? 'N/A',
                'message' => $response['pesan'] ?? $response['message'] ?? 'N/A',
                'has_data' => isset($response['data']),
                'data_structure' => isset($response['data']) ? array_keys($response['data']) : [],
                'data_count' => isset($response['data']['data']) ? count($response['data']['data']) : (isset($response['data']) && is_array($response['data']) ? count($response['data']) : 0),
                'api_base_url' => env('API_BASE_URL', 'http://127.0.0.1:8002/api'),
                'session_token' => session('api_token') ? 'Present (' . strlen(session('api_token')) . ' chars)' : 'Missing'
            ],
            'first_item' => isset($response['data']['data']) && count($response['data']['data']) > 0 ? $response['data']['data'][0] : null,
            'full_response' => $response
        ], 200, [], JSON_PRETTY_PRINT);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->name('debug.gaji');

// Debug auth status
Route::get('/debug-auth', function () {
    return response()->json([
        'session_data' => [
            'authenticated' => session('authenticated'),
            'api_token' => session('api_token') ? 'Present (' . strlen(session('api_token')) . ' chars)' : 'Missing',
            'user_id' => session('user_id'),
            'user_name' => session('user_name'),
            'user_role' => session('user_role'),
        ],
        'auth_status' => Auth::check(),
        'env_api_url' => env('API_BASE_URL', 'http://127.0.0.1:8002/api')
    ], 200, [], JSON_PRETTY_PRINT);
})->name('debug.auth');

// Debug route for PDF export testing
Route::get('/debug-pdf-export', [App\Http\Controllers\DebugController::class, 'testPdfExport'])->name('debug.pdf-export');

// Debug route for training deletion
Route::get('/debug-training-delete/{id}', function($id) {
    try {
        \Log::info('Debug: Testing training deletion', ['id' => $id]);
        
        // Check authentication
        if (!session('authenticated')) {
            return response()->json(['error' => 'Not authenticated', 'session' => session()->all()]);
        }
        
        $pelatihanService = new \App\Services\PelatihanService();
        
        // First, try to get the training
        $getResponse = $pelatihanService->getById($id);
        \Log::info('Debug: Get training response', ['response' => $getResponse]);
        
        if (!$getResponse || !isset($getResponse['status']) || $getResponse['status'] !== 'success') {
            return response()->json(['error' => 'Training not found', 'response' => $getResponse]);
        }
        
        // Now try to delete
        $deleteResponse = $pelatihanService->delete($id);
        \Log::info('Debug: Delete training response', ['response' => $deleteResponse]);
        
        return response()->json([
            'training_data' => $getResponse,
            'delete_result' => $deleteResponse
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Debug delete error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json(['error' => $e->getMessage()]);
    }
})->middleware(['role:admin,hrd,front_office,kasir,dokter,beautician,pelanggan', 'api.check']);

// PDF Test Routes (No Authentication Required)
Route::get('test/pdf/simple', [App\Http\Controllers\PdfTestController::class, 'testSimple'])->name('test.pdf.simple');
Route::get('test/pdf/slip', [App\Http\Controllers\PdfTestController::class, 'testSlip'])->name('test.pdf.slip');

// Route untuk check session status via AJAX
Route::get('/check-session', function() {
    return response()->json([
        'authenticated' => session('authenticated', false),
        'has_api_token' => session()->has('api_token'),
        'user_id' => session('user_id'),
        'user_role' => session('user_role'),
        'session_id' => session()->getId(),
        'timestamp' => time()
    ]);
})->middleware(['web']);

// Route untuk refresh CSRF token
Route::get('/csrf-token', function() {
    return response()->json([
        'csrf_token' => csrf_token(),
        'timestamp' => time()
    ]);
})->middleware(['web']);

// Debug route untuk check session dan token API
Route::get('/debug-session-token', function() {
    $sessionData = [
        'session_id' => session()->getId(),
        'authenticated' => session('authenticated'),
        'api_token' => session('api_token') ? substr(session('api_token'), 0, 20) . '...' : null,
        'api_token_length' => session('api_token') ? strlen(session('api_token')) : 0,
        'user_id' => session('user_id'),
        'user_role' => session('user_role'),
        'user_name' => session('user_name'),
        'user_email' => session('user_email'),
        'api_user' => session('api_user') ? 'Present' : 'Missing',
        'all_session_keys' => array_keys(session()->all())
    ];
    
    // Test API connection if token exists
    if (session('api_token')) {
        try {
            $authService = app(\App\Services\AuthService::class);
            $profileResponse = $authService->withToken(session('api_token'))->getProfile();
            $sessionData['token_test'] = [
                'status' => $profileResponse['status'] ?? 'unknown',
                'message' => $profileResponse['message'] ?? 'no message',
                'valid' => isset($profileResponse['status']) && $profileResponse['status'] === 'success'
            ];
        } catch (\Exception $e) {
            $sessionData['token_test'] = [
                'error' => $e->getMessage(),
                'valid' => false
            ];
        }
    } else {
        $sessionData['token_test'] = 'No token to test';
    }
    
    return response()->json($sessionData, 200, [], JSON_PRETTY_PRINT);
})->middleware(['web']);

require __DIR__.'/debug.php';
