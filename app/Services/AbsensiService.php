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
        return $this->withToken()->get('absensi', $params);
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
     * Ambil absensi user hari ini
     *
     * @return array
     */
    public function getUserTodayAttendance()
    {
        return $this->withToken()->get('absensi/user/today');
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
        return $this->withToken()->post('absensi', $data);
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
        return $this->withToken()->put("absensi/{$id}", $data);
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
        try {
            return $this->apiService->delete("absensi/{$id}");
        } catch (\Exception $e) {
            Log::error('AbsensiService::delete - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal menghapus absensi: ' . $e->getMessage()
            ];
        }
    }
}
