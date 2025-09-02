<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $userService, $authService;
    
    public function __construct(UserService $userService, AuthService $authService)
    {
        $this->userService = $userService;
        $this->authService = $authService;
    }

    
    /**
     * Tampilkan daftar pengguna
     */
    public function index(Request $request)
    {
        $params = [];
        
        // Search by name or email
        if ($request->filled('search')) {
            $params['search'] = $request->search;
        }
        
        // Filter by role
        if ($request->filled('role')) {
            $params['role'] = $request->role;
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $params['status'] = $request->status;
        }
        
        // Filter by gender
        if ($request->filled('gender')) {
            $params['gender'] = $request->gender;
        }
        
        // Add pagination parameters
        $params['page'] = $request->input('page', 1);
        $params['per_page'] = 15;
        
        // Ambil data pegawai dari API (yang mencakup user data)
        $response = $this->userService->getAll($params);
        
        // Log response untuk debugging
        \Log::info('UserController index response', [
            'response' => $response,
            'params' => $params
        ]);
        
        // Check if response is successful
        if (!isset($response['status']) || $response['status'] !== 'success') {
            $users = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                15,
                1,
                ['path' => request()->url(), 'pageName' => 'page']
            );
            
            return view('users.index', compact('users'))
                ->with('error', 'Gagal memuat data user: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        // Transform users data from new API response structure
        $usersData = [];
        $responseData = $response['data'] ?? [];
        
        if (isset($responseData['users']) && is_array($responseData['users'])) {
            foreach ($responseData['users'] as $user) {
                // Direct user data transformation
                $userData = [
                    'id' => $user['id_user'] ?? null,
                    'id_user' => $user['id_user'] ?? null,
                    'name' => $user['nama_user'] ?? 'Tidak ada nama',
                    'email' => $user['email'] ?? 'Tidak ada email',
                    'role' => $user['role'] ?? 'tidak diketahui',
                    'created_at' => $user['created_at'] ?? null,
                    'updated_at' => $user['updated_at'] ?? null,
                    'foto_profil' => $user['foto_profil'] ?? null,
                    'no_telp' => $user['no_telp'] ?? null,
                    'tanggal_lahir' => $user['tanggal_lahir'] ?? null,
                    'is_active' => true, // Assume active if not specified
                ];
                
                // Convert gender if needed (currently not provided in API response)
                $userData['gender'] = null; // Will be set to null since not provided in current API
                
                $usersData[] = (object) $userData;
            }
            
            // Create Laravel paginator using pagination data from API
            $paginationData = $responseData['pagination'] ?? [];
            $users = new \Illuminate\Pagination\LengthAwarePaginator(
                $usersData,
                $paginationData['total'] ?? count($usersData),
                $paginationData['per_page'] ?? 15,
                $paginationData['current_page'] ?? 1,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
        } else {
            $users = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                15,
                1,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        }
        
        return view('users.index', compact('users'));
    }

    /**
     * Tampilkan form untuk menambah pengguna baru
     */
    public function create()
    {
        $roles = [
            'admin' => 'Admin',
            'hrd' => 'HRD',
            'front_office' => 'Front Office',
            'kasir' => 'Kasir',
            'dokter' => 'Dokter',
            'beautician' => 'Beautician',
            'pelanggan' => 'Pelanggan'
        ];
        
        return view('users.create', compact('roles'));
    }

    /**
     * Simpan pengguna baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,hrd,front_office,kasir,dokter,beautician,pelanggan',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:male,female',
            'is_active' => 'boolean'
        ]);
        
        // Transform data untuk sesuai dengan API
        $apiData = [
            'nama_user' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'password_confirmation' => $validated['password_confirmation'] ?? $validated['password'],
            'role' => $validated['role'],
            'no_telp' => $validated['phone'] ?? null,
            'tanggal_lahir' => $validated['birth_date'] ?? null,
            'gender' => $validated['gender'],
            'address' => $validated['address'] ?? null,
            'is_active' => $request->has('is_active') ? 1 : 0
        ];
        
        // Kirim ke API
        $response = $this->authService->register($apiData);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('users.index')
                            ->with('success', 'Pengguna berhasil ditambahkan.');
        }
        
        return redirect()->route('users.create')
                        ->with('error', 'Gagal menambahkan pengguna: ' . ($response['message'] ?? 'Terjadi kesalahan.'))
                        ->withInput();
    }

    /**
     * Tampilkan detail pengguna
     */
    public function show($id)
    {
        try {
            $response = $this->userService->getById($id);
            
            \Log::info('UserController show response', [
                'id' => $id,
                'response' => $response
            ]);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                // Transform API response to object for view compatibility
                $userData = $response['data']['user'] ?? $response['data'];
                $user = (object) [
                    'id' => $userData['id_user'] ?? null,
                    'id_user' => $userData['id_user'] ?? null,
                    'name' => $userData['nama_user'] ?? 'Tidak ada nama',
                    'nama_user' => $userData['nama_user'] ?? 'Tidak ada nama',
                    'email' => $userData['email'] ?? 'Tidak ada email',
                    'role' => $userData['role'] ?? 'tidak diketahui',
                    'no_telp' => $userData['no_telp'] ?? null,
                    'phone' => $userData['no_telp'] ?? null,
                    'tanggal_lahir' => $userData['tanggal_lahir'] ?? null,
                    'birth_date' => $userData['tanggal_lahir'] ? \Carbon\Carbon::parse($userData['tanggal_lahir']) : null,
                    'foto_profil' => $userData['foto_profil'] ?? null,
                    'gender' => $userData['gender'] ?? null,
                    'address' => $userData['address'] ?? null,
                    'is_active' => $userData['is_active'] ?? true,
                    'created_at' => $userData['created_at'] ? \Carbon\Carbon::parse($userData['created_at']) : null,
                    'updated_at' => $userData['updated_at'] ? \Carbon\Carbon::parse($userData['updated_at']) : null,
                    'email_verified_at' => isset($userData['email_verified_at']) && $userData['email_verified_at'] ? \Carbon\Carbon::parse($userData['email_verified_at']) : null,
                ];
                
                return view('users.show', compact('user'));
            }
        } catch (\Exception $e) {
            \Log::error('Error in UserController show:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
        }
        
        return redirect()->route('users.index')
                        ->with('error', 'Pengguna tidak ditemukan.');
    }

    /**
     * Tampilkan form edit pengguna
     */
    public function edit($id)
    {
        try {
            $response = $this->userService->getById($id);
            
            \Log::info('UserController edit response', [
                'id' => $id,
                'response' => $response
            ]);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                // Transform API response to object for view compatibility
                $userData = $response['data']['user'] ?? $response['data'];
                $user = (object) [
                    'id' => $userData['id_user'] ?? null,
                    'id_user' => $userData['id_user'] ?? null,
                    'name' => $userData['nama_user'] ?? 'Tidak ada nama',
                    'nama_user' => $userData['nama_user'] ?? 'Tidak ada nama',
                    'email' => $userData['email'] ?? 'Tidak ada email',
                    'role' => $userData['role'] ?? 'tidak diketahui',
                    'no_telp' => $userData['no_telp'] ?? null,
                    'phone' => $userData['no_telp'] ?? null,
                    'tanggal_lahir' => $userData['tanggal_lahir'] ?? null,
                    'birth_date' => $userData['tanggal_lahir'] ? \Carbon\Carbon::parse($userData['tanggal_lahir']) : null,
                    'foto_profil' => $userData['foto_profil'] ?? null,
                    'gender' => $userData['gender'] ?? null,
                    'address' => $userData['address'] ?? null,
                    'is_active' => $userData['is_active'] ?? true,
                    'created_at' => $userData['created_at'] ? \Carbon\Carbon::parse($userData['created_at']) : null,
                    'updated_at' => $userData['updated_at'] ? \Carbon\Carbon::parse($userData['updated_at']) : null,
                    'email_verified_at' => isset($userData['email_verified_at']) && $userData['email_verified_at'] ? \Carbon\Carbon::parse($userData['email_verified_at']) : null,
                ];
                
                $roles = [
                    'admin' => 'Admin',
                    'hrd' => 'HRD',
                    'front_office' => 'Front Office',
                    'kasir' => 'Kasir',
                    'dokter' => 'Dokter',
                    'beautician' => 'Beautician',
                    'pelanggan' => 'Pelanggan'
                ];
                
                return view('users.edit', compact('user', 'roles'));
            }
        } catch (\Exception $e) {
            \Log::error('Error in UserController edit:', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
        }
        
        return redirect()->route('users.index')
                        ->with('error', 'Pengguna tidak ditemukan.');
    }

    /**
     * Update pengguna
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'nama_user' => 'required|string|max:255',
            'no_telp' => 'required|string|max:13',
            'tanggal_lahir' => 'required|date'
        ];
        
        $validated = $request->validate($rules);
        
        // Format nomor telepon (hapus +62 jika ada)
        $noTelp = $validated['no_telp'];
        $noTelp = preg_replace('/^\+62|^62|^0/', '', $noTelp);
        
        // Build API request data
        $apiData = [
            'nama_user' => $validated['nama_user'],
            'no_telp' => $noTelp,
            'tanggal_lahir' => $validated['tanggal_lahir']
        ];
        
        // Log data yang akan dikirim ke API
        \Log::info('Updating user profile', [
            'user_id' => $id,
            'api_data' => $apiData
        ]);
        
        // Kirim ke API
        $response = $this->userService->update($id, $apiData);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('users.index')
                            ->with('success', 'Profil pengguna berhasil diperbarui.');
        }
        
        // Handle specific error messages from the API
        $errorMessage = '';
        if (isset($response['message'])) {
            $errorMessage = $response['message'];
        } elseif (isset($response['errors']) && is_array($response['errors'])) {
            $errorMessage = collect($response['errors'])->first()[0] ?? 'Terjadi kesalahan validasi';
        } else {
            $errorMessage = 'Terjadi kesalahan saat memperbarui profil.';
        }
        
        return redirect()->route('users.edit', $id)
                        ->with('error', 'Gagal memperbarui profil: ' . $errorMessage)
                        ->withInput();
    }

    /**
     * Hapus pengguna
     */
    public function destroy($id)
    {
        // Cek apakah user sudah login
        if (!is_authenticated()) {
            return redirect()->route('login')
                           ->with('error', 'Anda harus login terlebih dahulu.');
        }
        
        // Cegah penghapusan pengguna yang sedang login
        $currentUser = auth_user();
        $currentUserId = null;
        
        if (is_object($currentUser)) {
            $currentUserId = $currentUser->id_user ?? $currentUser->id ?? null;
        } elseif (is_array($currentUser)) {
            $currentUserId = $currentUser['id_user'] ?? $currentUser['id'] ?? null;
        }
        
        if ($id == $currentUserId) {
            return redirect()->route('users.index')
                           ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }
        
        $response = $this->userService->delete($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('users.index')
                            ->with('success', 'Pengguna berhasil dihapus.');
        }
        
        return redirect()->route('users.index')
                        ->with('error', 'Gagal menghapus pengguna: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }
    
    /**
     * Toggle status aktif pengguna
     */
    public function toggleStatus($id)
    {
        $response = $this->userService->toggleStatus($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->back()
                            ->with('success', 'Status pengguna berhasil diubah.');
        }
        
        return redirect()->back()
                        ->with('error', 'Gagal mengubah status pengguna: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }
}
