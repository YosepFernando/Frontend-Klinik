<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class AuthService extends ApiService
{
    /**
     * Login ke API
     *
     * @param string $email
     * @param string $password
     * @return array
     */
    public function login($email, $password)
    {
        // Test koneksi terlebih dahulu
        if (!$this->testConnection()) {
            return [
                'status' => 'error',
                'message' => 'Tidak dapat terhubung ke server API. Pastikan server API berjalan di http://localhost:8002',
            ];
        }

        $response = $this->post('auth/login', [
            'email' => $email,
            'password' => $password,
        ]);
        
        // Log response untuk debugging
        \Log::info('Auth API Response:', ['response' => $response]);
        
        // Periksa apakah response valid dan berhasil
        if (is_array($response) && 
            isset($response['status']) && 
            $response['status'] === 'success' && 
            isset($response['data']['token'])) {
            // Simpan token ke session
            Session::put('api_token', $response['data']['token']);
            Session::put('api_user', $response['data']['user']);
        }
        
        return $response;
    }
    
    /**
     * Register user baru
     *
     * @param array $userData
     * @return array
     */
    public function register($userData)
    {
        return $this->post('auth/register', $userData);
    }
    
    /**
     * Ambil profil user
     *
     * @return array
     */
    public function getProfile()
    {
        return $this->withToken()->get('auth/profile');
    }
    
    /**
     * Update profil user
     *
     * @param array $userData
     * @return array
     */
    public function updateProfile($userData)
    {
        return $this->withToken()->put('auth/profile', $userData);
    }
    
    /**
     * Logout
     *
     * @return array
     */
    public function logout()
    {
        $response = $this->withToken()->post('auth/logout');
        
        // Hapus token dari session
        Session::forget('api_token');
        Session::forget('api_user');
        
        return $response;
    }
    
    /**
     * Logout dari semua device
     *
     * @return array
     */
    public function logoutAll()
    {
        $response = $this->withToken()->post('auth/logout-all');
        
        // Hapus token dari session
        Session::forget('api_token');
        Session::forget('api_user');
        
        return $response;
    }
    
    /**
     * Ambil statistik dashboard
     *
     * @return array
     */
    public function getDashboardStats()
    {
        return $this->withToken()->get('dashboard/stats');
    }
    
    /**
     * Ambil semua user
     *
     * @return array
     */
    public function getAllUsers($params = [])
    {
        return $this->withToken()->get('users', $params);
    }
    
    /**
     * Ambil user berdasarkan ID
     *
     * @param int $id
     * @return array
     */
    public function getUserById($id)
    {
        return $this->withToken()->get("user/{$id}");
    }
    
    /**
     * Update status user
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateUserStatus($id, $data)
    {
        return $this->withToken()->put("users/{$id}/status", $data);
    }
    
    /**
     * Hapus user
     *
     * @param int $id
     * @return array
     */
    public function deleteUser($id)
    {
        return $this->withToken()->delete("users/{$id}");
    }
}
