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
