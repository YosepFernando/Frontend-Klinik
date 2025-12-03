<?php

namespace App\Http\Controllers;

use App\Services\PelatihanService;
use App\Services\PegawaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TrainingController extends Controller
{
    protected $pelatihanService;
    protected $pegawaiService;
    
    /**
     * Constructor untuk menginisialisasi service
     */
    public function __construct(PelatihanService $pelatihanService, PegawaiService $pegawaiService)
    {
        $this->pelatihanService = $pelatihanService;
        $this->pegawaiService = $pegawaiService;
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
        
        // Jika user adalah pegawai (bukan admin/hrd), hanya tampilkan pelatihan yang dia ikuti
        $response = null;
        if (!is_admin() && !is_hrd()) {
            // Get pegawai data from authenticated user (using token)
            $pegawaiResponse = $this->pegawaiService->getMyPegawaiData();
            
            Log::info('TrainingController@index - Pegawai Response', [
                'status' => $pegawaiResponse['status'] ?? 'no_status',
                'has_data' => isset($pegawaiResponse['data']),
                'error' => $pegawaiResponse['message'] ?? null
            ]);
            
            if (isset($pegawaiResponse['status']) && $pegawaiResponse['status'] === 'success' && isset($pegawaiResponse['data'])) {
                $pegawaiId = $pegawaiResponse['data']['id_pegawai'];
                // Get pelatihan by pegawai
                $response = $this->pelatihanService->getByPegawai($pegawaiId);
            } else {
                // Jika tidak ada data pegawai (user bukan pegawai, misal kasir), tampilkan pesan
                $errorMsg = 'Fitur pelatihan hanya tersedia untuk pegawai. Silakan hubungi HRD jika Anda memerlukan akses.';
                if (isset($pegawaiResponse['message']) && str_contains($pegawaiResponse['message'], 'tidak ditemukan')) {
                    $errorMsg = 'Data pegawai Anda tidak ditemukan di sistem. Silakan hubungi HRD.';
                }
                
                return view('trainings.index')->with([
                    'trainingsData' => [],
                    'paginationInfo' => null,
                    'info' => $errorMsg
                ]);
            }
        } else {
            // Admin/HRD melihat semua pelatihan
            $response = $this->pelatihanService->getAll($params);
        }
        
        // Handle authentication error specifically
        if (isset($response['message']) && 
            (str_contains(strtolower($response['message']), 'unauthorized') || 
             str_contains(strtolower($response['message']), 'unauthenticated'))) {
            Log::warning('API authentication failed in index', ['response' => $response]);
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
        
        // Debug logging
        Log::info('TrainingController@index - API Data Structure', [
            'user_role' => is_admin() ? 'admin' : (is_hrd() ? 'hrd' : 'pegawai'),
            'is_array' => is_array($apiData),
            'data_count' => is_array($apiData) ? count($apiData) : 0,
            'has_nested_data' => isset($apiData['data']),
            'sample_keys' => is_array($apiData) && count($apiData) > 0 ? array_keys($apiData[0] ?? []) : []
        ]);
        
        // Transform data untuk view
        $trainingsData = [];
        
        // Jika pegawai, data structure berbeda (array of peserta dengan nested pelatihan)
        // API getByPegawai returns direct array, not paginated
        if (!is_admin() && !is_hrd()) {
            // Data langsung array dari API, bukan pagination
            if (is_array($apiData) && !isset($apiData['data'])) {
                Log::info('Processing pegawai data', ['count' => count($apiData)]);
                
                foreach ($apiData as $pesertaData) {
                    $training = $pesertaData['pelatihan'] ?? null;
                    
                    Log::info('Processing peserta', [
                        'has_pelatihan' => !is_null($training),
                        'pelatihan_id' => $training['id_pelatihan'] ?? 'N/A'
                    ]);
                    
                    if ($training) {
                        // Check if user has uploaded proof
                        $hasBukti = isset($pesertaData['bukti_pelatihan']) && count($pesertaData['bukti_pelatihan']) > 0;
                        $buktiStatus = null;
                        
                        if ($hasBukti) {
                            $buktiStatus = $pesertaData['bukti_pelatihan'][0]['status_verifikasi'] ?? 'menunggu';
                        }
                        
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
                            'status' => 'active',
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
                            
                            // Participant info
                            'status_kehadiran' => $pesertaData['status_kehadiran'] ?? 'terdaftar',
                            'has_bukti' => $hasBukti,
                            'bukti_status' => $buktiStatus,
                        ];
                        
                        $trainingsData[] = $transformedTraining;
                    }
                }
                
                Log::info('Pegawai trainings processed', ['total' => count($trainingsData)]);
            }
        } 
        // Admin/HRD - data pagination dari API
        elseif (isset($apiData['data']) && is_array($apiData['data'])) {
            Log::info('Processing admin/hrd data', ['count' => count($apiData['data'])]);
            
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
        // Untuk pegawai, tidak ada pagination dari API
        if (!is_admin() && !is_hrd()) {
            $totalTrainings = count($trainingsData);
            $paginationInfo = [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $totalTrainings,
                'total' => $totalTrainings,
                'has_pages' => false,
                'links' => []
            ];
            
            Log::info('Pagination info for pegawai', $paginationInfo);
        } else {
            // Admin/HRD - ada pagination dari API
            $paginationInfo = [
                'current_page' => $apiData['current_page'] ?? 1,
                'last_page' => $apiData['last_page'] ?? 1,
                'per_page' => $apiData['per_page'] ?? 15,
                'total' => $apiData['total'] ?? count($trainingsData),
                'has_pages' => ($apiData['last_page'] ?? 1) > 1,
                'links' => $apiData['links'] ?? []
            ];
        }
        
        Log::info('Final trainings data', [
            'count' => count($trainingsData),
            'pagination' => $paginationInfo
        ]);
        
        return view('trainings.index', compact('trainingsData', 'paginationInfo'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            // Get all active employees for selection tanpa autentikasi
            $employeesResponse = $this->pegawaiService->getAll(['status' => 'active']);
            $employees = [];
            
            if (isset($employeesResponse['status']) && $employeesResponse['status'] === 'success') {
                $employees = $employeesResponse['data']['data'] ?? $employeesResponse['data'] ?? [];
            } else {
                // Fallback jika API tidak tersedia
                Log::warning('Failed to fetch employees for training creation', ['response' => $employeesResponse]);
            }

            return view('trainings.create', compact('employees'));
        } catch (\Exception $e) {
            Log::error('Error in create training form', ['error' => $e->getMessage()]);
            return view('trainings.create', ['employees' => []]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Log request untuk debugging
        Log::info('Creating new training', [
            'request_data' => $request->all()
        ]);

        try {
            // Validasi dasar
            $rules = [
                'judul' => 'required|string|max:100',
                'deskripsi' => 'required|string',
                'jenis_pelatihan' => 'required|string|in:offline,online,video,document',
                'durasi' => 'nullable|integer|min:1',
                'tanggal' => 'required|date',
                'participants' => 'nullable|array',
                'participants.*' => 'integer'
            ];

            // Validasi link_url berdasarkan jenis pelatihan
            if ($request->jenis_pelatihan === 'offline') {
                $rules['link_url'] = 'required|string|max:255'; // Untuk alamat offline
            } else {
                $rules['link_url'] = 'required|string|max:255'; // Untuk URL
            }

            $validator = Validator::make($request->all(), $rules, [
                'judul.required' => 'Judul pelatihan wajib diisi',
                'judul.max' => 'Judul maksimal 100 karakter',
                'deskripsi.required' => 'Deskripsi pelatihan wajib diisi',
                'jenis_pelatihan.required' => 'Jenis pelatihan wajib dipilih',
                'jenis_pelatihan.in' => 'Jenis pelatihan tidak valid',
                'link_url.required' => 'URL/Alamat pelatihan wajib diisi',
                'tanggal.required' => 'Tanggal pelatihan wajib diisi',
                'tanggal.date' => 'Format tanggal tidak valid',
                'participants.array' => 'Format peserta tidak valid',
                'participants.*.integer' => 'ID peserta harus berupa angka'
            ]);

            if ($validator->fails()) {
                Log::warning('Validation failed', ['errors' => $validator->errors()]);
                return back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Gagal membuat pelatihan. Mohon periksa kembali input Anda.');
            }

            // Filter participants: remove null, empty string, and non-numeric values
            $participants = [];
            if ($request->has('participants') && is_array($request->participants)) {
                $participants = array_filter($request->participants, function($value) {
                    return !is_null($value) && $value !== '' && is_numeric($value) && $value > 0;
                });
                // Re-index array to avoid gaps
                $participants = array_values($participants);
            }

            // Siapkan data untuk API
            $data = [
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'jenis_pelatihan' => $request->jenis_pelatihan,
                'jadwal_pelatihan' => $request->tanggal,
                'link_url' => $request->link_url,
                'durasi' => $request->durasi,
                'participants' => $participants
            ];

            // Log data yang akan dikirim ke API
            Log::info('Sending data to API', ['data' => $data]);

            // Kirim ke API
            $response = $this->pelatihanService->store($data);
            
            // Log response dari API
            Log::info('API Response', ['response' => $response]);

            // Cek response
            if (!isset($response['status'])) {
                throw new \Exception('Invalid API response format');
            }

            if ($response['status'] === 'success') {
                return redirect()
                    ->route('trainings.index')
                    ->with('success', 'Pelatihan berhasil dibuat!');
            } else {
                // Ambil error message dari response
                $errorMessage = $response['message'] ?? 'Unknown error from API';
                if (isset($response['errors'])) {
                    $errorDetails = [];
                    foreach ($response['errors'] as $field => $messages) {
                        $errorDetails[] = implode(', ', (array)$messages);
                    }
                    $errorMessage .= ': ' . implode('; ', $errorDetails);
                }
                throw new \Exception($errorMessage);
            }

        } catch (\Exception $e) {
            Log::error('Error creating training', [
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
        
        // Ambil daftar peserta jika admin/hrd
        $participants = [];
        $isParticipant = false;
        $userProof = null; // Untuk menyimpan bukti pelatihan user
        
        if (is_admin() || is_hrd()) {
            // Get participants list from the training data (already loaded with peserta relation)
            $participants = $trainingData['peserta'] ?? [];
            
            Log::info('TrainingController@show - Participants Data', [
                'training_id' => $id,
                'has_peserta' => isset($trainingData['peserta']),
                'participants_count' => count($participants),
                'sample_participant' => count($participants) > 0 ? array_keys($participants[0]) : []
            ]);
        } else {
            // Check if current user is participant
            $pegawaiResponse = $this->pegawaiService->getMyPegawaiData();
            
            if (isset($pegawaiResponse['status']) && $pegawaiResponse['status'] === 'success' && isset($pegawaiResponse['data'])) {
                $pegawaiId = $pegawaiResponse['data']['id_pegawai'];
                
                // Check from the peserta data if user is participant
                $pesertaList = $trainingData['peserta'] ?? [];
                foreach ($pesertaList as $peserta) {
                    if (isset($peserta['id_pegawai']) && $peserta['id_pegawai'] == $pegawaiId) {
                        $isParticipant = true;
                        
                        // Cek apakah user sudah upload bukti
                        if (isset($peserta['bukti_pelatihan']) && count($peserta['bukti_pelatihan']) > 0) {
                            $userProof = $peserta['bukti_pelatihan'][0]; // Ambil bukti pertama
                        }
                        break;
                    }
                }
                
                Log::info('TrainingController@show - Participant Check', [
                    'training_id' => $id,
                    'pegawai_id' => $pegawaiId,
                    'is_participant' => $isParticipant,
                    'has_proof' => $userProof !== null,
                    'peserta_count' => count($pesertaList)
                ]);
            }
        }
        
        return view('trainings.show', compact('training', 'participants', 'isParticipant', 'userProof'));
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
            'jenis_pelatihan' => 'required|in:offline,online,video,document',
            'durasi' => 'nullable|integer|min:1',
            'jadwal_pelatihan' => 'nullable|date',
        ];

        // Add conditional validation based on training type
        if ($request->jenis_pelatihan === 'offline') {
            // Offline types require address (stored in link_url field)
            $rules['link_url'] = 'required|string|min:10|max:500';
        } else {
            // Online types require valid URL
            $rules['link_url'] = 'required|url|max:255';
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
            'link_url.max' => 'Alamat/URL maksimal 255 karakter',
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
            case 'online':
                return 'Meeting Online';
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
            case 'online':
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
    
    /**
     * Show participants management page for training
     */
    public function manageParticipants($id)
    {
        // Check if user is authenticated and has admin/hrd role
        if (!session('authenticated')) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu.');
        }
        
        $userRole = session('user_role');
        if (!in_array($userRole, ['admin', 'hrd'])) {
            return redirect()->route('trainings.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengelola peserta pelatihan.');
        }
        
        // Get training data
        $response = $this->pelatihanService->getById($id);
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return redirect()->route('trainings.index')
                ->with('error', 'Pelatihan tidak ditemukan.');
        }
        
        $training = $response['data'];
        
        // Get all employees (implementation depends on your employee service)
        // For now, we'll use a placeholder
        $employees = []; // You need to implement this
        
        // Get current participants
        $participants = []; // You need to implement this
        
        return view('trainings.manage-participants', compact('training', 'employees', 'participants'));
    }
    
    /**
     * Add participants to training
     */
    public function addParticipants(Request $request, $id)
    {
        $request->validate([
            'pegawai_ids' => 'required|array',
            'pegawai_ids.*' => 'integer'
        ]);
        
        // Check if user is authenticated and has admin/hrd role
        if (!session('authenticated')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $userRole = session('user_role');
        if (!in_array($userRole, ['admin', 'hrd'])) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        // Call API to add participants
        $response = $this->pelatihanService->addParticipants($id, $request->pegawai_ids);
        
        if ($response['status'] === 'success') {
            return response()->json(['success' => true, 'message' => 'Peserta berhasil ditambahkan']);
        }
        
        return response()->json(['success' => false, 'message' => $response['message'] ?? 'Gagal menambah peserta']);
    }
    
    /**
     * Remove participant from training
     */
    public function removeParticipant($trainingId, $participantId)
    {
        // Check if user is authenticated and has admin/hrd role
        if (!session('authenticated')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $userRole = session('user_role');
        if (!in_array($userRole, ['admin', 'hrd'])) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        // Call API to remove participant
        $response = $this->pelatihanService->removeParticipant($trainingId, $participantId);
        
        if ($response['status'] === 'success') {
            return response()->json(['success' => true, 'message' => 'Peserta berhasil dihapus']);
        }
        
        return response()->json(['success' => false, 'message' => $response['message'] ?? 'Gagal menghapus peserta']);
    }
    
    /**
     * Show upload proof page
     */
    public function showUploadProof($id)
    {
        if (!session('authenticated')) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu.');
        }
        
        // Get pegawai data
        $pegawaiResponse = $this->pegawaiService->getMyPegawaiData();
        if (!isset($pegawaiResponse['status']) || $pegawaiResponse['status'] !== 'success' || !isset($pegawaiResponse['data'])) {
            return redirect()->route('trainings.index')
                ->with('error', 'Data pegawai tidak ditemukan. Fitur ini hanya untuk pegawai.');
        }
        
        $pegawai = $pegawaiResponse['data'];
        $pegawaiId = $pegawai['id_pegawai'];
        
        // Get training data
        $response = $this->pelatihanService->getById($id);
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return redirect()->route('trainings.index')
                ->with('error', 'Pelatihan tidak ditemukan.');
        }
        
        $training = $response['data'];
        
        // Check if user is participant
        $participantResponse = $this->pelatihanService->checkParticipant($id, $pegawaiId);
        if (!$participantResponse || !$participantResponse['is_participant']) {
            return redirect()->route('trainings.index')
                ->with('error', 'Anda tidak terdaftar sebagai peserta pelatihan ini.');
        }
        
        return view('trainings.upload-proof', compact('training', 'pegawai'));
    }
    
    /**
     * Upload training proof
     */
    public function uploadProof(Request $request, $id)
    {
        $request->validate([
            'bukti_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'keterangan' => 'nullable|string|max:500'
        ]);
        
        if (!session('authenticated')) {
            return redirect()->back()->with('error', 'Sesi login Anda telah berakhir.');
        }
        
        // Get pegawai data
        $pegawaiResponse = $this->pegawaiService->getMyPegawaiData();
        if (!isset($pegawaiResponse['status']) || $pegawaiResponse['status'] !== 'success' || !isset($pegawaiResponse['data'])) {
            return redirect()->back()->with('error', 'Data pegawai tidak ditemukan. Fitur ini hanya untuk pegawai.');
        }
        
        $pegawaiId = $pegawaiResponse['data']['id_pegawai'];
        
        try {
            $file = $request->file('bukti_file');
            
            // 1. Generate nama file dengan timestamp yang SAMA
            $timestamp = time();
            $originalName = $file->getClientOriginalName();
            $fileName = $timestamp . '_' . $originalName;
            
            // 2. Simpan file ke storage frontend dengan nama yang sudah digenerate
            $filePath = $file->storeAs('bukti_pelatihan', $fileName, 'public');
            
            Log::info('File saved to frontend storage', [
                'timestamp' => $timestamp,
                'filename' => $fileName,
                'path' => $filePath,
                'full_path' => storage_path('app/public/' . $filePath)
            ]);
            
            // 3. Kirim file ASLI ke backend API (biar backend yang rename sesuai kebutuhannya)
            $response = $this->pelatihanService->uploadBukti($id, $pegawaiId, $file, $request->keterangan);
            
            Log::info('Backend API response', [
                'response' => $response
            ]);
            
            // 4. Cek response
            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->route('trainings.show', $id)
                    ->with('success', 'Bukti pelatihan berhasil diupload dan menunggu verifikasi.');
            }
            
            // 5. Jika gagal, hapus file dari frontend storage
            Storage::disk('public')->delete($filePath);
            
            Log::warning('Upload failed, file deleted from frontend', [
                'path' => $filePath
            ]);
            
            return redirect()->back()
                ->with('error', $response['message'] ?? 'Gagal mengupload bukti pelatihan.')
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('Error uploading proof', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Hapus file jika ada error
            if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengupload file.')
                ->withInput();
        }
    }
    
    /**
     * Show proof verification page for admin/hrd
     */
    public function verifyProof($trainingId)
    {
        if (!session('authenticated')) {
            return redirect()->route('login')->with('error', 'Anda harus login terlebih dahulu.');
        }
        
        $userRole = session('user_role');
        if (!in_array($userRole, ['admin', 'hrd'])) {
            return redirect()->route('trainings.index')
                ->with('error', 'Anda tidak memiliki akses untuk verifikasi bukti.');
        }
        
        // Get training data
        $trainingResponse = $this->pelatihanService->getById($trainingId);
        if (!isset($trainingResponse['status']) || $trainingResponse['status'] !== 'success') {
            return redirect()->route('trainings.index')
                ->with('error', 'Pelatihan tidak ditemukan.');
        }
        
        $training = $trainingResponse['data'];
        
        // Get bukti list
        $buktiResponse = $this->pelatihanService->getBuktiByPelatihan($trainingId);
        $buktiList = $buktiResponse['data'] ?? [];
        
        return view('trainings.verify-proof', compact('training', 'buktiList'));
    }
    
    /**
     * Process proof verification
     */
    public function processVerification(Request $request, $trainingId, $buktiId)
    {
        $request->validate([
            'status' => 'required|in:disetujui,ditolak',
            'catatan' => 'nullable|string|max:500'
        ]);
        
        if (!session('authenticated')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $userRole = session('user_role');
        if (!in_array($userRole, ['admin', 'hrd'])) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        $response = $this->pelatihanService->verifyBukti($buktiId, $request->status, $request->catatan);
        
        if ($response['status'] === 'success') {
            return response()->json([
                'success' => true,
                'message' => 'Bukti berhasil diverifikasi'
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => $response['message'] ?? 'Gagal memverifikasi bukti'
        ], 500);
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
