<?php

namespace App\Services;

class PelatihanService extends ApiService
{
    /**
     * Ambil daftar pelatihan
     *
     * @param array $params
     * @return array
     */
    public function getAll($params = [])
    {
        return $this->withToken()->get('pelatihan', $params);
    }
    
    /**
     * Ambil pelatihan berdasarkan ID
     *
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
        return $this->withToken()->get("pelatihan/{$id}");
    }
    
    /**
     * Buat pelatihan baru
     *
     * @param array $data
     * @return array
     */
    public function store($data)
    {
        return $this->withToken()->post('pelatihan', $data);
    }
    
    /**
     * Update pelatihan
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data)
    {
        return $this->withToken()->put("pelatihan/{$id}", $data);
    }
    
    /**
     * Hapus pelatihan
     *
     * @param int $id
     * @return array
     */
    public function delete($id)
    {
        return $this->withToken()->delete("pelatihan/{$id}");
    }
    
    /**
     * Ambil pelatihan berdasarkan pegawai
     *
     * @param int $pegawaiId
     * @param array $params
     * @return array
     */
    public function getByPegawai($pegawaiId, $params = [])
    {
        return $this->withToken()->get("pegawai/{$pegawaiId}/pelatihan", $params);
    }
}
