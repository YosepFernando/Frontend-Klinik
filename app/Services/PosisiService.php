<?php

namespace App\Services;

class PosisiService extends ApiService
{
    /**
     * Ambil daftar posisi
     *
     * @param array $params
     * @return array
     */
    public function getAll($params = [])
    {
        return $this->withToken()->get('posisi', $params);
    }
    
    /**
     * Ambil posisi berdasarkan ID
     *
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
        return $this->withToken()->get("posisi/{$id}");
    }
    
    /**
     * Buat posisi baru
     *
     * @param array $data
     * @return array
     */
    public function store($data)
    {
        return $this->withToken()->post('posisi', $data);
    }
    
    /**
     * Update posisi
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data)
    {
        return $this->withToken()->put("posisi/{$id}", $data);
    }
    
    /**
     * Hapus posisi
     *
     * @param int $id
     * @return array
     */
    public function delete($id)
    {
        return $this->withToken()->delete("posisi/{$id}");
    }
}
