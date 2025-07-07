<?php

if (!function_exists('is_admin')) {
    /**
     * Check if current user is admin
     *
     * @return bool
     */
    function is_admin()
    {
        if (!auth()->check()) {
            return false;
        }
        
        return auth()->user()->role === 'admin';
    }
}

if (!function_exists('is_hrd')) {
    /**
     * Check if current user is HRD
     *
     * @return bool
     */
    function is_hrd()
    {
        if (!auth()->check()) {
            return false;
        }
        
        return auth()->user()->role === 'hrd';
    }
}

if (!function_exists('is_doctor')) {
    /**
     * Check if current user is doctor
     *
     * @return bool
     */
    function is_doctor()
    {
        if (!auth()->check()) {
            return false;
        }
        
        return auth()->user()->role === 'dokter';
    }
}

if (!function_exists('is_beautician')) {
    /**
     * Check if current user is beautician
     *
     * @return bool
     */
    function is_beautician()
    {
        if (!auth()->check()) {
            return false;
        }
        
        return auth()->user()->role === 'beautician';
    }
}

if (!function_exists('is_front_office')) {
    /**
     * Check if current user is front office
     *
     * @return bool
     */
    function is_front_office()
    {
        if (!auth()->check()) {
            return false;
        }
        
        return auth()->user()->role === 'front_office';
    }
}

if (!function_exists('is_kasir')) {
    /**
     * Check if current user is kasir
     *
     * @return bool
     */
    function is_kasir()
    {
        if (!auth()->check()) {
            return false;
        }
        
        return auth()->user()->role === 'kasir';
    }
}

if (!function_exists('is_pelanggan')) {
    /**
     * Check if current user is pelanggan
     *
     * @return bool
     */
    function is_pelanggan()
    {
        if (!auth()->check()) {
            return false;
        }
        
        return auth()->user()->role === 'pelanggan';
    }
}

if (!function_exists('is_staff')) {
    /**
     * Check if current user is staff (any role except pelanggan)
     *
     * @return bool
     */
    function is_staff()
    {
        if (!auth()->check()) {
            return false;
        }
        
        return in_array(auth()->user()->role, ['admin', 'hrd', 'front_office', 'kasir', 'dokter', 'beautician']);
    }
}

if (!function_exists('can_manage_training')) {
    /**
     * Check if current user can manage training (create, edit, delete)
     *
     * @return bool
     */
    function can_manage_training()
    {
        return is_admin() || is_hrd();
    }
}

if (!function_exists('can_view_training')) {
    /**
     * Check if current user can view training
     *
     * @return bool
     */
    function can_view_training()
    {
        return is_staff(); // All staff except pelanggan
    }
}

// Alias functions untuk kompatibilitas dengan naming convention di view
if (!function_exists('is_hrd_alias')) {
    function is_hrd_alias()
    {
        return is_hrd();
    }
}

if (!function_exists('is_front_office_alias')) {
    function is_front_office_alias()
    {
        return is_front_office();
    }
}
