<?php

namespace App\Http\Controllers;

use App\Services\AbsensiService;
use App\Services\PegawaiService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;

class AbsensiController extends Controller
{
    protected $absensiService;
    protected $pegawaiService;
    
    // Office coordinates (sesuaikan dengan lokasi kantor klinik)
    const OFFICE_LATITUDE = -8.79677;
    const OFFICE_LONGITUDE =  115.17140;
    const OFFICE_RADIUS = 100; // dalam meter
    
    public function __construct(AbsensiService $absensiService, PegawaiService $pegawaiService)
    {
        $this->absensiService = $absensiService;
        $this->pegawaiService = $pegawaiService;
    }
    
    /**
     * Menghitung jarak antara dua koordinat dalam meter
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Cek apakah lokasi berada dalam radius kantor
     */
    private function isWithinOfficeRadius($latitude, $longitude)
    {
        $distance = $this->calculateDistance(
            self::OFFICE_LATITUDE,
            self::OFFICE_LONGITUDE,
            $latitude,
            $longitude
        );
        
        return $distance <= self::OFFICE_RADIUS;
    }

    /**
     * Tampilkan daftar absensi
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $params = [];
        
        // Tambahkan parameter filtering
        if ($request->filled('tanggal_absensi')) {
            $params['tanggal_absensi'] = $request->tanggal_absensi;
        }
        
        if ($request->filled('bulan')) {
            $params['bulan'] = $request->bulan;
        }
        
        if ($request->filled('tahun')) {
            $params['tahun'] = $request->tahun;
        }
        
        if ($request->filled('status')) {
            $params['status'] = $request->status;
        }
        
        if ($request->filled('id_user')) {
            $params['id_user'] = $request->id_user;
        }
        
        // Tambahkan parameter untuk pagination
        $params['page'] = $request->input('page', 1);
        $params['per_page'] = 15; // Items per page
        
        // Ambil data absensi dari API
        $response = [];
        
        // Jika user bukan admin/HRD, tambahkan filter berdasarkan id_user dari session
        if (!is_admin() && !is_hrd()) {
            // Ambil id_user dari session login
            $apiUser = session('api_user');
            
            \Log::info('Filter absensi untuk user non-admin/hrd', [
                'session_api_user' => $apiUser,
                'has_id_user' => isset($apiUser['id_user']),
                'id_user_value' => $apiUser['id_user'] ?? null
            ]);
            
            if ($apiUser && isset($apiUser['id_user'])) {
                $params['id_user'] = $apiUser['id_user'];
            } else {
                // Fallback: coba ambil dari session login data lain
                $sessionUserId = session('user_id');
                if ($sessionUserId) {
                    $params['id_user'] = $sessionUserId;
                    \Log::info('Menggunakan fallback user_id dari session', ['user_id' => $sessionUserId]);
                }
            }
        }
        
        // Gunakan endpoint yang sama untuk semua user, filtering dilakukan di backend
        $response = $this->absensiService->getAll($params);
        
        \Log::info('Request params sent to API', [
            'params' => $params,
            'user_role' => is_admin() ? 'admin' : (is_hrd() ? 'hrd' : 'regular_user'),
            'session_api_user' => session('api_user')
        ]);
        
        \Log::info('Absensi API Full Response', [
            'response' => $response,
            'status' => $response['status'] ?? 'unknown',
            'has_data' => isset($response['data'])
        ]);
        
        // Handle different API response structures
        $absensiData = [];
        
        if (isset($response['status']) && $response['status'] === 'success') {
            if (isset($response['data'])) {
                // Check if data is paginated (Laravel pagination structure)
                if (isset($response['data']['data']) && is_array($response['data']['data'])) {
                    $absensiData = $response['data']['data'];
                } 
                // Check if data is a direct array of absensi records
                elseif (is_array($response['data']) && (empty($response['data']) || isset($response['data'][0]))) {
                    $absensiData = $response['data'];
                }
                // Check if data is a single absensi record
                elseif (isset($response['data']['id_absensi'])) {
                    $absensiData = [$response['data']]; // Wrap single record in array
                }
                // If data structure is unclear, log it and use empty array
                else {
                    \Log::warning('Unexpected absensi data structure', [
                        'data_structure' => $response['data'],
                        'data_keys' => array_keys($response['data'])
                    ]);
                    $absensiData = [];
                }
            }
        } else {
            \Log::error('Absensi API returned error', [
                'response' => $response,
                'message' => $response['message'] ?? 'Unknown error'
            ]);
        }
        
        // Map data dengan properti yang diperlukan oleh view
        $absensi = collect($absensiData)->map(function($item) {
            \Log::info('Processing absensi item', [
                'item_structure' => array_keys($item),
                'has_pegawai' => isset($item['pegawai']),
                'pegawai_keys' => isset($item['pegawai']) ? array_keys($item['pegawai']) : []
            ]);
            
            // Create mapped item with consistent structure
            $mappedItem = (object) [
                'id' => $item['id_absensi'] ?? $item['id'] ?? null,
                'id_absensi' => $item['id_absensi'] ?? $item['id'] ?? null,
                'id_pegawai' => $item['id_pegawai'] ?? null,
                'tanggal_absensi' => isset($item['tanggal_absensi']) ? Carbon::parse($item['tanggal_absensi']) : null,
                'status' => $item['status'] ?? 'Hadir',
                'jam_masuk' => null,
                'jam_keluar' => null,
                'durasi_kerja' => $item['durasi_kerja'] ?? '-',
                'catatan' => $item['catatan'] ?? '-',
                'alamat_masuk' => $item['alamat_masuk'] ?? '-',
                'created_at' => isset($item['created_at']) ? Carbon::parse($item['created_at']) : null,
                'updated_at' => isset($item['updated_at']) ? Carbon::parse($item['updated_at']) : null,
            ];
            
            // Handle jam_masuk and jam_keluar fields
            if (isset($item['jam_masuk'])) {
                $mappedItem->jam_masuk = Carbon::parse($item['jam_masuk']);
            } elseif (isset($item['created_at'])) {
                $mappedItem->jam_masuk = Carbon::parse($item['created_at']);
            }
            
            if (isset($item['jam_keluar'])) {
                $mappedItem->jam_keluar = Carbon::parse($item['jam_keluar']);
            } elseif (isset($item['updated_at']) && $item['updated_at'] !== $item['created_at']) {
                $mappedItem->jam_keluar = Carbon::parse($item['updated_at']);
            }
            
            // Handle pegawai relationship
            if (isset($item['pegawai']) && is_array($item['pegawai'])) {
                $pegawai = (object) $item['pegawai'];
                
                // Handle nested user relationship
                if (isset($item['pegawai']['user']) && is_array($item['pegawai']['user'])) {
                    $pegawai->user = (object) $item['pegawai']['user'];
                }
                
                // Handle nested posisi relationship
                if (isset($item['pegawai']['posisi']) && is_array($item['pegawai']['posisi'])) {
                    $pegawai->posisi = (object) $item['pegawai']['posisi'];
                }
                
                $mappedItem->pegawai = $pegawai;
            }
            
            return $mappedItem;
        });
        
        \Log::info('Final absensi data', [
            'count' => $absensi->count(),
            'sample' => $absensi->first()
        ]);
        
        // Ambil data pengguna untuk filter (hanya untuk admin/HRD)
        $users = collect();
        if (is_admin() || is_hrd()) {
            $pegawaiResponse = $this->pegawaiService->getAll();
            
            // Handle pegawai data structure
            if (isset($pegawaiResponse['status']) && $pegawaiResponse['status'] === 'success') {
                if (isset($pegawaiResponse['data']['data'])) {
                    $pegawaiData = $pegawaiResponse['data']['data'];
                } else {
                    $pegawaiData = $pegawaiResponse['data'] ?? [];
                }
                
                $users = collect($pegawaiData)->map(function($pegawai) {
                    if (is_array($pegawai)) {
                        $pegawai = (object) $pegawai;
                    }
                    return $pegawai;
                });
            }
        }
        
        // Ambil status absensi hari ini untuk menentukan button visibility
        $todayStatus = null;
        if (!is_admin() && !is_hrd()) {
            $statusResponse = $this->absensiService->getTodayStatus();
            if (isset($statusResponse['status']) && $statusResponse['status'] === 'success') {
                $todayStatus = $statusResponse['data'];
            }
        }
        
        // Create pagination info
        $paginationInfo = [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 15,
            'total' => $absensi->count(),
            'has_pages' => false,
            'links' => []
        ];
        
        // Extract pagination metadata from API response if available
        if (isset($response['status']) && $response['status'] === 'success' && isset($response['data'])) {
            // Check if response has Laravel pagination structure
            if (isset($response['data']['current_page'])) {
                $paginationInfo = [
                    'current_page' => $response['data']['current_page'] ?? 1,
                    'last_page' => $response['data']['last_page'] ?? 1,
                    'per_page' => $response['data']['per_page'] ?? 15,
                    'total' => $response['data']['total'] ?? 0,
                    'has_pages' => ($response['data']['last_page'] ?? 1) > 1,
                    'links' => $response['data']['links'] ?? []
                ];
            }
        }
        
        // Debug logging for pagination
        \Log::info('Absensi Pagination Info', [
            'pagination_info' => $paginationInfo,
            'absensi_count' => $absensi->count()
        ]);

        return view('absensi.index', compact('absensi', 'users', 'todayStatus', 'paginationInfo'));
    }

    /**
     * Tampilkan form untuk absensi masuk
     */
    public function create()
    {
        // Add office coordinates for view
        return view('absensi.create', [
            'office_latitude' => self::OFFICE_LATITUDE,
            'office_longitude' => self::OFFICE_LONGITUDE,
            'office_radius' => self::OFFICE_RADIUS
        ]);
    }

