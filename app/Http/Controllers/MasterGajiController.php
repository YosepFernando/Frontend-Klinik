<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GajiService;
use App\Services\PegawaiService;
use Illuminate\Support\Facades\Log;

class MasterGajiController extends Controller
{
    protected $gajiService;
    protected $pegawaiService;

    public function __construct(GajiService $gajiService, PegawaiService $pegawaiService)
    {
        $this->gajiService = $gajiService;
        $this->pegawaiService = $pegawaiService;
    }

    /**
     * Store a newly created master gaji in storage.
     */
    public function store(Request $request)
    {
        try {
            // Check authentication
            if (!session('authenticated') || !session('api_token')) {
                Log::warning('MasterGajiController::store - No valid session found');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sesi Anda telah berakhir. Silakan login kembali.'
                ], 401);
            }

            // Check user role permission
            $userRole = session('user_role');
            if (!in_array($userRole, ['admin', 'hrd'])) {
                Log::warning('MasterGajiController::store - Insufficient permissions', [
                    'user_role' => $userRole
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki izin untuk menambahkan gaji pegawai.'
                ], 403);
            }

            // Validate input
            $validated = $request->validate([
                'id_pegawai' => 'required|integer',
                'periode_bulan' => 'required|integer|min:1|max:12',
                'periode_tahun' => 'required|integer|min:2020|max:' . (date('Y') + 1),
                'gaji_pokok' => 'required|numeric|min:0',
                'gaji_bonus' => 'nullable|numeric|min:0',
                'gaji_kehadiran' => 'nullable|numeric|min:0',
                'keterangan' => 'nullable|string|max:1000',
                'status' => 'nullable|in:Belum Terbayar,Terbayar'
            ]);

            Log::info('MasterGajiController::store - Processing request', [
                'data' => $validated,
                'user_id' => session('user_id'),
                'user_role' => $userRole
            ]);

            // Set default values
            $validated['gaji_bonus'] = $validated['gaji_bonus'] ?? 0;
            $validated['gaji_kehadiran'] = $validated['gaji_kehadiran'] ?? 0;
            $validated['status'] = $validated['status'] ?? 'Belum Terbayar';
            
            // Calculate total gaji
            $validated['gaji_total'] = $validated['gaji_pokok'] + 
                                      $validated['gaji_bonus'] + 
                                      $validated['gaji_kehadiran'];

            // Call API to create master gaji
            $response = $this->gajiService->store($validated);

            Log::info('MasterGajiController::store - API Response', [
                'response' => $response
            ]);

            // Handle authentication error
            if (isset($response['message']) && 
                (str_contains(strtolower($response['message']), 'unauthorized') || 
                 str_contains(strtolower($response['message']), 'unauthenticated'))) {
                Log::warning('API authentication failed during store', [
                    'response' => $response
                ]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sesi login Anda telah berakhir. Silakan login kembali.'
                ], 401);
            }

            // Check if successful
            if (isset($response['status']) && $response['status'] === 'success') {
                Log::info('Master gaji created successfully');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Gaji pegawai berhasil ditambahkan.',
                    'data' => $response['data'] ?? null
                ]);
            } else {
                Log::warning('Failed to create master gaji', [
                    'response' => $response
                ]);
                $errorMessage = $response['message'] ?? 'Terjadi kesalahan saat menambahkan gaji pegawai.';
                return response()->json([
                    'status' => 'error',
                    'message' => $errorMessage
                ], 400);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('MasterGajiController::store - Validation error', [
                'errors' => $e->errors()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak valid.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('MasterGajiController::store - Exception: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Get all pegawai for dropdown
     */
    public function getPegawai()
    {
        try {
            // Check authentication
            if (!session('authenticated') || !session('api_token')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sesi Anda telah berakhir. Silakan login kembali.'
                ], 401);
            }

            // Get pegawai data
            $response = $this->pegawaiService->getAll();

            Log::info('MasterGajiController::getPegawai - API Response', [
                'response_keys' => array_keys($response ?? []),
                'has_data' => isset($response['data'])
            ]);

            if (isset($response['status']) && in_array($response['status'], ['success', 'sukses'])) {
                return response()->json($response);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal mengambil data pegawai.'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('MasterGajiController::getPegawai - Exception: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem.'
            ], 500);
        }
    }
}
