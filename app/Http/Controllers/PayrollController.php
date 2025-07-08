<?php

namespace App\Http\Controllers;

use App\Services\GajiService;
use App\Services\PegawaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            
            // Add search parameters
            if ($request->filled('search')) {
                $params['search'] = $request->search;
            }
            
            if ($request->filled('periode_bulan')) {
                $params['periode_bulan'] = $request->periode_bulan;
            }
            
            if ($request->filled('periode_tahun')) {
                $params['periode_tahun'] = $request->periode_tahun;
            }
            
            if ($request->filled('pegawai_id')) {
                $params['id_pegawai'] = $request->pegawai_id;
            }
            
            if ($request->filled('status')) {
                $params['status'] = $request->status;
            }
            
            // Get payroll data from API
            $response = $this->gajiService->getAll($params);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                // Handle paginated response
                $data = $response['data']['data'] ?? $response['data'] ?? [];
                $payrolls = collect($data);
            } else {
                $payrolls = collect([]);
                if (isset($response['message'])) {
                    session()->flash('error', 'Gagal mengambil data gaji: ' . $response['message']);
                }
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
            $response = $this->gajiService->getById($id);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                $payroll = $response['data'];
                return view('payroll.show', compact('payroll'));
            }
            
            return redirect()->route('payroll.index')
                ->with('error', 'Data gaji tidak ditemukan.');
                
        } catch (\Exception $e) {
            Log::error('PayrollController::show - ' . $e->getMessage());
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
            $response = $this->gajiService->getById($id);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                $payroll = $response['data'];
                
                // Get employees for selection
                $employeesResponse = $this->pegawaiService->getAll();
                $employeesData = $employeesResponse['data']['data'] ?? $employeesResponse['data'] ?? [];
                $employees = collect($employeesData);
                
                return view('payroll.edit', compact('payroll', 'employees'));
            }
            
            return redirect()->route('payroll.index')
                ->with('error', 'Data gaji tidak ditemukan.');
                
        } catch (\Exception $e) {
            Log::error('PayrollController::edit - ' . $e->getMessage());
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
}
