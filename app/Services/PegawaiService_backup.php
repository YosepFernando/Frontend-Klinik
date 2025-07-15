<?php

namespace App\Services;

class PegawaiService extends ApiService
{
    /**
     * Ambil     /**
     * Ambil pegawai berdasarkan user ID
     *
     * @param int $userId
     * @return array
     */
    public function getByUserId($userId)
    {
        \Log::info("Mengambil data pegawai untuk user_id: {$userId}");
        
        try {
            $response = $this->withToken()->get("pegawai/user/{$userId}");
            
            \Log::info("Response pegawai untuk user_id {$userId}:", [
                'status' => $response['status'] ?? 'no_status',
                'has_data' => isset($response['data']),
                'data_keys' => isset($response['data']) ? array_keys($response['data']) : [],
                'nama' => isset($response['data']['nama']) ? $response['data']['nama'] : 'nama_tidak_ditemukan'
            ]);
            
            return $response;
        } catch (\Exception $e) {
            \Log::error("Error mengambil pegawai untuk user_id {$userId}: " . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil data pegawai: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Ambil daftar pegawai
     *
     * @param array $params
     * @return array
     */
    public function getAll($params = [])
    {
        return $this->withToken()->get('pegawai', $params);
    }
    
    /**
     * Ambil pegawai berdasarkan ID
     *
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
        return $this->withToken()->get("pegawai/{$id}");
    }
    
    /**
     * Buat pegawai baru
     *
     * @param array $data
     * @return array
     */
    public function store($data)
    {
        return $this->withToken()->post('pegawai', $data);
    }
    
    /**
     * Update pegawai
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data)
    {
        return $this->withToken()->put("pegawai/{$id}", $data);
    }
    
    /**
     * Hapus pegawai
     *
     * @param int $id
     * @return array
     */
    public function delete($id)
    {
        return $this->withToken()->delete("pegawai/{$id}");
    }
    
    /**
     * Ambil absensi pegawai
     *
     * @param int $id
     * @return array
     */
    public function getAbsensi($id)
    {
        return $this->withToken()->get("pegawai/{$id}/absensi");
    }
    
    /**
     * Ambil gaji pegawai
     *
     * @param int $id
     * @return array
     */
    public function getGaji($id)
    {
        return $this->withToken()->get("pegawai/{$id}/gaji");
    }
    
    /**
     * Ambil pelatihan pegawai
     *
     * @param int $id
     * @return array
     */
    public function getPelatihan($id)
    {
        return $this->withToken()->get("pegawai/{$id}/pelatihan");
    }
    
    /**
     * Ambil pegawai berdasarkan User ID
     *
     * @param int $userId
     * @return array
     */
    public function getByUserId($userId)
    {
        \Log::info("Mengambil data pegawai untuk user_id: {$userId}");
        
        try {
            $response = $this->withToken()->get("pegawai/user/{$userId}");
            
            \Log::info("Response pegawai untuk user_id {$userId}:", [
                'status' => $response['status'] ?? 'no_status',
                'has_data' => isset($response['data']),
                'data_keys' => isset($response['data']) ? array_keys($response['data']) : [],
                'nama' => isset($response['data']['nama']) ? $response['data']['nama'] : 'nama_tidak_ditemukan'
            ]);
            
            return $response;
        } catch (\Exception $e) {
            \Log::error("Error mengambil pegawai untuk user_id {$userId}: " . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil data pegawai: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Ambil data pegawai sendiri berdasarkan user_id yang sedang login
     *
     * @return array
     */
    public function getMyPegawaiData()
    {
        return $this->withToken()->get('pegawai/my-data');
    }
}
