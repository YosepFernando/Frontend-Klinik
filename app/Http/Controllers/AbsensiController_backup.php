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
     * Calculate distance between two coordinates in meters
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth radius in meters
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Check if location is within office radius
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
     * Display a listing of the resource.
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
            // Ambil data absensi untuk user saja
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
        // Ambil data pengguna untuk filter (hanya untuk admin/HRD)
        $users = collect();
        if ($user->isAdmin() || $user->isHRD()) {
            $pegawaiResponse = $this->pegawaiService->getAll();
            $users = collect($pegawaiResponse['data'] ?? []);
        }
        
        return view('absensi.index', compact('absensi', 'users'));
    }

    /**
     * Show the form for creating a new resource.
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
     * Store a newly created resource in storage.
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
        
        if ($response['status'] === 'success') {
            return redirect()->route('absensi.index')
                ->with('success', 'Absensi berhasil disimpan.');
        }
        
        return redirect()->route('absensi.create')
            ->with('error', 'Gagal menyimpan absensi: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $response = $this->absensiService->getById($id);
        
        if ($response['status'] === 'success') {
            $absensi = $response['data'];
            return view('absensi.show', compact('absensi'));
        }
        
        return redirect()->route('absensi.index')
            ->with('error', 'Data absensi tidak ditemukan.');
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
        
        if ($response['status'] === 'success') {
            return redirect()->route('absensi.index')
                ->with('success', 'Check-out berhasil disimpan.');
        }
        
        return redirect()->route('absensi.index')
            ->with('error', 'Gagal melakukan check-out: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Report attendance
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
        
        // Ambil data absensi dari API
        $response = $this->absensiService->getAll($params);
        
        $absensi = collect($response['data'] ?? []);
        
        return view('absensi.report', compact('absensi'));
    }
    
    /**
     * Dashboard for admin only
     */
    public function dashboard()
    {
        // Ambil statistik absensi dari API
        $response = $this->absensiService->getAll(['limit' => 5]);
        
        $recentAbsensi = collect($response['data'] ?? []);
        
        return view('absensi.dashboard', compact('recentAbsensi'));
    }
}
        if (!$user->isAdmin() && !$user->isHRD()) {
            $pegawai = $user->pegawai;
            if ($pegawai) {
                $query->where('id_pegawai', $pegawai->id_pegawai);
            } else {
                // If user has no pegawai record, show empty result
                $query->whereRaw('1 = 0');
            }
        }
        
        // Date filter
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        
        // Month filter
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        
        // Year filter
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }
        
        // Status filter (only for admin/HRD)
        if (($user->isAdmin() || $user->isHRD()) && $request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // User filter (only for admin/HRD)
        if (($user->isAdmin() || $user->isHRD()) && $request->filled('user_id')) {
            $selectedUser = User::find($request->user_id);
            if ($selectedUser && $selectedUser->pegawai) {
                $query->where('id_pegawai', $selectedUser->pegawai->id_pegawai);
            }
        }
        
        $absensi = $query->orderBy('tanggal', 'desc')->paginate(15);
        
        // Get users for filter (only for admin/HRD)
        $users = collect();
        if ($user->isAdmin() || $user->isHRD()) {
            $users = User::whereIn('role', ['admin', 'front_office', 'kasir', 'dokter', 'beautician', 'hrd'])
                        ->where('is_active', true)
                        ->whereHas('pegawai')
                        ->orderBy('name')
                        ->get();
        }
        
        return view('absensi.index', compact('absensi', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;
        
        if (!$pegawai) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda belum terdaftar sebagai pegawai. Hubungi HRD untuk pendaftaran.');
        }
        
        // Check if user already has attendance for today
        $today = Carbon::today();
        $existingAbsensi = Absensi::where('id_pegawai', $pegawai->id_pegawai)
                                  ->whereDate('tanggal', $today)
                                  ->first();
        
        if ($existingAbsensi) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda sudah melakukan absensi hari ini.');
        }
        
        return view('absensi.create');
    }

    /**
     * Store a newly created resource in storage (Check In)
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;
        
        if (!$pegawai) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda belum terdaftar sebagai pegawai. Hubungi HRD untuk pendaftaran.');
        }
        
        $today = Carbon::today();
        
        // Check if user already has attendance for today
        $existingAbsensi = Absensi::where('id_pegawai', $pegawai->id_pegawai)
                                  ->whereDate('tanggal', $today)
                                  ->first();
        
        if ($existingAbsensi) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda sudah melakukan absensi hari ini.');
        }
        
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'alamat_absen' => 'required|string|max:500',
            'keterangan' => 'nullable|string|max:255',
        ]);
        
        // Check if location is within office radius
        if (!$this->isWithinOfficeRadius($request->latitude, $request->longitude)) {
            return back()->with('error', 'Anda berada di luar radius kantor. Silakan absen dari kantor atau hubungi HRD untuk izin absen dari luar kantor.');
        }
        
        $checkInTime = now();
        
        Absensi::create([
            'id_pegawai' => $pegawai->id_pegawai,
            'tanggal' => $today,
            'jam_masuk' => $checkInTime,
            'latitude_masuk' => $request->latitude,
            'longitude_masuk' => $request->longitude,
            'alamat_masuk' => $request->alamat_absen,
            'catatan' => $request->keterangan,
        ]);
        
        return redirect()->route('absensi.index')
            ->with('success', 'Check-in berhasil!');
    }

    /**
     * Check out functionality
     */
    public function checkOut(Request $request)
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;
        
        if (!$pegawai) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda belum terdaftar sebagai pegawai. Hubungi HRD untuk pendaftaran.');
        }
        
        $today = Carbon::today();
        
        $absensi = Absensi::where('id_pegawai', $pegawai->id_pegawai)
                          ->whereDate('tanggal', $today)
                          ->whereNull('jam_keluar')
                          ->first();
        
        if (!$absensi) {
            return redirect()->route('absensi.index')
                ->with('error', 'Tidak ada data check-in untuk hari ini atau Anda sudah check-out.');
        }
        
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'alamat_checkout' => 'required|string|max:500',
            'keterangan_keluar' => 'nullable|string|max:255',
        ]);
        
        // Check if location is within office radius for checkout
        if (!$this->isWithinOfficeRadius($request->latitude, $request->longitude)) {
            return back()->with('error', 'Anda berada di luar radius kantor. Silakan check-out dari kantor atau hubungi HRD.');
        }
        
        $checkOutTime = now();
        
        $absensi->update([
            'jam_keluar' => $checkOutTime,
            'latitude_keluar' => $request->latitude,
            'longitude_keluar' => $request->longitude,
            'alamat_keluar' => $request->alamat_checkout,
            'catatan' => $absensi->catatan . ($request->keterangan_keluar ? ' | Keluar: ' . $request->keterangan_keluar : ''),
        ]);
        
        return redirect()->route('absensi.index')
            ->with('success', 'Check-out berhasil!');
    }

    /**
     * Submit absence (sick/permission)
     */
    public function submitAbsence(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        // Check if user already has attendance for today
        $existingAbsensi = Absensi::where('user_id', $user->id)
                                  ->whereDate('tanggal', $today)
                                  ->first();
        
        if ($existingAbsensi) {
            return redirect()->route('absensi.index')
                ->with('error', 'Anda sudah melakukan absensi hari ini.');
        }
        
        $request->validate([
            'status' => 'required|in:sakit,izin',
            'keterangan' => 'required|string|max:255',
        ]);
        
        // Get pegawai ID
        $pegawai = $user->pegawai;
        
        Absensi::create([
            'user_id' => $user->id,
            'pegawai_id' => $pegawai ? $pegawai->id : null,
            'tanggal' => $today,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);
        
        return redirect()->route('absensi.index')
            ->with('success', 'Laporan ketidakhadiran berhasil disubmit.');
    }

    /**
     * Show the specified resource.
     */
    public function show(Absensi $absensi)
    {
        // Check if user can view this attendance record
        $user = auth()->user();
        if (!$user->isAdmin() && !$user->isHRD() && $absensi->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
        
        return view('absensi.show', compact('absensi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Absensi $absensi)
    {
        $user = auth()->user();
        
        // Check if user can edit this attendance record
        if (!$user->isAdmin() && !$user->isHRD()) {
            // Regular users can only edit their own attendance
            if (!$user->pegawai || $absensi->id_pegawai !== $user->pegawai->id_pegawai) {
                abort(403, 'Anda hanya dapat mengedit absensi Anda sendiri.');
            }
        }
        
        return view('absensi.edit', compact('absensi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Absensi $absensi)
    {
        $user = auth()->user();
        
        // Check if user can update this attendance record
        if (!$user->isAdmin() && !$user->isHRD()) {
            // Regular users can only edit their own attendance
            if (!$user->pegawai || $absensi->id_pegawai !== $user->pegawai->id_pegawai) {
                abort(403, 'Anda hanya dapat mengedit absensi Anda sendiri.');
            }
            
            // Regular users can only update notes/keterangan
            $request->validate([
                'keterangan' => 'nullable|string|max:255',
            ]);
            
            $absensi->update([
                'keterangan' => $request->keterangan
            ]);
            
            return redirect()->route('absensi.index')->with('success', 'Keterangan absensi berhasil diperbarui.');
        }
        
        // Admin/HRD can update everything
        $request->validate([
            'status' => 'required|in:Hadir,Terlambat,Sakit,Izin,Tidak Hadir',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string|max:255',
        ]);
        
        $updateData = [
            'status' => $request->status,
            'keterangan' => $request->keterangan
        ];
        
        if ($request->jam_masuk) {
            $updateData['jam_masuk'] = Carbon::createFromFormat('H:i', $request->jam_masuk);
        }
        
        if ($request->jam_keluar) {
            $updateData['jam_keluar'] = Carbon::createFromFormat('H:i', $request->jam_keluar);
        }
        
        $absensi->update($updateData);
        
        return redirect()->route('absensi.index')
            ->with('success', 'Data absensi berhasil diupdate.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Absensi $absensi)
    {
        // Only admin can delete attendance
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }
        
        $absensi->delete();
        
        return redirect()->route('absensi.index')
            ->with('success', 'Data absensi berhasil dihapus.');
    }

    /**
     * Generate attendance report for admin/HRD
     */
    public function report(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isHRD()) {
            abort(403, 'Unauthorized');
        }
        
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        $user_id = $request->get('user_id');
        
        $query = Absensi::with(['pegawai.user', 'pegawai.posisi'])
                        ->whereMonth('tanggal', $bulan)
                        ->whereYear('tanggal', $tahun);
        
        if ($user_id) {
            $selectedUser = User::find($user_id);
            if ($selectedUser && $selectedUser->pegawai) {
                $query->where('id_pegawai', $selectedUser->pegawai->id_pegawai);
            }
        }
        
        $absensi = $query->orderBy('tanggal', 'asc')->get();
        
        // Get all employees for filter
        $users = User::whereIn('role', ['admin', 'front_office', 'kasir', 'dokter', 'beautician', 'hrd'])
                    ->where('is_active', true)
                    ->whereHas('pegawai')
                    ->orderBy('name')
                    ->get();
        
        // Generate statistics
        $totalDays = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
        $workDays = $this->calculateWorkDays($bulan, $tahun);
        
        $stats = [];
        foreach ($users as $user) {
            $userAbsensi = $absensi->where('pegawai.user.id', $user->id);
            $stats[$user->id] = [
                'name' => $user->name,
                'role' => $user->role,
                'total_hadir' => $userAbsensi->where('status', 'Hadir')->count(),
                'total_terlambat' => $userAbsensi->where('status', 'Terlambat')->count(),
                'total_sakit' => $userAbsensi->where('status', 'Sakit')->count(),
                'total_izin' => $userAbsensi->where('status', 'Izin')->count(),
                'total_tidak_hadir' => $userAbsensi->where('status', 'Tidak Hadir')->count(),
                'percentage' => $workDays > 0 ? round(($userAbsensi->whereIn('status', ['Hadir', 'Terlambat'])->count() / $workDays) * 100, 1) : 0
            ];
        }
        
        return view('absensi.report', compact('absensi', 'users', 'stats', 'bulan', 'tahun', 'user_id', 'workDays'));
    }
    
    /**
     * Calculate work days in a month (excluding Sundays)
     */
    private function calculateWorkDays($month, $year)
    {
        $totalDays = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $workDays = 0;
        
        for ($day = 1; $day <= $totalDays; $day++) {
            $date = Carbon::create($year, $month, $day);
            // Exclude Sundays (0 = Sunday)
            if ($date->dayOfWeek !== 0) {
                $workDays++;
            }
        }
        
        return $workDays;
    }
    
    /**
     * Admin/HRD can manually add attendance record
     */
    public function adminCreate()
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isHRD()) {
            abort(403, 'Unauthorized');
        }
        
        $users = User::whereIn('role', ['admin', 'front_office', 'kasir', 'dokter', 'beautician', 'hrd'])
                    ->where('is_active', true)
                    ->whereHas('pegawai')
                    ->orderBy('name')
                    ->get();
        
        return view('absensi.admin-create', compact('users'));
    }
    
    /**
     * Store attendance record by admin/HRD
     */
    public function adminStore(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isHRD()) {
            abort(403, 'Unauthorized');
        }
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'status' => 'required|in:Hadir,Terlambat,Sakit,Izin,Tidak Hadir',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string|max:255',
        ]);
        
        $user = User::find($request->user_id);
        if (!$user->pegawai) {
            return redirect()->back()->with('error', 'User belum terdaftar sebagai pegawai.');
        }
        
        // Check if attendance already exists for this date
        $existingAbsensi = Absensi::where('id_pegawai', $user->pegawai->id_pegawai)
                                  ->whereDate('tanggal', $request->tanggal)
                                  ->first();
        
        if ($existingAbsensi) {
            return redirect()->back()->with('error', 'Absensi untuk tanggal ini sudah ada.');
        }
        
        Absensi::create([
            'id_pegawai' => $user->pegawai->id_pegawai,
            'tanggal' => $request->tanggal,
            'jam_masuk' => $request->jam_masuk ? Carbon::createFromFormat('H:i', $request->jam_masuk) : null,
            'jam_keluar' => $request->jam_keluar ? Carbon::createFromFormat('H:i', $request->jam_keluar) : null,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
            'alamat_masuk' => 'Ditambahkan oleh ' . auth()->user()->name,
            'latitude_masuk' => self::OFFICE_LATITUDE,
            'longitude_masuk' => self::OFFICE_LONGITUDE,
        ]);
        
        return redirect()->route('absensi.index')->with('success', 'Absensi berhasil ditambahkan.');
    }
    
    /**
     * Admin/HRD can edit attendance record
     */
    public function adminEdit(Absensi $absensi)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isHRD()) {
            abort(403, 'Unauthorized');
        }
        
        $users = User::whereIn('role', ['admin', 'front_office', 'kasir', 'dokter', 'beautician', 'hrd'])
                    ->where('is_active', true)
                    ->whereHas('pegawai')
                    ->orderBy('name')
                    ->get();
        
        return view('absensi.admin-edit', compact('absensi', 'users'));
    }
    
    /**
     * Update attendance record by admin/HRD
     */
    public function adminUpdate(Request $request, Absensi $absensi)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->isHRD()) {
            abort(403, 'Unauthorized');
        }
        
        $request->validate([
            'status' => 'required|in:Hadir,Terlambat,Sakit,Izin,Tidak Hadir',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string|max:255',
        ]);
        
        $absensi->update([
            'jam_masuk' => $request->jam_masuk ? Carbon::createFromFormat('H:i', $request->jam_masuk) : null,
            'jam_keluar' => $request->jam_keluar ? Carbon::createFromFormat('H:i', $request->jam_keluar) : null,
            'status' => $request->status,
            'keterangan' => $request->keterangan,
        ]);
        
        return redirect()->route('absensi.index')->with('success', 'Absensi berhasil diperbarui.');
    }
    
    /**
     * Dashboard overview for Admin only
     */
    public function dashboard()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized - Admin access only');
        }

        $today = Carbon::today();
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        // Get all active employees
        $totalActiveEmployees = User::whereIn('role', ['admin', 'front_office', 'kasir', 'dokter', 'beautician', 'hrd'])
                                   ->where('is_active', true)
                                   ->whereHas('pegawai')
                                   ->count();

        // Today's statistics
        $todayAttendances = Absensi::whereDate('tanggal', $today)->with(['pegawai.user'])->get();
        $todayStats = [
            'hadir' => $todayAttendances->where('status', 'Hadir')->count(),
            'terlambat' => $todayAttendances->where('status', 'Terlambat')->count(),
            'izin_sakit' => $todayAttendances->whereIn('status', ['Sakit', 'Izin'])->count(),
            'belum_absen' => $totalActiveEmployees - $todayAttendances->count()
        ];

        // Weekly statistics (last 7 days)
        $weeklyStats = [
            'labels' => [],
            'hadir' => [],
            'terlambat' => []
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $weeklyStats['labels'][] = $date->format('M d');
            
            $dayAttendances = Absensi::whereDate('tanggal', $date)->get();
            $weeklyStats['hadir'][] = $dayAttendances->where('status', 'Hadir')->count();
            $weeklyStats['terlambat'][] = $dayAttendances->where('status', 'Terlambat')->count();
        }

        // Monthly statistics
        $monthlyAttendances = Absensi::whereMonth('tanggal', $thisMonth)
                                    ->whereYear('tanggal', $thisYear)
                                    ->get();
        
        $monthlyStats = [
            $monthlyAttendances->where('status', 'Hadir')->count(),
            $monthlyAttendances->where('status', 'Terlambat')->count(),
            $monthlyAttendances->where('status', 'Sakit')->count(),
            $monthlyAttendances->where('status', 'Izin')->count(),
            $monthlyAttendances->where('status', 'Tidak Hadir')->count()
        ];

        // Recent attendances (today)
        $recentAttendances = Absensi::whereDate('tanggal', $today)
                                   ->with(['pegawai.user'])
                                   ->orderBy('created_at', 'desc')
                                   ->limit(10)
                                   ->get();

        // Top employees (best attendance this month)
        $topEmployees = User::whereIn('role', ['admin', 'front_office', 'kasir', 'dokter', 'beautician', 'hrd'])
                           ->where('is_active', true)
                           ->whereHas('pegawai')
                           ->with(['pegawai'])
                           ->get()
                           ->map(function ($user) use ($thisMonth, $thisYear) {
                               $userAttendances = Absensi::where('id_pegawai', $user->pegawai->id_pegawai)
                                                        ->whereMonth('tanggal', $thisMonth)
                                                        ->whereYear('tanggal', $thisYear)
                                                        ->get();
                               
                               $totalDays = Carbon::now()->day; // Days passed in current month
                               $attendedDays = $userAttendances->whereIn('status', ['Hadir', 'Terlambat'])->count();
                               
                               $user->attendance_percentage = $totalDays > 0 ? ($attendedDays / $totalDays) * 100 : 0;
                               return $user;
                           })
                           ->sortByDesc('attendance_percentage')
                           ->take(5);

        return view('absensi.dashboard', compact(
            'todayStats',
            'totalActiveEmployees',
            'weeklyStats',
            'monthlyStats',
            'recentAttendances',
            'topEmployees'
        ));
    }
}
