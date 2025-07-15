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
            
            // Filter berdasarkan role user
            // Jika bukan admin/hrd, hanya tampilkan gaji user sendiri
            if (!in_array($user->role, ['admin', 'hrd'])) {
                // **STEP 1: Ambil user_id dari session (hasil API login)**
                $userId = session('user_id') ?? $user->id;
                $pegawaiId = null;
                
                Log::info('PayrollController - Processing non-admin user:', [
                    'user_id_from_session' => session('user_id'),
                    'user_id_from_auth' => $user->id,
                    'final_user_id' => $userId
                ]);
                
                // **STEP 2: Cek apakah pegawai_id sudah ada di session**
                $pegawaiId = session('pegawai_id');
                
                if (!$pegawaiId) {
                    // **STEP 3: Jika belum ada, call API pegawai untuk mencari pegawai berdasarkan user_id**
                    try {
                        Log::info('PayrollController - Calling PegawaiService to find pegawai with matching user_id:', [
                            'user_id' => $userId
                        ]);
                        
                        // Pastikan menggunakan token untuk API call
                        $this->pegawaiService->withToken(session('api_token'));
                        
                        // **STEP 3A: Gunakan endpoint khusus untuk mendapatkan data pegawai sendiri**
                        $myPegawaiResponse = $this->pegawaiService->getMyPegawaiData();
                        
                        Log::info('PayrollController - My Pegawai API response:', [
                            'response_status' => $myPegawaiResponse['status'] ?? 'N/A',
                            'response_message' => $myPegawaiResponse['message'] ?? $myPegawaiResponse['pesan'] ?? 'N/A',
                            'has_data' => isset($myPegawaiResponse['data']),
                            'data_type' => isset($myPegawaiResponse['data']) ? gettype($myPegawaiResponse['data']) : 'N/A'
                        ]);
                        
                        if (isset($myPegawaiResponse['status']) && 
                            in_array($myPegawaiResponse['status'], ['success', 'sukses']) && 
                            !empty($myPegawaiResponse['data'])) {
                            
                            $pegawaiData = $myPegawaiResponse['data'];
                            $pegawaiId = $pegawaiData['id_pegawai'] ?? $pegawaiData['id'] ?? null;
                            
                            if ($pegawaiId) {
                                // **STEP 4: Simpan data pegawai ke session untuk penggunaan selanjutnya**
                                session([
                                    'pegawai_id' => $pegawaiId, 
                                    'pegawai_data' => $pegawaiData
                                ]);
                                
                                Log::info('PayrollController - Found pegawai data and saved to session:', [
                                    'user_id' => $userId,
                                    'pegawai_id' => $pegawaiId,
                                    'nama_lengkap' => $pegawaiData['nama_lengkap'] ?? $pegawaiData['nama'] ?? 'N/A',
                                    'posisi' => isset($pegawaiData['posisi']['nama_posisi']) ? $pegawaiData['posisi']['nama_posisi'] : 'N/A'
                                ]);
                            }
                        } else {
                            Log::warning('PayrollController - Failed to get my pegawai data:', [
                                'response' => $myPegawaiResponse
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('PayrollController - Failed to get pegawai list:', [
                            'user_id' => $userId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
                
            // **STEP 5: Jika pegawai_id ditemukan, filter gaji berdasarkan pegawai_id**
            if ($pegawaiId) {
                Log::info('PayrollController - Found pegawai_id for non-admin user:', [
                    'pegawai_id' => $pegawaiId,
                    'user_role' => $user->role,
                    'user_id' => $userId
                ]);
            } else {
                Log::warning('PayrollController - No pegawai_id found for user', [
                    'user_id' => $userId,
                    'user_role' => $user->role,
                    'session_pegawai_id' => session('pegawai_id'),
                    'session_pegawai_data' => session('pegawai_data') ? 'present' : 'missing'
                ]);
                
                // Return empty result dengan pesan yang lebih informatif
                $payrolls = collect([]);
                $payrolls->paginationData = [
                    'current_page' => 1, 'last_page' => 1, 'total' => 0,
                    'per_page' => 15, 'from' => 0, 'to' => 0,
                    'has_pages' => false, 'has_more_pages' => false,
                    'on_first_page' => true, 'prev_page_url' => null, 'next_page_url' => null,
                ];
                
                $employees = collect([]);
                return view('payroll.index', compact('payrolls', 'employees'))
                    ->with('error', 'Data pegawai tidak ditemukan untuk user ID: ' . $userId . '. Silakan logout dan login kembali, atau hubungi administrator.');
            }
            }
            
            // Add search parameters (hanya untuk admin/hrd)
            if (in_array($user->role, ['admin', 'hrd'])) {
                if ($request->filled('search')) {
                    $params['search'] = $request->search;
                }
                
                if ($request->filled('pegawai_id')) {
                    $params['id_pegawai'] = $request->pegawai_id;
                }
            }
            
            if ($request->filled('periode_bulan')) {
                $params['periode_bulan'] = $request->periode_bulan;
            }
            
            if ($request->filled('periode_tahun')) {
                $params['periode_tahun'] = $request->periode_tahun;
            }
            
            if ($request->filled('status')) {
                $params['status'] = $request->status;
            }
            
            // **STEP 5: Call API gaji dengan endpoint yang sesuai berdasarkan role**
            if (!in_array($user->role, ['admin', 'hrd'])) {
                // Non-admin: gunakan endpoint khusus untuk gaji sendiri
                // Hapus parameter id_pegawai karena endpoint my-data sudah otomatis filter
                unset($params['id_pegawai']);
                
                Log::info('PayrollController - Calling getMyGaji API for non-admin user:', [
                    'user_id' => $userId,
                    'user_role' => $user->role,
                    'pegawai_id' => $pegawaiId,
                    'params' => $params
                ]);
                
                $response = $this->gajiService->getMyGaji($params);
            } else {
                // Admin/HRD: gunakan endpoint umum dengan parameter filter
                Log::info('PayrollController - Calling getAll API for admin/hrd user:', [
                    'user_role' => $user->role,
                    'params' => $params
                ]);
                
                $response = $this->gajiService->getAll($params);
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
            Log::info('PayrollController::show - Attempting to get payroll ID: ' . $id, [
                'id' => $id,
                'id_type' => gettype($id),
                'session_token' => session('api_token') ? 'Present' : 'Missing'
            ]);
            
            $response = $this->gajiService->getById($id);
            
            Log::info('PayrollController::show - API Response:', [
                'id' => $id,
                'response_status' => $response['status'] ?? 'N/A',
                'response_message' => $response['pesan'] ?? $response['message'] ?? 'N/A',
                'has_data' => isset($response['data']),
                'response_full' => $response
            ]);
            
            if (isset($response['status']) && ($response['status'] === 'success' || $response['status'] === 'sukses')) {
                $payroll = $response['data'];
                return view('payroll.show', compact('payroll'));
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
            Log::info('PayrollController::edit - Attempting to get payroll ID: ' . $id, [
                'id' => $id,
                'id_type' => gettype($id),
                'session_token' => session('api_token') ? 'Present' : 'Missing'
            ]);
            
            $response = $this->gajiService->getById($id);
            
            Log::info('PayrollController::edit - API Response:', [
                'id' => $id,
                'response_status' => $response['status'] ?? 'N/A',
                'response_message' => $response['pesan'] ?? $response['message'] ?? 'N/A',
                'has_data' => isset($response['data']),
                'response_full' => $response
            ]);
            
            if (isset($response['status']) && ($response['status'] === 'success' || $response['status'] === 'sukses')) {
                $payroll = $response['data'];
                
                // Get employees for selection
                $employeesResponse = $this->pegawaiService->getAll();
                $employeesData = $employeesResponse['data']['data'] ?? $employeesResponse['data'] ?? [];
                $employees = collect($employeesData);
                
                return view('payroll.edit', compact('payroll', 'employees'));
            }
            
            $errorMessage = $response['pesan'] ?? $response['message'] ?? 'Data gaji tidak ditemukan.';
            return redirect()->route('payroll.index')
                ->with('error', $errorMessage);
                
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
            
            $response = $this->gajiService->update($id, $validated);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->route('payroll.index')
                    ->with('success', 'Data gaji berhasil diperbarui.');
            }
            
            return redirect()->route('payroll.edit', $id)
                ->with('error', 'Gagal memperbarui data gaji: ' . ($response['message'] ?? 'Terjadi kesalahan.'))
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('PayrollController::update - ' . $e->getMessage());
            return redirect()->route('payroll.edit', $id)
                ->with('error', 'Terjadi kesalahan saat memperbarui data gaji.')
                ->withInput();
        }
    }
    
    /**
     * Remove the specified payroll record.
     */
    public function destroy($id)
    {
        try {
            $response = $this->gajiService->delete($id);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->route('payroll.index')
                    ->with('success', 'Data gaji berhasil dihapus.');
            }
            
            return redirect()->route('payroll.index')
                ->with('error', 'Gagal menghapus data gaji: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
                
        } catch (\Exception $e) {
            Log::error('PayrollController::destroy - ' . $e->getMessage());
            return redirect()->route('payroll.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data gaji.');
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
            $validated = $request->validate([
                'status' => 'required|in:Belum Terbayar,Terbayar'
            ]);
            
            if ($validated['status'] == 'Terbayar') {
                $response = $this->gajiService->confirmPayment($id);
            } else {
                $response = $this->gajiService->updatePaymentStatus($id, $validated['status']);
            }
            
            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->route('payroll.show', $id)
                    ->with('success', 'Status pembayaran berhasil diperbarui.');
            }
            
            return redirect()->route('payroll.show', $id)
                ->with('error', 'Gagal memperbarui status pembayaran: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
                
        } catch (\Exception $e) {
            Log::error('PayrollController::updatePaymentStatus - ' . $e->getMessage());
            return redirect()->route('payroll.show', $id)
                ->with('error', 'Terjadi kesalahan saat memperbarui status pembayaran.');
        }
    }
    
    /**
     * Get payroll by employee.
     */
    public function getByEmployee($pegawaiId)
    {
        try {
            $response = $this->gajiService->getByPegawai($pegawaiId);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                $payrolls = collect($response['data'] ?? []);
                
                // Get employee data
                $employeeResponse = $this->pegawaiService->getById($pegawaiId);
                $employee = $employeeResponse['data'] ?? null;
                
                return view('payroll.employee', compact('payrolls', 'employee'));
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
                'bulan' => $request->bulan,
                'tahun' => $request->tahun,
                'pegawai_id' => $request->pegawai_id,
                'status' => $request->status
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
            $response = $this->gajiService->getAll($params);
            
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
            if (isset($filters['bulan']) && isset($filters['tahun'])) {
                $namaFile .= '_' . $filters['bulan'] . '_' . $filters['tahun'];
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
}
