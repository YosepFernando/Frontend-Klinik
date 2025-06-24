<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Treatment;
use App\Models\User;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (auth()->user() && auth()->user()->isPelanggan()) {
                abort(403, 'Akses ditolak. Fitur jadwal treatment tidak tersedia untuk pelanggan.');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        
        if ($user->isDokter() || $user->isBeautician()) {
            $appointments = Appointment::where('staff_id', $user->id)
                ->with(['treatment', 'patient'])
                ->latest()
                ->paginate(10);
        } else {
            $appointments = Appointment::with(['treatment', 'patient', 'staff'])
                ->latest()
                ->paginate(10);
        }
        
        return view('appointments.index', compact('appointments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $treatments = Treatment::where('is_active', true)->get();
        $selectedTreatment = $request->get('treatment');
        
        // Only admin, HRD, and front office can assign staff and select patient
        if (auth()->user()->isAdmin() || auth()->user()->isHRD() || auth()->user()->isFrontOffice()) {
            $patients = User::where('role', 'pelanggan')->where('is_active', true)->get();
            $staff = User::whereIn('role', ['dokter', 'beautician'])->where('is_active', true)->get();
        } else {
            // For dokter/beautician, they can only create appointments for patients
            $patients = User::where('role', 'pelanggan')->where('is_active', true)->get();
            $staff = collect();
        }
        
        return view('appointments.create', compact('treatments', 'patients', 'staff', 'selectedTreatment'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        $rules = [
            'treatment_id' => 'required|exists:treatments,id',
            'appointment_date' => 'required|date|after:now',
            'notes' => 'nullable|string',
            'patient_id' => 'required|exists:users,id',
        ];
        
        $request->validate($rules);
        
        $treatment = Treatment::findOrFail($request->treatment_id);
        
        $appointmentData = [
            'patient_id' => $request->patient_id,
            'treatment_id' => $request->treatment_id,
            'staff_id' => $request->staff_id,
            'appointment_date' => $request->appointment_date,
            'notes' => $request->notes,
            'total_price' => $treatment->price,
            'status' => 'scheduled',
            'payment_status' => 'pending',
        ];
        
        Appointment::create($appointmentData);
        
        return redirect()->route('appointments.index')
            ->with('success', 'Appointment berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        // Check authorization for dokter/beautician
        $user = auth()->user();
        if (($user->isDokter() || $user->isBeautician()) && $appointment->staff_id !== $user->id) {
            abort(403, 'Anda hanya dapat melihat appointment yang ditugaskan kepada Anda.');
        }
        
        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        $treatments = Treatment::where('is_active', true)->get();
        $patients = User::where('role', 'pelanggan')->where('is_active', true)->get();
        $staff = User::whereIn('role', ['dokter', 'beautician'])->where('is_active', true)->get();
        
        return view('appointments.edit', compact('appointment', 'treatments', 'patients', 'staff'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $request->validate([
            'treatment_id' => 'required|exists:treatments,id',
            'patient_id' => 'required|exists:users,id',
            'appointment_date' => 'required|date',
            'status' => 'required|in:scheduled,confirmed,in_progress,completed,cancelled',
            'payment_status' => 'required|in:pending,paid,cancelled',
            'notes' => 'nullable|string',
        ]);
        
        $treatment = Treatment::findOrFail($request->treatment_id);
        
        $appointment->update([
            'treatment_id' => $request->treatment_id,
            'patient_id' => $request->patient_id,
            'staff_id' => $request->staff_id,
            'appointment_date' => $request->appointment_date,
            'status' => $request->status,
            'payment_status' => $request->payment_status,
            'notes' => $request->notes,
            'total_price' => $treatment->price,
        ]);
        
        return redirect()->route('appointments.index')
            ->with('success', 'Appointment berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        
        return redirect()->route('appointments.index')
            ->with('success', 'Appointment berhasil dihapus.');
    }
}
