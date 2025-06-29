<?php

namespace App\Http\Controllers;

use App\Services\AbsensiService;
use App\Services\PegawaiService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
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
     * Tampilkan daftar kehadiran
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $params = [];
        
        // Filter berdasarkan tanggal
        if ($request->filled('date')) {
            $params['tanggal'] = $request->date;
        }
        
        // Filter berdasarkan user (hanya untuk admin/HRD)
        if ($request->filled('user_id')) {
            $params['user_id'] = $request->user_id;
        }
        
        // Ambil data kehadiran dari API
        $response = [];
        
        // Jika user bukan admin/HRD, filter kehadiran untuk user saja
        if (!$user->isAdmin() && !$user->isHRD()) {
            $response = $this->absensiService->getUserAttendanceHistory($params);
        } else {
            $response = $this->absensiService->getAll($params);
        }
        
        $attendances = collect($response['data'] ?? []);
        
        // Ambil data pengguna untuk filter (hanya untuk admin/HRD)
        $users = collect();
        if ($user->isAdmin() || $user->isHRD()) {
            $pegawaiResponse = $this->pegawaiService->getAll();
            $users = collect($pegawaiResponse['data'] ?? []);
        }
        
        return view('attendances.index', compact('attendances', 'users'));
    }

    /**
     * Tampilkan form untuk kehadiran baru
     */
    public function create()
    {
        $user = auth()->user();
        
        // Cek apakah user sudah melakukan kehadiran hari ini
        $response = $this->absensiService->getUserTodayAttendance();
        
        if (isset($response['data']) && !empty($response['data'])) {
            return redirect()->route('attendances.index')
                ->with('error', 'Anda sudah melakukan kehadiran hari ini.');
        }
        
        return view('attendances.create');
    }

    /**
     * Simpan kehadiran baru (Check In)
     */
    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:255',
        ]);
        
        // Cek lokasi
        $isWithinRadius = $this->isWithinOfficeRadius($request->latitude, $request->longitude);
        
        if (!$isWithinRadius) {
            return back()->with('error', 'Anda berada di luar radius kantor. Silakan absen dari kantor atau hubungi HRD untuk izin absen dari luar kantor.');
        }
        
        // Tentukan status berdasarkan waktu check-in
        $checkInTime = Carbon::now();
        $workStartTime = Carbon::createFromTime(8, 0, 0); // 08:00
        $status = $checkInTime->format('H:i') > $workStartTime->format('H:i') ? 'terlambat' : 'hadir';
        
        // Siapkan data untuk API
        $data = [
            'tanggal' => $checkInTime->format('Y-m-d'),
            'jam_masuk' => $checkInTime->format('H:i:s'),
            'latitude_masuk' => $request->latitude,
            'longitude_masuk' => $request->longitude,
            'alamat_absen' => $request->address,
            'status' => $status,
            'keterangan' => $request->notes,
            'status_lokasi' => 'di kantor',
        ];
        
        // Kirim ke API
        $response = $this->absensiService->store($data);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('attendances.index')
                ->with('success', 'Check-in berhasil pada ' . $checkInTime->format('H:i'));
        }
        
        return redirect()->route('attendances.create')
            ->with('error', 'Gagal melakukan check-in: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Tampilkan detail kehadiran
     */
    public function show($id)
    {
        $response = $this->absensiService->getById($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            $attendance = $response['data'];
            return view('attendances.show', compact('attendance'));
        }
        
        return redirect()->route('attendances.index')
            ->with('error', 'Data kehadiran tidak ditemukan.');
    }

    /**
     * Form edit kehadiran (hanya admin/HRD)
     */
    public function edit($id)
    {
        $user = auth()->user();
        
        // Hanya admin dan HRD yang bisa mengedit
        if (!$user->isAdmin() && !$user->isHRD()) {
            return redirect()->route('attendances.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit kehadiran.');
        }
        
        $response = $this->absensiService->getById($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            $attendance = $response['data'];
            
            // Ambil data pegawai untuk dropdown
            $pegawaiResponse = $this->pegawaiService->getAll();
            $pegawai = collect($pegawaiResponse['data'] ?? []);
            
            return view('attendances.edit', compact('attendance', 'pegawai'));
        }
        
        return redirect()->route('attendances.index')
            ->with('error', 'Data kehadiran tidak ditemukan.');
    }

    /**
     * Update kehadiran (hanya admin/HRD)
     */
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        
        // Hanya admin dan HRD yang bisa mengupdate
        if (!$user->isAdmin() && !$user->isHRD()) {
            return redirect()->route('attendances.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengupdate kehadiran.');
        }
        
        $request->validate([
            'date' => 'required|date',
            'clock_in' => 'required',
            'clock_out' => 'nullable',
            'notes' => 'nullable|string',
            'status' => 'required|in:present,late,absent,sick,permission',
        ]);
        
        $data = [
            'tanggal' => $request->date,
            'jam_masuk' => $request->clock_in,
            'jam_pulang' => $request->clock_out,
            'keterangan' => $request->notes,
            'status' => $request->status,
        ];
        
        $response = $this->absensiService->update($id, $data);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('attendances.index')
                ->with('success', 'Data kehadiran berhasil diupdate.');
        }
        
        return redirect()->route('attendances.edit', $id)
            ->with('error', 'Gagal mengupdate kehadiran: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Hapus kehadiran (hanya admin/HRD)
     */
    public function destroy($id)
    {
        $user = auth()->user();
        
        // Hanya admin dan HRD yang bisa menghapus
        if (!$user->isAdmin() && !$user->isHRD()) {
            return redirect()->route('attendances.index')
                ->with('error', 'Anda tidak memiliki akses untuk menghapus kehadiran.');
        }
        
        $response = $this->absensiService->delete($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('attendances.index')
                ->with('success', 'Data kehadiran berhasil dihapus.');
        }
        
        return redirect()->route('attendances.index')
            ->with('error', 'Gagal menghapus kehadiran: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Check out
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'id_attendance' => 'required|integer',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:255',
        ]);
        
        // Cek lokasi
        $isWithinRadius = $this->isWithinOfficeRadius($request->latitude, $request->longitude);
        
        // Siapkan data untuk API
        $data = [
            'jam_pulang' => Carbon::now()->format('H:i:s'),
            'latitude_pulang' => $request->latitude,
            'longitude_pulang' => $request->longitude,
            'alamat_pulang' => $request->address,
            'keterangan_pulang' => $request->notes,
            'status_lokasi_pulang' => $isWithinRadius ? 'di kantor' : 'di luar kantor',
        ];
        
        // Kirim ke API
        $response = $this->absensiService->update($request->id_attendance, $data);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('attendances.index')
                ->with('success', 'Check-out berhasil disimpan.');
        }
        
        return redirect()->route('attendances.index')
            ->with('error', 'Gagal melakukan check-out: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Submit izin tidak masuk
     */
    public function submitAbsence(Request $request)
    {
        $request->validate([
            'absence_type' => 'required|in:sick,permission',
            'reason' => 'required|string|max:500',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);
        
        // Handle upload dokumen
        $documentPath = null;
        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $documentPath = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/attendance'), $documentPath);
        }
        
        // Siapkan data untuk API
        $data = [
            'tanggal' => Carbon::now()->format('Y-m-d'),
            'status' => $request->absence_type === 'sick' ? 'sakit' : 'izin',
            'keterangan' => $request->reason,
            'dokumen' => $documentPath,
        ];
        
        // Kirim ke API
        $response = $this->absensiService->store($data);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('attendances.index')
                ->with('success', 'Permohonan izin berhasil diajukan.');
        }
        
        return redirect()->route('attendances.index')
            ->with('error', 'Gagal mengajukan permohonan izin: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }
}
