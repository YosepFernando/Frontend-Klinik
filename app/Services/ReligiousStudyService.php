<?php

namespace App\Services;

use App\Services\ApiService;
use Illuminate\Support\Facades\Log;

class ReligiousStudyService
{
    protected $apiService;
    
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }
    
    /**
     * Ambil semua data kajian keagamaan
     */
    public function getAll($params = [])
    {
        try {
            $queryString = http_build_query($params);
            $endpoint = 'religious-studies' . ($queryString ? '?' . $queryString : '');
            return $this->apiService->get($endpoint);
        } catch (\Exception $e) {
            Log::error('ReligiousStudyService::getAll - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil data kajian keagamaan: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Ambil data kajian keagamaan berdasarkan ID
     */
    public function getById($id)
    {
        try {
            return $this->apiService->get("religious-studies/{$id}");
        } catch (\Exception $e) {
            Log::error('ReligiousStudyService::getById - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil data kajian keagamaan: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Simpan data kajian keagamaan baru
     */
    public function store($data)
    {
        try {
            return $this->apiService->post('religious-studies', $data);
        } catch (\Exception $e) {
            Log::error('ReligiousStudyService::store - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal menyimpan kajian keagamaan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update data kajian keagamaan
     */
    public function update($id, $data)
    {
        try {
            return $this->apiService->put("religious-studies/{$id}", $data);
        } catch (\Exception $e) {
            Log::error('ReligiousStudyService::update - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengupdate kajian keagamaan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Hapus data kajian keagamaan
     */
    public function delete($id)
    {
        try {
            return $this->apiService->delete("religious-studies/{$id}");
        } catch (\Exception $e) {
            Log::error('ReligiousStudyService::delete - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal menghapus kajian keagamaan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Daftar peserta kajian keagamaan
     */
    public function joinStudy($studyId)
    {
        try {
            return $this->apiService->post("religious-studies/{$studyId}/join");
        } catch (\Exception $e) {
            Log::error('ReligiousStudyService::joinStudy - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mendaftar kajian keagamaan: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Batal daftar kajian keagamaan
     */
    public function leaveStudy($studyId)
    {
        try {
            return $this->apiService->post("religious-studies/{$studyId}/leave");
        } catch (\Exception $e) {
            Log::error('ReligiousStudyService::leaveStudy - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal membatalkan pendaftaran: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Ambil daftar peserta kajian keagamaan
     */
    public function getParticipants($studyId)
    {
        try {
            return $this->apiService->get("religious-studies/{$studyId}/participants");
        } catch (\Exception $e) {
            Log::error('ReligiousStudyService::getParticipants - ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Gagal mengambil daftar peserta: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
}
