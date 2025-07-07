<?php

namespace App\Services;

class TreatmentService extends ApiService
{
    /**
     * Ambil daftar treatment
     */
    public function getAll($params = [])
    {
        return $this->withToken()->get('treatments', $params);
    }
    
    /**
     * Ambil treatment berdasarkan ID
     */
    public function getById($id)
    {
        return $this->withToken()->get("treatments/{$id}");
    }
    
    /**
     * Buat treatment baru
     */
    public function store($data)
    {
        return $this->withToken()->post('treatments', $data);
    }
    
    /**
     * Update treatment
     */
    public function update($id, $data)
    {
        return $this->withToken()->put("treatments/{$id}", $data);
    }
    
    /**
     * Hapus treatment
     */
    public function delete($id)
    {
        return $this->withToken()->delete("treatments/{$id}");
    }
    
    /**
     * Ambil kategori treatment
     */
    public function getCategories()
    {
        return $this->withToken()->get('treatments/categories');
    }
    
    /**
     * Ambil treatment berdasarkan role
     */
    public function getAvailableForRole($role)
    {
        return $this->withToken()->get("treatments/role/{$role}");
    }
}
