<?php

namespace App\Services;

class AbsensiService extends ApiService
{
    /**
     * Ambil daftar absensi
     *
     * @param array $params
     * @return array
     */
    public function getAll($params = [])
    {
        \Log::info('Getting all attendance data', ['params' => $params]);
        
        $token = \Session::get('api_token');
        \Log::info('API Token for absensi request', [
            'token_present' => !empty($token),
            'token_length' => $token ? strlen($token) : 0,
            'session_id' => session()->getId()
        ]);
        
        // If no token, try to get it from the user session
        if (!$token) {
            \Log::warning('No API token found in session, checking auth_user()');
            $user = auth_user();
            if ($user) {
                \Log::info('User found but no token in session', ['user_id' => $user->id ?? 'unknown']);
            }
        }
        
        $response = $this->withToken()->get('absensi', $params);
        
        \Log::info('Absensi API Response', [
            'response_structure' => array_keys($response),
            'has_data' => isset($response['data']),
            'has_nested_data' => isset($response['data']['data']),
            'message' => $response['message'] ?? 'no_message'
        ]);
        
        return $response;
    }
    
    /**
     * Ambil absensi berdasarkan ID
     *
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
        return $this->withToken()->get("absensi/{$id}");
    }
    
    /**
     * Get today's attendance status for authenticated user
     * (Alias untuk getTodayStatus untuk backward compatibility)
     *
     * @return array
     * @deprecated Use getTodayStatus() instead
     */
    public function getUserTodayAttendance()
    {
        return $this->getTodayStatus();
    }
    
    /**
     * Ambil riwayat absensi user
     *
     * @param array $params
     * @return array
     */
    public function getUserAttendanceHistory($params = [])
    {
        return $this->withToken()->get('absensi/user/history', $params);
    }
    
    /**
     * Buat absensi baru (check-in)
     *
     * @param array $data
     * @return array
     */
    public function store($data)
    {
        \Log::info('Mengirim data absensi ke API', [
            'endpoint' => 'absensi',
            'data' => array_diff_key($data, ['foto_masuk' => '']), // Exclude foto untuk log
            'has_token' => \Session::has('api_token')
        ]);
        
        try {
            return $this->withToken()->post('absensi', $data);
        } catch (\Exception $e) {
            \Log::error('Error mengirim absensi ke API: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => array_diff_key($data, ['foto_masuk' => ''])
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Gagal mengirim data absensi: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update absensi (check-out)
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data)
    {
        \Log::info('AbsensiService::update called', [
            'id' => $id,
            'data' => $data
        ]);
        
        $response = $this->withToken()->put("absensi/{$id}", $data);
        
        \Log::info('AbsensiService::update response', [
            'response' => $response
        ]);
        
        return $response;
    }
    
    /**
     * Ambil daftar absensi berdasarkan pegawai
     *
     * @param int $pegawaiId
     * @param array $params
     * @return array
     */
    public function getByPegawai($pegawaiId, $params = [])
    {
        return $this->withToken()->get("pegawai/{$pegawaiId}/absensi", $params);
    }

    /**
     * Ambil statistik absensi
     */
    public function getStats()
    {
        try {
            return $this->apiService->get('absensi/stats');
        } catch (\Exception $e) {
            Log::error('AbsensiService::getStats - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil statistik absensi: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Hapus data absensi
     */
    public function delete($id)
    {
        \Log::info('AbsensiService::delete called', [
            'id' => $id,
            'api_base_url' => $this->baseUrl,
            'has_token' => \Session::has('api_token')
        ]);
        
        try {
            // Use Api-klinik public endpoint on port 8002
            $response = $this->makeRequest('DELETE', "http://localhost:8002/api/public/absensi/{$id}", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);
            
            \Log::info('AbsensiService::delete response from Api-klinik', [
                'id' => $id,
                'response' => $response,
                'status' => $response['status'] ?? 'unknown',
                'message' => $response['message'] ?? 'no message'
            ]);

            // Handle Api-klinik response format
            if (isset($response['status']) && $response['status'] === 'success') {
                return [
                    'success' => true,
                    'message' => $response['message'] ?? 'Data absensi berhasil dihapus'
                ];
            } else {
                throw new \Exception($response['message'] ?? 'Unknown error from API');
            }
        } catch (\Exception $e) {
            \Log::error('Error in AbsensiService::delete', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Gagal menghapus data absensi: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get today's attendance status for authenticated user
     *
     * @return array
     */
    public function getTodayStatus()
    {
        \Log::info('Getting today attendance status');
        
        $token = \Session::get('api_token');
        \Log::info('API Token for today status request', ['token_present' => !empty($token)]);
        
        try {
            $response = $this->withToken()->get('absensi/today-status');
            
            \Log::info('Today Status API Response', [
                'response_structure' => array_keys($response),
                'has_data' => isset($response['data'])
            ]);
            
            return $response;
        } catch (\Exception $e) {
            \Log::error('Error getting today status: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Gagal mendapatkan status absensi hari ini: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Check out (update jam keluar)
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function checkOut($id, $data)
    {
        \Log::info('Mengirim data checkout ke API', [
            'endpoint' => "absensi/{$id}/checkout",
            'data' => $data,
            'has_token' => \Session::has('api_token')
        ]);
        
        try {
            return $this->withToken()->post("absensi/{$id}/checkout", $data);
        } catch (\Exception $e) {
            \Log::error('Error mengirim checkout ke API: ' . $e->getMessage(), [
                'exception' => $e,
                'data' => $data
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Gagal melakukan checkout: ' . $e->getMessage()
            ];
        }
    }
}
