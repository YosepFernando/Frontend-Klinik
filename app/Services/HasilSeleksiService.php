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
    public function getByLamaran($userId)
    {
        return $this->withToken()->get("hasil-seleksi/user/{$userId}");
    }
    
    /**
     * Ambil hasil seleksi berdasarkan user dan lowongan
     */
    public function getByUserAndLowongan($userId, $lowonganId)
    {
        return $this->withToken()->get("hasil-seleksi", [
            'id_user' => $userId,
            'id_lowongan_pekerjaan' => $lowonganId
        ]);
    }
    
    /**
     * Finalisasi hasil seleksi
     */
    public function finalize($id)
    {
        return $this->withToken()->post("hasil-seleksi/{$id}/finalize");
    }
    
    /**
     * Buat keputusan final untuk lamaran
     */
    public function makeFinalDecision($userId, $lowonganId, $data)
    {
        return $this->withToken()->post('hasil-seleksi', [
            'id_user' => $userId,
            'id_lowongan_pekerjaan' => $lowonganId,
            'status' => $data['final_status'],
            'tanggal_mulai_kerja' => $data['start_date'] ?? null,
            'catatan' => $data['final_notes'] ?? null,
        ]);
    }
    
    /**
     * Update keputusan final untuk hasil seleksi yang sudah ada
     */
    public function updateFinalDecision($hasilSeleksiId, $data)
    {
        return $this->withToken()->put("hasil-seleksi/{$hasilSeleksiId}", [
            'status' => $data['final_status'],
            'tanggal_mulai_kerja' => $data['start_date'] ?? null,
            'catatan' => $data['final_notes'] ?? null,
        ]);
    }

    /**
     * Ambil hasil seleksi berdasarkan lowongan pekerjaan
     */
    public function getByLowongan($lowonganId)
    {
        return $this->withToken()->get("hasil-seleksi", ['id_lowongan_pekerjaan' => $lowonganId]);
    }

}
