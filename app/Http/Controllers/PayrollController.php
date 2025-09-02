<?php

namespace App\Http\Controllers;

use App\Services\GajiService;
use App\Services\PegawaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollController extends Controller
{
    protected $gajiService;
    protected $pegawaiService;
    
    public function __construct(GajiService $gajiService, PegawaiService $pegawaiService)
    {
        $this->gajiService = $gajiService;
        $this->pegawaiService = $pegawaiService;
    }
    
    /**
     * Display a listing of payroll records.
     */
    public function index(Request $request)
    {
        try {
            // Check if user is authenticated and has valid session
            if (!session('api_token') || !session('authenticated')) {
                Log::warning('PayrollController::index - No valid authentication found', [
                    'has_token' => session('api_token') ? 'yes' : 'no',
                    'authenticated' => session('authenticated') ? 'yes' : 'no',
                    'user_id' => session('user_id')
                ]);
                
                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali untuk mengakses data payroll.');
            }
            
            $params = [];
            $user = auth_user();
            
            // Handle refresh pegawai request
            if ($request->has('refresh_pegawai') && !in_array($user->role, ['admin', 'hrd'])) {
                Log::info('PayrollController - Refresh pegawai data requested');
                if (refresh_pegawai_data()) {
                    return redirect()->route('payroll.index')->with('success', 'Data pegawai berhasil diperbarui');
                } else {
                    return redirect()->route('payroll.index')->with('error', 'Gagal memperbarui data pegawai');
                }
            }
            
            // Log user info for debugging
            Log::info('PayrollController@index - User info:', [
                'user_id' => $user->id ?? 'N/A',
                'user_role' => $user->role ?? 'N/A',
                'session_user_id' => session('user_id'),
                'session_pegawai_id' => session('pegawai_id'),
                'session_api_token' => session('api_token') ? 'present' : 'missing'
            ]);
            
            // Add pagination parameter
            if ($request->filled('page')) {
                $params['page'] = $request->page;
            }
            
            // Add common filter parameters BEFORE API call
            if ($request->filled('periode_bulan')) {
                $params['periode_bulan'] = $request->periode_bulan;
            }
            
            if ($request->filled('periode_tahun')) {
                $params['periode_tahun'] = $request->periode_tahun;
            }
            
            if ($request->filled('status')) {
                $params['status'] = $request->status;
            }
            
            // Filter berdasarkan role user
            // Jika bukan admin/hrd, gunakan API endpoint khusus untuk gaji pegawai sendiri
            if (!in_array($user->role, ['admin', 'hrd'])) {
                Log::info('PayrollController - Using my-data endpoint for non-admin user:', [
                    'user_role' => $user->role,
                    'session_user_id' => session('user_id'),
                    'filter_params' => $params
                ]);
                
                // Gunakan endpoint khusus untuk data gaji pegawai sendiri
                $response = $this->gajiService->withToken(session('api_token'))->getMyData($params);
                
                Log::info('PayrollController - My Gaji API response:', [
                    'response_status' => $response['status'] ?? 'N/A',
                    'response_message' => $response['message'] ?? 'N/A',
                    'has_data' => isset($response['data']),
                    'data_type' => isset($response['data']) ? gettype($response['data']) : 'N/A'
                ]);
            } else {
                // Admin/HRD bisa melihat semua data gaji
                
                // Add search parameters (hanya untuk admin/hrd)
                if ($request->filled('search')) {
                    $params['search'] = $request->search;
                }
                
                if ($request->filled('pegawai_id')) {
                    $params['id_pegawai'] = $request->pegawai_id;
                }
                
                Log::info('PayrollController - Calling getAll API for admin/hrd user:', [
                    'user_role' => $user->role,
                    'params' => $params
                ]);
                
                $response = $this->gajiService->withToken(session('api_token'))->getAll($params);
            }
            
            Log::info('PayrollController - API Response:', [
                'params' => $params,
                'response_status' => $response['status'] ?? 'N/A',
                'response_message' => $response['pesan'] ?? $response['message'] ?? 'N/A',
                'has_data' => isset($response['data']),
                'data_count' => isset($response['data']['data']) ? count($response['data']['data']) : (isset($response['data']) && is_array($response['data']) ? count($response['data']) : 0),
                'session_token' => session('api_token') ? 'Present' : 'Missing',
                'full_response_keys' => array_keys($response ?? [])
            ]);
            
            if (isset($response['status']) && ($response['status'] === 'success' || $response['status'] === 'sukses')) {
                // Handle paginated response - Use simple collection with manual pagination
                $data = $response['data']['data'] ?? $response['data'] ?? [];
                $payrolls = collect($data);
                
                // Add pagination metadata as properties
                $payrolls->paginationData = [
                    'current_page' => $response['data']['current_page'] ?? 1,
                    'last_page' => $response['data']['last_page'] ?? 1,
                    'total' => $response['data']['total'] ?? count($data),
                    'per_page' => $response['data']['per_page'] ?? 15,
                    'from' => $response['data']['from'] ?? 1,
                    'to' => $response['data']['to'] ?? count($data),
                    'has_pages' => ($response['data']['last_page'] ?? 1) > 1,
                    'has_more_pages' => ($response['data']['current_page'] ?? 1) < ($response['data']['last_page'] ?? 1),
                    'on_first_page' => ($response['data']['current_page'] ?? 1) <= 1,
                    'prev_page_url' => $response['data']['prev_page_url'] ?? null,
                    'next_page_url' => $response['data']['next_page_url'] ?? null,
                ];
                
            } else {
                $payrolls = collect([]);
                $payrolls->paginationData = [
                    'current_page' => 1,
                    'last_page' => 1,
                    'total' => 0,
                    'per_page' => 15,
                    'from' => 0,
                    'to' => 0,
                    'has_pages' => false,
                    'has_more_pages' => false,
                    'on_first_page' => true,
                    'prev_page_url' => null,
                    'next_page_url' => null,
                ];
                
                $message = $response['pesan'] ?? $response['message'] ?? 'Gagal mengambil data gaji';
                session()->flash('error', $message);
            }
            
            // Get employees for filter dropdown
            $employeesResponse = $this->pegawaiService->getAll();
            // Handle paginated response
            $employeesData = $employeesResponse['data']['data'] ?? $employeesResponse['data'] ?? [];
            $employees = collect($employeesData);
            
            return view('payroll.index', compact('payrolls', 'employees'));
            
        } catch (\Exception $e) {
            Log::error('PayrollController::index - ' . $e->getMessage());
            return view('payroll.index')
                ->with('payrolls', collect([]))
                ->with('employees', collect([]))
                ->with('error', 'Terjadi kesalahan saat memuat data gaji.');
        }
    }
    
    /**
     * Show the form for creating a new payroll record.
     */
    public function create()
    {
        try {
            // Get employees for selection
            $employeesResponse = $this->pegawaiService->getAll();
            $employeesData = $employeesResponse['data']['data'] ?? $employeesResponse['data'] ?? [];
            $employees = collect($employeesData);
            
            return view('payroll.create', compact('employees'));
            
        } catch (\Exception $e) {
            Log::error('PayrollController::create - ' . $e->getMessage());
            return redirect()->route('payroll.index')
                ->with('error', 'Terjadi kesalahan saat memuat form tambah gaji.');
        }
    }
    
    /**
     * Store a newly created payroll record.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_pegawai' => 'required|integer',
                'periode_bulan' => 'required|integer|min:1|max:12',
                'periode_tahun' => 'required|integer|min:2020|max:' . (date('Y') + 1),
                'gaji_pokok' => 'required|numeric|min:0',
                'gaji_bonus' => 'nullable|numeric|min:0',
                'gaji_kehadiran' => 'nullable|numeric|min:0',
                'keterangan' => 'nullable|string|max:1000'
            ]);
            
            // Set default values for optional fields
            $validated['gaji_bonus'] = $validated['gaji_bonus'] ?? 0;
            $validated['gaji_kehadiran'] = $validated['gaji_kehadiran'] ?? 0;
            
            // Calculate total
            $validated['gaji_total'] = $validated['gaji_pokok'] + 
                                      $validated['gaji_bonus'] + 
                                      $validated['gaji_kehadiran'];
            
            $response = $this->gajiService->store($validated);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->route('payroll.index')
                    ->with('success', 'Data gaji berhasil ditambahkan.');
            }
            
            return redirect()->route('payroll.create')
                ->with('error', 'Gagal menambahkan data gaji: ' . ($response['message'] ?? 'Terjadi kesalahan.'))
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('PayrollController::store - ' . $e->getMessage());
            return redirect()->route('payroll.create')
                ->with('error', 'Terjadi kesalahan saat menyimpan data gaji.')
                ->withInput();
        }
    }
    
    /**
     * Display the specified payroll record.
     */
    public function show($id)
    {
        try {
            // Check if user is authenticated and has valid session
            if (!session('api_token') || !session('authenticated')) {
                Log::warning('PayrollController::show - No valid authentication found', [
                    'id' => $id,
                    'has_token' => session('api_token') ? 'yes' : 'no',
                    'authenticated' => session('authenticated') ? 'yes' : 'no',
                    'user_id' => session('user_id')
                ]);
                
                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali untuk mengakses data gaji.');
            }
            
            Log::info('PayrollController::show - Attempting to get payroll ID: ' . $id, [
                'id' => $id,
                'id_type' => gettype($id),
                'session_token' => session('api_token') ? 'Present' : 'Missing',
                'user_id' => session('user_id'),
                'user_role' => session('user_role')
            ]);
            
            $response = $this->gajiService->withToken(session('api_token'))->getById($id);
            
            Log::info('PayrollController::show - API Response:', [
                'id' => $id,
                'response_status' => $response['status'] ?? 'N/A',
                'response_message' => $response['pesan'] ?? $response['message'] ?? 'N/A',
                'has_data' => isset($response['data']),
                'response_type' => gettype($response)
            ]);
            
            // Handle different response formats from API
            if (isset($response['success']) && $response['success'] === true) {
                $payroll = $response['data'];
                return view('payroll.show', compact('payroll'));
            } elseif (isset($response['status']) && ($response['status'] === 'success' || $response['status'] === 'sukses')) {
                $payroll = $response['data'];
                return view('payroll.show', compact('payroll'));
            } elseif (isset($response['data']) && !empty($response['data'])) {
                // Sometimes API returns data directly without status wrapper
                $payroll = $response['data'];
                return view('payroll.show', compact('payroll'));
            }
            
            // Handle authentication errors
            if (isset($response['message'])) {
                if ($response['message'] === 'Unauthenticated.' || 
                    strpos($response['message'], 'Unauthorized') !== false ||
                    strpos($response['message'], 'Token') !== false ||
                    strpos($response['message'], 'Invalid credentials') !== false) {
                    
                    Log::warning('PayrollController::show - API authentication failed', [
                        'id' => $id,
                        'message' => $response['message'],
                        'session_token_present' => session('api_token') ? 'yes' : 'no'
                    ]);
                    
                    // Clear invalid session data
                    session()->forget(['api_token', 'authenticated', 'user_id', 'user_role']);
                    
                    return redirect()->route('login')
                        ->with('error', 'Sesi Anda telah berakhir atau tidak valid. Silakan login kembali.');
                }
            }
            
            $errorMessage = $response['pesan'] ?? $response['message'] ?? 'Data gaji tidak ditemukan.';
            return redirect()->route('payroll.index')
                ->with('error', $errorMessage);
                
        } catch (\Exception $e) {
            Log::error('PayrollController::show - Exception: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('payroll.index')
                ->with('error', 'Terjadi kesalahan saat memuat data gaji.');
        }
    }
    
    /**
     * Show the form for editing the specified payroll record.
     */
    public function edit($id)
    {
        try {
            // Check if user is authenticated and has valid session
            if (!session('api_token') || !session('authenticated')) {
                Log::warning('PayrollController::edit - No valid authentication found', [
                    'id' => $id,
                    'has_token' => session('api_token') ? 'yes' : 'no',
                    'authenticated' => session('authenticated') ? 'yes' : 'no',
                    'user_id' => session('user_id')
                ]);
                
                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali untuk mengakses data gaji.');
            }
            
            Log::info('PayrollController::edit - Attempting to get payroll ID: ' . $id, [
                'id' => $id,
                'id_type' => gettype($id),
                'session_token' => session('api_token') ? 'Present' : 'Missing',
                'user_id' => session('user_id'),
                'user_role' => session('user_role'),
                'can_manage_payroll' => can_manage_payroll(),
                'is_admin_or_hrd' => is_admin_or_hrd()
            ]);
            
            $response = $this->gajiService->withToken(session('api_token'))->getById($id);
            
            Log::info('PayrollController::edit - API Response:', [
                'id' => $id,
                'response_status' => $response['status'] ?? 'N/A',
                'response_message' => $response['pesan'] ?? $response['message'] ?? 'N/A',
                'has_data' => isset($response['data']),
                'response_type' => gettype($response)
            ]);
            
            // Handle different response formats from API
            if (isset($response['success']) && $response['success'] === true) {
                $payroll = $response['data'];
            } elseif (isset($response['status']) && ($response['status'] === 'success' || $response['status'] === 'sukses')) {
                $payroll = $response['data'];
            } elseif (isset($response['data']) && !empty($response['data'])) {
                // Sometimes API returns data directly without status wrapper
                $payroll = $response['data'];
            } else {
                // Handle authentication errors
                if (isset($response['message'])) {
                    if ($response['message'] === 'Unauthenticated.' || 
                        strpos($response['message'], 'Unauthorized') !== false ||
                        strpos($response['message'], 'Token') !== false ||
                        strpos($response['message'], 'Invalid credentials') !== false) {
                        return redirect()->back()->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
                    }
                }
                
                $errorMessage = $response['pesan'] ?? $response['message'] ?? 'Data gaji tidak ditemukan.';
                return redirect()->route('payroll.index')->with('error', $errorMessage);
            }
            
            // Get employees for selection
            $employeesResponse = $this->pegawaiService->withToken(session('api_token'))->getAll();
            $employeesData = $employeesResponse['data']['data'] ?? $employeesResponse['data'] ?? [];
            $employees = collect($employeesData);
            
            return view('payroll.edit', compact('payroll', 'employees'));
                
        } catch (\Exception $e) {
            Log::error('PayrollController::edit - Exception: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('payroll.index')
                ->with('error', 'Terjadi kesalahan saat memuat form edit gaji.');
        }
    }
    
    /**
     * Update the specified payroll record.
     */
    public function update(Request $request, $id)
    {
        try {
            // Check authentication first
            if (!session('api_token') || !session('authenticated')) {
                Log::warning('PayrollController::update - No valid authentication found', [
                    'id' => $id,
                    'has_token' => session('api_token') ? 'yes' : 'no',
                    'authenticated' => session('authenticated') ? 'yes' : 'no',
                    'user_id' => session('user_id')
                ]);
                
                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
            }
            
            // Check permissions using helper functions
            $user = auth_user();
            $userRole = $user->role ?? session('user_role');
            $userId = $user->id ?? session('user_id');
            
            Log::info('PayrollController::update - Checking permissions', [
                'user_id' => $userId,
                'user_role' => $userRole,
                'session_user_role' => session('user_role'),
                'auth_user_data' => [
                    'id' => $user->id ?? 'null',
                    'role' => $user->role ?? 'null',
                    'name' => $user->name ?? 'null'
                ],
                'helper_results' => [
                    'is_admin()' => is_admin(),
                    'is_hrd()' => is_hrd(),
                    'is_admin_or_hrd()' => is_admin_or_hrd(),
                    'can_manage_payroll()' => can_manage_payroll()
                ]
            ]);
            
            // First check if user can manage payroll (admin/hrd)
            if (!can_manage_payroll()) {
                Log::warning('PayrollController::update - User cannot manage payroll', [
                    'user_id' => $userId,
                    'user_role' => $userRole,
                    'can_manage_payroll' => can_manage_payroll()
                ]);
                
                // Allow pegawai to edit their own payroll if needed
                $isOwner = false;
                
                // Get payroll data to check owner
                try {
                    $payrollResponse = $this->gajiService->withToken(session('api_token'))->getById($id);
                    if (isset($payrollResponse['data']['pegawai']['user']['id_user'])) {
                        $payrollOwnerId = $payrollResponse['data']['pegawai']['user']['id_user'];
                        $isOwner = ($payrollOwnerId == $userId);
                        
                        Log::info('PayrollController::update - Owner check', [
                            'payroll_owner_id' => $payrollOwnerId,
                            'current_user_id' => $userId,
                            'is_owner' => $isOwner
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('PayrollController::update - Error checking payroll owner: ' . $e->getMessage());
                }
                
                // If not owner either, deny access
                if (!$isOwner) {
                    return redirect()->route('payroll.index')
                        ->with('error', 'Anda tidak memiliki izin untuk mengubah data gaji ini. Role: ' . ($userRole ?? 'tidak diketahui'));
                }
            }
            
            $validated = $request->validate([
                'id_pegawai' => 'required|integer',
                'periode_bulan' => 'required|integer|min:1|max:12',
                'periode_tahun' => 'required|integer|min:2020|max:' . (date('Y') + 1),
                'gaji_pokok' => 'required|numeric|min:0',
                'gaji_bonus' => 'nullable|numeric|min:0',
                'gaji_kehadiran' => 'nullable|numeric|min:0',
                'keterangan' => 'nullable|string|max:1000',
                'status' => 'required|in:Belum Terbayar,Terbayar'
            ]);
            
            // Set default values for optional fields
            $validated['gaji_bonus'] = $validated['gaji_bonus'] ?? 0;
            $validated['gaji_kehadiran'] = $validated['gaji_kehadiran'] ?? 0;
            
            // Calculate total
            $validated['gaji_total'] = $validated['gaji_pokok'] + 
                                      $validated['gaji_bonus'] + 
                                      $validated['gaji_kehadiran'];
            
            Log::info('PayrollController::update - Processing update', [
                'id' => $id,
                'user_id' => session('user_id'),
                'data' => $validated,
                'api_token' => session('api_token') ? 'present' : 'missing'
            ]);
            
            $response = $this->gajiService->withToken(session('api_token'))->update($id, $validated);
            
            Log::info('PayrollController::update - API Response', [
                'response' => $response,
                'response_status' => $response['status'] ?? 'N/A',
                'response_message' => $response['message'] ?? $response['pesan'] ?? 'N/A',
                'has_data' => isset($response['data']),
                'full_response' => json_encode($response, JSON_PRETTY_PRINT)
            ]);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->route('payroll.index')
                    ->with('success', 'Data gaji berhasil diperbarui.');
            } elseif (isset($response['status']) && $response['status'] === 'sukses') {
                return redirect()->route('payroll.index')
                    ->with('success', 'Data gaji berhasil diperbarui.');
            }
            
            // Handle specific error messages from API
            $errorMessage = $response['message'] ?? $response['pesan'] ?? 'Terjadi kesalahan saat memperbarui data gaji.';
            
            // Handle authentication error specifically  
            if (str_contains(strtolower($errorMessage), 'unauthenticated') || 
                str_contains(strtolower($errorMessage), 'unauthorized')) {
                Log::error('PayrollController::update - Authentication failed', [
                    'response' => $response,
                    'token_exists' => session('api_token') ? 'yes' : 'no'
                ]);
                
                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
            }
            
            // Handle permission error
            if (str_contains(strtolower($errorMessage), 'tidak memiliki izin') || 
                str_contains(strtolower($errorMessage), 'permission') ||
                str_contains(strtolower($errorMessage), 'forbidden')) {
                return redirect()->route('payroll.index')
                    ->with('error', $errorMessage);
            }
            
            // Handle validation errors
            if (isset($response['errors']) && is_array($response['errors'])) {
                $validationErrors = collect($response['errors'])->flatten()->implode(', ');
                return redirect()->back()
                    ->withErrors($response['errors'])
                    ->withInput()
                    ->with('error', 'Validasi gagal: ' . $validationErrors);
            }
            
            return redirect()->route('payroll.edit', $id)
                ->with('error', 'Gagal memperbarui data gaji: ' . ($response['message'] ?? 'Terjadi kesalahan.'))
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('PayrollController::update - Exception: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('payroll.edit', $id)
                ->with('error', 'Terjadi kesalahan saat memperbarui data gaji: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Remove the specified payroll record.
     */
    public function destroy($id)
    {
        try {
            // Check if user is authenticated and has valid session
            if (!session('api_token') || !session('authenticated')) {
                Log::warning('PayrollController::destroy - No valid authentication found', [
                    'id' => $id,
                    'has_token' => session('api_token') ? 'yes' : 'no',
                    'authenticated' => session('authenticated') ? 'yes' : 'no',
                    'user_id' => session('user_id')
                ]);
                
                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali untuk menghapus data gaji.');
            }

            // Check if user has permission (admin or hrd)
            $user = auth_user();
            if (!$user || !in_array($user->role, ['admin', 'hrd'])) {
                Log::warning('PayrollController::destroy - Insufficient permissions', [
                    'id' => $id,
                    'user_id' => session('user_id'),
                    'user_role' => session('user_role')
                ]);
                
                return redirect()->route('payroll.index')
                    ->with('error', 'Anda tidak memiliki izin untuk menghapus data gaji.');
            }

            // Validate ID
            if (!$id || !is_numeric($id)) {
                Log::warning('PayrollController::destroy - Invalid payroll ID provided', ['id' => $id]);
                return redirect()->route('payroll.index')
                    ->with('error', 'ID data gaji tidak valid.');
            }

            Log::info('PayrollController::destroy called', [
                'id' => $id,
                'user_id' => session('user_id'),
                'user_role' => session('user_role')
            ]);
            
            $response = $this->gajiService->delete($id);
            
            Log::info('Delete payroll API response', [
                'id' => $id,
                'response' => $response
            ]);

            // Handle authentication error specifically
            if (isset($response['message']) && 
                (str_contains(strtolower($response['message']), 'unauthorized') || 
                 str_contains(strtolower($response['message']), 'unauthenticated'))) {
                Log::warning('API authentication failed during payroll delete', [
                    'id' => $id,
                    'response' => $response
                ]);
                return redirect()->route('login')
                    ->with('error', 'Sesi login Anda telah berakhir. Silakan login kembali untuk menghapus data gaji.');
            }
            
            if (isset($response['status']) && $response['status'] === 'success') {
                Log::info('Payroll deleted successfully', ['id' => $id]);
                return redirect()->route('payroll.index')
                    ->with('success', 'Data gaji berhasil dihapus.');
            }
            
            Log::warning('Delete payroll API returned error', [
                'id' => $id,
                'response' => $response
            ]);
            return redirect()->route('payroll.index')
                ->with('error', 'Gagal menghapus data gaji: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
                
        } catch (\Exception $e) {
            Log::error('PayrollController::destroy - ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('payroll.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data gaji: ' . $e->getMessage());
        }
    }
    
    /**
     * Generate payroll for a specific month/year.
     */
    public function generatePayroll(Request $request)
    {
        try {
            $validated = $request->validate([
                'bulan' => 'required|integer|min:1|max:12',
                'tahun' => 'required|integer|min:2020|max:' . (date('Y') + 1),
                'pegawai_ids' => 'nullable|array',
                'pegawai_ids.*' => 'integer'
            ]);
            
            $response = $this->gajiService->calculate($validated);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->route('payroll.index')
                    ->with('success', 'Payroll berhasil digenerate untuk ' . 
                           $this->getMonthName($validated['bulan']) . ' ' . $validated['tahun']);
            }
            
            return redirect()->route('payroll.index')
                ->with('error', 'Gagal generate payroll: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
                
        } catch (\Exception $e) {
            Log::error('PayrollController::generatePayroll - ' . $e->getMessage());
            return redirect()->route('payroll.index')
                ->with('error', 'Terjadi kesalahan saat generate payroll.');
        }
    }
    
    /**
     * Show generate payroll form.
     */
    public function showGenerateForm()
    {
        try {
            // Get employees for selection
            $employeesResponse = $this->pegawaiService->getAll();
            $employeesData = $employeesResponse['data']['data'] ?? $employeesResponse['data'] ?? [];
            $employees = collect($employeesData);
            
            return view('payroll.generate', compact('employees'));
            
        } catch (\Exception $e) {
            Log::error('PayrollController::showGenerateForm - ' . $e->getMessage());
            return redirect()->route('payroll.index')
                ->with('error', 'Terjadi kesalahan saat memuat form generate payroll.');
        }
    }
    
    /**
     * Update payment status.
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        try {
            // More robust session checking with regeneration protection
            $hasValidSession = session()->has('api_token') && 
                               session()->has('authenticated') && 
                               session('authenticated') === true;
            
            if (!$hasValidSession) {
                Log::warning('PayrollController::updatePaymentStatus - No valid authentication found', [
                    'id' => $id,
                    'has_token' => session()->has('api_token') ? 'yes' : 'no',
                    'authenticated' => session('authenticated'),
                    'user_id' => session('user_id'),
                    'session_id' => session()->getId(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'csrf_token_valid' => $request->hasValidSignature() || hash_equals($request->session()->token(), $request->input('_token'))
                ]);
                
                // Clear all session data to ensure clean state
                session()->flush();
                session()->regenerate();
                
                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir atau tidak valid. Silakan login kembali.');
            }

            // Get user role from session with fallback
            $userRole = session('user_role');
            $userId = session('user_id');
            
            // Check if user has permission (admin or hrd) - from session
            if (!in_array($userRole, ['admin', 'hrd'])) {
                Log::warning('PayrollController::updatePaymentStatus - Insufficient permissions', [
                    'id' => $id,
                    'user_id' => $userId,
                    'user_role' => $userRole
                ]);
                
                return redirect()->route('payroll.show', $id)
                    ->with('error', 'Anda tidak memiliki izin untuk mengubah status pembayaran.');
            }

            // Validate status input with proper validation rules
            $validated = $request->validate([
                'status' => 'required|in:Belum Terbayar,Terbayar',
                'tanggal_pembayaran' => 'nullable|date'
            ]);

            Log::info('PayrollController::updatePaymentStatus - Processing payment status update', [
                'id' => $id,
                'new_status' => $validated['status'],
                'user_id' => $userId,
                'user_role' => $userRole,
                'csrf_token_valid' => hash_equals($request->session()->token(), $request->input('_token')),
                'request_data' => $request->except(['_token']) // Don't log token
            ]);

            // Get current API token from session
            $apiToken = session('api_token');
            if (!$apiToken) {
                Log::error('PayrollController::updatePaymentStatus - No API token in session');
                return redirect()->route('login')
                    ->with('error', 'Token API tidak ditemukan. Silakan login kembali.');
            }

            // Set token to service before making API call
            $this->gajiService->withToken($apiToken);

            Log::info('PayrollController::updatePaymentStatus - About to call API', [
                'id' => $id,
                'token_length' => strlen($apiToken),
                'token_prefix' => substr($apiToken, 0, 20) . '...',
                'payload' => ['status' => $validated['status'], 'tanggal_pembayaran' => $validated['tanggal_pembayaran'] ?? null]
            ]);

            // Call the service to update payment status using the PUT /api/gaji/{id} endpoint
            $response = $this->gajiService->updatePaymentStatus($id, $validated['status'], $validated['tanggal_pembayaran'] ?? null);

            Log::info('PayrollController::updatePaymentStatus - API Response received', [
                'id' => $id,
                'response_status' => $response['status'] ?? 'unknown',
                'response_message' => $response['message'] ?? $response['pesan'] ?? 'no message',
                'full_response' => $response
            ]);

            // Handle authentication errors more carefully
            if (isset($response['message'])) {
                $authErrorMessages = [
                    'Unauthenticated',
                    'Unauthorized', 
                    'Token',
                    'Invalid credentials',
                    'Authentication failed',
                    'Access denied',
                    'Token has expired',
                    'Token not provided',
                    'Invalid token'
                ];
                
                $isAuthError = false;
                foreach ($authErrorMessages as $errorMsg) {
                    if (stripos($response['message'], $errorMsg) !== false) {
                        $isAuthError = true;
                        break;
                    }
                }
                
                if ($isAuthError) {
                    Log::warning('PayrollController::updatePaymentStatus - API authentication failed', [
                        'id' => $id,
                        'message' => $response['message'],
                        'session_token_present' => !empty($apiToken),
                        'token_length' => strlen($apiToken ?? ''),
                        'user_id' => $userId
                    ]);
                    
                    // Try to refresh the token by making a profile call first
                    try {
                        $authService = app(\App\Services\AuthService::class);
                        $profileResponse = $authService->withToken($apiToken)->getProfile();
                        
                        if (isset($profileResponse['status']) && $profileResponse['status'] === 'success') {
                            // Token is still valid, maybe temporary API issue
                            Log::info('PayrollController::updatePaymentStatus - Token validation successful, retrying API call');
                            
                            // Retry the gaji API call once more
                            $retryResponse = $this->gajiService->withToken($apiToken)->updatePaymentStatus($id, $validated['status'], $validated['tanggal_pembayaran'] ?? null);
                            
                            if (isset($retryResponse['status']) && in_array($retryResponse['status'], ['success', 'sukses'])) {
                                Log::info('PayrollController::updatePaymentStatus - Retry successful');
                                return redirect()->route('payroll.show', $id)
                                    ->with('success', 'Status pembayaran berhasil diperbarui menjadi "' . $validated['status'] . '".');
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('PayrollController::updatePaymentStatus - Token validation failed: ' . $e->getMessage());
                    }
                    
                    // If token validation failed or retry failed, redirect with soft error
                    return redirect()->route('payroll.show', $id)
                        ->with('error', 'Token API tidak valid. Silakan refresh halaman atau login kembali jika masalah berlanjut.');
                }
            }

            // Check if the response status is successful
            if ((isset($response['status']) && in_array($response['status'], ['success', 'sukses']))) {
                Log::info('PayrollController::updatePaymentStatus - Success', [
                    'id' => $id,
                    'status' => $validated['status']
                ]);
                
                return redirect()->route('payroll.show', $id)
                    ->with('success', 'Status pembayaran berhasil diperbarui menjadi "' . $validated['status'] . '".');
            }
            
            $errorMessage = $response['message'] ?? $response['pesan'] ?? 'Terjadi kesalahan saat memperbarui status pembayaran.';
            
            Log::warning('PayrollController::updatePaymentStatus - Failed to update', [
                'id' => $id,
                'error_message' => $errorMessage,
                'full_response' => $response
            ]);
            
            return redirect()->route('payroll.show', $id)
                ->with('error', 'Gagal memperbarui status pembayaran: ' . $errorMessage);
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('PayrollController::updatePaymentStatus - Validation error', [
                'id' => $id,
                'errors' => $e->errors()
            ]);
            
            return redirect()->route('payroll.show', $id)
                ->withErrors($e->errors())
                ->with('error', 'Data tidak valid. Silakan periksa input Anda.');
                
        } catch (\Exception $e) {
            Log::error('PayrollController::updatePaymentStatus - Exception: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('payroll.show', $id)
                ->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi atau hubungi administrator.');
        }
    }
    
    /**
     * Get payroll by employee.
     */
    public function getByEmployee($pegawaiId)
    {
        try {
            $response = $this->gajiService->withToken(session('api_token'))->getByPegawai($pegawaiId);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                $payrolls = collect($response['data'] ?? []);
                
                // Get employee data
                $employeeResponse = $this->pegawaiService->withToken(session('api_token'))->getById($pegawaiId);
                $employee = $employeeResponse['data'] ?? null;
                
                return view('payroll.employee', compact('payrolls', 'employee'));
            }
            
            // Handle authentication errors
            if (isset($response['message'])) {
                if ($response['message'] === 'Unauthenticated.' || 
                    strpos($response['message'], 'Unauthorized') !== false ||
                    strpos($response['message'], 'Token') !== false ||
                    strpos($response['message'], 'Invalid credentials') !== false) {
                    return redirect()->back()->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
                }
            }
            
            return redirect()->route('payroll.index')
                ->with('error', 'Data gaji pegawai tidak ditemukan.');
                
        } catch (\Exception $e) {
            Log::error('PayrollController::getByEmployee - ' . $e->getMessage());
            return redirect()->route('payroll.index')
                ->with('error', 'Terjadi kesalahan saat memuat data gaji pegawai.');
        }
    }
    
    /**
     * Get month name in Indonesian.
     */
    private function getMonthName($month)
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return $months[$month] ?? '';
    }
    
    /**
     * Export data payroll ke PDF dengan data lengkap dari API
     */
    public function exportPdf(Request $request)
    {
        try {
            \Log::info('Mulai proses export PDF Payroll', [
                'request_method' => $request->method(),
                'request_params' => $request->all()
            ]);
            
            // Ambil filter dari request
            $filters = [
                'periode_bulan' => $request->periode_bulan,
                'periode_tahun' => $request->periode_tahun,
                'pegawai_id' => $request->pegawai_id,
                'status' => $request->status,
                'search' => $request->search
            ];

            // Hapus filter yang kosong
            $filters = array_filter($filters, function($value) {
                return !is_null($value) && $value !== '';
            });

            \Log::info('Filter PDF Export Payroll:', $filters);

            // Ambil data payroll dari API
            $user = auth_user();
            $params = $filters;

            // Filter berdasarkan role user
            if (!in_array($user->role, ['admin', 'hrd'])) {
                $userId = session('user_id') ?? $user->id;
                $pegawaiId = session('pegawai_id');
                
                if (!$pegawaiId) {
                    $this->pegawaiService->withToken(session('api_token'));
                    $myPegawaiResponse = $this->pegawaiService->getMyPegawaiData();
                    
                    if (isset($myPegawaiResponse['status']) && 
                        in_array($myPegawaiResponse['status'], ['success', 'sukses']) && 
                        !empty($myPegawaiResponse['data'])) {
                        $pegawaiData = $myPegawaiResponse['data'];
                        $pegawaiId = $pegawaiData['id_pegawai'] ?? $pegawaiData['id'] ?? null;
                    }
                }
                
                if ($pegawaiId) {
                    $params['pegawai_id'] = $pegawaiId;
                }
            }

            // Panggil API untuk mendapatkan data payroll
            $response = $this->gajiService->withToken(session('api_token'))->getAll($params);
            
            \Log::info('Respon API untuk PDF Payroll:', [
                'has_status' => isset($response['status']),
                'status' => $response['status'] ?? 'tidak_ada_status',
                'has_data' => isset($response['data']),
                'message' => $response['message'] ?? 'tidak_ada_pesan',
                'response_keys' => array_keys($response)
            ]);
            
            // Periksa error autentikasi terlebih dahulu
            if (isset($response['message'])) {
                if ($response['message'] === 'Unauthenticated.' || 
                    strpos($response['message'], 'Unauthorized') !== false ||
                    strpos($response['message'], 'Token') !== false) {
                    \Log::error('Error autentikasi API untuk PDF Payroll');
                    return redirect()->back()->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
                }
            }
            
            if (!isset($response['status']) || !in_array($response['status'], ['success', 'sukses'])) {
                \Log::warning('API tidak mengembalikan status sukses untuk Payroll:', $response);
                // Jika error karena data tidak ditemukan, tetap generate PDF kosong
                if (isset($response['message']) && 
                    (strpos($response['message'], 'tidak ditemukan') !== false || 
                     strpos($response['message'], 'not found') !== false ||
                     strpos($response['message'], 'No data') !== false ||
                     strpos($response['message'], 'Data tidak ada') !== false)) {
                    \Log::info('Data payroll tidak ditemukan dari API, membuat PDF kosong');
                    $response = ['status' => 'success', 'data' => []];
                } else {
                    $errorMsg = $response['message'] ?? 'Terjadi kesalahan saat mengambil data dari API';
                    return redirect()->back()->with('error', 'Gagal mengambil data payroll: ' . $errorMsg);
                }
            }

            // Handle nested data structure
            $payrollData = [];
            if (isset($response['data']['data'])) {
                $payrollData = $response['data']['data'];
            } elseif (isset($response['data'])) {
                $payrollData = is_array($response['data']) ? $response['data'] : [];
            }

            \Log::info('Data Payroll untuk PDF:', [
                'jumlah_data' => count($payrollData),
                'sample_data' => count($payrollData) > 0 ? array_keys($payrollData[0]) : [],
                'first_record' => count($payrollData) > 0 ? $payrollData[0] : null
            ]);

            // Enrich data payroll dengan informasi pegawai sesuai struktur API klinik yang benar
            if (!empty($payrollData)) {
                try {
                    foreach ($payrollData as $index => &$item) {
                        \Log::info("Processing payroll item $index", [
                            'available_keys' => is_array($item) ? array_keys($item) : 'not_array',
                            'has_pegawai' => isset($item['pegawai']),
                            'pegawai_structure' => isset($item['pegawai']) ? array_keys($item['pegawai']) : 'no_pegawai'
                        ]);
                        
                        // Pastikan struktur data sesuai API klinik
                        $namaPegawai = null;
                        $posisi = null;
                        
                        // Ambil nama pegawai dari struktur API klinik yang benar
                        if (isset($item['pegawai']['nama_lengkap'])) {
                            $namaPegawai = $item['pegawai']['nama_lengkap'];
                        } elseif (isset($item['pegawai']['user']['nama_user'])) {
                            $namaPegawai = $item['pegawai']['user']['nama_user'];
                        } elseif (isset($item['nama_lengkap'])) {
                            $namaPegawai = $item['nama_lengkap'];
                        }
                        
                        // Ambil posisi dari struktur API klinik yang benar
                        if (isset($item['pegawai']['posisi']['nama_posisi'])) {
                            $posisi = $item['pegawai']['posisi']['nama_posisi'];
                        }
                        
                        // Jika masih belum lengkap, coba ambil dari API pegawai
                        if ((!$namaPegawai || !$posisi) && isset($item['id_pegawai'])) {
                            try {
                                $pegawaiResponse = $this->pegawaiService->getById($item['id_pegawai']);
                                if (isset($pegawaiResponse['status']) && in_array($pegawaiResponse['status'], ['success', 'sukses'])) {
                                    $pegawaiInfo = $pegawaiResponse['data'];
                                    
                                    if (!$namaPegawai) {
                                        $namaPegawai = $pegawaiInfo['nama_lengkap'] ?? 
                                                      $pegawaiInfo['nama'] ?? 
                                                      'Nama Tidak Ditemukan';
                                    }
                                    
                                    if (!$posisi) {
                                        $posisi = $pegawaiInfo['posisi']['nama_posisi'] ?? 
                                                 $pegawaiInfo['nama_posisi'] ?? 
                                                 'Posisi Tidak Diketahui';
                                    }
                                    
                                    \Log::info("Berhasil enrich data payroll untuk id_pegawai {$item['id_pegawai']}: {$namaPegawai} - {$posisi}");
                                }
                            } catch (\Exception $e) {
                                \Log::error("Error mengambil data pegawai untuk id_pegawai {$item['id_pegawai']}: " . $e->getMessage());
                            }
                        }
                        
                        // Set fallback jika masih kosong
                        if (!$namaPegawai) $namaPegawai = 'Nama Tidak Tersedia';
                        if (!$posisi) $posisi = 'Posisi Tidak Diketahui';
                        
                        // Pastikan struktur data konsisten untuk template (sesuai API klinik)
                        if (is_array($item)) {
                            // Field utama
                            $item['nama_pegawai'] = $namaPegawai;
                            $item['posisi'] = $posisi;
                            
                            // Periode dari API klinik menggunakan periode_bulan dan periode_tahun
                            $periode = 'N/A';
                            if (isset($item['periode_bulan']) && isset($item['periode_tahun'])) {
                                $periode = $item['periode_bulan'] . '/' . $item['periode_tahun'];
                            } elseif (isset($item['bulan']) && isset($item['tahun'])) {
                                $periode = $item['bulan'] . '/' . $item['tahun'];
                            }
                            $item['periode'] = $periode;
                            
                            // Mapping field gaji sesuai struktur API klinik
                            $gajiPokok = floatval($item['gaji_pokok'] ?? 0);
                            $gajiBonus = floatval($item['gaji_bonus'] ?? 0);
                            
                            // API klinik menggunakan gaji_kehadiran, bukan gaji_absensi
                            $gajiKehadiran = floatval($item['gaji_kehadiran'] ?? 0);
                            $gajiAbsensi = $gajiKehadiran; // untuk kompatibilitas template
                            
                            $totalGaji = floatval($item['gaji_total'] ?? ($gajiPokok + $gajiBonus + $gajiKehadiran));
                            $status = $item['status'] ?? 'pending';
                            
                            // Set semua field yang diperlukan template
                            $item['gaji_pokok'] = $gajiPokok;
                            $item['gaji_bonus'] = $gajiBonus;
                            $item['gaji_absensi'] = $gajiAbsensi; // untuk template
                            $item['gaji_kehadiran'] = $gajiKehadiran; // field asli API
                            $item['total_gaji'] = $totalGaji;
                            $item['status'] = $status;
                            
                            // Pastikan struktur pegawai ada untuk kompatibilitas
                            if (!isset($item['pegawai'])) {
                                $item['pegawai'] = [];
                            }
                            $item['pegawai']['nama_lengkap'] = $namaPegawai;
                            $item['pegawai']['nama'] = $namaPegawai; // untuk kompatibilitas
                            if (!isset($item['pegawai']['posisi'])) {
                                $item['pegawai']['posisi'] = [];
                            }
                            $item['pegawai']['posisi']['nama_posisi'] = $posisi;
                        }
                        
                        \Log::info("Payroll item processed", [
                            'nama_pegawai' => $namaPegawai,
                            'posisi' => $posisi,
                            'periode' => $periode ?? 'N/A',
                            'gaji_pokok' => $gajiPokok ?? 0,
                            'total_gaji' => $totalGaji ?? 0
                        ]);
                    }
                    unset($item); // Break reference
                    
                    \Log::info('Data enrichment payroll selesai', [
                        'total_records_processed' => count($payrollData),
                        'sample_enriched_data' => count($payrollData) > 0 ? [
                            'nama_pegawai' => $payrollData[0]['nama_pegawai'] ?? 'not_set',
                            'posisi' => $payrollData[0]['posisi'] ?? 'not_set',
                            'gaji_pokok' => $payrollData[0]['gaji_pokok'] ?? 0
                        ] : null
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Error saat enrich data payroll: ' . $e->getMessage());
                }
            }

            // If no data, still generate PDF with empty message
            if (empty($payrollData)) {
                $payrollData = [];
                \Log::warning('Tidak ada data payroll ditemukan dari API', ['filters' => $filters]);
            }
            
            // If filtering by specific pegawai, get pegawai name
            $namaPegawai = null;
            if (isset($filters['pegawai_id'])) {
                try {
                    $pegawaiResponse = $this->pegawaiService->getById($filters['pegawai_id']);
                    if (isset($pegawaiResponse['status']) && in_array($pegawaiResponse['status'], ['success', 'sukses'])) {
                        $namaPegawai = $pegawaiResponse['data']['nama'] ?? null;
                        if ($namaPegawai) {
                            $filters['pegawai_name'] = $namaPegawai;
                            $filters['nama_pegawai'] = $namaPegawai; // Untuk kompatibilitas template
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Gagal mengambil nama pegawai untuk PDF header:', ['error' => $e->getMessage()]);
                }
            }

            // Convert month number to name if available
            if (isset($filters['bulan']) && is_numeric($filters['bulan'])) {
                $filters['bulan_nama'] = $this->getMonthName($filters['bulan']);
            }

            // Prepare data for PDF
            $data = [
                'payroll' => $payrollData,
                'filters' => $filters,
                'tanggal_cetak' => now(),
                'total_data' => count($payrollData),
                'user_info' => [
                    'nama' => $user->name ?? 'Administrator',
                    'role' => $user->role ?? 'user'
                ]
            ];

            \Log::info('Memulai generate PDF Payroll dengan data:', [
                'jumlah_payroll' => count($payrollData),
                'ada_filter' => !empty($filters),
                'nama_pegawai' => $namaPegawai
            ]);

            // Generate PDF
            $pdf = Pdf::loadView('pdf.payroll-report', $data);
            $pdf->setPaper('A4', 'landscape');

            // Set filename berdasarkan filter
            $namaFile = 'laporan_payroll';
            if ($namaPegawai) {
                $namaFile .= '_' . str_replace(' ', '_', strtolower($namaPegawai));
            }
            if (isset($filters['periode_bulan']) && isset($filters['periode_tahun'])) {
                $namaFile .= '_' . $filters['periode_bulan'] . '_' . $filters['periode_tahun'];
            }
            $namaFile .= '_' . date('Y-m-d_H-i-s') . '.pdf';

            \Log::info('PDF Payroll berhasil dibuat:', [
                'nama_file' => $namaFile,
                'total_data' => count($payrollData)
            ]);

            return $pdf->download($namaFile);

        } catch (\Exception $e) {
            \Log::error('Error saat membuat PDF Payroll:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membuat laporan PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export slip gaji individual ke PDF
     */
    public function exportSlip($id)
    {
        try {
            // Check authentication
            if (!session('api_token') || !session('authenticated')) {
                \Log::warning('PayrollController::exportSlip - No authentication', ['id' => $id]);
                return response('Authentication required', 401);
            }
            
            // Clear output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Get current user info
            $user = auth_user();
            
            // Determine which API endpoint to use based on role
            if (!in_array($user->role, ['admin', 'hrd'])) {
                // Non-admin: first get personal gaji data to verify access
                $myGajiResponse = $this->gajiService->withToken(session('api_token'))->getMyData();
                
                \Log::info('PayrollController::exportSlip - My gaji data response:', [
                    'user_role' => $user->role,
                    'response_status' => $myGajiResponse['status'] ?? 'N/A',
                    'has_data' => isset($myGajiResponse['data']),
                    'slip_id' => $id
                ]);
                
                // Check if the requested slip belongs to current user
                $userOwnsSlip = false;
                $targetSlipData = null;
                
                if (isset($myGajiResponse['status']) && 
                    in_array($myGajiResponse['status'], ['success', 'sukses']) && 
                    isset($myGajiResponse['data']['data'])) {
                    
                    foreach ($myGajiResponse['data']['data'] as $slip) {
                        if (($slip['id_gaji'] ?? $slip['id']) == $id) {
                            $userOwnsSlip = true;
                            $targetSlipData = $slip;
                            break;
                        }
                    }
                }
                
                if (!$userOwnsSlip) {
                    \Log::warning('PayrollController::exportSlip - Access denied to slip', [
                        'user_role' => $user->role,
                        'user_id' => session('user_id'),
                        'slip_id' => $id
                    ]);
                    return response('Access denied to this slip gaji', 403);
                }
                
                // Use the data from my-data response
                $response = ['success' => true, 'data' => $targetSlipData];
                
            } else {
                // Admin/HRD: can access any slip
                $response = $this->gajiService->withToken(session('api_token'))->getById($id);
            }

            // Check for auth errors
            if (isset($response['message']) && 
                (strpos($response['message'], 'Unauthenticated') !== false || 
                 strpos($response['message'], 'Unauthorized') !== false)) {
                
                \Log::warning('PayrollController::exportSlip - API auth failed', ['id' => $id]);
                session()->forget(['api_token', 'authenticated', 'user_id', 'user_role']);
                return response('API Authentication failed', 401);
            }

            // Check response success
            if (!isset($response['status']) || 
                !in_array($response['status'], ['success', 'sukses']) || 
                !isset($response['data'])) {
                
                $errorMsg = $response['pesan'] ?? $response['message'] ?? 'Data not found';
                \Log::warning('PayrollController::exportSlip - Slip data not found', [
                    'id' => $id,
                    'error' => $errorMsg,
                    'response_status' => $response['status'] ?? 'N/A'
                ]);
                return response('Data slip gaji tidak ditemukan: ' . $errorMsg, 404);
            }

            $payrollData = $response['data'];
            
            // Extract employee info
            if (isset($payrollData['pegawai'])) {
                $pegawai = $payrollData['pegawai'];
                $namaPegawai = $pegawai['nama_lengkap'] ?? 'Unknown';
                $posisi = $pegawai['posisi']['nama_posisi'] ?? 'Unknown';
                $nip = $pegawai['NIP'] ?? 'N/A';
            } else {
                $namaPegawai = 'Unknown Employee';
                $posisi = 'Unknown Position';
                $nip = 'N/A';
            }

            // Prepare data for PDF
            $data = [
                'payroll' => $payrollData,
                'nama_pegawai' => $namaPegawai,
                'posisi' => $posisi,
                'nip' => $nip,
                'tanggal_cetak' => now()
            ];

            // Generate PDF
            $pdf = Pdf::loadView('pdf.slip-gaji', $data);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans'
            ]);

            $filename = 'slip_gaji_' . str_replace(' ', '_', strtolower($namaPegawai)) . '_' . date('Y-m-d_H-i-s') . '.pdf';
            
            \Log::info('PDF Generation Success', ['filename' => $filename, 'id' => $id]);
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Export Slip Error', [
                'error' => $e->getMessage(),
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response('PDF Generation Error: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Simple PDF test
     */
    public function testSimplePdf()
    {
        try {
            // Clear output buffer
            while (ob_get_level()) {
                ob_end_clean();
            }

            $data = ['test_id' => 'SIMPLE_TEST_' . time()];

            $pdf = Pdf::loadView('pdf.test-simple', $data);
            $pdf->setPaper('A4', 'portrait');
            
            $filename = 'test_simple_' . date('Y-m-d_H-i-s') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Simple PDF Test Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response('Simple PDF Test Error: ' . $e->getMessage(), 500);
        }
    }
}
