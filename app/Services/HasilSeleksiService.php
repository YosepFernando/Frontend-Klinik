<?php

namespace App\Services;

class HasilSeleksiService extends ApiService
{
    /**
     * Ambil daftar hasil seleksi
     */
    public function getAll($params = [])
    {
        return $this->withToken()->get('hasil-seleksi', $params);
    }
    
    /**
     * Ambil hasil seleksi berdasarkan ID
     */
    public function getById($id)
    {
        return $this->withToken()->get("hasil-seleksi/{$id}");
    }
    
    /**
     * Buat hasil seleksi baru
     */
    public function store($data)
    {
        return $this->withToken()->post('hasil-seleksi', $data);
    }
    
    /**
     * Update hasil seleksi
     */
    public function update($id, $data)
    {
        return $this->withToken()->put("hasil-seleksi/{$id}", $data);
    }
    
    /**
     * Hapus hasil seleksi
     */
    public function delete($id)
    {
        return $this->withToken()->delete("hasil-seleksi/{$id}");
    }
    
    /**
     * Ambil hasil seleksi berdasarkan lamaran
     */
    public function getByLamaran($lamaranId)
    {
        return $this->withToken()->get("lamaran/{$lamaranId}/hasil-seleksi");
    }
    
    /**
     * Finalisasi hasil seleksi
     */
    public function finalize($id)
    {
        return $this->withToken()->post("hasil-seleksi/{$id}/finalize");
    }
}
