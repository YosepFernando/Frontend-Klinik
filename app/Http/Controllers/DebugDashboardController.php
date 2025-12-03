<?php

namespace App\Http\Controllers;

use App\Services\PegawaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugDashboardController extends Controller
{
    protected $pegawaiService;
    
    public function __construct(PegawaiService $pegawaiService)
    {
        $this->pegawaiService = $pegawaiService;
    }
    
    public function testGenderData()
    {
        try {
            // Ambil data dari service seperti di DashboardController
            $respAll = $this->pegawaiService->getAll(['limit' => 10000, 'page' => 1]);
            $items = data_get($respAll, 'data.data', data_get($respAll, 'data', []));
            
            // Initialize counters
            $pegawaiCount = 0;
            $genderStats = ['male' => 0, 'female' => 0, 'other' => 0];
            $positionStats = [];
            $debugInfo = [];

            // Process semua data pegawai sekaligus (kecuali admin)
            if (is_array($items)) {
                foreach ($items as $item) {
                    // Ambil posisi dari relasi atau field langsung
                    $posisi = null;
                    
                    if (isset($item['posisi']['nama_posisi'])) {
                        $posisi = strtolower(trim($item['posisi']['nama_posisi']));
                    } elseif (isset($item['posisi_nama'])) {
                        $posisi = strtolower(trim($item['posisi_nama']));
                    } elseif (isset($item['nama_posisi'])) {
                        $posisi = strtolower(trim($item['nama_posisi']));
                    } elseif (isset($item['position'])) {
                        $posisi = strtolower(trim($item['position']));
                    }
                    
                    // Skip admin
                    if (!$posisi || in_array($posisi, ['admin', 'administrator'])) {
                        continue;
                    }
                    
                    // Count total pegawai (non-admin)
                    $pegawaiCount++;
                    
                    // Count gender (non-admin) - mengambil dari biodata jika ada
                    $jk = '';
                    
                    // Cek data gender dari berbagai sumber dengan prioritas
                    if (isset($item['jenis_kelamin'])) {
                        $jk = strtolower(trim((string) $item['jenis_kelamin']));
                    } elseif (isset($item['user']['biodata']['jenis_kelamin'])) {
                        $jk = strtolower(trim((string) $item['user']['biodata']['jenis_kelamin']));
                    } elseif (isset($item['biodata']['jenis_kelamin'])) {
                        $jk = strtolower(trim((string) $item['biodata']['jenis_kelamin']));
                    } elseif (isset($item['jk'])) {
                        $jk = strtolower(trim((string) $item['jk']));
                    } elseif (isset($item['gender'])) {
                        $jk = strtolower(trim((string) $item['gender']));
                    }
                    
                    // Debug info per item
                    $debugInfo[] = [
                        'nama' => $item['nama_lengkap'] ?? $item['nama_user'] ?? 'Unknown',
                        'posisi' => $posisi,
                        'jenis_kelamin_raw' => $jk,
                        'available_fields' => array_keys($item),
                        'user_fields' => isset($item['user']) ? array_keys($item['user']) : null,
                        'biodata_fields' => isset($item['user']['biodata']) ? array_keys($item['user']['biodata']) : null,
                    ];
                    
                    if (in_array($jk, ['l','laki','laki-laki','m','male'])) {
                        $genderStats['male']++;
                    } elseif (in_array($jk, ['p','perempuan','f','female'])) {
                        $genderStats['female']++;
                    } else {
                        $genderStats['other']++;
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'raw_response' => $respAll,
                    'items_count' => count($items),
                    'pegawaiCount' => $pegawaiCount,
                    'genderStats' => $genderStats,
                    'debug_info' => $debugInfo,
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
