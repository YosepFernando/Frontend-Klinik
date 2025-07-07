<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class GajiService
{
    protected $apiService;
    
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    
    /**
     * Ambil daftar gaji
     */
    public function getAll($params = [])
    {
        try {
            $queryString = http_build_query($params);
            $endpoint = 'gaji' . ($queryString ? '?' . $queryString : '');
            return $this->apiService->withToken()->get($endpoint);
        } catch (\Exception $e) {
            Log::error('GajiService::getAll - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil data gaji: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Ambil gaji berdasarkan ID
     */
    public function getById($id)
    {
        try {
            return $this->apiService->withToken()->get("gaji/{$id}");
        } catch (\Exception $e) {
            Log::error('GajiService::getById - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil data gaji: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Buat data gaji baru
     */
    public function store($data)
    {
        try {
            return $this->apiService->withToken()->post('gaji', $data);
        } catch (\Exception $e) {
            Log::error('GajiService::store - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal menyimpan data gaji: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update data gaji
     */
    public function update($id, $data)
    {
        try {
            return $this->apiService->withToken()->put("gaji/{$id}", $data);
        } catch (\Exception $e) {
            Log::error('GajiService::update - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengupdate data gaji: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Hapus data gaji
     */
    public function delete($id)
    {
        try {
            return $this->apiService->withToken()->delete("gaji/{$id}");
        } catch (\Exception $e) {
            Log::error('GajiService::delete - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal menghapus data gaji: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Ambil gaji berdasarkan pegawai
     */
    public function getByPegawai($pegawaiId)
    {
        try {
            return $this->apiService->withToken()->get("gaji/pegawai/{$pegawaiId}");
        } catch (\Exception $e) {
            Log::error('GajiService::getByPegawai - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil data gaji pegawai: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Generate/Hitung gaji menggunakan endpoint generate-payroll
     */
    public function calculate($data)
    {
        try {
            return $this->apiService->withToken()->post('gaji/generate-payroll', $data);
        } catch (\Exception $e) {
            Log::error('GajiService::calculate - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal generate payroll: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update status pembayaran gaji
     */
    public function updatePaymentStatus($id, $status)
    {
        try {
            return $this->apiService->withToken()->put("gaji/{$id}/payment-status", ['status_pembayaran' => $status]);
        } catch (\Exception $e) {
            Log::error('GajiService::updatePaymentStatus - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengupdate status pembayaran: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Konfirmasi pembayaran gaji
     */
    public function confirmPayment($id)
    {
        try {
            return $this->updatePaymentStatus($id, 'paid');
        } catch (\Exception $e) {
            Log::error('GajiService::confirmPayment - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal konfirmasi pembayaran: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Ambil laporan bulanan
     */
    public function getMonthlyReport($month, $year)
    {
        try {
            return $this->apiService->withToken()->get("gaji/reports/monthly?bulan={$month}&tahun={$year}");
        } catch (\Exception $e) {
            Log::error('GajiService::getMonthlyReport - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil laporan bulanan: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Ambil slip gaji (jika tersedia)
     */
    public function getSlip($id)
    {
        try {
            return $this->apiService->withToken()->get("gaji/{$id}/slip");
        } catch (\Exception $e) {
            Log::error('GajiService::getSlip - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil slip gaji: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}
