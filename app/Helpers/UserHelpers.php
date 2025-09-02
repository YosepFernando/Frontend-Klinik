<?php

if (!function_exists('auth_user')) {
    /**
     * Get authenticated user from session
     *
     * @return object|null
     */
    function auth_user()
    {
        if (!session('authenticated')) {
            return null;
        }
        
        // Create user object from session data
        $user = new \stdClass();
        $user->id = session('user_id');
        $user->email = session('user_email');
        $user->name = session('user_name');
        $user->role = session('user_role');
        
        // Additional data from api_user if available
        $apiUser = api_user();
        if ($apiUser) {
            $user->phone = $apiUser['no_telp'] ?? null;
            $user->tanggal_lahir = $apiUser['tanggal_lahir'] ?? null;
            // Use id_user from API data if available for consistency with API endpoints
            $user->id_user = $apiUser['id_user'] ?? $user->id;
        } else {
            // Fallback if no API user data
            $user->id_user = $user->id;
        }
        
        $user->is_active = true;
        
        return $user;
    }
}

if (!function_exists('is_authenticated')) {
    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    function is_authenticated()
    {
        $authenticated = session('authenticated', false);
        $hasToken = session()->has('api_token');
        $hasRole = session('user_role') !== null;
        
        // Debug log
        if (config('app.debug')) {
            \Log::debug('is_authenticated() called', [
                'authenticated' => $authenticated,
                'has_token' => $hasToken,
                'has_role' => $hasRole,
                'user_id' => session('user_id'),
                'result' => ($authenticated && $hasToken)
            ]);
        }
        
        return $authenticated && $hasToken;
    }
}

if (!function_exists('api_user')) {
    /**
     * Get API user data from session
     *
     * @param string|null $key
     * @return mixed
     */
    function api_user($key = null)
    {
        $user = session('api_user');
        
        if ($key) {
            return data_get($user, $key);
        }
        
        return $user;
    }
}

if (!function_exists('api_token')) {
    /**
     * Get API token from session
     *
     * @return string|null
     */
    function api_token()
    {
        return session('api_token');
    }
}

if (!function_exists('user_role')) {
    /**
     * Get user role from session
     *
     * @return string|null
     */
    function user_role()
    {
        // Debug log
        if (config('app.debug')) {
            \Log::debug('user_role() called', [
                'session_user_role' => session('user_role'),
                'api_user' => session('api_user'),
                'authenticated' => session('authenticated'),
            ]);
        }
        
        // Prioritas session('user_role') dulu
        if (session('user_role')) {
            return session('user_role');
        }
        
        // Fallback ke api_user('role') jika session('user_role') tidak ada
        $apiUser = session('api_user');
        if ($apiUser && isset($apiUser['role'])) {
            return $apiUser['role'];
        }
        
        return null;
    }
}

if (!function_exists('has_role')) {
    /**
     * Check if user has specific role
     *
     * @param string|array $roles
     * @return bool
     */
    function has_role($roles)
    {
        $userRole = user_role();
        
        // Debug log
        if (config('app.debug')) {
            \Log::debug('has_role() called', [
                'requested_roles' => $roles,
                'user_role' => $userRole,
                'result' => is_array($roles) ? in_array($userRole, $roles) : $userRole === $roles
            ]);
        }
        
        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }
        
        return $userRole === $roles;
    }
}

if (!function_exists('is_admin')) {
    /**
     * Check if user is admin
     *
     * @return bool
     */
    function is_admin()
    {
        return has_role('admin');
    }
}

if (!function_exists('is_hrd')) {
    /**
     * Check if user is HRD
     *
     * @return bool
     */
    function is_hrd()
    {
        return has_role('hrd');
    }
}

if (!function_exists('is_admin_or_hrd')) {
    /**
     * Check if user is admin or HRD
     *
     * @return bool
     */
    function is_admin_or_hrd()
    {
        return has_role(['admin', 'hrd']);
    }
}

