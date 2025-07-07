<?php

namespace App\Services;

class AppointmentService extends ApiService
{
    /**
     * Ambil daftar appointment
     */
    public function getAll($params = [])
    {
        return $this->withToken()->get('appointments', $params);
    }
    
    /**
     * Ambil appointment berdasarkan ID
     */
    public function getById($id)
    {
        return $this->withToken()->get("appointments/{$id}");
    }
    
    /**
     * Buat appointment baru
     */
    public function store($data)
    {
        return $this->withToken()->post('appointments', $data);
    }
    
    /**
     * Update appointment
     */
    public function update($id, $data)
    {
        return $this->withToken()->put("appointments/{$id}", $data);
    }
    
    /**
     * Hapus appointment
     */
    public function delete($id)
    {
        return $this->withToken()->delete("appointments/{$id}");
    }
    
    /**
     * Konfirmasi appointment
     */
    public function confirm($id)
    {
        return $this->withToken()->post("appointments/{$id}/confirm");
    }
    
    /**
     * Cancel appointment
     */
    public function cancel($id, $reason = null)
    {
        return $this->withToken()->post("appointments/{$id}/cancel", [
            'reason' => $reason
        ]);
    }
    
    /**
     * Reschedule appointment
     */
    public function reschedule($id, $data)
    {
        return $this->withToken()->post("appointments/{$id}/reschedule", $data);
    }
}
