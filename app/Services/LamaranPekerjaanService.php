<?php

namespace App\Services;

class LamaranPekerjaanService extends ApiService
{
    /**
     * Ambil daftar lamaran pekerjaan
     *
     * @param array $params
     * @return array
     */
    public function getAll($params = [])
    {
        return $this->withToken()->get('lamaran', $params);
    }
    
    /**
     * Ambil lamaran pekerjaan berdasarkan ID
     *
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
        return $this->withToken()->get("lamaran/{$id}");
    }
    
    /**
     * Kirim lamaran pekerjaan
     *
     * @param array $data
     * @return array
     */
    public function apply($data)
    {
        return $this->withToken()->post('lowongan/apply', $data);
    }
    
    /**
     * Update lamaran pekerjaan
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function update($id, $data)
    {
        return $this->withToken()->put("lamaran/{$id}", $data);
    }
    
    /**
     * Hapus lamaran pekerjaan
     *
     * @param int $id
     * @return array
     */
    public function delete($id)
    {
        return $this->withToken()->delete("lamaran/{$id}");
    }
}
