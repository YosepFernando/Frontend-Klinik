<?php

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

if (!function_exists('is_dokter')) {
    /**
     * Check if user is dokter
     *
     * @return bool
     */
    function is_dokter()
    {
        return has_role('dokter');
    }
}

if (!function_exists('is_beautician')) {
    /**
     * Check if user is beautician
     *
     * @return bool
     */
    function is_beautician()
    {
        return has_role('beautician');
    }
}

if (!function_exists('is_pelanggan')) {
    /**
     * Check if user is pelanggan
     *
     * @return bool
     */
    function is_pelanggan()
    {
        return has_role('pelanggan');
    }
}

if (!function_exists('is_kasir')) {
    /**
     * Check if user is kasir
     *
     * @return bool
     */
    function is_kasir()
    {
        return has_role('kasir');
    }
}

if (!function_exists('is_front_office')) {
    /**
     * Check if user is front_office
     *
     * @return bool
     */
    function is_front_office()
    {
        return has_role('front_office');
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
