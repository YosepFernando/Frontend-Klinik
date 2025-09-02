<?php

namespace App\Services;

class DashboardService extends ApiService
{
    /**
     * Ambil statistik dashboard umum
     */
    public function getGeneralStats()
    {
        return $this->withToken()->get('dashboard/stats');
    }
    
    /**
     * Ambil data dashboard
     */
    public function getDashboardData()
    {
        return $this->withToken()->get('dashboard');
    }
    
    /**
     * Ambil statistik untuk admin/HRD
     */
    public function getAdminStats()
    {
        return $this->withToken()->get('dashboard/admin-stats');
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
