<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class UserService extends ApiService
{
    /**
     * Ambil daftar user dari endpoint pegawai
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
        try {
            Log::info('Updating user', [
                'user_id' => $id,
                'data' => $data
            ]);

            // Send PUT request to users/{id} endpoint for admin user updates
            $response = $this->withToken()->put("users/{$id}", $data);

            Log::info('Update user response', [
                'response' => $response,
                'url' => rtrim($this->baseUrl, '/') . "/users/{$id}"
            ]);

            return $response;
        } catch (\Exception $e) {
            Log::error('Error updating user', [
                'error' => $e->getMessage(),
                'user_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'status' => 'error',
                'message' => 'Gagal mengupdate user: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Hapus pengguna
     */
    public function delete($id)
    {
        try {
            // Use Api-klinik public endpoint on port 8002
            return $this->makeRequest('DELETE', "http://localhost:8002/api/public/users/{$id}", [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);
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
            return $this->withToken()->get('users/roles');
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
            return $this->withToken()->post("users/{$id}/toggle-status");
        } catch (\Exception $e) {
            Log::error('UserService::toggleStatus - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengubah status pengguna: ' . $e->getMessage()
            ];
        }
    }
}
