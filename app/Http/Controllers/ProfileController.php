<?php

namespace App\Http\Controllers;

use App\Services\ApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    protected $apiService;
    
    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Display the user's profile form.
     */
    public function show()
    {
        // Ambil data profile dari API
        $response = $this->apiService->withToken()->get('profile');
        
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data profile: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        $profile = $response['data'] ?? [];
        
        return view('profile.show', compact('profile'));
    }

    /**
     * Show the form for editing the profile.
     */
    public function edit()
    {
        // Ambil data profile dari API
        $response = $this->apiService->withToken()->get('profile');
        
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data profile: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        $profile = $response['data'] ?? [];
        
        // Debug: Log struktur data untuk troubleshooting
        Log::info('Profile data structure for edit', [
            'profile_keys' => array_keys($profile),
            'biodata_keys' => isset($profile['biodata']) ? array_keys($profile['biodata']) : 'no biodata',
            'tanggal_lahir_from_profile' => $profile['tanggal_lahir'] ?? 'not set',
            'tanggal_lahir_from_biodata' => isset($profile['biodata']['tanggal_lahir']) ? $profile['biodata']['tanggal_lahir'] : 'not set'
        ]);
        
        return view('profile.edit', compact('profile'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $request->validate([
            'nama_user' => 'required|string|max:255',
            'email' => 'required|email|max:100',
            'no_telp' => 'nullable|string|max:20',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'NIK' => 'nullable|string|size:16|regex:/^[0-9]+$/',
            'jenis_kelamin' => 'nullable|in:L,P',
            'agama' => 'nullable|string|max:20',
            'current_password' => 'nullable|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ], [
            'NIK.size' => 'NIK harus tepat 16 digit.',
            'NIK.regex' => 'NIK hanya boleh berisi angka.',
        ]);

        // Kirim data ke API
        $response = $this->apiService->withToken()->put('profile', $request->all());
        
        // Log response untuk debugging
        Log::info('Profile update response:', $response);
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('profile.show')
                ->with('success', 'Profile berhasil diperbarui.');
        } else {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui profile: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }

    /**
     * Upload profile photo.
     */
    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'foto_profil' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Prepare file for API upload
            $file = $request->file('foto_profil');
            $response = $this->apiService->withToken()->uploadFile('profile/upload-photo', [], [
                'foto_profil' => $file
            ]);
            
            // Log response untuk debugging
            Log::info('Photo upload response:', $response);
            
            // Periksa respons dari API
            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->route('profile.show')
                    ->with('success', 'Foto profile berhasil diupload.');
            } else {
                return back()
                    ->with('error', 'Gagal mengupload foto: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
            }
            
        } catch (\Exception $e) {
            Log::error('Photo upload error:', ['error' => $e->getMessage()]);
            return back()->with('error', 'Terjadi kesalahan saat mengupload foto.');
        }
    }
}