    /**
     * Simpan absensi masuk (check-in)
     */
    public function store(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
            'keterangan'=> 'nullable|string',
            'status'    => 'nullable|in:Hadir,Sakit,Izin',
        ]);
        
        $latitude  = $request->latitude;
        $longitude = $request->longitude;
        
        // Validasi radius kantor
        if (! $this->isWithinOfficeRadius($latitude, $longitude)) {
            return redirect()->route('absensi.create')
                             ->with('error', 'Anda berada di luar radius kantor.');
        }
        
        \Log::info('Akan mengirim data absensi ke API', [
            'latitude'  => $latitude,
            'longitude' => $longitude,
            'keterangan'=> $request->keterangan,
            'status'    => $request->status,
        ]);
        
        $data = [
            'status'    => $request->status ?? 'Hadir',
            'latitude'  => $latitude,
            'longitude' => $longitude,
        ];
        
        if ($request->filled('keterangan')) {
            $data['keterangan'] = $request->keterangan;
        }
        
        $response = $this->absensiService->store($data);
        
        \Log::info('Response API Absensi', ['response' => $response]);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('absensi.index')
                             ->with('success', 'Check-in berhasil disimpan.');
        }
        
        return redirect()->route('absensi.create')
                         ->with('error', 'Gagal melakukan check-in: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Tampilkan detail absensi
     */
    public function show($id)
    {
        $response = $this->absensiService->getById($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            $absensi = $response['data'];
            return view('absensi.show', compact('absensi'));
        }
        
        return redirect()->route('absensi.index')
            ->with('error', 'Data absensi tidak ditemukan.');
    }

    /**
     * Form edit absensi (hanya admin/HRD)
     */
    public function edit($id)
    {
        $user = auth()->user();
        
        // Hanya admin dan HRD yang bisa mengedit
        if (!$user->isAdmin() && !$user->isHRD()) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit absensi.');
        }
        
        $response = $this->absensiService->getById($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            $absensi = $response['data'];
            
            // Ambil data pegawai untuk dropdown
            $pegawaiResponse = $this->pegawaiService->getAll();
            $pegawai = collect($pegawaiResponse['data'] ?? []);
            
            return view('absensi.edit', compact('absensi', 'pegawai'));
        }
        
        return redirect()->route('absensi.index')
            ->with('error', 'Data absensi tidak ditemukan.');
    }

    /**
     * Update absensi (hanya admin/HRD)
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isHRD()) {
            return redirect()->route('absensi.index')
                             ->with('error', 'Anda tidak memiliki akses untuk mengupdate absensi.');
        }
        
        $request->validate([
            'tanggal_absensi' => 'required|date',
            'jam_masuk'  => 'required',
            'jam_keluar' => 'nullable',
            'status'     => 'required|in:Hadir,Sakit,Izin,Alfa',
            'keterangan' => 'nullable|string',
        ]);
        
        $data = [
            'tanggal_absensi' => $request->tanggal_absensi,
            'jam_masuk'  => $request->jam_masuk,
            'jam_keluar' => $request->jam_keluar,
            'status'     => $request->status,
            'keterangan' => $request->keterangan,
        ];
        
        $response = $this->absensiService->update($id, $data);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('absensi.index')
                             ->with('success', 'Data absensi berhasil diupdate.');
        }
        
        return redirect()->route('absensi.edit', $id)
                         ->with('error', 'Gagal mengupdate absensi: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Hapus absensi (hanya admin/HRD)
     */
    public function destroy($id)
    {
        try {
            \Log::info('AbsensiController::destroy called', [
                'id' => $id,
                'session_authenticated' => session('authenticated'),
                'session_user_id' => session('user_id'),
                'session_user_role' => session('user_role'),
                'auth_check' => auth()->check(),
                'auth_user_id' => auth()->check() ? auth()->user()->id : null
            ]);

            // Cek authentication session terlebih dahulu
            if (!session('authenticated') || !session('api_token')) {
                \Log::warning('AbsensiController::destroy - No valid session found');
                return redirect()->route('login')
                    ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
            }

            // Ambil role dari session atau auth user
            $userRole = session('user_role');
            $user = null;
            
            if (auth()->check()) {
                $user = auth()->user();
                $userRole = $user->role ?? $userRole;
            }

            // Hanya admin dan HRD yang bisa menghapus
            if (!in_array($userRole, ['admin', 'hrd'])) {
                \Log::warning('AbsensiController::destroy - Insufficient permissions', [
                    'user_role' => $userRole,
                    'required_roles' => ['admin', 'hrd']
                ]);
                return redirect()->route('absensi.index')
                    ->with('error', 'Anda tidak memiliki akses untuk menghapus absensi.');
            }

            \Log::info('AbsensiController::destroy - Processing deletion', [
                'id' => $id,
                'user_role' => $userRole,
                'user_id' => session('user_id')
            ]);
            
            $response = $this->absensiService->delete($id);
            
            \Log::info('Delete absensi response', [
                'id' => $id,
                'response' => $response,
                'response_keys' => array_keys($response ?? [])
            ]);

            // Handle authentication error specifically
            if (isset($response['message']) && 
                (str_contains(strtolower($response['message']), 'unauthorized') || 
                 str_contains(strtolower($response['message']), 'unauthenticated'))) {
                \Log::warning('API authentication failed during delete', [
                    'id' => $id,
                    'response' => $response
                ]);
                return redirect()->route('login')
                    ->with('error', 'Sesi login Anda telah berakhir. Silakan login kembali.');
            }

            // Check API response - Multiple possible success formats
            if ((isset($response['success']) && $response['success']) ||
                (isset($response['status']) && $response['status'] === 'success')) {
                \Log::info('Absensi deleted successfully', ['id' => $id]);
                return redirect()->route('absensi.index')
                    ->with('success', $response['message'] ?? 'Data absensi berhasil dihapus.');
            } else {
                // Jika API mengembalikan error
                \Log::error('Delete absensi API error', [
                    'id' => $id,
                    'response' => $response
                ]);
                $errorMsg = $response['message'] ?? 'Gagal menghapus data absensi.';
                return redirect()->route('absensi.index')
                    ->with('error', $errorMsg);
            }
        } catch (\Exception $e) {
            \Log::error('Error deleting absensi', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('absensi.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Checkout (update jam keluar)
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'alamat_checkout' => 'nullable|string',
            'keterangan_keluar' => 'nullable|string',
        ]);
        
        // Get today's attendance record first
        $todayStatusResponse = $this->absensiService->getTodayStatus();
        
        if (!isset($todayStatusResponse['status']) || $todayStatusResponse['status'] !== 'success') {
            return redirect()->route('absensi.index')
                ->with('error', 'Gagal mendapatkan status absensi hari ini.');
        }
        
        $todayStatus = $todayStatusResponse['data'];
        
        if (!$todayStatus['has_checked_in']) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda belum melakukan check-in hari ini.');
        }
        
        if ($todayStatus['has_checked_out']) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda sudah melakukan check-out hari ini.');
        }
        
        $absensiId = $todayStatus['attendance']['id_absensi'];
        
        // Kirim ke API using checkout endpoint
        $response = $this->absensiService->checkOut($absensiId, [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'alamat_checkout' => $request->alamat_checkout,
            'keterangan_keluar' => $request->keterangan_keluar,
        ]);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('absensi.index')
                ->with('success', 'Check-out berhasil disimpan.');
        }
        
        return redirect()->route('absensi.index')
            ->with('error', 'Gagal melakukan check-out: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }
    
    /**
     * Submit absence (sakit/izin)
     */
    public function submitAbsence(Request $request)
    {
        $request->validate([
            'status' => 'required|in:Sakit,Izin',
            'keterangan' => 'required|string',
        ]);
        
        // Check if user already has attendance for today
        $todayStatusResponse = $this->absensiService->getTodayStatus();
        
        if (isset($todayStatusResponse['status']) && $todayStatusResponse['status'] === 'success') {
            $todayStatus = $todayStatusResponse['data'];
            if ($todayStatus['has_checked_in']) {
                return redirect()->route('absensi.index')
                    ->with('error', 'Anda sudah melakukan absensi hari ini.');
            }
        }
        
        // Submit absence
        $data = [
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ];
        
        $response = $this->absensiService->store($data);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('absensi.index')
                ->with('success', 'Laporan ' . strtolower($request->status) . ' berhasil dikirim.');
        }
        
        return redirect()->route('absensi.index')
            ->with('error', 'Gagal mengirim laporan: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Laporan absensi
     */
    public function report(Request $request)
    {
        $user = auth()->user();
        $params = [];
        
        if ($request->filled('bulan')) {
            $params['bulan'] = $request->bulan;
        }
        
        if ($request->filled('tahun')) {
            $params['tahun'] = $request->tahun;
        }
        
        if ($request->filled('user_id')) {
            $params['user_id'] = $request->user_id;
        }
        
        // Ambil data absensi dari API
        $response = $this->absensiService->getAll($params);
        $absensi = collect($response['data'] ?? []);
        
        // Ambil data pegawai untuk filter
        $pegawaiResponse = $this->pegawaiService->getAll();
        $pegawai = collect($pegawaiResponse['data'] ?? []);
        
        return view('absensi.report', compact('absensi', 'pegawai'));
    }
    
    /**
     * Dashboard absensi (hanya admin/HRD)
     */
    public function dashboard()
    {
        $user = auth()->user();
        
        // Hanya admin dan HRD yang bisa akses dashboard
        if (!$user->isAdmin() && !$user->isHRD()) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda tidak memiliki akses untuk melihat dashboard absensi.');
        }
        
        // Ambil statistik absensi dari API
        $response = $this->absensiService->getAll(['limit' => 10]);
        $recentAbsensi = collect($response['data'] ?? []);
        
        // Ambil data untuk statistik
        $statsResponse = $this->absensiService->getStats();
        $stats = $statsResponse['data'] ?? [];
        
        return view('absensi.dashboard', compact('recentAbsensi', 'stats'));
    }

    /**
     * Form untuk admin menambah absensi manual
     */
    public function adminCreate()
    {
        $user = auth()->user();
        
        // Hanya admin dan HRD yang bisa menambah manual
        if (!$user->isAdmin() && !$user->isHRD()) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda tidak memiliki akses untuk menambah absensi manual.');
        }
        
        // Ambil data pegawai
        $pegawaiResponse = $this->pegawaiService->getAll();
        $pegawai = collect($pegawaiResponse['data'] ?? []);
        
        return view('absensi.admin-create', compact('pegawai'));
    }

    /**
     * Simpan absensi manual oleh admin
     */
    public function adminStore(Request $request)
    {
        $user = auth()->user();
        
        // Hanya admin dan HRD yang bisa menambah manual
        if (!$user->isAdmin() && !$user->isHRD()) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda tidak memiliki akses untuk menambah absensi manual.');
        }
        
        $request->validate([
            'id_pegawai' => 'required|integer',
            'tanggal_absensi' => 'required|date',
            'jam_masuk' => 'required',
            'jam_keluar' => 'nullable',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:hadir,sakit,izin,alfa',
        ]);
        
        $data = [
            'id_pegawai' => $request->id_pegawai,
            'tanggal_absensi' => $request->tanggal_absensi,
            'jam_masuk' => $request->jam_masuk,
            'jam_keluar' => $request->jam_keluar,
            'keterangan' => $request->keterangan,
            'status' => $request->status,
        ];
        
        $response = $this->absensiService->store($data);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('absensi.index')
                ->with('success', 'Absensi manual berhasil ditambahkan.');
        }
        
        return redirect()->route('absensi.admin-create')
            ->with('error', 'Gagal menambahkan absensi: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Form edit absensi untuk admin/HRD
     */
    public function adminEdit($id)
    {
        // Cek apakah user sudah terautentikasi
        if (!is_authenticated()) {
            return redirect()->route('login')
                ->with('error', 'Anda harus login terlebih dahulu.');
        }
        
        // Hanya admin dan HRD yang bisa edit
        if (!is_admin() && !is_hrd()) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit absensi.');
        }
        
        try {
            // Ambil data absensi
            $response = $this->absensiService->getById($id);
            
            \Log::info('AdminEdit API Response', [
                'response' => $response,
                'id' => $id
            ]);
            
            $absensi = null;
            
            // Handle response structure dari API
            if (isset($response['status']) && $response['status'] === 'success' && isset($response['data'])) {
                $absensi = $response['data'];
            } elseif (is_array($response) && !isset($response['status'])) {
                // Jika response langsung berupa data tanpa wrapper
                $absensi = $response;
            }
            
            if (!$absensi) {
                \Log::error('Absensi data not found', [
                    'id' => $id,
                    'response' => $response
                ]);
                
                return redirect()->route('absensi.index')
                    ->with('error', 'Data absensi tidak ditemukan.');
            }
            
            // Ambil data pegawai untuk dropdown
            $pegawaiData = $this->pegawaiService->getAll();
            $pegawai = [];
            
            if (isset($pegawaiData['data']) && is_array($pegawaiData['data'])) {
                $pegawai = $pegawaiData['data'];
            }
            
            return view('absensi.admin-edit', compact('absensi', 'pegawai'));
            
        } catch (\Exception $e) {
            \Log::error('Error in adminEdit:', ['error' => $e->getMessage()]);
            
            return redirect()->route('absensi.index')
                ->with('error', 'Terjadi kesalahan saat mengambil data absensi.');
        }
    }

    /**
     * Update absensi oleh admin/HRD
     */
    public function adminUpdate(Request $request, $id)
    {
        // Cek apakah user sudah terautentikasi
        if (!is_authenticated()) {
            return redirect()->route('login')
                ->with('error', 'Anda harus login terlebih dahulu.');
        }
        
        // Hanya admin dan HRD yang bisa update
        if (!is_admin() && !is_hrd()) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengupdate absensi.');
        }

        $request->validate([
            'tanggal_absensi' => 'required|date',
            'status' => 'required|in:Hadir,Sakit,Izin,Alpa',
            'jam_masuk' => 'required|date_format:H:i:s',
            'jam_keluar' => 'nullable|date_format:H:i:s',
            'keterangan' => 'nullable|string',
        ], [
            'tanggal_absensi.required' => 'Tanggal absensi harus diisi.',
            'tanggal_absensi.date' => 'Format tanggal tidak valid.',
            'status.required' => 'Status harus dipilih.',
            'status.in' => 'Status yang dipilih tidak valid.',
            'jam_masuk.required' => 'Jam masuk harus diisi.',
            'jam_masuk.date_format' => 'Format jam masuk harus HH:MM:SS.',
            'jam_keluar.date_format' => 'Format jam keluar harus HH:MM:SS.',
        ]);
        
        try {
            $data = [
                'tanggal_absensi' => $request->tanggal_absensi,
                'status' => $request->status,
                'jam_masuk' => $request->jam_masuk,
                'keterangan' => $request->keterangan,
            ];
            
            // Hanya kirim jam_keluar jika ada nilainya
            if ($request->filled('jam_keluar')) {
                $data['jam_keluar'] = $request->jam_keluar;
            }
            
            // Log data yang akan dikirim ke API untuk debugging
            \Log::info('AdminUpdate - Data to be sent to API:', [
                'id' => $id,
                'data' => $data,
                'request_all' => $request->all()
            ]);
            
            $response = $this->absensiService->update($id, $data);
            
            // Log response dari API
            \Log::info('AdminUpdate - API Response:', [
                'response' => $response
            ]);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->route('absensi.index')
                    ->with('success', 'Data absensi berhasil diperbarui.');
            }
            
            // Log error response untuk debugging
            \Log::error('AdminUpdate - API Error Response:', [
                'response' => $response
            ]);
            
            return redirect()->route('absensi.admin-edit', $id)
                ->with('error', 'Gagal memperbarui absensi: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
                
        } catch (\Exception $e) {
            \Log::error('Error in adminUpdate:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('absensi.admin-edit', $id)
                ->with('error', 'Terjadi kesalahan saat memperbarui data absensi.');
        }
    }
    
    /**
     * Export data absensi ke PDF dengan data dari API
     */
    public function exportPdf(Request $request)
    {
        try {
            \Log::info('Mulai proses export PDF Absensi', [
                'request_method' => $request->method(),
                'request_params' => $request->all()
            ]);
            
            // Ambil filter dari request
            $filters = [];
            
            // Tambahkan filter berdasarkan parameter yang diterima
            if ($request->filled('start_date')) {
                $filters['start_date'] = $request->start_date;
            }
            if ($request->filled('end_date')) {
                $filters['end_date'] = $request->end_date;
            }
            if ($request->filled('tanggal_absensi')) {
                $filters['tanggal'] = $request->tanggal_absensi;
            }
            if ($request->filled('bulan')) {
                $filters['bulan'] = $request->bulan;
            }
            if ($request->filled('tahun')) {
                $filters['tahun'] = $request->tahun;
            }
            if ($request->filled('status')) {
                $filters['status'] = $request->status;
            }
            if ($request->filled('id_user')) {
                $filters['id_user'] = $request->id_user;
            }
            
            // Jika tidak ada filter tanggal, berikan filter default untuk bulan ini
            if (!$request->filled('start_date') && !$request->filled('end_date') && 
                !$request->filled('tanggal_absensi') && !$request->filled('bulan') && !$request->filled('tahun')) {
                $filters['bulan'] = date('n'); // Bulan ini
                $filters['tahun'] = date('Y'); // Tahun ini
            }

            $user = auth_user();
            
            // Filter berdasarkan role - non-admin/hrd hanya bisa lihat data sendiri
            if (!in_array($user->role, ['admin', 'hrd'])) {
                $userId = session('user_id') ?? $user->id;
                $filters['id_user'] = $userId;
                \Log::info('Filter untuk user non-admin/hrd', ['user_id' => $userId]);
            }

            \Log::info('Filter PDF Export Absensi:', $filters);

            // Ambil data absensi dari API menggunakan AbsensiService
            $response = $this->absensiService->getAll($filters);
            
            \Log::info('Respon API untuk PDF Absensi:', [
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
                    \Log::error('Error autentikasi API untuk PDF Absensi');
                    return redirect()->back()->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
                }
            }
            
            // Periksa apakah API berhasil
            if (!isset($response['status']) || !in_array($response['status'], ['success', 'sukses'])) {
                \Log::warning('API tidak mengembalikan status sukses:', $response);
                
                // Jika error karena data tidak ditemukan, tetap generate PDF kosong
                if (isset($response['message']) && 
                    (strpos($response['message'], 'tidak ditemukan') !== false || 
                     strpos($response['message'], 'not found') !== false ||
                     strpos($response['message'], 'No data') !== false ||
                     strpos($response['message'], 'Data tidak ada') !== false)) {
                    \Log::info('Data tidak ditemukan dari API, membuat PDF kosong');
                    $response = ['status' => 'success', 'data' => []];
                } else {
                    $errorMsg = $response['message'] ?? 'Terjadi kesalahan saat mengambil data dari API';
                    return redirect()->back()->with('error', 'Gagal mengambil data absensi: ' . $errorMsg);
                }
            }

            // Ambil data dari response API
            $absensiData = [];
            if (isset($response['data']['data'])) {
                // Format API dengan nested data (pagination)
                $absensiData = $response['data']['data'];
            } elseif (isset($response['data'])) {
                // Format API langsung
                $absensiData = is_array($response['data']) ? $response['data'] : [];
            }

            \Log::info('Data Absensi untuk PDF:', [
                'jumlah_data' => count($absensiData),
                'sample_data' => count($absensiData) > 0 ? array_keys($absensiData[0]) : [],
                'first_record' => count($absensiData) > 0 ? $absensiData[0] : null
            ]);

            // Enrich data absensi dengan nama pegawai sesuai struktur API yang benar
            if (!empty($absensiData)) {
                try {
                    foreach ($absensiData as $index => &$item) {
                        \Log::info("Processing absensi item $index", [
                            'available_keys' => is_array($item) ? array_keys($item) : 'not_array',
                            'has_pegawai' => isset($item['pegawai']),
                            'pegawai_structure' => isset($item['pegawai']) ? array_keys($item['pegawai']) : 'no_pegawai',
                            'original_item' => $item
                        ]);
                        
                        // Periksa struktur nama pegawai dari API klinik yang benar
                        $namaPegawai = null;
                        
                        // Struktur API klinik: pegawai.nama_lengkap
                        if (isset($item['pegawai']['nama_lengkap'])) {
                            $namaPegawai = $item['pegawai']['nama_lengkap'];
                        }
                        // Fallback: pegawai.user.nama_user
                        elseif (isset($item['pegawai']['user']['nama_user'])) {
                            $namaPegawai = $item['pegawai']['user']['nama_user'];
                        }
                        // Fallback: langsung dari field nama
                        elseif (isset($item['nama_lengkap'])) {
                            $namaPegawai = $item['nama_lengkap'];
                        }
                        // Fallback: field nama_pegawai yang sudah ada
                        elseif (isset($item['nama_pegawai'])) {
                            $namaPegawai = $item['nama_pegawai'];
                        }
                        
                        // Jika masih belum ada nama, coba ambil dari API pegawai
                        if (!$namaPegawai && isset($item['id_pegawai'])) {
                            try {
                                \Log::info("Fetching pegawai data for id_pegawai: " . $item['id_pegawai']);
                                $pegawaiResponse = $this->pegawaiService->getById($item['id_pegawai']);
                                \Log::info("Pegawai API response", [
                                    'id_pegawai' => $item['id_pegawai'],
                                    'response' => $pegawaiResponse
                                ]);
                                
                                if (isset($pegawaiResponse['status']) && in_array($pegawaiResponse['status'], ['success', 'sukses'])) {
                                    $pegawaiData = $pegawaiResponse['data'];
                                    $namaPegawai = $pegawaiData['nama_lengkap'] ?? 
                                                  $pegawaiData['nama'] ?? 
                                                  $pegawaiData['user']['nama_user'] ?? 
                                                  'Pegawai Tidak Ditemukan';
                                    
                                    \Log::info("Berhasil mengambil nama pegawai dari API untuk id_pegawai {$item['id_pegawai']}: {$namaPegawai}");
                                }
                            } catch (\Exception $e) {
                                \Log::error("Error mengambil nama pegawai untuk id_pegawai {$item['id_pegawai']}: " . $e->getMessage());
                                $namaPegawai = 'Error Load Nama';
                            }
                        }
                        
                        // Set nama pegawai dengan fallback
                        if (!$namaPegawai) {
                            $namaPegawai = 'Nama Tidak Tersedia';
                        }
                        
                        // Pastikan struktur data konsisten untuk template
                        if (is_array($item)) {
                            $item['nama_pegawai'] = $namaPegawai;
                            
                            // Pastikan struktur pegawai ada untuk kompatibilitas template
                            if (!isset($item['pegawai'])) {
                                $item['pegawai'] = [];
                            }
                            $item['pegawai']['nama_lengkap'] = $namaPegawai;
                            $item['pegawai']['nama'] = $namaPegawai; // untuk kompatibilitas
                            
                            // Pastikan posisi ada
                            if (isset($item['pegawai']['posisi']['nama_posisi'])) {
                                $item['posisi'] = $item['pegawai']['posisi']['nama_posisi'];
                            }
                            
                            // Pastikan tanggal_absensi terformat dengan benar
                            if (isset($item['tanggal_absensi'])) {
                                // Gunakan tanggal_absensi yang sudah benar
                                $item['tanggal'] = $item['tanggal_absensi'];
                            } elseif (isset($item['tanggal'])) {
                                // Set tanggal_absensi jika hanya ada tanggal
                                $item['tanggal_absensi'] = $item['tanggal'];
                            } else {
                                // Set default jika tidak ada
                                $item['tanggal'] = date('Y-m-d');
                                $item['tanggal_absensi'] = date('Y-m-d');
                            }
                        }
                        
                        \Log::info("Absensi item processed", [
                            'nama_pegawai' => $namaPegawai,
                            'id_absensi' => $item['id_absensi'] ?? 'no_id',
                            'tanggal' => $item['tanggal'] ?? 'no_date',
                            'tanggal_absensi' => $item['tanggal_absensi'] ?? 'no_date',
                            'final_item_keys' => array_keys($item)
                        ]);
                    }
                    unset($item); // Break reference
                    
                    \Log::info('Data enrichment absensi selesai', [
                        'total_records_processed' => count($absensiData),
                        'sample_enriched_data' => count($absensiData) > 0 ? [
                            'nama_pegawai' => $absensiData[0]['nama_pegawai'] ?? 'not_set',
                            'tanggal' => $absensiData[0]['tanggal'] ?? 'not_set',
                            'tanggal_absensi' => $absensiData[0]['tanggal_absensi'] ?? 'not_set'
                        ] : null
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Error saat enrich data nama pegawai: ' . $e->getMessage(), [
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Jika tidak ada data, tetap lanjut untuk generate PDF kosong
            if (empty($absensiData)) {
                $absensiData = [];
                \Log::warning('Tidak ada data absensi ditemukan dari API', ['filters' => $filters]);
            }
            
            // Jika filter berdasarkan user tertentu, ambil nama user untuk tampilan
            $namaPegawai = null;
            if (isset($filters['id_user'])) {
                try {
                    $pegawaiResponse = $this->pegawaiService->getByUserId($filters['id_user']);
                    if (isset($pegawaiResponse['status']) && in_array($pegawaiResponse['status'], ['success', 'sukses'])) {
                        $namaPegawai = $pegawaiResponse['data']['nama'] ?? null;
                        if ($namaPegawai) {
                            $filters['nama_pegawai'] = $namaPegawai;
                            $filters['pegawai_name'] = $namaPegawai; // Untuk kompatibilitas template
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Gagal mengambil nama pegawai untuk PDF header:', ['error' => $e->getMessage()]);
                }
            }

            // Persiapkan data untuk PDF
            $data = [
                'absensi' => $absensiData,
                'filters' => $filters,
                'tanggal_cetak' => now(),
                'total_data' => count($absensiData),
                'user_info' => [
                    'nama' => $user->name ?? 'Administrator',
                    'role' => $user->role ?? 'user'
                ]
            ];

            \Log::info('Memulai generate PDF dengan data:', [
                'jumlah_absensi' => count($absensiData),
                'ada_filter' => !empty($filters),
                'nama_pegawai' => $namaPegawai
            ]);

            // Generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.absensi-report', $data);
            $pdf->setPaper('A4', 'landscape');

            // Set nama file berdasarkan filter
            $namaFile = 'laporan_absensi';
            if ($namaPegawai) {
                $namaFile .= '_' . str_replace(' ', '_', strtolower($namaPegawai));
            }
            if (isset($filters['start_date']) && isset($filters['end_date'])) {
                $namaFile .= '_' . $filters['start_date'] . '_sampai_' . $filters['end_date'];
            } elseif (isset($filters['bulan']) && isset($filters['tahun'])) {
                $namaFile .= '_' . $filters['bulan'] . '_' . $filters['tahun'];
            }
            $namaFile .= '_' . date('Y-m-d_H-i-s') . '.pdf';

            \Log::info('PDF Absensi berhasil dibuat:', [
                'nama_file' => $namaFile,
                'total_data' => count($absensiData)
            ]);

            return $pdf->download($namaFile);

        } catch (\Exception $e) {
            \Log::error('Error saat membuat PDF Absensi:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membuat laporan PDF: ' . $e->getMessage());
        }
    }
    
    /**
     * Export rekap absensi bulanan ke PDF
     */
    public function exportMonthlyPdf(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'bulan' => 'required|integer|min:1|max:12',
                'tahun' => 'required|integer|min:2020|max:2030',
                'id_user' => 'nullable|integer'
            ]);

            // Hanya admin dan HRD yang bisa export
            if (!is_admin() && !is_hrd()) {
                return redirect()->back()
                    ->with('error', 'Anda tidak memiliki akses untuk mengekspor laporan.');
            }

            $bulan = $request->bulan;
            $tahun = $request->tahun;
            $idUser = $request->id_user;

            // Get month name in Indonesian
            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ][$bulan];

            // Parameter untuk API
            $params = [
                'bulan' => $bulan,
                'tahun' => $tahun
            ];

            if ($idUser) {
                $params['id_user'] = $idUser;
            }

            // Ambil data pegawai jika ada filter id_user
            $userName = 'Semua Pegawai';
            if ($idUser) {
                $userResponse = $this->pegawaiService->getById($idUser);
                if (isset($userResponse['data']['nama'])) {
                    $userName = $userResponse['data']['nama'];
                }
            }

            // Ambil data absensi dari API
            $response = $this->absensiService->getAll($params);

            // Set title for PDF
            $judul = "Rekap Absensi $userName - $namaBulan $tahun";

            // Inisialisasi array kosong untuk mencegah error
            $processedData = [];

            // Proses data absensi jika ada
            if (isset($response['data'])) {
                $absensiData = [];
                
                // Handle berbagai format response
                if (isset($response['data']['data']) && is_array($response['data']['data'])) {
                    $absensiData = $response['data']['data'];
                } elseif (is_array($response['data'])) {
                    $absensiData = $response['data'];
                }

                // Proses setiap item absensi
                foreach ($absensiData as $item) {
                    if (is_object($item)) {
                        $item = (array) $item;
                    }

                    // Pastikan semua field memiliki nilai default
                    $processedItem = [
                        'id_absensi' => $item['id_absensi'] ?? $item['id'] ?? '-',
                        'tanggal' => $item['tanggal_absensi'] ?? '-',
                        'jam_masuk' => $item['jam_masuk'] ?? '-',
                        'jam_keluar' => $item['jam_keluar'] ?? '-',
                        'status' => $item['status'] ?? 'Tidak Diketahui',
                        'nama_pegawai' => $this->extractNamaPegawai($item) ?? '-',
                        'pegawai_id' => $item['id_pegawai'] ?? $item['pegawai_id'] ?? '-',
                        'keterangan' => $item['keterangan'] ?? '-'
                    ];

                    // Format tanggal jika ada
                    if ($processedItem['tanggal'] !== '-') {
                        try {
                            $tanggal = Carbon::parse($processedItem['tanggal'])->format('d/m/Y');
                            $processedItem['tanggal'] = $tanggal;
                        } catch (\Exception $e) {
                            $processedItem['tanggal'] = '-';
                        }
                    }

                    $processedData[] = $processedItem;
                }
            }

            // Siapkan data untuk view
            $data = [
                'absensi' => $processedData,
                'bulan' => $namaBulan,
                'tahun' => $tahun,
                'tanggal_export' => Carbon::now()->format('d/m/Y H:i:s'),
                'total_records' => count($processedData),
                'judul' => $judul,
                'nama_pegawai' => $userName,
                'periode' => "$namaBulan $tahun"
            ];

            // Log data yang akan dikirim ke view
            \Log::info('Data for PDF export', [
                'total_records' => count($processedData),
                'bulan' => $namaBulan,
                'tahun' => $tahun
            ]);

            // Generate PDF
            $pdf = Pdf::loadView('absensi.pdf.monthly-report', $data);
            
            // Set paper ke landscape untuk data yang lebih lebar
            $pdf->setPaper('a4', 'landscape');

            // Download PDF
            return $pdf->download("Rekap_Absensi_{$namaBulan}_{$tahun}.pdf");

        } catch (\Exception $e) {
            \Log::error('Error saat export PDF bulanan:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'bulan' => $request->bulan ?? null,
                'tahun' => $request->tahun ?? null
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengekspor laporan PDF: ' . $e->getMessage());
        }
    }

    /**
     * Helper method untuk extract nama pegawai dari data API
     */
    private function extractNamaPegawai($item)
    {
        // Coba berbagai kemungkinan field nama
        $possibleFields = [
            'nama_pegawai',
            'pegawai.nama_lengkap',
            'pegawai.user.nama',
            'pegawai.user.name',
            'nama',
            'name'
        ];

        foreach ($possibleFields as $field) {
            if (strpos($field, '.') !== false) {
                // Handle nested fields
                $parts = explode('.', $field);
                $value = $item;
                
                foreach ($parts as $part) {
                    if (is_array($value) && isset($value[$part])) {
                        $value = $value[$part];
                    } else {
                        $value = null;
                        break;
                    }
                }
                
                if ($value) {
                    return $value;
                }
            } else {
                // Handle direct fields
                if (isset($item[$field]) && !empty($item[$field])) {
                    return $item[$field];
                }
            }
        }

        return 'Tidak diketahui';
    }

    /**
     * Debug method untuk melihat struktur data API absensi
     */
    public function debugApiData(Request $request)
    {
        try {
            \Log::info('=== DEBUG API ABSENSI DIMULAI ===');
            
            // Ambil data absensi dari API dengan filter minimal
            $filters = [
                'limit' => 5  // Batasi hanya 5 record untuk debug
            ];
            
            $response = $this->absensiService->getAll($filters);
            
            \Log::info('Debug - Raw API Response:', [
                'response_keys' => array_keys($response),
                'full_response' => $response
            ]);
            
            if (isset($response['data'])) {
                if (isset($response['data']['data']) && is_array($response['data']['data'])) {
                    $absensiData = $response['data']['data'];
                    \Log::info('Debug - Pagination format detected');
                } else {
                    $absensiData = $response['data'];
                    \Log::info('Debug - Direct format detected');
                }
                
                if (!empty($absensiData) && is_array($absensiData)) {
                    $firstRecord = $absensiData[0];
                    
                    \Log::info('Debug - First record structure:', [
                        'is_array' => is_array($firstRecord),
                        'keys' => is_array($firstRecord) ? array_keys($firstRecord) : 'not_array',
                        'full_record' => $firstRecord
                    ]);
                    
                    // Cek kemungkinan field nama
                    $possibleNameFields = [
                        'nama_pegawai', 'pegawai', 'nama', 'user_name', 
                        'pegawai_nama', 'name', 'full_name', 'user'
                    ];
                    
                    $foundFields = [];
                    foreach ($possibleNameFields as $field) {
                        if (is_array($firstRecord) && isset($firstRecord[$field])) {
                            $foundFields[$field] = $firstRecord[$field];
                        }
                    }
                    
                    \Log::info('Debug - Found name-related fields:', $foundFields);
                } else {
                    \Log::warning('Debug - No data records found or data is not array');
                }
            } else {
                \Log::error('Debug - No data key in response');
            }
            
            \Log::info('=== DEBUG API ABSENSI SELESAI ===');
            
            return response()->json([
                'status' => 'success',
                'message' => 'Debug selesai, periksa log untuk detail',
                'response_structure' => array_keys($response),
                'has_data' => isset($response['data']),
                'data_count' => isset($response['data']) ? (is_array($response['data']) ? count($response['data']) : 'not_array') : 0
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error saat debug API data: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error saat debug: ' . $e->getMessage()
            ], 500);
        }
    }
}
