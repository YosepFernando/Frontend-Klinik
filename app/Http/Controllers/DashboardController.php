<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashboardService;
use App\Services\AbsensiService;
use App\Services\PelatihanService;
use App\Services\LowonganPekerjaanService;
use App\Services\LamaranPekerjaanService;

class DashboardController extends Controller
{
    protected $dashboardService;
    protected $absensiService;
    protected $pelatihanService;
    protected $lowonganService;
    protected $lamaranService;
    
    public function __construct(
        DashboardService $dashboardService,
        AbsensiService $absensiService,
        PelatihanService $pelatihanService,
        LowonganPekerjaanService $lowonganService,
        LamaranPekerjaanService $lamaranService
    ) {
        $this->middleware('auth');
        $this->dashboardService = $dashboardService;
        $this->absensiService = $absensiService;
        $this->pelatihanService = $pelatihanService;
        $this->lowonganService = $lowonganService;
        $this->lamaranService = $lamaranService;
    }

    /**
     * Tampilkan dashboard utama
     */
    public function index()
    {
        $user = auth()->user();
        
        // Data dashboard berdasarkan role
        $data = [
            'user' => $user,
        ];

        try {
            // Role-specific data
            switch ($user->role) {
                case 'admin':
                case 'hrd':
                    // Ambil statistik admin dari API
                    $adminStats = $this->dashboardService->getAdminStats();
                    $data = array_merge($data, $adminStats['data'] ?? []);
                    
                    // Ambil data pelatihan terbaru
                    $pelatihanResponse = $this->pelatihanService->getAll(['limit' => 5]);
                    $data['upcomingTrainings'] = collect($pelatihanResponse['data'] ?? []);
                    
                    // Ambil data absensi terbaru 
                    $absensiResponse = $this->absensiService->getAll(['limit' => 5]);
                    $data['recentAbsensi'] = collect($absensiResponse['data'] ?? []);
                    
                    break;
                    
                case 'pelanggan':
                    // Ambil data khusus pelanggan dari API
                    $customerData = $this->dashboardService->getCustomerData($user->id);
                    $data = array_merge($data, $customerData['data'] ?? []);
                    
                    // Ambil lamaran pekerjaan user
                    $lamaranResponse = $this->lamaranService->getUserApplications(['limit' => 5]);
                    $data['myApplications'] = collect($lamaranResponse['data'] ?? []);
                    
                    break;
                    
                case 'dokter':
                case 'beautician':
                case 'front_office':
                case 'kasir':
                    // Ambil data khusus staff dari API
                    $staffData = $this->dashboardService->getStaffData($user->id);
                    $data = array_merge($data, $staffData['data'] ?? []);
                    
                    // Ambil absensi user hari ini
                    $todayAttendance = $this->absensiService->getUserTodayAttendance();
                    $data['todayAttendance'] = $todayAttendance['data'] ?? null;
                    
                    break;
            }
            
        } catch (\Exception $e) {
            // Jika ada error, set data default
            $data['error'] = 'Gagal memuat data dashboard: ' . $e->getMessage();
        }

        return view('dashboard', $data);
    }

    /**
     * Dashboard HRD khusus untuk admin
     */
    public function hrdDashboard()
    {
        $user = auth()->user();
        
        // Hanya admin yang bisa akses dashboard HRD
        if (!$user->isAdmin()) {
            abort(403, 'Akses ditolak - Hanya admin yang dapat mengakses halaman ini');
        }

        try {
            // Ambil statistik rekrutmen dari API
            $recruitmentStats = $this->dashboardService->getRecruitmentStats();
            
            // Ambil data lowongan terbaru
            $lowonganResponse = $this->lowonganService->getAll(['limit' => 5]);
            $recentRecruitments = collect($lowonganResponse['data'] ?? []);
            
            // Ambil data pelatihan terbaru
            $pelatihanResponse = $this->pelatihanService->getAll(['limit' => 5]);
            $recentTrainings = collect($pelatihanResponse['data'] ?? []);
            
            // Ambil data kajian keagamaan dari API
            $religiousData = $this->dashboardService->getTrainingAndReligiousData();
            
            $data = array_merge($recruitmentStats['data'] ?? [], [
                'recentRecruitments' => $recentRecruitments,
                'recentTrainings' => $recentTrainings,
                'religiousStudies' => collect($religiousData['data']['religious_studies'] ?? []),
            ]);
            
        } catch (\Exception $e) {
            $data = [
                'error' => 'Gagal memuat data dashboard HRD: ' . $e->getMessage(),
                'recentRecruitments' => collect(),
                'recentTrainings' => collect(),
                'religiousStudies' => collect(),
            ];
        }

        return view('admin.hrd-dashboard', $data);
    }
}
