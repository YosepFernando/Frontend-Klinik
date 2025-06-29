<?php

namespace App\Services;

class PegawaiService extends ApiService
{
    /**
     * Ambil daftar pegawai
     *
     * @param array $params
     * @return array
     */
    public function getAll($params = [])
    {
        return $this->withToken()->get('pegawai', $params);
    }
    
    /**
     * Ambil pegawai berdasarkan ID
     *
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
        return $this->withToken()->get("pegawai/{$id}");
    }
    
    /**
     * Buat pegawai baru
     *
     * @param array $data
     * @return array
     */
    public function store($data)
    {
        return $this->withToken()->post('pegawai', $data);
    }
    
    /**
     * Update pegawai
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data)
    {
        return $this->withToken()->put("pegawai/{$id}", $data);
    }
    
    /**
     * Hapus pegawai
     *
     * @param int $id
     * @return array
     */
    public function delete($id)
    {
        return $this->withToken()->delete("pegawai/{$id}");
    }
}
