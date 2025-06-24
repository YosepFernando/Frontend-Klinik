<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Treatment;
use App\Models\Training;
use App\Models\ReligiousStudy;
use App\Models\Attendance;
use App\Models\Recruitment;
use App\Models\RecruitmentApplication;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        
        // Dashboard data based on role
        $data = [
            'user' => $user,
            'totalUsers' => User::count(),
            'totalAppointments' => Appointment::count(),
            'totalTreatments' => Treatment::count(),
            'upcomingAppointments' => collect(),
            'upcomingTrainings' => collect(),
            'upcomingReligiousStudies' => collect(),
        ];

        // Role-specific data
        switch ($user->role) {
            case 'admin':
            case 'hrd':
                $data['totalUsers'] = User::count();
                $data['totalActiveUsers'] = User::where('is_active', true)->count();
                $data['pendingAppointments'] = Appointment::where('status', 'scheduled')->count();
                $data['completedAppointments'] = Appointment::where('status', 'completed')->count();
                $data['todayAppointments'] = Appointment::whereDate('appointment_date', now()->format('Y-m-d'))->count();
                $data['upcomingTrainings'] = Training::where('is_active', true)
                    ->latest()
                    ->limit(5)
                    ->get();
                $data['upcomingReligiousStudies'] = ReligiousStudy::where('scheduled_date', '>', now())
                    ->limit(5)
                    ->get();
                break;
                
            case 'pelanggan':
                $data['upcomingAppointments'] = Appointment::where('patient_id', $user->id)
                    ->where('appointment_date', '>', now())
                    ->with('treatment', 'staff')
                    ->limit(5)
                    ->get();
                    
                // Add recruitment applications for pelanggan
                $data['myApplications'] = RecruitmentApplication::where('user_id', $user->id)
                    ->with(['recruitment', 'recruitment.posisi'])
                    ->latest()
                    ->limit(5)
                    ->get();
                    
                $data['totalApplications'] = RecruitmentApplication::where('user_id', $user->id)->count();
                $data['acceptedApplications'] = RecruitmentApplication::where('user_id', $user->id)
                    ->where('overall_status', 'accepted')
                    ->count();
                $data['pendingApplications'] = RecruitmentApplication::where('user_id', $user->id)
                    ->whereIn('overall_status', ['applied', 'document_review', 'interview_stage', 'final_review'])
                    ->count();
                break;
                
            case 'dokter':
            case 'beautician':
                $data['upcomingAppointments'] = Appointment::where('staff_id', $user->id)
                    ->where('appointment_date', '>', now())
                    ->with('treatment', 'patient')
                    ->limit(5)
                    ->get();
                break;
        }

        return view('dashboard', $data);
    }

    /**
     * HRD Dashboard for admin role only
     */
    public function hrdDashboard()
    {
        // Only admin can access this dashboard
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized - Admin access only');
        }

        $data = [
            'totalRecruitments' => \App\Models\Recruitment::count(),
            'totalApplications' => \App\Models\RecruitmentApplication::count(),
            'totalTrainings' => Training::count(),
            'totalReligiousStudies' => ReligiousStudy::count(),
            'recentRecruitments' => \App\Models\Recruitment::latest()->take(5)->get(),
            'recentTrainings' => Training::latest()->take(5)->get(),
        ];

        return view('admin.hrd-dashboard', $data);
    }
}
