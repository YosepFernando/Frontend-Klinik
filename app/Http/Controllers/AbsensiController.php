<?php

namespace App\Http\Controllers;

use App\Services\AbsensiService;
use App\Services\PegawaiService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

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
        if ($request->filled('tanggal')) {
            $params['tanggal'] = $request->tanggal;
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
                'tanggal' => isset($item['tanggal']) ? Carbon::parse($item['tanggal']) : null,
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
        
        return view('absensi.index', compact('absensi', 'users'));
    }

    /**
     * Tampilkan form untuk absensi masuk
     */
    public function create()
    {
        $user = auth()->user();
        
        // Log data user untuk debugging
        \Log::info('User Data di AbsensiController::create', [
            'auth_user' => $user,
            'session_api_user' => session('api_user'),
            'session_user_id' => session('user_id'),
            'session_user_name' => session('user_name'),
            'session_user_role' => session('user_role')
        ]);
        
        // Cek apakah user sudah absen hari ini
        $response = $this->absensiService->getUserTodayAttendance();
        
        if (isset($response['data']) && !empty($response['data'])) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda sudah melakukan absensi hari ini.');
        }
        
        return view('absensi.create');
    }

    /**
     * Simpan absensi masuk
     */
    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto_masuk' => 'nullable|image|max:2048',
            'keterangan' => 'nullable|string',
        ]);
        
        // Cek lokasi
        $isWithinRadius = $this->isWithinOfficeRadius($request->latitude, $request->longitude);
        
        // Handle upload foto
        $fotoMasuk = null;
        if ($request->hasFile('foto_masuk')) {
            $file = $request->file('foto_masuk');
            $fotoMasuk = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/absensi'), $fotoMasuk);
        }
        
        // Dapatkan ID pegawai
        $pegawaiId = null;
        $pegawaiData = session('pegawai_data');
        $apiUser = session('api_user');
        
        // Log data yang tersedia untuk debugging
        \Log::info('Data User untuk Mendapatkan ID Pegawai', [
            'session_pegawai_data' => $pegawaiData,
            'session_pegawai_id' => session('pegawai_id'),
            'session_api_user' => $apiUser,
            'auth_user' => auth()->user(),
            'auth_user_id' => auth()->id(),
            'session_user_id' => session('user_id')
        ]);
        
        // Cara 1: Dari session pegawai_data (paling reliable)
        if (!$pegawaiId && !empty($pegawaiData)) {
            if (is_array($pegawaiData)) {
                $pegawaiId = $pegawaiData['id_pegawai'] ?? null;
                \Log::info('ID Pegawai dari session pegawai_data', ['pegawaiId' => $pegawaiId]);
            }
        }
        
        // Cara 2: Dari session pegawai_id langsung
        if (!$pegawaiId) {
            $pegawaiId = session('pegawai_id');
            if ($pegawaiId) {
                \Log::info('ID Pegawai dari session pegawai_id', ['pegawaiId' => $pegawaiId]);
            }
        }
        
        // Cara 3: Dari auth()->user()->pegawai
        if (!$pegawaiId && auth()->check() && auth()->user()) {
            if (isset(auth()->user()->pegawai)) {
                $pegawaiId = auth()->user()->pegawai->id_pegawai ?? auth()->user()->pegawai->id ?? null;
                \Log::info('ID Pegawai dari auth user pegawai', ['pegawaiId' => $pegawaiId]);
            }
            
            // Jika masih null, coba dari property id_pegawai langsung di user
            if (!$pegawaiId && isset(auth()->user()->id_pegawai)) {
                $pegawaiId = auth()->user()->id_pegawai;
                \Log::info('ID Pegawai dari auth user id_pegawai', ['pegawaiId' => $pegawaiId]);
            }
        }
        
        // Cara 4: Coba dari session api_user
        if (!$pegawaiId && is_array($apiUser)) {
            // Coba dari id_pegawai langsung di api_user
            if (isset($apiUser['id_pegawai'])) {
                $pegawaiId = $apiUser['id_pegawai'];
                \Log::info('ID Pegawai dari api_user[id_pegawai]', ['pegawaiId' => $pegawaiId]);
            }
            
            // Coba dari array pegawai dalam api_user
            if (!$pegawaiId && isset($apiUser['pegawai'])) {
                if (is_array($apiUser['pegawai'])) {
                    $pegawaiId = $apiUser['pegawai']['id_pegawai'] ?? $apiUser['pegawai']['id'] ?? null;
                    \Log::info('ID Pegawai dari api_user[pegawai] array', ['pegawaiId' => $pegawaiId]);
                } elseif (is_object($apiUser['pegawai'])) {
                    $pegawaiId = $apiUser['pegawai']->id_pegawai ?? $apiUser['pegawai']->id ?? null;
                    \Log::info('ID Pegawai dari api_user[pegawai] object', ['pegawaiId' => $pegawaiId]);
                }
            }
        }
        
        // Cara 5: Jika masih null, coba ambil dari API berdasarkan user_id
        if (!$pegawaiId) {
            $userId = session('user_id');
            if ($userId) {
                try {
                    $pegawaiResponse = $this->pegawaiService->getByUserId($userId);
                    if (isset($pegawaiResponse['status']) && $pegawaiResponse['status'] === 'success' && !empty($pegawaiResponse['data'])) {
                        $pegawaiFromApi = $pegawaiResponse['data'];
                        $pegawaiId = $pegawaiFromApi['id_pegawai'] ?? null;
                        
                        // Simpan data pegawai ke session untuk penggunaan selanjutnya
                        if ($pegawaiId) {
                            session(['pegawai_data' => $pegawaiFromApi, 'pegawai_id' => $pegawaiId]);
                            \Log::info('ID Pegawai dari API call', ['pegawaiId' => $pegawaiId]);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Error getting pegawai data from API', ['error' => $e->getMessage()]);
                }
            }
        }
        
        // Cara 6: Terakhir, gunakan user_id sebagai fallback
        if (!$pegawaiId) {
            $userId = session('user_id');
            if ($userId) {
                $pegawaiId = $userId;
                \Log::info('Menggunakan user_id sebagai pegawaiId fallback', ['pegawaiId' => $pegawaiId]);
            }
        }
        
        if (!$pegawaiId) {
            return redirect()->route('absensi.create')
                ->with('error', 'ID Pegawai tidak ditemukan. Harap hubungi administrator.');
        }
        
        // Log data yang akan dikirim
        \Log::info('Akan mengirim data absensi ke API', [
            'pegawai_id' => $pegawaiId,
            'tanggal' => Carbon::now()->format('Y-m-d'),
            'has_token' => \Session::has('api_token')
        ]);
        
        // Siapkan data untuk API sesuai dengan struktur API yang sebenarnya
        // API akan otomatis mengambil pegawai_id dari $user->pegawai
        $data = [
            'lokasi_masuk' => $isWithinRadius ? 'Kantor' : 'Luar Kantor',
            'keterangan' => $request->keterangan,
        ];
        
        // Kirim ke API
        $response = $this->absensiService->store($data);
        
        // Log response API
        \Log::info('Response API Absensi', ['response' => $response]);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('absensi.index')
                ->with('success', 'Absensi berhasil disimpan.');
        }
        
        return redirect()->route('absensi.create')
            ->with('error', 'Gagal menyimpan absensi: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
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
        
        // Hanya admin dan HRD yang bisa mengupdate
        if (!$user->isAdmin() && !$user->isHRD()) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengupdate absensi.');
        }
        
        $request->validate([
            'tanggal' => 'required|date',
            'jam_masuk' => 'required',
            'jam_pulang' => 'nullable',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:hadir,sakit,izin,alfa',
        ]);
        
        $data = [
            'tanggal' => $request->tanggal,
            'jam_masuk' => $request->jam_masuk,
            'jam_pulang' => $request->jam_pulang,
            'keterangan' => $request->keterangan,
            'status' => $request->status,
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
        $user = auth()->user();
        
        // Hanya admin dan HRD yang bisa menghapus
        if (!$user->isAdmin() && !$user->isHRD()) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda tidak memiliki akses untuk menghapus absensi.');
        }
        
        $response = $this->absensiService->delete($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('absensi.index')
                ->with('success', 'Data absensi berhasil dihapus.');
        }
        
        return redirect()->route('absensi.index')
            ->with('error', 'Gagal menghapus absensi: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Checkout (update jam pulang)
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'id_absensi' => 'required|integer',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto_pulang' => 'nullable|image|max:2048',
            'keterangan_pulang' => 'nullable|string',
        ]);
        
        // Cek lokasi
        $isWithinRadius = $this->isWithinOfficeRadius($request->latitude, $request->longitude);
        
        // Handle upload foto
        $fotoPulang = null;
        if ($request->hasFile('foto_pulang')) {
            $file = $request->file('foto_pulang');
            $fotoPulang = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/absensi'), $fotoPulang);
        }
        
        // Siapkan data untuk API
        $data = [
            'jam_pulang' => Carbon::now()->format('H:i:s'),
            'latitude_pulang' => $request->latitude,
            'longitude_pulang' => $request->longitude,
            'foto_pulang' => $fotoPulang,
            'keterangan_pulang' => $request->keterangan_pulang,
            'status_lokasi_pulang' => $isWithinRadius ? 'di kantor' : 'di luar kantor',
        ];
        
        // Kirim ke API
        $response = $this->absensiService->update($request->id_absensi, $data);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('absensi.index')
                ->with('success', 'Check-out berhasil disimpan.');
        }
        
        return redirect()->route('absensi.index')
            ->with('error', 'Gagal melakukan check-out: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
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
            'tanggal' => 'required|date',
            'jam_masuk' => 'required',
            'jam_pulang' => 'nullable',
            'keterangan' => 'nullable|string',
            'status' => 'required|in:hadir,sakit,izin,alfa',
        ]);
        
        $data = [
            'id_pegawai' => $request->id_pegawai,
            'tanggal' => $request->tanggal,
            'jam_masuk' => $request->jam_masuk,
            'jam_pulang' => $request->jam_pulang,
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
}