if (!function_exists('is_staff')) {
    /**
     * Check if user is staff
     *
     * @return bool
     */
    function is_staff()
    {
        return has_role(['dokter', 'beautician', 'front_office', 'kasir']);
    }
}

if (!function_exists('is_customer')) {
    /**
     * Check if user is customer
     *
     * @return bool
     */
    function is_customer()
    {
        return has_role('pelanggan');
    }
}

if (!function_exists('debug_user_session')) {
    /**
     * Get debug information about user session
     *
     * @return array
     */
    function debug_user_session()
    {
        return [
            'authenticated' => session('authenticated', false),
            'user_id' => session('user_id'),
            'user_role' => session('user_role'),
            'user_name' => session('user_name'),
            'user_email' => session('user_email'),
            'api_token' => session('api_token') ? 'Present' : 'Missing',
            'pegawai_id' => session('pegawai_id'),
            'pegawai_data' => session('pegawai_data') ? 'Present' : 'Missing',
            'api_user' => session('api_user') ? 'Present' : 'Missing',
        ];
    }
}

if (!function_exists('get_current_pegawai_id')) {
    /**
     * Get current logged in user's pegawai_id
     *
     * @return int|null
     */
    function get_current_pegawai_id()
    {
        $user = auth_user();
        if (!$user) {
            return null;
        }
        
        // If admin/hrd, return null (they don't have pegawai_id)
        if (in_array($user->role, ['admin', 'hrd'])) {
            return null;
        }
        
        // Try to get from session first
        $pegawaiId = session('pegawai_id');
        
        if (!$pegawaiId) {
            // Try to get from pegawai_data
            $pegawaiData = session('pegawai_data');
            if (is_array($pegawaiData)) {
                $pegawaiId = $pegawaiData['id_pegawai'] ?? $pegawaiData['id'] ?? null;
            }
        }
        
        return $pegawaiId;
    }
}

if (!function_exists('refresh_pegawai_data')) {
    /**
     * Refresh pegawai data from API using new method
     *
     * @return bool
     */
    function refresh_pegawai_data()
    {
        $user = auth_user();
        if (!$user || in_array($user->role, ['admin', 'hrd'])) {
            return false;
        }
        
        try {
            $pegawaiService = app(\App\Services\PegawaiService::class);
            $pegawaiService->withToken(session('api_token'));
            
            // Get all pegawai and find matching user_id
            $response = $pegawaiService->getAll();
            
            if (isset($response['status']) && 
                in_array($response['status'], ['success', 'sukses']) && 
                !empty($response['data'])) {
                
                $allPegawaiData = $response['data'];
                
                // Handle paginated response
                if (isset($allPegawaiData['data'])) {
                    $allPegawaiData = $allPegawaiData['data'];
                }
                
                // Find pegawai with matching user_id
                $matchingPegawai = null;
                foreach ($allPegawaiData as $pegawai) {
                    $pegawaiUserId = $pegawai['user_id'] ?? $pegawai['id_user'] ?? null;
                    
                    if ($pegawaiUserId && $pegawaiUserId == $user->id) {
                        $matchingPegawai = $pegawai;
                        break;
                    }
                }
                
                if ($matchingPegawai) {
                    $pegawaiId = $matchingPegawai['id_pegawai'] ?? $matchingPegawai['id'] ?? null;
                    
                    session([
                        'pegawai_data' => $matchingPegawai,
                        'pegawai_id' => $pegawaiId
                    ]);
                    
                    \Log::info('refresh_pegawai_data - Found and saved matching pegawai:', [
                        'user_id' => $user->id,
                        'pegawai_id' => $pegawaiId,
                        'nama' => $matchingPegawai['nama_lengkap'] ?? $matchingPegawai['nama'] ?? 'N/A'
                    ]);
                    
                    return true;
                } else {
                    \Log::warning('refresh_pegawai_data - No matching pegawai found:', [
                        'user_id' => $user->id,
                        'total_pegawai' => count($allPegawaiData)
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('refresh_pegawai_data failed: ' . $e->getMessage());
        }
        
        return false;
    }
}
