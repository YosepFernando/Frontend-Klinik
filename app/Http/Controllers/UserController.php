<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
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
        
        // Ambil data pengguna dari API
        $response = $this->userService->getAll($params);
        $users = collect($response['data'] ?? []);
        
        // Ambil daftar role untuk filter
        $rolesResponse = $this->userService->getRoles();
        $roles = collect($rolesResponse['data'] ?? []);
        
        return view('users.index', compact('users', 'roles'));
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
        
        $validated['is_active'] = $request->has('is_active');
        
        // Kirim ke API
        $response = $this->userService->store($validated);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('users.index')
                            ->with('success', 'Pengguna berhasil ditambahkan.');
        }
        
        return redirect()->route('users.create')
                        ->with('error', 'Gagal menambahkan pengguna: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Tampilkan detail pengguna
     */
    public function show($id)
    {
        $response = $this->userService->getById($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            $user = $response['data'];
            return view('users.show', compact('user'));
        }
        
        return redirect()->route('users.index')
                        ->with('error', 'Pengguna tidak ditemukan.');
    }

    /**
     * Tampilkan form edit pengguna
     */
    public function edit($id)
    {
        $response = $this->userService->getById($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            $user = $response['data'];
            
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
        
        return redirect()->route('users.index')
                        ->with('error', 'Pengguna tidak ditemukan.');
    }

    /**
     * Update pengguna
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,hrd,front_office,kasir,dokter,beautician,pelanggan',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'gender' => 'required|in:male,female',
            'is_active' => 'boolean'
        ]);
        
        // Hapus password jika tidak diisi
        if (!$request->filled('password')) {
            unset($validated['password']);
        }
        
        $validated['is_active'] = $request->has('is_active');
        
        // Kirim ke API
        $response = $this->userService->update($id, $validated);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('users.index')
                            ->with('success', 'Pengguna berhasil diperbarui.');
        }
        
        return redirect()->route('users.edit', $id)
                        ->with('error', 'Gagal memperbarui pengguna: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Hapus pengguna
     */
    public function destroy($id)
    {
        // Cegah penghapusan pengguna yang sedang login
        if ($id == auth()->id()) {
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
