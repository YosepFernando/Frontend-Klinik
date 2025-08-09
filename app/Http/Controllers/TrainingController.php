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
        // Check if user is authenticated
        if (!session('authenticated')) {
            return redirect()->route('login')
                ->with('error', 'Anda harus login terlebih dahulu untuk mengakses data pelatihan.');
        }
        
        // Check if API token exists
        $apiToken = session('api_token');
        if (!$apiToken) {
            return redirect()->route('login')
                ->with('error', 'Sesi login Anda telah berakhir. Silakan login kembali.');
        }
        
        // Persiapkan parameter untuk API
        $params = [];
        
        // Search by title (API menggunakan 'judul' untuk pencarian)
        if ($request->filled('search')) {
            $params['judul'] = $request->search;
        }
        
        // Filter by training type
        if ($request->filled('jenis_pelatihan')) {
            $params['jenis_pelatihan'] = $request->jenis_pelatihan;
        }
        
        // Tambahkan parameter untuk pagination
        $params['page'] = $request->input('page', 1);
        $params['per_page'] = 12;
        
        // Tambahkan parameter untuk sorting
        $params['sort'] = 'jadwal_pelatihan';
        $params['order'] = 'desc'; // Terbaru dulu
        
        // Filter status pelatihan (past/upcoming)
        if ($request->filled('status_filter')) {
            $params['status_filter'] = $request->status_filter;
        }
        
        // Ambil data dari API
        $response = $this->pelatihanService->getAll($params);
        
        // Handle authentication error specifically
        if (isset($response['message']) && 
            (str_contains(strtolower($response['message']), 'unauthorized') || 
             str_contains(strtolower($response['message']), 'unauthenticated'))) {
            \Log::warning('API authentication failed in index', ['response' => $response]);
            return redirect()->route('login')
                ->with('error', 'Sesi login Anda telah berakhir. Silakan login kembali.');
        }
        
        // Periksa apakah respons berhasil
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return view('trainings.index')->with([
                'trainingsData' => [],
                'paginationInfo' => null,
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
                    'created_at' => $training['created_at'] ?? null,
                    'updated_at' => $training['updated_at'] ?? null,
                    
                    // Computed properties for view
                    'status' => 'active', // Semua pelatihan dianggap aktif karena tidak ada field is_active
                    'status_display' => 'Aktif',
                    'status_badge_class' => 'badge bg-success',
                    'jenis_display' => $this->getJenisDisplay($training['jenis_pelatihan'] ?? 'offline'),
                    'jenis_badge_class' => $this->getJenisBadgeClass($training['jenis_pelatihan'] ?? 'offline'),
                    'durasi_display' => $this->getDurasiDisplay($training['durasi'] ?? 0),
                    'location_info' => $this->getLocationInfo($training),
                    
                    // Time status properties
                    'is_past' => $this->isPastTraining($training['jadwal_pelatihan'] ?? null),
                    'is_upcoming' => $this->isUpcomingTraining($training['jadwal_pelatihan'] ?? null, $training['jenis_pelatihan'] ?? 'offline'),
                    'time_status' => $this->getTimeStatus($training['jadwal_pelatihan'] ?? null),
                    'jadwal_formatted' => $this->formatJadwal($training['jadwal_pelatihan'] ?? null),
                ];
                
                $trainingsData[] = $transformedTraining;
            }
        }
        
        // Sort data berdasarkan status: upcoming first, then past
        usort($trainingsData, function($a, $b) {
            // Filter berdasarkan status jika diminta
            if (request('status_filter')) {
                $filter = request('status_filter');
                if ($filter === 'upcoming' && $a['is_past']) return 1;
                if ($filter === 'upcoming' && $b['is_past']) return -1;
                if ($filter === 'past' && !$a['is_past']) return 1;
                if ($filter === 'past' && !$b['is_past']) return -1;
            }
            
            // Sort by date: upcoming first (asc), then past (desc)
            if ($a['jadwal_pelatihan'] && $b['jadwal_pelatihan']) {
                $dateA = \Carbon\Carbon::parse($a['jadwal_pelatihan']);
                $dateB = \Carbon\Carbon::parse($b['jadwal_pelatihan']);
                
                // Jika keduanya upcoming atau keduanya past, sort by date
                if ($a['is_past'] === $b['is_past']) {
                    return $a['is_past'] ? $dateB->timestamp - $dateA->timestamp : $dateA->timestamp - $dateB->timestamp;
                }
                
                // Upcoming trainings first
                return $a['is_past'] ? 1 : -1;
            }
            
            return 0;
        });
        
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
        // Log request untuk debugging
        \Log::info('Creating new training', [
            'request_data' => $request->all()
        ]);

        try {
            // Validasi dasar
            $rules = [
                'judul' => 'required|string|max:100',
                'deskripsi' => 'required|string',
                'jenis_pelatihan' => 'required|string|in:video,document,zoom,offline',
                'durasi' => 'nullable|integer|min:1',
                'tanggal' => 'required|date'
            ];

            // Validasi link_url berdasarkan jenis pelatihan
            if ($request->jenis_pelatihan === 'offline') {
                $rules['link_url'] = 'required|string|max:255'; // Untuk alamat offline
            } else {
                $rules['link_url'] = 'required|string|max:255'; // Untuk URL
            }

            $validator = \Validator::make($request->all(), $rules, [
                'judul.required' => 'Judul pelatihan wajib diisi',
                'judul.max' => 'Judul maksimal 100 karakter',
                'deskripsi.required' => 'Deskripsi pelatihan wajib diisi',
                'jenis_pelatihan.required' => 'Jenis pelatihan wajib dipilih',
                'jenis_pelatihan.in' => 'Jenis pelatihan tidak valid',
                'link_url.required' => 'URL/Alamat pelatihan wajib diisi',
                'tanggal.required' => 'Tanggal pelatihan wajib diisi',
                'tanggal.date' => 'Format tanggal tidak valid'
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Gagal membuat pelatihan. Mohon periksa kembali input Anda.');
            }

            // Siapkan data untuk API
            $data = [
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'jenis_pelatihan' => $request->jenis_pelatihan,
                'jadwal_pelatihan' => $request->tanggal,
                'link_url' => $request->link_url,
                'durasi' => $request->durasi
            ];

            // Log data yang akan dikirim ke API
            \Log::info('Sending data to API', ['data' => $data]);

            // Kirim ke API
            $response = $this->pelatihanService->store($data);
            
            // Log response dari API
            \Log::info('API Response', ['response' => $response]);

            // Cek response
            if (!isset($response['status'])) {
                throw new \Exception('Invalid API response format');
            }

            if ($response['status'] === 'success') {
                return redirect()
                    ->route('trainings.index')
                    ->with('success', 'Pelatihan berhasil dibuat!');
            } else {
                throw new \Exception($response['message'] ?? 'Unknown error from API');
            }

        } catch (\Exception $e) {
            \Log::error('Error creating training', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat pelatihan: ' . $e->getMessage());
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
            'created_at' => $trainingData['created_at'] ? \Carbon\Carbon::parse($trainingData['created_at']) : null,
            'updated_at' => $trainingData['updated_at'] ? \Carbon\Carbon::parse($trainingData['updated_at']) : null,
            
            // Computed properties for view - semua pelatihan dianggap aktif
            'status' => 'active',
            'status_display' => 'Aktif',
            'status_badge_class' => 'badge bg-success',
            'jenis_display' => $this->getJenisDisplay($trainingData['jenis_pelatihan'] ?? 'offline'),
            'jenis_badge_class' => $this->getJenisBadgeClass($trainingData['jenis_pelatihan'] ?? 'offline'),
            'durasi_display' => $this->getDurasiDisplay($trainingData['durasi'] ?? 0),
            'location_info' => $this->getLocationInfo($trainingData),
            'jadwal_formatted' => $this->formatJadwal($trainingData['jadwal_pelatihan'] ?? null),
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
            'created_at' => $trainingData['created_at'] ? \Carbon\Carbon::parse($trainingData['created_at']) : null,
            'updated_at' => $trainingData['updated_at'] ? \Carbon\Carbon::parse($trainingData['updated_at']) : null,
            
            // Computed properties for view - semua pelatihan dianggap aktif
            'status' => 'active',
            'status_display' => 'Aktif',
            'status_badge_class' => 'badge bg-success',
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
        ];

        // Add conditional validation based on training type
        $onlineTypes = ['video', 'document', 'zoom', 'video/meet', 'video/online meet'];
        
        if (in_array($request->jenis_pelatihan, $onlineTypes)) {
            // Online types require valid URL
            $rules['link_url'] = 'required|url';
        } elseif ($request->jenis_pelatihan === 'offline') {
            // Offline types require address (stored in link_url field)
            $rules['link_url'] = 'required|string|min:10|max:500';
        } else {
            // Other types don't require link_url
            $rules['link_url'] = 'nullable';
        }

        $messages = [
            'judul.required' => 'Judul pelatihan wajib diisi',
            'judul.max' => 'Judul maksimal 100 karakter',
            'deskripsi.required' => 'Deskripsi pelatihan wajib diisi',
            'jenis_pelatihan.required' => 'Jenis pelatihan wajib dipilih',
            'jenis_pelatihan.in' => 'Jenis pelatihan tidak valid',
            'link_url.required' => $request->jenis_pelatihan === 'offline' ? 'Alamat pelatihan wajib diisi' : 'URL pelatihan wajib diisi',
            'link_url.url' => 'Format URL tidak valid',
            'link_url.min' => 'Alamat minimal 10 karakter',
            'link_url.max' => 'Alamat maksimal 500 karakter',
            'jadwal_pelatihan.date' => 'Format tanggal tidak valid',
            'durasi.integer' => 'Durasi harus berupa angka',
            'durasi.min' => 'Durasi minimal 1 menit'
        ];

        $request->validate($rules, $messages);

        // Persiapkan data untuk dikirim ke API
        $data = [
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'jenis_pelatihan' => $request->jenis_pelatihan,
            'link_url' => $request->link_url,
            'durasi' => $request->durasi,
            'jadwal_pelatihan' => $request->jadwal_pelatihan,
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
        try {
            // Log the deletion attempt
            \Log::info('TrainingController@destroy called', [
                'training_id' => $id,
                'user_id' => session('user_id'),
                'authenticated' => session('authenticated')
            ]);
            
            // Check if user is authenticated
            if (!session('authenticated')) {
                \Log::warning('Deletion attempt without authentication', ['training_id' => $id]);
                return redirect()->route('trainings.index')
                    ->with('error', 'Anda harus login terlebih dahulu untuk menghapus data pelatihan.');
            }
            
            // Check if API token exists
            $apiToken = session('api_token');
            if (!$apiToken) {
                \Log::warning('No API token found in session', ['training_id' => $id]);
                return redirect()->route('login')
                    ->with('error', 'Sesi login Anda telah berakhir. Silakan login kembali.');
            }
            
            // Validate ID
            if (!$id || !is_numeric($id)) {
                \Log::warning('Invalid training ID provided', ['training_id' => $id]);
                return redirect()->route('trainings.index')
                    ->with('error', 'ID pelatihan tidak valid.');
            }
            
            // First, check if the training exists
            $checkResponse = $this->pelatihanService->getById($id);
            \Log::info('Check training exists response', [
                'training_id' => $id,
                'response' => $checkResponse
            ]);
            
            // Handle authentication error specifically
            if (isset($checkResponse['message']) && 
                (str_contains(strtolower($checkResponse['message']), 'unauthorized') || 
                 str_contains(strtolower($checkResponse['message']), 'unauthenticated'))) {
                \Log::warning('API authentication failed during check', [
                    'training_id' => $id,
                    'response' => $checkResponse
                ]);
                return redirect()->route('login')
                    ->with('error', 'Sesi login Anda telah berakhir. Silakan login kembali untuk menghapus data pelatihan.');
            }
            
            if (!isset($checkResponse['status']) || $checkResponse['status'] !== 'success') {
                \Log::warning('Training not found before deletion', [
                    'training_id' => $id,
                    'response' => $checkResponse
                ]);
                return redirect()->route('trainings.index')
                    ->with('error', 'Pelatihan tidak ditemukan atau sudah dihapus sebelumnya.');
            }
            
            // Now attempt to delete
            $response = $this->pelatihanService->delete($id);
            
            \Log::info('Delete API response', [
                'training_id' => $id,
                'response' => $response
            ]);
            
            // Handle authentication error specifically for delete operation
            if (isset($response['message']) && 
                (str_contains(strtolower($response['message']), 'unauthorized') || 
                 str_contains(strtolower($response['message']), 'unauthenticated'))) {
                \Log::warning('API authentication failed during delete', [
                    'training_id' => $id,
                    'response' => $response
                ]);
                return redirect()->route('login')
                    ->with('error', 'Sesi login Anda telah berakhir. Silakan login kembali untuk menghapus data pelatihan.');
            }
            
            // Periksa respons dari API
            if (isset($response['status']) && $response['status'] === 'success') {
                \Log::info('Training deleted successfully', ['training_id' => $id]);
                return redirect()->route('trainings.index')
                    ->with('success', 'Pelatihan berhasil dihapus.');
            } else {
                \Log::warning('Delete API returned error', [
                    'training_id' => $id,
                    'response' => $response
                ]);
                $errorMessage = 'Gagal menghapus pelatihan.';
                if (isset($response['message'])) {
                    $errorMessage .= ' Error: ' . $response['message'];
                }
                return redirect()->route('trainings.index')
                    ->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            \Log::error('Error deleting training: ' . $e->getMessage(), [
                'training_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Check if the exception message contains authentication-related errors
            $errorMessage = $e->getMessage();
            if (str_contains(strtolower($errorMessage), 'unauthorized') || 
                str_contains(strtolower($errorMessage), 'unauthenticated') ||
                str_contains(strtolower($errorMessage), '401')) {
                return redirect()->route('login')
                    ->with('error', 'Sesi login Anda telah berakhir. Silakan login kembali.');
            }
            
            return redirect()->route('trainings.index')
                ->with('error', 'Terjadi kesalahan saat menghapus pelatihan. Silakan coba lagi atau hubungi administrator.');
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
                // Untuk offline, gunakan link_url sebagai alamat lokasi
                return $training['link_url'] ?? 'Lokasi belum ditentukan';
            default:
                return $training['link_url'] ?? 'Lokasi tidak tersedia';
        }
    }
    
    private function isPastTraining($jadwalPelatihan)
    {
        if (!$jadwalPelatihan) {
            return false;
        }
        
        try {
            $jadwal = \Carbon\Carbon::parse($jadwalPelatihan);
            return $jadwal->isPast();
        } catch (\Exception $e) {
            return false;
        }
    }
    
    private function isUpcomingTraining($jadwalPelatihan, $jenispelatihan)
    {
        if (!$jadwalPelatihan || $jenispelatihan !== 'zoom') {
            return false;
        }
        
        try {
            $jadwal = \Carbon\Carbon::parse($jadwalPelatihan);
            $now = \Carbon\Carbon::now();
            
            return $jadwal->isFuture() && $jadwal->diffInDays($now) <= 7;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    private function getTimeStatus($jadwalPelatihan)
    {
        if (!$jadwalPelatihan) {
            return 'not_scheduled';
        }
        
        try {
            $jadwal = \Carbon\Carbon::parse($jadwalPelatihan);
            
            if ($jadwal->isPast()) {
                return 'past';
            } elseif ($jadwal->isToday()) {
                return 'today';
            } elseif ($jadwal->isTomorrow()) {
                return 'tomorrow';
            } elseif ($jadwal->isFuture()) {
                return 'future';
            }
            
            return 'normal';
        } catch (\Exception $e) {
            return 'error';
        }
    }
    
    private function formatJadwal($jadwalPelatihan)
    {
        if (!$jadwalPelatihan) {
            return 'Tidak dijadwalkan';
        }
        
        try {
            $jadwal = \Carbon\Carbon::parse($jadwalPelatihan);
            $now = \Carbon\Carbon::now();
            
            if ($jadwal->isToday()) {
                return 'Hari ini, ' . $jadwal->format('H:i');
            } elseif ($jadwal->isTomorrow()) {
                return 'Besok, ' . $jadwal->format('H:i');
            } elseif ($jadwal->isYesterday()) {
                return 'Kemarin, ' . $jadwal->format('H:i');
            } elseif ($jadwal->isPast()) {
                return 'Selesai pada ' . $jadwal->format('d M Y, H:i');
            } else {
                return $jadwal->format('d M Y, H:i');
            }
        } catch (\Exception $e) {
            return 'Format tanggal tidak valid';
        }
    }
}
