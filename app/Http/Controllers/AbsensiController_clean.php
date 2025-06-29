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
    const OFFICE_LATITUDE = -8.781952;
    const OFFICE_LONGITUDE = 115.179793;
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
        
        if ($request->filled('user_id')) {
            $params['user_id'] = $request->user_id;
        }
        
        // Ambil data absensi dari API
        $response = [];
        
        // Jika user bukan admin/HRD, filter absensi untuk user saja
        if (!$user->isAdmin() && !$user->isHRD()) {
            $response = $this->absensiService->getUserAttendanceHistory($params);
        } else {
            $response = $this->absensiService->getAll($params);
        }
        
        $absensi = collect($response['data'] ?? []);
        
        // Ambil data pengguna untuk filter (hanya untuk admin/HRD)
        $users = collect();
        if ($user->isAdmin() || $user->isHRD()) {
            $pegawaiResponse = $this->pegawaiService->getAll();
            $users = collect($pegawaiResponse['data'] ?? []);
        }
        
        return view('absensi.index', compact('absensi', 'users'));
    }

    /**
     * Tampilkan form untuk absensi masuk
     */
    public function create()
    {
        $user = auth()->user();
        
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
        
        // Siapkan data untuk API
        $data = [
            'tanggal' => Carbon::now()->format('Y-m-d'),
            'jam_masuk' => Carbon::now()->format('H:i:s'),
            'latitude_masuk' => $request->latitude,
            'longitude_masuk' => $request->longitude,
            'foto_masuk' => $fotoMasuk,
            'keterangan' => $request->keterangan,
            'status_lokasi' => $isWithinRadius ? 'di kantor' : 'di luar kantor',
        ];
        
        // Kirim ke API
        $response = $this->absensiService->store($data);
        
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
