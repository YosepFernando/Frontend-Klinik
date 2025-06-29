<?php

namespace App\Services;

class LowonganPekerjaanService extends ApiService
{
    /**
     * Ambil daftar lowongan pekerjaan
     *
     * @param array $params
     * @return array
     */
    public function getAll($params = [])
    {
        return $this->get('lowongan', $params);
    }
    
    /**
     * Ambil lowongan pekerjaan berdasarkan ID
     *
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
        return $this->get("lowongan/{$id}");
    }
    
    /**
     * Buat lowongan pekerjaan baru
     *
     * @param array $data
     * @return array
     */
    public function store($data)
    {
        return $this->withToken()->post('lowongan', $data);
    }
    
    /**
     * Update lowongan pekerjaan
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data)
    {
        return $this->withToken()->put("lowongan/{$id}", $data);
    }
    
    /**
     * Hapus lowongan pekerjaan
     *
     * @param int $id
     * @return array
     */
    public function delete($id)
    {
        return $this->withToken()->delete("lowongan/{$id}");
    }
}
