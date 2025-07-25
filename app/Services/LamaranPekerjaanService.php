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
        return $this->withToken()->get('public/lamaran-pekerjaan', $params);
    }
    
    /**
     * Ambil lamaran pekerjaan berdasarkan ID
     *
     * @param int $id
     * @return array
     */
    public function getById($id)
    {
        return $this->withToken()->get("public/lamaran-pekerjaan/{$id}");
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
     * Kirim lamaran pekerjaan dengan multipart data
     *
     * @param array $multipartData
     * @return array
     */
    public function applyWithMultipart($multipartData)
    {
        return $this->withToken()->postMultipart('lowongan/apply', $multipartData);
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
        return $this->withToken()->put("lamaran-pekerjaan/{$id}", $data);
    }
    
    /**
     * Hapus lamaran pekerjaan
     *
     * @param int $id
     * @return array
     */
    public function delete($id)
    {
        return $this->withToken()->delete("lamaran-pekerjaan/{$id}");
    }
    
    /**
     * Ambil lamaran pekerjaan berdasarkan ID lowongan
     *
     * @param int $lowonganId
     * @return array
     */
    public function getByLowongan($lowonganId)
    {
        return $this->withToken()->get("lamaran-pekerjaan", ['id_lowongan_pekerjaan' => $lowonganId]);
    }
    
    /**
     * Update status dokumen lamaran
     *
     * @param int $id
     * @param array $data
     * @return array
     */
    public function updateDocumentStatus($id, $data)
    {
        return $this->withToken()->patch("lamaran-pekerjaan/{$id}", [
            'status_dokumen' => $data['document_status'],
            'catatan_dokumen' => $data['document_notes'] ?? null,
        ]);
    }
    
    /**
     * Ambil lamaran berdasarkan user ID
     *
     * @param int $userId
     * @return array
     */
    public function getByUser($userId)
    {
        return $this->withToken()->get("public/lamaran-pekerjaan", ['id_user' => $userId]);
    }
}
