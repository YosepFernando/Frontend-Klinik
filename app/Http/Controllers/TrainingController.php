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
        
        // Search by title (API menggunakan 'judul' untuk pencarian)
        if ($request->filled('search')) {
            $params['judul'] = $request->search;
        }
        
        // Filter by active status
        if ($request->filled('is_active')) {
            $params['is_active'] = $request->is_active;
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
            return view('trainings.index')->with([
                'trainings' => collect([]),
                'error' => 'Gagal memuat data pelatihan: ' . ($response['message'] ?? 'Terjadi kesalahan pada server')
            ]);
        }
        
        // Siapkan data untuk view - ambil data dari response API
        $apiData = $response['data'] ?? [];
        
        // Transform data untuk view
        $trainingsData = [];
        if (isset($apiData['data']) && is_array($apiData['data'])) {
            foreach ($apiData['data'] as $training) {
                // Transform data sesuai dengan struktur view
                $transformedTraining = [
                    'id' => $training['id_pelatihan'] ?? null,
                    'id_pelatihan' => $training['id_pelatihan'] ?? null,
                    'judul' => $training['judul'] ?? 'Judul tidak tersedia',
                    'deskripsi' => $training['deskripsi'] ?? 'Tidak ada deskripsi',
                    'jenis_pelatihan' => $training['jenis_pelatihan'] ?? 'offline',
                    'jadwal_pelatihan' => $training['jadwal_pelatihan'] ?? null,
                    'link_url' => $training['link_url'] ?? null,
                    'durasi' => $training['durasi'] ?? 0,
                    'is_active' => $training['is_active'] ?? false,
                    'created_at' => $training['created_at'] ?? null,
                    'updated_at' => $training['updated_at'] ?? null,
                    
                    // Computed properties for view
                    'status' => $training['is_active'] ? 'active' : 'inactive',
                    'status_display' => $training['is_active'] ? 'Aktif' : 'Tidak Aktif',
                    'status_badge_class' => $training['is_active'] ? 'badge bg-success' : 'badge bg-secondary',
                    'jenis_display' => $this->getJenisDisplay($training['jenis_pelatihan'] ?? 'offline'),
                    'jenis_badge_class' => $this->getJenisBadgeClass($training['jenis_pelatihan'] ?? 'offline'),
                    'durasi_display' => $this->getDurasiDisplay($training['durasi'] ?? 0),
                    'location_info' => $this->getLocationInfo($training)
                ];
                
                $trainingsData[] = $transformedTraining;
            }
        }
        
        // Create pagination info
        $paginationInfo = [
            'current_page' => $apiData['current_page'] ?? 1,
            'last_page' => $apiData['last_page'] ?? 1,
            'per_page' => $apiData['per_page'] ?? 15,
            'total' => $apiData['total'] ?? 0,
            'has_pages' => ($apiData['last_page'] ?? 1) > 1,
            'links' => $apiData['links'] ?? []
        ];
        
        return view('trainings.index', compact('trainingsData', 'paginationInfo'));
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
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tanggal' => 'required|date',
            'jenis_pelatihan' => 'required|string|in:Internal,Eksternal,video,document,zoom,video/meet,video/online meet,offline',
            'durasi' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ];
        
        // Add conditional validation based on training type
        $onlineTypes = ['video', 'document', 'zoom', 'offline'];
        
        if (in_array($request->jenis_pelatihan, $onlineTypes)) {
            // Online types require link_url
            $rules['link_url'] = 'required|url|max:255';
        } else {
            // Offline types don't need link_url
            $rules['link_url'] = 'nullable|url|max:255';
        }

        $request->validate($rules);

        // Persiapkan data untuk dikirim ke API sesuai dengan struktur yang diterima
        $data = [
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'jenis_pelatihan' => $request->jenis_pelatihan,
            'jadwal_pelatihan' => $request->tanggal,
            'link_url' => $request->link_url,
            'durasi' => $request->durasi,
            'is_active' => $request->has('is_active') ? true : false,
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
        
        $trainingData = $response['data'] ?? null;
        
        if (!$trainingData) {
            return back()->with('error', 'Data pelatihan tidak ditemukan');
        }
        
        // Transform data untuk view - sama seperti di method index
        $training = (object) [
            'id' => $trainingData['id_pelatihan'] ?? null,
            'id_pelatihan' => $trainingData['id_pelatihan'] ?? null,
            'judul' => $trainingData['judul'] ?? 'Judul tidak tersedia',
            'deskripsi' => $trainingData['deskripsi'] ?? 'Tidak ada deskripsi',
            'jenis_pelatihan' => $trainingData['jenis_pelatihan'] ?? 'offline',
            'jadwal_pelatihan' => $trainingData['jadwal_pelatihan'] ?? null,
            'link_url' => $trainingData['link_url'] ?? null,
            'access_link' => $trainingData['link_url'] ?? null,
            'durasi' => $trainingData['durasi'] ?? 0,
            'is_active' => $trainingData['is_active'] ?? false,
            'created_at' => $trainingData['created_at'] ? \Carbon\Carbon::parse($trainingData['created_at']) : null,
            'updated_at' => $trainingData['updated_at'] ? \Carbon\Carbon::parse($trainingData['updated_at']) : null,
            
            // Computed properties for view
            'status' => $trainingData['is_active'] ? 'active' : 'inactive',
            'status_display' => $trainingData['is_active'] ? 'Aktif' : 'Tidak Aktif',
            'status_badge_class' => $trainingData['is_active'] ? 'badge bg-success' : 'badge bg-secondary',
            'jenis_display' => $this->getJenisDisplay($trainingData['jenis_pelatihan'] ?? 'offline'),
            'jenis_badge_class' => $this->getJenisBadgeClass($trainingData['jenis_pelatihan'] ?? 'offline'),
            'durasi_display' => $this->getDurasiDisplay($trainingData['durasi'] ?? 0),
            'location_info' => $this->getLocationInfo($trainingData)
        ];
        
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
        
        $trainingData = $response['data'] ?? null;
        
        if (!$trainingData) {
            return back()->with('error', 'Data pelatihan tidak ditemukan');
        }
        
        // Transform data untuk view - sama seperti di method show
        $training = (object) [
            'id' => $trainingData['id_pelatihan'] ?? null,
            'id_pelatihan' => $trainingData['id_pelatihan'] ?? null,
            'judul' => $trainingData['judul'] ?? 'Judul tidak tersedia',
            'deskripsi' => $trainingData['deskripsi'] ?? 'Tidak ada deskripsi',
            'jenis_pelatihan' => $trainingData['jenis_pelatihan'] ?? 'offline',
            'jadwal_pelatihan' => $trainingData['jadwal_pelatihan'] ?? null,
            'link_url' => $trainingData['link_url'] ?? null,
            'access_link' => $trainingData['link_url'] ?? null,
            'durasi' => $trainingData['durasi'] ?? 0,
            'is_active' => $trainingData['is_active'] ?? false,
            'created_at' => $trainingData['created_at'] ? \Carbon\Carbon::parse($trainingData['created_at']) : null,
            'updated_at' => $trainingData['updated_at'] ? \Carbon\Carbon::parse($trainingData['updated_at']) : null,
            
            // Computed properties for view
            'status' => $trainingData['is_active'] ? 'active' : 'inactive',
            'status_display' => $trainingData['is_active'] ? 'Aktif' : 'Tidak Aktif',
            'status_badge_class' => $trainingData['is_active'] ? 'badge bg-success' : 'badge bg-secondary',
            'jenis_display' => $this->getJenisDisplay($trainingData['jenis_pelatihan'] ?? 'offline'),
            'jenis_badge_class' => $this->getJenisBadgeClass($trainingData['jenis_pelatihan'] ?? 'offline'),
            'durasi_display' => $this->getDurasiDisplay($trainingData['durasi'] ?? 0),
            'location_info' => $this->getLocationInfo($trainingData)
        ];
        
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
            'jenis_pelatihan' => 'required|in:Internal,Eksternal,video,document,zoom,video/meet,video/online meet,offline',
            'durasi' => 'nullable|integer|min:1',
            'jadwal_pelatihan' => 'nullable|date',
            'is_active' => 'boolean',
        ];

        // Add conditional validation based on training type
        $onlineTypes = ['video', 'document', 'zoom', 'video/meet', 'video/online meet'];
        
        if (in_array($request->jenis_pelatihan, $onlineTypes)) {
            // Online types require link_url
            $rules['link_url'] = 'required|url';
        } else {
            // Offline types don't need link_url
            $rules['link_url'] = 'nullable';
        }

        $request->validate($rules);

        // Persiapkan data untuk dikirim ke API
        $data = [
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'jenis_pelatihan' => $request->jenis_pelatihan,
            'link_url' => $request->link_url,
            'durasi' => $request->durasi,
            'jadwal_pelatihan' => $request->jadwal_pelatihan,
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
    
    /**
     * Helper methods untuk transformasi data
     */
    private function getJenisDisplay($jenis)
    {
        switch ($jenis) {
            case 'video':
                return 'Video Online';
            case 'document':
                return 'Dokumen Online';
            case 'zoom':
                return 'Zoom Meeting';
            case 'video/meet':
                return 'Video Meeting';
            case 'video/online meet':
                return 'Video Online Meet';
            case 'Internal':
                return 'Internal';
            case 'Eksternal':
                return 'Eksternal';
            case 'offline':
                return 'Offline/Tatap Muka';
            default:
                return ucfirst($jenis);
        }
    }
    
    private function getJenisBadgeClass($jenis)
    {
        switch ($jenis) {
            case 'video':
                return 'badge bg-info';
            case 'document':
                return 'badge bg-warning';
            case 'zoom':
                return 'badge bg-primary';
            case 'video/meet':
            case 'video/online meet':
                return 'badge bg-info';
            case 'Internal':
                return 'badge bg-success';
            case 'Eksternal':
                return 'badge bg-secondary';
            case 'offline':
                return 'badge bg-danger';
            default:
                return 'badge bg-secondary';
        }
    }
    
    private function getDurasiDisplay($durasi)
    {
        if (!$durasi || $durasi <= 0) {
            return 'Tidak ditentukan';
        }
        
        $hours = floor($durasi / 60);
        $minutes = $durasi % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours} jam {$minutes} menit";
        } elseif ($hours > 0) {
            return "{$hours} jam";
        } else {
            return "{$minutes} menit";
        }
    }
    
    private function getLocationInfo($training)
    {
        $jenis = $training['jenis_pelatihan'] ?? 'offline';
        
        switch ($jenis) {
            case 'video':
                return 'Video Online';
            case 'document':
                return 'Dokumen Online';
            case 'zoom':
                return 'Zoom Meeting';
            case 'video/meet':
                return 'Video Meeting';
            case 'video/online meet':
                return 'Video Online Meet';
            case 'Internal':
                return 'Internal';
            case 'Eksternal':
                return 'Eksternal';
            case 'offline':
                return $training['konten'] ?? 'Lokasi tidak tersedia';
            default:
                return $training['link_url'] ?? 'Lokasi tidak tersedia';
        }
    }
}
