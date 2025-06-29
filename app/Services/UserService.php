<?php

namespace App\Services;

class UserService extends ApiService
{
    /**
     * Ambil daftar user
     *
     * @param array $params
     * @return array
     */
    public function getAll($params = [])
    {
        return $this->withToken()->get('users', $params);
    }
    
    /**
     * Ambil user berdasarkan ID
     *
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
        return $this->withToken()->get("users/{$id}");
    }
    
    /**
     * Ambil daftar user yang belum memiliki pegawai
     *
     * @return array
     */
    public function getUsersWithoutPegawai()
    {
        return $this->withToken()->get("users/without-pegawai");
    }
    
    /**
     * Buat user baru
     *
     * @param array $data
     * @return array
     */
    public function store($data)
    {
        return $this->withToken()->post('users', $data);
    }
    
    /**
     * Update user
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data)
    {
        return $this->withToken()->put("users/{$id}", $data);
    }
    
    /**
     * Hapus pengguna
     */
    public function delete($id)
    {
        try {
            return $this->apiService->delete("users/{$id}");
        } catch (\Exception $e) {
            Log::error('UserService::delete - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal menghapus pengguna: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Ambil daftar role pengguna
     */
    public function getRoles()
    {
        try {
            return $this->apiService->get('users/roles');
        } catch (\Exception $e) {
            Log::error('UserService::getRoles - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil daftar role: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Toggle status aktif pengguna
     */
    public function toggleStatus($id)
    {
        try {
            return $this->apiService->post("users/{$id}/toggle-status");
        } catch (\Exception $e) {
            Log::error('UserService::toggleStatus - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengubah status pengguna: ' . $e->getMessage()
            ];
        }
    }
}
