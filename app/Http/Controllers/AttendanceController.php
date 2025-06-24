<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // Office coordinates (contoh: Jakarta - ganti dengan koordinat kantor sebenarnya)
    const OFFICE_LATITUDE = -8.781952;
    const OFFICE_LONGITUDE = 115.179793;
    const OFFICE_RADIUS = 100; // dalam meter
    
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
        $query = Attendance::with('user');
        
        // Filter by user for non-admin roles
        if (!$user->isAdmin() && !$user->isHRD()) {
            $query->where('user_id', $user->id);
        }
        
        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }
        
        // User filter (only for admin/HRD)
        if (($user->isAdmin() || $user->isHRD()) && $request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        $attendances = $query->orderBy('date', 'desc')->paginate(15);
        
        // Get users for filter (only for admin/HRD)
        $users = collect();
        if ($user->isAdmin() || $user->isHRD()) {
            $users = User::whereIn('role', ['admin', 'front_office', 'kasir', 'dokter', 'beautician', 'hrd'])
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->get();
        }
        
        return view('attendances.index', compact('attendances', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        
        // Check if user already has attendance for today
        $today = Carbon::today();
        $existingAttendance = Attendance::where('user_id', $user->id)
                                      ->whereDate('date', $today)
                                      ->first();
        
        if ($existingAttendance) {
            return redirect()->route('attendances.index')
                ->with('error', 'Anda sudah melakukan absensi hari ini.');
        }
        
        return view('attendances.create');
    }

    /**
     * Store a newly created resource in storage (Check In)
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        // Check if user already has attendance for today
        $existingAttendance = Attendance::where('user_id', $user->id)
                                      ->whereDate('date', $today)
                                      ->first();
        
        if ($existingAttendance) {
            return redirect()->route('attendances.index')
                ->with('error', 'Anda sudah melakukan absensi hari ini.');
        }
        
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:255',
        ]);
        
        // Check if location is within office radius
        if (!$this->isWithinOfficeRadius($request->latitude, $request->longitude)) {
            return back()->with('error', 'Anda berada di luar radius kantor. Silakan absen dari kantor atau hubungi HRD untuk izin absen dari luar kantor.');
        }
        
        $checkInTime = now();
        $workStartTime = Carbon::createFromTime(8, 0, 0); // 08:00
        
        // Determine status based on check-in time
        $status = 'present';
        if ($checkInTime->format('H:i') > $workStartTime->format('H:i')) {
            $status = 'late';
        }
        
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => $checkInTime->format('H:i:s'),
            'clock_in_latitude' => $request->latitude,
            'clock_in_longitude' => $request->longitude,
            'clock_in_address' => $request->address,
            'status' => $status,
            'notes' => $request->notes,
        ]);
        
        return redirect()->route('attendances.index')
            ->with('success', 'Check-in berhasil pada ' . $checkInTime->format('H:i'));
    }

    /**
     * Check out
     */
    public function checkOut(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        $attendance = Attendance::where('user_id', $user->id)
                               ->whereDate('date', $today)
                               ->whereNull('clock_out')
                               ->first();
        
        if (!$attendance) {
            return redirect()->route('attendances.index')
                ->with('error', 'Tidak ditemukan data check-in untuk hari ini.');
        }
        
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'required|string|max:500',
        ]);
        
        // Check if location is within office radius
        if (!$this->isWithinOfficeRadius($request->latitude, $request->longitude)) {
            return back()->with('error', 'Anda berada di luar radius kantor. Silakan clock-out dari kantor atau hubungi HRD untuk izin clock-out dari luar kantor.');
        }
        
        $checkOutTime = now();
        $attendance->update([
            'clock_out' => $checkOutTime->format('H:i:s'),
            'clock_out_latitude' => $request->latitude,
            'clock_out_longitude' => $request->longitude,
            'clock_out_address' => $request->address,
        ]);
        
        return redirect()->route('attendances.index')
            ->with('success', 'Check-out berhasil pada ' . $checkOutTime->format('H:i'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        $user = auth()->user();
        
        // Check authorization
        if (!$user->isAdmin() && !$user->isHRD() && $attendance->user_id !== $user->id) {
            abort(403);
        }
        
        return view('attendances.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        return view('attendances.edit', compact('attendance'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'status' => 'required|in:present,absent,late,sick,permission',
            'notes' => 'nullable|string|max:255',
        ]);
        
        $attendance->update([
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);
        
        return redirect()->route('attendances.index')
            ->with('success', 'Data absensi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        
        return redirect()->route('attendances.index')
            ->with('success', 'Data absensi berhasil dihapus.');
    }

    /**
     * Quick absence submission
     */
    public function submitAbsence(Request $request)
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        // Check if user already has attendance for today
        $existingAttendance = Attendance::where('user_id', $user->id)
                                      ->whereDate('date', $today)
                                      ->first();
        
        if ($existingAttendance) {
            return redirect()->route('attendances.index')
                ->with('error', 'Anda sudah melakukan absensi hari ini.');
        }
        
        $request->validate([
            'status' => 'required|in:sick,permission',
            'notes' => 'required|string|max:255',
        ]);
        
        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);
        
        return redirect()->route('attendances.index')
            ->with('success', 'Laporan ' . ($request->status === 'sick' ? 'sakit' : 'izin') . ' berhasil dikirim.');
    }
}
