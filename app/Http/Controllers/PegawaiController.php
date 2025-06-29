<?php

namespace App\Http\Controllers;

use App\Services\PegawaiService;
use App\Services\PosisiService;
use App\Services\UserService;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    protected $pegawaiService;
    protected $posisiService;
    protected $userService;
    
    /**
     * Constructor untuk menginisialisasi service
     */
    public function __construct(
        PegawaiService $pegawaiService,
        PosisiService $posisiService,
        UserService $userService
    ) {
        $this->pegawaiService = $pegawaiService;
        $this->posisiService = $posisiService;
        $this->userService = $userService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Persiapkan parameter untuk API
        $params = [];
        
        // Filter by position
        if ($request->filled('posisi_id')) {
            $params['posisi_id'] = $request->posisi_id;
        }
        
        // Filter by gender
        if ($request->filled('jenis_kelamin')) {
            $params['jenis_kelamin'] = $request->jenis_kelamin;
        }
        
        // Search by name or email
        if ($request->filled('search')) {
            $params['search'] = $request->search;
        }
        
        // Tambahkan parameter untuk pagination
        $params['page'] = $request->input('page', 1);
        $params['per_page'] = 15;
        
        // Ambil data dari API
        $response = $this->pegawaiService->getAll($params);
        
        // Periksa apakah respons berhasil
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data pegawai: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        // Siapkan data untuk view
        $pegawai = $response['data'] ?? [];
        
        // Ambil data posisi dari API
        $posisiResponse = $this->posisiService->getAll();
        $posisi = $posisiResponse['status'] === 'success' ? $posisiResponse['data'] : [];
        
        return view('pegawai.index', compact('pegawai', 'posisi'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil data posisi dari API
        $posisiResponse = $this->posisiService->getAll();
        $posisi = $posisiResponse['status'] === 'success' ? $posisiResponse['data'] : [];
        
        // Ambil data user yang belum memiliki pegawai dari API
        $usersResponse = $this->userService->getUsersWithoutPegawai();
        $users = $usersResponse['status'] === 'success' ? $usersResponse['data'] : [];
        
        return view('pegawai.create', compact('posisi', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'nullable|exists:users,id',
            'nama_lengkap' => 'required|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'NIK' => 'nullable|string|max:16',
            'id_posisi' => 'required',
            'agama' => 'nullable|string|max:20',
            'tanggal_masuk' => 'required|date',
        ]);

        // Kirim data ke API
        $response = $this->pegawaiService->store($request->all());
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('pegawai.index')
                ->with('success', 'Data pegawai berhasil ditambahkan.');
        } else {
            return back()->withInput()
                ->with('error', 'Gagal menambahkan data pegawai: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Ambil detail pegawai dari API
        $response = $this->pegawaiService->getById($id);
        
        // Periksa respons dari API
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data pegawai: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        $pegawai = $response['data'] ?? null;
        
        if (!$pegawai) {
            return back()->with('error', 'Data pegawai tidak ditemukan');
        }
        
        return view('pegawai.show', compact('pegawai'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Ambil detail pegawai dari API
        $response = $this->pegawaiService->getById($id);
        
        // Periksa respons dari API
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data pegawai: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        $pegawai = $response['data'] ?? null;
        
        if (!$pegawai) {
            return back()->with('error', 'Data pegawai tidak ditemukan');
        }
        
        // Ambil data posisi dari API
        $posisiResponse = $this->posisiService->getAll();
        $posisi = $posisiResponse['status'] === 'success' ? $posisiResponse['data'] : [];
        
        // Ambil data user yang belum memiliki pegawai dari API (termasuk user yang terkait dengan pegawai ini)
        $usersResponse = $this->userService->getUsersWithoutPegawai();
        $users = $usersResponse['status'] === 'success' ? $usersResponse['data'] : [];
        
        // Tambahkan user yang terkait dengan pegawai ini jika belum ada
        if (isset($pegawai['id_user']) && $pegawai['id_user']) {
            $userFound = false;
            foreach ($users as $user) {
                if ($user['id'] == $pegawai['id_user']) {
                    $userFound = true;
                    break;
                }
            }
            
            if (!$userFound) {
                $userResponse = $this->userService->getById($pegawai['id_user']);
                if (isset($userResponse['status']) && $userResponse['status'] === 'success' && isset($userResponse['data'])) {
                    $users[] = $userResponse['data'];
                }
            }
        }
        
        return view('pegawai.edit', compact('pegawai', 'posisi', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_user' => 'nullable|exists:users,id',
            'nama_lengkap' => 'required|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:laki-laki,perempuan,L,P',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'NIK' => 'nullable|string|max:16',
            'id_posisi' => 'required',
            'agama' => 'nullable|string|max:20',
            'tanggal_masuk' => 'required|date',
            'tanggal_keluar' => 'nullable|date|after_or_equal:tanggal_masuk',
        ]);

        // Kirim data ke API
        $response = $this->pegawaiService->update($id, $request->all());
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('pegawai.index')
                ->with('success', 'Data pegawai berhasil diperbarui.');
        } else {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui data pegawai: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Kirim permintaan hapus ke API
        $response = $this->pegawaiService->delete($id);
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('pegawai.index')
                ->with('success', 'Data pegawai berhasil dihapus.');
        } else {
            return back()->with('error', 'Gagal menghapus data pegawai: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }
}
