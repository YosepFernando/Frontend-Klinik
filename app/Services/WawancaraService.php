<?php

namespace App\Services;

class WawancaraService extends ApiService
{
    /**
     * Ambil daftar wawancara
     */
    public function getAll($params = [])
    {
        return $this->withToken()->get('public/wawancara', $params);
    }
    
    /**
     * Ambil wawancara berdasarkan ID
     */
    public function getById($id)
    {
        return $this->withToken()->get("public/wawancara/{$id}");
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
        return $this->withToken()->get("public/wawancara", ['id_lamaran_pekerjaan' => $lamaranId]);
    }
    
    /**
     * Update hasil wawancara
     */
    public function updateResult($id, $data)
    {
        return $this->withToken()->put("wawancara/{$id}", [
            'status' => $data['status'] ?? $data['interview_status'] ?? null,
            'nilai' => $data['nilai'] ?? $data['interview_score'] ?? null,
            'catatan' => $data['catatan'] ?? $data['interview_notes'] ?? null,
        ]);
    }
    
    /**
     * Jadwalkan wawancara untuk lamaran
     */
    public function scheduleInterview($lamaranId, $data)
    {
        return $this->withToken()->post('wawancara', [
            'id_lamaran_pekerjaan' => $lamaranId,
            'tanggal_wawancara' => $data['interview_date'],
            'lokasi' => $data['interview_location'],
            'catatan' => $data['interview_notes'] ?? null,
            'status' => 'pending',
        ]);
    }
    
    /**
     * Jadwalkan wawancara untuk lamaran dengan user ID
     */
    public function scheduleInterviewForUser($userId, $data)
    {
        return $this->withToken()->post('wawancara', [
            'id_user' => $userId,
            'id_lamaran_pekerjaan' => $data['id_lamaran_pekerjaan'],
            'tanggal_wawancara' => $data['interview_date'],
            'lokasi' => $data['interview_location'],
            'catatan' => $data['interview_notes'] ?? null,
            'status' => 'pending',
        ]);
    }
    
    /**
     * Ambil wawancara berdasarkan lowongan pekerjaan
     */
    public function getByLowongan($lowonganId)
    {
        return $this->withToken()->get("public/wawancara", ['id_lowongan_pekerjaan' => $lowonganId]);
    }

}
