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
     * Ambil lowongan pekerjaan berdasarkan ID (public endpoint)
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
        return $this->withToken()->post('lowongan-pekerjaan', $data);
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
        return $this->withToken()->put("lowongan-pekerjaan/{$id}", $data);
    }
    
    /**
     * Hapus lowongan pekerjaan (soft delete)
     *
     * @param int $id
     * @return array
     */
    public function delete($id)
    {
        return $this->withToken()->delete("lowongan-pekerjaan/{$id}");
    }
    
    /**
     * Hapus lowongan pekerjaan secara permanen (force delete)
     *
     * @param int $id
     * @return array
     */
    public function forceDelete($id)
    {
        return $this->withToken()->delete("lowongan-pekerjaan/{$id}/force");
    }
    
    /**
     * Hapus multiple lowongan pekerjaan sekaligus (bulk delete)
     *
     * @param array $ids
     * @param bool $force
     * @return array
     */
    public function bulkDelete($ids, $force = false)
    {
        $data = ['ids' => $ids];
        if ($force) {
            $data['force'] = true;
        }
        
        return $this->withToken()->delete("lowongan-pekerjaan/bulk", $data);
    }
}
