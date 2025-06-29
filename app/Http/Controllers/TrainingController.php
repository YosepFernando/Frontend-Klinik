<?php

namespace App\Http\Controllers;

use App\Services\PelatihanService;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    protected $pelatihanService;
    
    /**
     * Constructor untuk menginisialisasi service
     */
    public function __construct(PelatihanService $pelatihanService)
    {
        $this->pelatihanService = $pelatihanService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Persiapkan parameter untuk API
        $params = [];
        
        // Search by title
        if ($request->filled('search')) {
            $params['search'] = $request->search;
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $params['status'] = $request->status;
        }
        
        // Filter by training type
        if ($request->filled('jenis_pelatihan')) {
            $params['jenis_pelatihan'] = $request->jenis_pelatihan;
        }
        
        // Tambahkan parameter untuk pagination
        $params['page'] = $request->input('page', 1);
        $params['per_page'] = 12;
        
        // Ambil data dari API
        $response = $this->pelatihanService->getAll($params);
        
        // Periksa apakah respons berhasil
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data pelatihan: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        // Siapkan data untuk view
        $trainings = $response['data'] ?? [];
        
        return view('trainings.index', compact('trainings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('trainings.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'judul' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'jenis_pelatihan' => 'required|in:video,document,offline',
            'durasi' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ];

        // Add conditional validation based on training type
        if ($request->jenis_pelatihan === 'offline') {
            $rules['konten'] = 'required|string|max:255'; // Lokasi untuk offline
            $rules['link_url'] = 'nullable';
        } else {
            $rules['link_url'] = 'required|url';
            $rules['konten'] = 'nullable';
        }

        $request->validate($rules);

        // Persiapkan data untuk dikirim ke API
        $data = [
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'jenis_pelatihan' => $request->jenis_pelatihan,
            'konten' => $request->konten,
            'link_url' => $request->link_url,
            'durasi' => $request->durasi,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ];

        // Kirim data ke API
        $response = $this->pelatihanService->store($data);
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('trainings.index')
                ->with('success', 'Pelatihan berhasil dibuat.');
        } else {
            return back()->withInput()
                ->with('error', 'Gagal membuat pelatihan: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Ambil detail pelatihan dari API
        $response = $this->pelatihanService->getById($id);
        
        // Periksa respons dari API
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data pelatihan: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        $training = $response['data'] ?? null;
        
        if (!$training) {
            return back()->with('error', 'Data pelatihan tidak ditemukan');
        }
        
        return view('trainings.show', compact('training'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Ambil detail pelatihan dari API
        $response = $this->pelatihanService->getById($id);
        
        // Periksa respons dari API
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data pelatihan: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        $training = $response['data'] ?? null;
        
        if (!$training) {
            return back()->with('error', 'Data pelatihan tidak ditemukan');
        }
        
        return view('trainings.edit', compact('training'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'judul' => 'required|string|max:100',
            'deskripsi' => 'required|string',
            'jenis_pelatihan' => 'required|in:video,document,offline',
            'durasi' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ];

        // Add conditional validation based on training type
        if ($request->jenis_pelatihan === 'offline') {
            $rules['konten'] = 'required|string|max:255'; // Lokasi untuk offline
            $rules['link_url'] = 'nullable';
        } else {
            $rules['link_url'] = 'required|url';
            $rules['konten'] = 'nullable';
        }

        $request->validate($rules);

        // Persiapkan data untuk dikirim ke API
        $data = [
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'jenis_pelatihan' => $request->jenis_pelatihan,
            'konten' => $request->konten,
            'link_url' => $request->link_url,
            'durasi' => $request->durasi,
            'is_active' => $request->has('is_active') ? 1 : 0,
        ];

        // Kirim data ke API
        $response = $this->pelatihanService->update($id, $data);
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('trainings.index')
                ->with('success', 'Pelatihan berhasil diperbarui.');
        } else {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui pelatihan: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Kirim permintaan hapus ke API
        $response = $this->pelatihanService->delete($id);
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('trainings.index')
                ->with('success', 'Pelatihan berhasil dihapus.');
        } else {
            return back()->with('error', 'Gagal menghapus pelatihan: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }
}
