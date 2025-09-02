<?php

namespace App\Services;

class CustomerService extends ApiService
{
    /**
     * Ambil daftar customer
     */
    public function getAll($params = [])
    {
        return $this->withToken()->get('customers', $params);
    }
    
    /**
     * Ambil customer berdasarkan ID
     */
    public function getById($id)
    {
        return $this->withToken()->get("customers/{$id}");
    }
    
    /**
     * Buat customer baru
     */
    public function store($data)
    {
        return $this->withToken()->post('customers', $data);
    }
    
    /**
     * Update customer
     */
    public function update($id, $data)
    {
        return $this->withToken()->put("customers/{$id}", $data);
    }
    
    /**
     * Hapus customer
     */
    public function delete($id)
    {
        return $this->withToken()->delete("customers/{$id}");
    }
    
    /**
     * Ambil appointment customer
     */
    public function getAppointments($id)
    {
        return $this->withToken()->get("customers/{$id}/appointments");
    }
    
    /**
     * Ambil statistik customer
     */
    public function getStatistics()
    {
        return $this->withToken()->get('customer-statistics');
    }
}
