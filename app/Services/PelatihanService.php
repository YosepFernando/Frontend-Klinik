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

    /**
     * Tambah peserta ke pelatihan
     *
     * @param int $trainingId
     * @param array $pegawaiIds
     * @return array
     */
    public function addParticipants($trainingId, $pegawaiIds)
    {
        return $this->withToken()->post("pelatihan/{$trainingId}/peserta", [
            'pegawai_ids' => $pegawaiIds
        ]);
    }

    /**
     * Hapus peserta dari pelatihan
     *
     * @param int $trainingId
     * @param int $participantId
     * @return array
     */
    public function removeParticipant($trainingId, $participantId)
    {
        return $this->withToken()->delete("pelatihan/{$trainingId}/peserta/{$participantId}");
    }

    /**
     * Cek apakah user adalah peserta pelatihan
     *
     * @param int $trainingId
     * @param int $userId
     * @return array
     */

    /**
     * Ambil bukti pelatihan user
     *
     * @param int $trainingId
     * @param int $userId
     * @return array
     */
    public function getUserProof($trainingId, $userId)
    {
        return $this->withToken()->get("pelatihan/{$trainingId}/bukti/user/{$userId}");
    }

    /**
     * Upload bukti pelatihan
     *
     * @param int $trainingId
     * @param int $userId
     * @param array $fileData
     * @return array
     */
    public function uploadProof($trainingId, $userId, $fileData)
    {
        // Method ini deprecated, gunakan uploadBukti() sebagai gantinya
        // Keeping for backward compatibility
        $data = [
            'id_pegawai' => $userId,
            'keterangan' => $fileData['keterangan'] ?? null
        ];

        $files = [
            'bukti_file' => $fileData['bukti_file'] ?? null
        ];

        return $this->withToken()->uploadFile("pelatihan/{$trainingId}/bukti", $data, $files);
    }

    /**
     * Ambil semua bukti pelatihan untuk training tertentu
     *
     * @param int $trainingId
     * @return array
     */
    public function getTrainingProofs($trainingId)
    {
        return $this->withToken()->get("pelatihan/{$trainingId}/bukti");
    }

    /**
     * Verifikasi bukti pelatihan
     *
     * @param int $proofId
     * @param array $data
     * @return array
     */
    public function verifyProof($proofId, $data)
    {
        return $this->withToken()->put("bukti-pelatihan/{$proofId}/verify", $data);
    }

    /**
     * Ambil daftar peserta pelatihan
     *
     * @param int $trainingId
     * @return array
     */
    public function getParticipants($trainingId)
    {
        return $this->withToken()->get("pelatihan/{$trainingId}/peserta");
    }

    /**
     * Update status kehadiran peserta
     *
     * @param int $trainingId
     * @param int $participantId
     * @param array $data
     * @return array
     */
    public function updateParticipantStatus($trainingId, $participantId, $data)
    {
        return $this->withToken()->put("pelatihan/{$trainingId}/peserta/{$participantId}", $data);
    }

    public function getBuktiByPelatihan($pelatihanId)
    {
        return $this->withToken()->get("pelatihan/{$pelatihanId}/bukti");
    }

    /**
     * Verify bukti
     */
    public function verifyBukti($buktiId, $status, $catatan = null)
    {
        return $this->withToken()->put("pelatihan/bukti/{$buktiId}/verify", [
            'status_verifikasi' => $status,
            'catatan_verifikasi' => $catatan
        ]);
    }

    /**
     * Check if user is participant
     */
    public function checkParticipant($pelatihanId, $pegawaiId)
    {
        return $this->withToken()->get("pelatihan/{$pelatihanId}/check-participant/{$pegawaiId}");
    }

    /**
     * Get pelatihan by pegawai
     */
    public function getByPegawai($pegawaiId)
    {
        return $this->withToken()->get("pelatihan/pegawai/{$pegawaiId}");
    }

    /**
     * Upload bukti pelatihan
     */
    public function uploadBukti($pelatihanId, $pegawaiId, $file, $keterangan = null)
    {
        $data = [
            'id_pegawai' => $pegawaiId,
            'keterangan' => $keterangan
        ];

        $files = [
            'bukti_file' => $file
        ];

        return $this->withToken()->uploadFile("pelatihan/{$pelatihanId}/bukti", $data, $files);
    }

    public function uploadBuktiPath($pelatihanId, $pegawaiId, $filePath, $keterangan = null)
    {
        $data = [
            'id_pegawai' => $pegawaiId, 
            'file_bukti' => $filePath, // Kirim path saja
            'keterangan' => $keterangan
        ];

        return $this->withToken()->post("pelatihan/{$pelatihanId}/bukti-path", $data);
    }
}
