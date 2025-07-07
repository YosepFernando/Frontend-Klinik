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
        // Menggunakan middleware 'api.auth' untuk authentikasi berbasis API token
        $this->middleware('api.auth');
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
        // Check authentication
        if (!is_authenticated()) {
            return redirect()->route('login');
        }
        
        $user = auth_user();
        
        // Data dashboard berdasarkan role
        $data = [
            'user' => $user,
        ];

        try {
            // Ambil data dashboard umum dari API
            $dashboardResponse = $this->dashboardService->getDashboardData();
            
            if (isset($dashboardResponse['status']) && $dashboardResponse['status'] === 'success') {
                $data = array_merge($data, $dashboardResponse['data'] ?? []);
            }
            
            // Role-specific data
            switch ($user->role) {
                case 'admin':
                case 'hrd':
                    // Ambil statistik admin dari API
                    $adminStatsResponse = $this->dashboardService->getAdminStats();
                    if (isset($adminStatsResponse['status']) && $adminStatsResponse['status'] === 'success') {
                        $data = array_merge($data, $adminStatsResponse['data'] ?? []);
                    }
                    
                    // Ambil data pelatihan terbaru
                    $pelatihanResponse = $this->pelatihanService->getAll(['limit' => 5]);
                    if (isset($pelatihanResponse['status']) && $pelatihanResponse['status'] === 'success') {
                        $data['upcomingTrainings'] = $pelatihanResponse['data']['pelatihan'] ?? [];
                    }
                    
                    // Ambil data absensi terbaru 
                    $absensiResponse = $this->absensiService->getAll(['limit' => 5]);
                    if (isset($absensiResponse['status']) && $absensiResponse['status'] === 'success') {
                        $data['recentAbsensi'] = $absensiResponse['data']['absensi'] ?? [];
                    }
                    
                    break;
                    
                case 'pelanggan':
                    // Ambil lamaran pekerjaan user
                    $lamaranResponse = $this->lamaranService->getAll(['user_id' => $user->id, 'limit' => 5]);
                    if (isset($lamaranResponse['status']) && $lamaranResponse['status'] === 'success') {
                        $data['myApplications'] = $lamaranResponse['data']['lamaran'] ?? [];
                    }
                    
                    break;
                    
                case 'dokter':
                case 'beautician':
                case 'front_office':
                case 'kasir':
                    // Ambil absensi user hari ini
                    $todayAttendanceResponse = $this->absensiService->getTodayAttendance($user->id);
                    if (isset($todayAttendanceResponse['status']) && $todayAttendanceResponse['status'] === 'success') {
                        $data['todayAttendance'] = $todayAttendanceResponse['data'] ?? null;
                    }
                    
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
        // Check authentication
        if (!is_authenticated()) {
            return redirect()->route('login');
        }
        
        $user = auth_user();
        
        // Hanya admin yang bisa akses dashboard HRD
        if (!$user || !in_array($user->role, ['admin', 'hrd'])) {
            abort(403, 'Akses ditolak - Hanya admin yang dapat mengakses halaman ini');
        }

        try {
            // Ambil statistik rekrutmen dari API
            $recruitmentStatsResponse = $this->dashboardService->getGeneralStats();
            
            // Ambil data lowongan terbaru
            $lowonganResponse = $this->lowonganService->getAll(['limit' => 5]);
            $recentRecruitments = [];
            if (isset($lowonganResponse['status']) && $lowonganResponse['status'] === 'success') {
                $recentRecruitments = $lowonganResponse['data']['lowongan'] ?? [];
            }
            
            // Ambil data pelatihan terbaru
            $pelatihanResponse = $this->pelatihanService->getAll(['limit' => 5]);
            $recentTrainings = [];
            if (isset($pelatihanResponse['status']) && $pelatihanResponse['status'] === 'success') {
                $recentTrainings = $pelatihanResponse['data']['pelatihan'] ?? [];
            }
            
            $data = array_merge($recruitmentStatsResponse['data'] ?? [], [
                'recentRecruitments' => $recentRecruitments,
                'recentTrainings' => $recentTrainings,
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
