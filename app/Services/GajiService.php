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
            return $this->apiService->get($endpoint);
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
            // Use authenticated API request with token
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
     * Set token untuk autentikasi API
     */
    public function withToken($token = null)
    {
        $this->apiService->withToken($token);
        return $this;
    }
    
    /**
     * Buat data gaji baru
     */
    public function store($data)
    {
        try {
            return $this->apiService->post('gaji', $data);
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
            // Use authenticated API request with token
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
            // Use Api-klinik public endpoint on port 8002
            return $this->apiService->makeRequest('DELETE', "http://localhost:8002/api/public/gaji/{$id}", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);
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
            return $this->apiService->get("gaji/pegawai/{$pegawaiId}");
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
     * Generate/Hitung gaji menggunakan endpoint calculate
     */
    public function calculate($data)
    {
        try {
            return $this->apiService->post('gaji/calculate', [
                'periode_bulan' => $data['bulan'] ?? date('n'),
                'periode_tahun' => $data['tahun'] ?? date('Y'),
                'pegawai_ids' => $data['pegawai_ids'] ?? []
            ]);
        } catch (\Exception $e) {
            Log::error('GajiService::calculate - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal menghitung gaji bulanan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update status pembayaran gaji
     */
    public function updatePaymentStatus($id, $status, $tanggalPembayaran = null)
    {
        try {
            Log::info('GajiService::updatePaymentStatus - Calling API', [
                'id' => $id,
                'status' => $status,
                'tanggal_pembayaran' => $tanggalPembayaran,
                'endpoint' => "gaji/{$id}"
            ]);

            // Prepare the payload with the updated status and optional payment date
            $payload = ['status' => $status];
            
            // Add payment date if provided and status is "Terbayar"
            if ($tanggalPembayaran && $status === 'Terbayar') {
                $payload['tanggal_pembayaran'] = $tanggalPembayaran;
            }

            // Call the API service to update the payment status
            $response = $this->apiService->put("gaji/{$id}", $payload);

            Log::info('GajiService::updatePaymentStatus - API Response', [
                'id' => $id,
                'payload' => $payload,
                'response_status' => $response['status'] ?? 'N/A',
                'response_message' => $response['message'] ?? $response['pesan'] ?? 'N/A'
            ]);

            // Return the response from API
            return $response;
        } catch (\Exception $e) {
            Log::error('GajiService::updatePaymentStatus - Exception: ' . $e->getMessage(), [
                'id' => $id,
                'status' => $status,
                'tanggal_pembayaran' => $tanggalPembayaran,
                'trace' => $e->getTraceAsString()
            ]);
            
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
            Log::info('GajiService::confirmPayment - Confirming payment', [
                'id' => $id
            ]);
            
            $response = $this->updatePaymentStatus($id, 'Terbayar');
            
            Log::info('GajiService::confirmPayment - Response from updatePaymentStatus', [
                'id' => $id,
                'response_status' => $response['status'] ?? 'N/A'
            ]);
            
            return $response;
        } catch (\Exception $e) {
            Log::error('GajiService::confirmPayment - Exception: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
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
            return $this->apiService->get("gaji/reports/monthly?bulan={$month}&tahun={$year}");
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
            return $this->apiService->get("gaji/{$id}/slip");
        } catch (\Exception $e) {
            Log::error('GajiService::getSlip - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil slip gaji: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Ambil data gaji sendiri untuk user yang sedang login
     *
     * @param array $params
     * @return array
     */
    public function getMyGaji($params = [])
    {
        try {
            $queryString = http_build_query($params);
            $endpoint = 'gaji/my-data' . ($queryString ? '?' . $queryString : '');
            return $this->apiService->get($endpoint);
        } catch (\Exception $e) {
            Log::error('GajiService::getMyGaji - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil data gaji: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Ambil data gaji pegawai sendiri
     */
    public function getMyData($params = [])
    {
        try {
            $queryString = http_build_query($params);
            $endpoint = 'gaji/my-data' . ($queryString ? '?' . $queryString : '');
            return $this->apiService->get($endpoint);
        } catch (\Exception $e) {
            Log::error('GajiService::getMyData - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil data gaji: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
}
