<?php

namespace App\Services;

use App\Services\ApiService;
use Illuminate\Support\Facades\Log;

class DashboardService
{
    protected $apiService;
    
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    
    /**
     * Ambil statistik dashboard umum
     */
    public function getGeneralStats()
    {
        try {
            return $this->apiService->get('dashboard/stats');
        } catch (\Exception $e) {
            Log::error('DashboardService::getGeneralStats - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil statistik dashboard: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Ambil statistik untuk admin/HRD
     */
    public function getAdminStats()
    {
        try {
            return $this->apiService->get('dashboard/admin-stats');
        } catch (\Exception $e) {
            Log::error('DashboardService::getAdminStats - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil statistik admin: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Ambil data untuk pelanggan
     */
    public function getCustomerData($userId)
    {
        try {
            return $this->apiService->get("dashboard/customer/{$userId}");
        } catch (\Exception $e) {
            Log::error('DashboardService::getCustomerData - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil data pelanggan: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Ambil data untuk dokter/beautician
     */
    public function getStaffData($userId)
    {
        try {
            return $this->apiService->get("dashboard/staff/{$userId}");
        } catch (\Exception $e) {
            Log::error('DashboardService::getStaffData - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil data staff: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Ambil statistik rekrutmen untuk HRD dashboard
     */
    public function getRecruitmentStats()
    {
        try {
            return $this->apiService->get('dashboard/recruitment-stats');
        } catch (\Exception $e) {
            Log::error('DashboardService::getRecruitmentStats - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil statistik rekrutmen: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Ambil data pelatihan dan kajian keagamaan terbaru
     */
    public function getTrainingAndReligiousData()
    {
        try {
            return $this->apiService->get('dashboard/training-religious');
        } catch (\Exception $e) {
            Log::error('DashboardService::getTrainingAndReligiousData - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil data pelatihan dan kajian: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
}
