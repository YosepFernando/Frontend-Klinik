<?php

namespace App\Services;

class HasilSeleksiService extends ApiService
{
    /**
     * Ambil daftar hasil seleksi
     */
    public function getAll($params = [])
    {
        return $this->withToken()->get('public/hasil-seleksi', $params);
    }
    
    /**
     * Ambil hasil seleksi berdasarkan ID
     */
    public function getById($id)
    {
        return $this->withToken()->get("public/hasil-seleksi/{$id}");
    }
    
    /**
     * Buat hasil seleksi baru
     */
    public function store($data)
    {
        return $this->withToken()->post('public/hasil-seleksi', $data);
    }
    
    /**
     * Update hasil seleksi
     */
    public function update($id, $data)
    {
        return $this->withToken()->put("public/hasil-seleksi/{$id}", $data);
    }
    
    /**
     * Hapus hasil seleksi
     */
    public function delete($id)
    {
        return $this->withToken()->delete("public/hasil-seleksi/{$id}");
    }
    
    /**
     * Ambil hasil seleksi berdasarkan user
     */
    public function getByUser($userId)
    {
        return $this->withToken()->get("public/hasil-seleksi/user/{$userId}");
    }
    
    /**
     * Ambil hasil seleksi berdasarkan user dan lamaran
     */
    public function getByUserAndLamaran($userId, $lamaranId)
    {
        return $this->withToken()->get("public/hasil-seleksi", [
            'id_user' => $userId,
            'id_lamaran_pekerjaan' => $lamaranId
        ]);
    }

    /**
     * Ambil hasil seleksi berdasarkan user dan lowongan (DEPRECATED - gunakan getByUserAndLamaran)
     * Metode ini tidak akurat karena API tidak mendukung filter berdasarkan id_lowongan_pekerjaan
     */
    public function getByUserAndLowongan($userId, $lowonganId)
    {
        // Ambil semua hasil seleksi user, lalu filter di aplikasi
        $response = $this->withToken()->get("public/hasil-seleksi", [
            'id_user' => $userId
        ]);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            $data = $response['data']['data'] ?? [];
            
            // Filter berdasarkan lowongan di sisi aplikasi
            $filtered = array_filter($data, function($item) use ($lowonganId) {
                $lamaran = $item['lamaran_pekerjaan'] ?? null;
                return $lamaran && isset($lamaran['id_lowongan_pekerjaan']) && 
                       $lamaran['id_lowongan_pekerjaan'] == $lowonganId;
            });
            
            $response['data']['data'] = array_values($filtered);
        }
        
        return $response;
    }

    /**
     * Ambil hasil seleksi berdasarkan lamaran
     */
    public function getByLamaran($lamaranId)
    {
        return $this->withToken()->get("public/hasil-seleksi", [
            'id_lamaran_pekerjaan' => $lamaranId
        ]);
    }
    
    /**
     * Finalisasi hasil seleksi
     */
    public function finalize($id)
    {
        return $this->withToken()->post("public/hasil-seleksi/{$id}/finalize");
    }
    
    /**
     * Buat keputusan final untuk lamaran
     */
    public function makeFinalDecision($userId, $lamaranId, $data)
    {
        return $this->withToken()->post('public/hasil-seleksi', [
            'id_user' => $userId,
            'id_lamaran_pekerjaan' => $lamaranId,
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
        return $this->withToken()->put("public/hasil-seleksi/{$hasilSeleksiId}", [
            'status' => $data['final_status'],
            'tanggal_mulai_kerja' => $data['start_date'] ?? null,
            'catatan' => $data['final_notes'] ?? null,
        ]);
    }

    /**
     * Auto create hasil seleksi dari wawancara yang lulus
     */
    public function createFromPassedInterview($userId, $lamaranId, $interviewData = [])
    {
        return $this->withToken()->post('public/hasil-seleksi', [
            'id_user' => $userId,
            'id_lamaran_pekerjaan' => $lamaranId,
            'status' => 'pending', // Default status menunggu keputusan final
            'catatan' => 'Otomatis dibuat dari hasil interview yang lulus. Nilai interview: ' . ($interviewData['nilai'] ?? 'N/A'),
        ]);
    }

    /**
     * Ambil hasil seleksi berdasarkan lowongan pekerjaan
     */
    public function getByLowongan($lowonganId)
    {
        return $this->withToken()->get("public/hasil-seleksi", ['id_lowongan_pekerjaan' => $lowonganId]);
    }

}
