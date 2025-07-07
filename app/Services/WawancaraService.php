<?php

namespace App\Services;

class WawancaraService extends ApiService
{
    /**
     * Ambil daftar wawancara
     */
    public function getAll($params = [])
    {
        return $this->withToken()->get('wawancara', $params);
    }
    
    /**
     * Ambil wawancara berdasarkan ID
     */
    public function getById($id)
    {
        return $this->withToken()->get("wawancara/{$id}");
    }
    
    /**
     * Buat jadwal wawancara baru
     */
    public function store($data)
    {
        return $this->withToken()->post('wawancara', $data);
    }
    
    /**
     * Update wawancara
     */
    public function update($id, $data)
    {
        return $this->withToken()->put("wawancara/{$id}", $data);
    }
    
    /**
     * Hapus wawancara
     */
    public function delete($id)
    {
        return $this->withToken()->delete("wawancara/{$id}");
    }
    
    /**
     * Ambil wawancara berdasarkan lamaran
     */
    public function getByLamaran($lamaranId)
    {
        return $this->withToken()->get("lamaran/{$lamaranId}/wawancara");
    }
    
    /**
     * Update hasil wawancara
     */
    public function updateResult($id, $data)
    {
        return $this->withToken()->put("wawancara/{$id}/result", $data);
    }
}
