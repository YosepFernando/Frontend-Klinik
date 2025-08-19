<?php

namespace App\Http\Controllers;

use App\Models\Recruitment;
use App\Models\RecruitmentApplication;
use App\Services\LowonganPekerjaanService;
use App\Services\LamaranPekerjaanService;
use App\Services\WawancaraService;
use App\Services\HasilSeleksiService;
use App\Services\PosisiService;
use App\Services\PegawaiService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecruitmentController extends Controller
{
    protected $lowonganService;
    protected $lamaranService;
    protected $wawancaraService;
    protected $hasilSeleksiService;
    protected $posisiService;
    protected $pegawaiService;
    protected $userService;
    
    /**
     * Constructor untuk menginisialisasi service
     */
    public function __construct(
        LowonganPekerjaanService $lowonganService,
        LamaranPekerjaanService $lamaranService,
        WawancaraService $wawancaraService,
        HasilSeleksiService $hasilSeleksiService,
        PosisiService $posisiService,
        PegawaiService $pegawaiService,
        UserService $userService
    ) {
        $this->lowonganService = $lowonganService;
        $this->lamaranService = $lamaranService;
        $this->wawancaraService = $wawancaraService;
        $this->hasilSeleksiService = $hasilSeleksiService;
        $this->posisiService = $posisiService;
        $this->pegawaiService = $pegawaiService;
        $this->userService = $userService;
        
        // Note: Middleware sudah diterapkan di routes/web.php
        // - api.auth untuk semua route dalam grup
        // - role:admin,hrd untuk method create, store, edit, update, destroy
        // - role:pelanggan untuk method apply, showApplyForm, applicationStatus, myApplications
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get page parameter from request
        $page = $request->get('page', 1);
        $perPage = 10;
        
        // Ambil data lowongan pekerjaan dari API
        $response = $this->lowonganService->getAll(['page' => $page, 'per_page' => $perPage]);
        
        // Periksa respons dari API
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data lowongan pekerjaan: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }

        // Extract pagination data from API response
        $paginationData = $response['data'] ?? [];
        $recruitmentsData = $paginationData['data'] ?? [];
        $transformedRecruitments = [];
        
        if (is_array($recruitmentsData)) {
            foreach ($recruitmentsData as $recruitment) {
                // Pastikan $recruitment adalah array sebelum mengakses key-nya
                if (!is_array($recruitment)) {
                    continue; // Skip jika bukan array
                }
                
                // Transform data menjadi object untuk compatibility dengan view
                // Map API field names to expected field names
                $transformedRecruitments[] = (object) [
                    'id' => $recruitment['id_lowongan_pekerjaan'] ?? null,
                    'position' => $recruitment['judul_pekerjaan'] ?? 'Posisi tidak tersedia',
                    'description' => $recruitment['deskripsi'] ?? 'Tidak ada deskripsi',
                    'slots' => $recruitment['jumlah_lowongan'] ?? 0,
                    'salary_range' => $this->formatSalaryRange($recruitment['gaji_minimal'] ?? null, $recruitment['gaji_maksimal'] ?? null),
                    'application_deadline' => isset($recruitment['tanggal_selesai']) && $recruitment['tanggal_selesai'] ? 
                        \Carbon\Carbon::parse($recruitment['tanggal_selesai']) : 
                        \Carbon\Carbon::now()->addDays(30), // default deadline 30 hari
                    'created_at' => isset($recruitment['created_at']) && $recruitment['created_at'] ? 
                        \Carbon\Carbon::parse($recruitment['created_at']) : null,
                    'updated_at' => isset($recruitment['updated_at']) && $recruitment['updated_at'] ? 
                        \Carbon\Carbon::parse($recruitment['updated_at']) : null,
                    'is_active' => ($recruitment['status'] ?? 'aktif') === 'aktif',
                    'status' => ($recruitment['status'] ?? 'aktif') === 'aktif' ? 'open' : 'closed',
                    'requirements' => $recruitment['persyaratan'] ?? '',
                    'benefits' => '', // Benefits not in API response, leave empty
                    'work_type' => 'full-time', // Work type not in API response, default to full-time
                    'employment_type_display' => $this->getEmploymentTypeDisplay('full-time'),
                    'location' => 'Klinik', // Location not in API response, default to Klinik
                    'experience_required' => $recruitment['pengalaman_minimal'] ?? '',
                ];
            }
        }

        // Create Laravel paginator from API pagination data
        $recruitments = new \Illuminate\Pagination\LengthAwarePaginator(
            $transformedRecruitments,
            $paginationData['total'] ?? 0,
            $paginationData['per_page'] ?? $perPage,
            $paginationData['current_page'] ?? 1,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        return view('recruitments.index', compact('recruitments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil data posisi dari API
        $posisiResponse = $this->posisiService->getAll();
        
        // Periksa respons dari API
        if (!isset($posisiResponse['status']) || $posisiResponse['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data posisi: ' . ($posisiResponse['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        // Filter posisi selain Admin dan transform to objects
        $posisi = collect($posisiResponse['data'] ?? [])->filter(function($item) {
            return is_array($item) && ($item['nama_posisi'] ?? '') !== 'Admin';
        })->map(function($item) {
            return (object) [
                'id_posisi' => $item['id_posisi'] ?? null,
                'nama_posisi' => $item['nama_posisi'] ?? 'Tidak diketahui',
                'gaji_pokok' => $item['gaji_pokok'] ?? null,
                'persen_bonus' => $item['persen_bonus'] ?? 0,
                'created_at' => $item['created_at'] ?? null,
                'updated_at' => $item['updated_at'] ?? null,
            ];
        })->values();
        
        return view('recruitments.create', compact('posisi'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_posisi' => 'required|integer',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'application_deadline' => 'required|date|after:today',
            'slots' => 'required|integer|min:1',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'status' => 'required|in:open,closed',
            'age_min' => 'nullable|integer|min:16|max:100',
            'age_max' => 'nullable|integer|min:16|max:100|gte:age_min',
            'job_title' => 'required|string|max:255',
            'experience_required' => 'nullable|string|max:255',
            'start_date' => 'required|date',
        ]);

        // Persiapkan data untuk dikirim ke API sesuai dengan format yang diminta
        $apiData = [
            'judul_pekerjaan' => $request->job_title,
            'id_posisi' => (int) $request->id_posisi,
            'jumlah_lowongan' => (int) $request->slots,
            'pengalaman_minimal' => $request->experience_required ?? 'Tidak ada pengalaman khusus',
            'gaji_minimal' => $request->salary_min ? (float) $request->salary_min : null,
            'gaji_maksimal' => $request->salary_max ? (float) $request->salary_max : null,
            'status' => $request->status === 'open' ? 'aktif' : 'nonaktif',
            'tanggal_mulai' => $request->start_date,
            'tanggal_selesai' => $request->application_deadline,
            'deskripsi' => $request->description,
            'persyaratan' => $request->requirements,
        ];

        // Kirim data ke API
        $response = $this->lowonganService->store($apiData);
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('recruitments.index')
                ->with('success', 'Lowongan kerja berhasil dibuat.');
        } else {
            return back()->withInput()
                ->with('error', 'Gagal membuat lowongan kerja: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Ambil detail lowongan pekerjaan dari API
        $response = $this->lowonganService->getById($id);
        
        // Periksa respons dari API
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data lowongan pekerjaan: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        $recruitmentData = $response['data'] ?? null;
        
        if (!$recruitmentData || !is_array($recruitmentData)) {
            return back()->with('error', 'Data lowongan pekerjaan tidak ditemukan atau format tidak valid');
        }
        
        // Transform data menjadi object untuk compatibility dengan view
        $recruitment = (object) [
            'id' => $recruitmentData['id_lowongan_pekerjaan'] ?? null,
            'position' => $recruitmentData['judul_pekerjaan'] ?? 'Posisi tidak tersedia',
            'description' => $recruitmentData['deskripsi'] ?? 'Tidak ada deskripsi',
            'slots' => $recruitmentData['jumlah_lowongan'] ?? 0,
            'salary_range' => $this->formatSalaryRange($recruitmentData['gaji_minimal'] ?? null, $recruitmentData['gaji_maksimal'] ?? null),
            'application_deadline' => isset($recruitmentData['tanggal_selesai']) && $recruitmentData['tanggal_selesai'] ? 
                \Carbon\Carbon::parse($recruitmentData['tanggal_selesai']) : 
                \Carbon\Carbon::now()->addDays(30), // default deadline 30 hari
            'created_at' => isset($recruitmentData['created_at']) && $recruitmentData['created_at'] ? 
                \Carbon\Carbon::parse($recruitmentData['created_at']) : null,
            'updated_at' => isset($recruitmentData['updated_at']) && $recruitmentData['updated_at'] ? 
                \Carbon\Carbon::parse($recruitmentData['updated_at']) : null,
            'is_active' => ($recruitmentData['status'] ?? 'aktif') === 'aktif',
            'status' => ($recruitmentData['status'] ?? 'aktif') === 'aktif' ? 'open' : 'closed',
            'requirements' => $recruitmentData['persyaratan'] ?? '',
            'benefits' => '', // Benefits not in API response, leave empty
            'work_type' => 'full-time', // Work type not in API response, default to full-time
            'employment_type_display' => $this->getEmploymentTypeDisplay('full-time'),
            'location' => 'Klinik', // Location not in API response, default to Klinik
            'experience_required' => $recruitmentData['pengalaman_minimal'] ?? '',
        ];
        
        return view('recruitments.show', compact('recruitment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // Ambil detail lowongan pekerjaan dari API
        $response = $this->lowonganService->getById($id);
        
        // Periksa respons dari API
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data lowongan pekerjaan: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        $recruitmentData = $response['data'] ?? null;
        
        if (!$recruitmentData || !is_array($recruitmentData)) {
            return back()->with('error', 'Data lowongan pekerjaan tidak ditemukan atau format tidak valid');
        }
        
        // Transform data menjadi object untuk compatibility dengan view
        $recruitment = (object) [
            'id' => $recruitmentData['id_lowongan_pekerjaan'] ?? null,
            'position' => $recruitmentData['judul_pekerjaan'] ?? 'Posisi tidak tersedia',
            'description' => $recruitmentData['deskripsi'] ?? 'Tidak ada deskripsi',
            'slots' => $recruitmentData['jumlah_lowongan'] ?? 0,
            'salary_range' => $this->formatSalaryRange($recruitmentData['gaji_minimal'] ?? null, $recruitmentData['gaji_maksimal'] ?? null),
            'application_deadline' => isset($recruitmentData['tanggal_selesai']) && $recruitmentData['tanggal_selesai'] ? 
                \Carbon\Carbon::parse($recruitmentData['tanggal_selesai']) : 
                \Carbon\Carbon::now()->addDays(30), // default deadline 30 hari
            'created_at' => isset($recruitmentData['created_at']) && $recruitmentData['created_at'] ? 
                \Carbon\Carbon::parse($recruitmentData['created_at']) : null,
            'updated_at' => isset($recruitmentData['updated_at']) && $recruitmentData['updated_at'] ? 
                \Carbon\Carbon::parse($recruitmentData['updated_at']) : null,
            'is_active' => ($recruitmentData['status'] ?? 'aktif') === 'aktif',
            'status' => ($recruitmentData['status'] ?? 'aktif') === 'aktif' ? 'open' : 'closed',
            'requirements' => $recruitmentData['persyaratan'] ?? '',
            'benefits' => '', // Benefits not in API response, leave empty
            'work_type' => 'full-time', // Work type not in API response, default to full-time
            'employment_type_display' => $this->getEmploymentTypeDisplay('full-time'),
            'location' => 'Klinik', // Location not in API response, default to Klinik
            'experience_required' => $recruitmentData['pengalaman_minimal'] ?? '',
            'id_posisi' => $recruitmentData['id_posisi'] ?? null,
            'gaji_minimal' => $recruitmentData['gaji_minimal'] ?? null,
            'gaji_maksimal' => $recruitmentData['gaji_maksimal'] ?? null,
            'tanggal_mulai' => $recruitmentData['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $recruitmentData['tanggal_selesai'] ?? null,
        ];
        
        // Ambil data posisi dari API
        $posisiResponse = $this->posisiService->getAll();
        
        // Periksa respons dari API
        if (!isset($posisiResponse['status']) || $posisiResponse['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data posisi: ' . ($posisiResponse['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        // Filter posisi selain Admin dan transform to objects
        $posisi = collect($posisiResponse['data'] ?? [])->filter(function($item) {
            return is_array($item) && ($item['nama_posisi'] ?? '') !== 'Admin';
        })->map(function($item) {
            return (object) [
                'id_posisi' => $item['id_posisi'] ?? null,
                'nama_posisi' => $item['nama_posisi'] ?? 'Tidak diketahui',
                'gaji_pokok' => $item['gaji_pokok'] ?? null,
                'persen_bonus' => $item['persen_bonus'] ?? 0,
                'created_at' => $item['created_at'] ?? null,
                'updated_at' => $item['updated_at'] ?? null,
            ];
        })->values();
        
        return view('recruitments.edit', compact('recruitment', 'posisi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
{
    // 1. Validasi Laravel pada form
    $request->validate([
        'job_title'             => 'required|string|max:255',
        'id_posisi'             => 'required|integer',
        'experience_required'   => 'nullable|string|max:255',
        'salary_min'            => 'nullable|numeric|min:0',
        'salary_max'            => 'nullable|numeric|min:0|gte:salary_min',
        'description'           => 'required|string',
        'requirements'          => 'required|string',
        'start_date'            => 'required|date',
        'application_deadline'  => 'required|date|after_or_equal:start_date',
        'slots'                 => 'required|integer|min:1',
        'status'                => 'required|in:open,closed',
    ]);

    // 2. Siapkan payload sesuai spec API
    $apiData = [
        'judul_pekerjaan'    => $request->job_title,
        'id_posisi'          => (int)$request->id_posisi,
        'jumlah_lowongan'    => (int)$request->slots,
        'pengalaman_minimal' => $request->experience_required ?? '',
        'gaji_minimal'       => $request->salary_min !== null ? (float)$request->salary_min : null,
        'gaji_maksimal'      => $request->salary_max !== null ? (float)$request->salary_max : null,
        'status'             => $request->status === 'open' ? 'aktif' : 'nonaktif',
        'tanggal_mulai'      => $request->start_date,
        'tanggal_selesai'    => $request->application_deadline,
        'deskripsi'          => $request->description,
        'persyaratan'        => $request->requirements,
    ];

    // 3. Panggil API update
    $response = $this->lowonganService->update($id, $apiData);

    // 4. Tangani respons
    if (isset($response['status']) && $response['status'] === 'success') {
        return redirect()->route('recruitments.index')
            ->with('success', 'Lowongan kerja berhasil diperbarui.');
    }

    // Jika error, tampilkan juga detail validasi dari API (jika ada)
    $errorMsg = $response['message'] ?? 'Terjadi kesalahan pada server';
    if (isset($response['errors']) && is_array($response['errors'])) {
        $details = [];
        foreach ($response['errors'] as $fieldErrors) {
            $details = array_merge($details, $fieldErrors);
        }
        $errorMsg .= ' : ' . implode('; ', $details);
    }

    return back()->withInput()
        ->with('error', 'Gagal memperbarui lowongan kerja: ' . $errorMsg);
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Log the deletion attempt
            \Log::info('RecruitmentController@destroy called', [
                'recruitment_id' => $id,
                'session_user_id' => session('user_id'),
                'session_authenticated' => session('authenticated'),
                'session_user_role' => session('user_role'),
                'has_api_token' => session('api_token') ? 'yes' : 'no'
            ]);
            
            // Check if user is authenticated via session
            if (!session('authenticated') || !session('api_token')) {
                \Log::warning('Deletion attempt without valid session', ['recruitment_id' => $id]);
                return redirect()->route('recruitments.index')
                    ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali untuk menghapus data lowongan.');
            }
            
            // Check user role permission
            $userRole = session('user_role');
            if (!in_array($userRole, ['admin', 'hrd'])) {
                \Log::warning('Insufficient permissions for recruitment deletion', [
                    'recruitment_id' => $id,
                    'user_role' => $userRole
                ]);
                return redirect()->route('recruitments.index')
                    ->with('error', 'Anda tidak memiliki izin untuk menghapus data lowongan.');
            }
            
            // Validate ID
            if (!$id || !is_numeric($id)) {
                \Log::warning('Invalid recruitment ID provided', ['recruitment_id' => $id]);
                return redirect()->route('recruitments.index')
                    ->with('error', 'ID lowongan tidak valid.');
            }
            
            // Kirim permintaan hapus ke API (soft delete)
            $response = $this->lowonganService->delete($id);
            
            \Log::info('Delete recruitment API response', [
                'recruitment_id' => $id,
                'response' => $response,
                'response_keys' => array_keys($response ?? [])
            ]);
            
            // Handle authentication error specifically
            if (isset($response['message']) && 
                (str_contains(strtolower($response['message']), 'unauthorized') || 
                 str_contains(strtolower($response['message']), 'unauthenticated'))) {
                \Log::warning('API authentication failed during recruitment delete', [
                    'recruitment_id' => $id,
                    'response' => $response
                ]);
                return redirect()->route('login')
                    ->with('error', 'Sesi login Anda telah berakhir. Silakan login kembali untuk menghapus data lowongan.');
            }
            
            // Periksa respons dari API - Handle multiple success formats
            if ((isset($response['status']) && $response['status'] === 'success') ||
                (isset($response['success']) && $response['success'] === true)) {
                \Log::info('Recruitment deleted successfully', ['recruitment_id' => $id]);
                return redirect()->route('recruitments.index')
                    ->with('success', $response['message'] ?? 'Lowongan kerja berhasil dihapus.');
            } else {
                \Log::warning('Delete recruitment API returned error', [
                    'recruitment_id' => $id,
                    'response' => $response
                ]);
                $errorMessage = 'Gagal menghapus lowongan kerja.';
                if (isset($response['message'])) {
                    $errorMessage .= ' Error: ' . $response['message'];
                }
                return redirect()->route('recruitments.index')
                    ->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            \Log::error('Error deleting recruitment: ' . $e->getMessage(), [
                'recruitment_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('recruitments.index')
                ->with('error', 'Terjadi kesalahan sistem saat menghapus lowongan: ' . $e->getMessage());
        }
    }

    /**
     * Force delete the specified resource from storage.
     */
    public function forceDestroy($id)
    {
        // Kirim permintaan hapus permanen ke API
        $response = $this->lowonganService->forceDelete($id);
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('recruitments.index')
                ->with('success', 'Lowongan kerja berhasil dihapus permanen.');
        } else {
            return back()->with('error', 'Gagal menghapus lowongan kerja: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }

    /**
     * Bulk delete multiple resources.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer',
            'force' => 'sometimes|boolean'
        ]);

        $ids = $request->input('ids');
        $force = $request->input('force', false);

        // Kirim permintaan bulk delete ke API
        $response = $this->lowonganService->bulkDelete($ids, $force);
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            $message = $force ? 'Lowongan kerja terpilih berhasil dihapus permanen.' : 'Lowongan kerja terpilih berhasil dihapus.';
            return redirect()->route('recruitments.index')->with('success', $message);
        } else {
            return back()->with('error', 'Gagal menghapus lowongan kerja: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }

    /**
     * Show the application form for a specific recruitment
     */
    public function showApplyForm($id)
    {
        $user = auth_user(); // Menggunakan custom auth helper - middleware sudah ensure user login dan role pelanggan
        
        \Log::info('ShowApplyForm: Starting', [
            'job_id' => $id,
            'user' => $user
        ]);
        
        // Ambil detail lowongan pekerjaan dari API
        $response = $this->lowonganService->getById($id);
        
        // Periksa respons dari API terlebih dahulu
        if (!isset($response['status']) || $response['status'] !== 'success') {
            \Log::error('ShowApplyForm: Failed to get job data', [
                'job_id' => $id,
                'api_response' => $response
            ]);
            return redirect()->route('recruitments.index')
                ->with('error', 'Lowongan pekerjaan tidak ditemukan.');
        }
        
        $jobData = $response['data'];
        
        // Periksa status lowongan dan deadline
        \Log::info('ShowApplyForm validation check', [
            'job_id' => $id,
            'job_status' => $jobData['status'] ?? 'no job status',
            'deadline' => $jobData['tanggal_selesai'] ?? 'no deadline'
        ]);
        
        $deadline = isset($jobData['tanggal_selesai']) ? \Carbon\Carbon::parse($jobData['tanggal_selesai']) : null;
        
        if (!isset($jobData['status']) || $jobData['status'] !== 'aktif' ||
            ($deadline && $deadline->isPast())) {
            return redirect()->route('recruitments.show', $id)
                ->with('error', 'Lowongan ini sudah ditutup atau sudah lewat deadline.');
        }
        
        // Periksa apakah user sudah melamar
        $userId = $this->getUserId($user);
        if (!$userId) {
            \Log::error('ShowApplyForm: User ID not found', ['user' => $user]);
            return redirect()->route('login')
                ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }
        
        $lamaranResponse = $this->lamaranService->getAll(['id_user' => $userId, 'id_lowongan_pekerjaan' => $id]);
        
        \Log::info('ShowApplyForm: Checking existing application', [
            'user_id' => $userId,
            'lowongan_id' => $id,
            'response_status' => $lamaranResponse['status'] ?? 'no status',
            'data_count' => isset($lamaranResponse['data']['data']) ? count($lamaranResponse['data']['data']) : 'no data'
        ]);
        
        if (isset($lamaranResponse['status']) && $lamaranResponse['status'] === 'success' && 
            isset($lamaranResponse['data']['data']) && count($lamaranResponse['data']['data']) > 0) {
            return redirect()->route('recruitments.show', $id)
                ->with('error', 'Anda sudah melamar untuk posisi ini.');
        }
        
        // Convert API data to object-like structure for the view
        $recruitment = (object) [
            'id' => $jobData['id_lowongan_pekerjaan'] ?? $id, // Use id_lowongan_pekerjaan from API or fallback to route parameter
            'position' => $jobData['judul_pekerjaan'] ?? 'Posisi tidak tersedia',
            'description' => $jobData['deskripsi'] ?? '',
            'requirements' => $jobData['persyaratan'] ?? '',
            'employment_type' => 'full-time', // Default since not in API response
            'employment_type_display' => $this->getEmploymentTypeDisplay('full-time'),
            'salary_range' => $this->formatSalaryRange($jobData['gaji_minimal'] ?? null, $jobData['gaji_maksimal'] ?? null),
            'application_deadline' => $deadline,
            'slots' => $jobData['jumlah_lowongan'] ?? 0,
            'status' => $jobData['status'] ?? '',
            'created_at' => isset($jobData['created_at']) ? \Carbon\Carbon::parse($jobData['created_at']) : null,
            'updated_at' => isset($jobData['updated_at']) ? \Carbon\Carbon::parse($jobData['updated_at']) : null,
        ];
        
        \Log::info('ShowApplyForm: Successfully prepared data', [
            'recruitment' => $recruitment
        ]);
        
        return view('recruitments.apply', compact('recruitment'));
    }

    /**
     * Apply for recruitment (for customers/pelanggan)
     */
    public function apply(Request $request, $id)
    {
        $user = auth_user(); // Menggunakan custom auth helper - middleware sudah ensure user login dan role pelanggan
        
        \Log::info('Apply: Starting application process', [
            'job_id' => $id,
            'user' => $user,
            'request_data' => $request->except(['cv', '_token'])
        ]);
        
        // Ambil detail lowongan pekerjaan dari API
        $response = $this->lowonganService->getById($id);
        
        // Periksa respons dari API terlebih dahulu
        if (!isset($response['status']) || $response['status'] !== 'success') {
            \Log::error('Apply: Failed to get job data', [
                'job_id' => $id,
                'api_response' => $response
            ]);
            return redirect()->route('recruitments.index')
                ->with('error', 'Lowongan pekerjaan tidak ditemukan.');
        }
        
        $jobData = $response['data'];
        
        // Periksa respons dari API dan cek status lowongan
        \Log::info('Apply validation check', [
            'job_id' => $id,
            'api_response_status' => $response['status'],
            'job_status' => $jobData['status'] ?? 'no job status',
            'deadline' => $jobData['tanggal_selesai'] ?? 'no deadline'
        ]);
        
        $deadline = isset($jobData['tanggal_selesai']) ? \Carbon\Carbon::parse($jobData['tanggal_selesai']) : null;
        
        if (!isset($jobData['status']) || $jobData['status'] !== 'aktif' ||
            ($deadline && $deadline->isPast())) {
            return redirect()->route('recruitments.show', $id)
                ->with('error', 'Lowongan ini sudah ditutup atau sudah lewat deadline.');
        }
        
        // Periksa apakah user sudah melamar
        $userId = $this->getUserId($user);
        if (!$userId) {
            \Log::error('Apply: User ID not found', ['user' => $user]);
            return redirect()->route('login')
                ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }
        
        $lamaranResponse = $this->lamaranService->getAll(['id_user' => $userId, 'id_lowongan_pekerjaan' => $id]);
        
        \Log::info('Checking existing application', [
            'user_id' => $userId,
            'lowongan_id' => $id,
            'response_status' => $lamaranResponse['status'] ?? 'no status',
            'data_count' => isset($lamaranResponse['data']['data']) ? count($lamaranResponse['data']['data']) : 'no data'
        ]);
        
        if (isset($lamaranResponse['status']) && $lamaranResponse['status'] === 'success' && 
            isset($lamaranResponse['data']['data']) && count($lamaranResponse['data']['data']) > 0) {
            return redirect()->route('recruitments.show', $id)
                ->with('error', 'Anda sudah melamar untuk posisi ini.');
        }
        
        $request->validate([
            'full_name' => 'required|string|max:255',
            'nik' => 'required|string|size:16|regex:/^[0-9]+$/',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:1000',
            'education' => 'required|in:SD,SMP,SMA,D1,D2,D3,D4,S1,S2,S3',
            'cover_letter' => 'required|string|max:5000',
            'cv' => 'required|file|mimes:pdf,doc,docx|max:2048', // 2MB sesuai API
        ]);

        // Persiapkan data untuk API
        $formData = [
            [
                'name' => 'id_lowongan_pekerjaan',
                'contents' => $id
            ],
            [
                'name' => 'nama_pelamar',
                'contents' => $request->full_name
            ],
            [
                'name' => 'NIK_pelamar',
                'contents' => $request->nik
            ],
            [
                'name' => 'email_pelamar',
                'contents' => $request->email
            ],
            [
                'name' => 'telepon_pelamar',
                'contents' => $request->phone
            ],
            [
                'name' => 'alamat_pelamar',
                'contents' => $request->address
            ],
            [
                'name' => 'pendidikan_terakhir',
                'contents' => $request->education
            ],
            [
                'name' => 'cv',
                'contents' => fopen($request->file('cv')->getPathname(), 'r'),
                'filename' => $request->file('cv')->getClientOriginalName()
            ]
        ];

        // Kirim data ke API menggunakan method khusus untuk multipart
        $response = $this->lamaranService->applyWithMultipart($formData);
        
        \Log::info('Respons pengiriman lamaran', [
            'respons' => $response,
            'user_id' => $userId,
            'lowongan_id' => $id
        ]);
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('recruitments.show', $id)
                ->with('success', 'Lamaran Anda berhasil dikirim! Tim HRD akan meninjau dokumen Anda.');
        } else {
            return back()->withInput()
                ->with('error', 'Gagal mengirim lamaran: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }
    
    /**
     * Show application status for user
     */
    public function applicationStatus($id)
    {
        $user = auth_user(); // Menggunakan custom auth helper - middleware sudah ensure user login dan role pelanggan
        
        // Ambil detail lowongan pekerjaan dari API
        $response = $this->lowonganService->getById($id);
        
        // Periksa respons dari API
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return redirect()->route('recruitments.index')
                ->with('error', 'Lowongan pekerjaan tidak ditemukan.');
        }
        
        // Periksa status lamaran user
        $userId = $this->getUserId($user);
        if (!$userId) {
            \Log::error('ApplicationStatus: User ID not found', ['user' => $user]);
            return redirect()->route('login')
                ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }
        
        $lamaranResponse = $this->lamaranService->getAll(['user_id' => $userId, 'recruitment_id' => $id]);
        
        if (!isset($lamaranResponse['status']) || $lamaranResponse['status'] !== 'success' || 
            !isset($lamaranResponse['data']) || count($lamaranResponse['data']) === 0) {
            return redirect()->route('recruitments.show', $id)
                ->with('error', 'Anda belum melamar untuk posisi ini.');
        }
        
        $recruitmentData = $response['data'];
        $applicationData = $lamaranResponse['data'][0]; // Ambil lamaran pertama (seharusnya hanya ada satu)
        
        // Transform data menjadi object untuk compatibility dengan view
        $recruitment = (object) [
            'id' => $recruitmentData['id_lowongan_pekerjaan'] ?? $id,
            'position' => $recruitmentData['judul_pekerjaan'] ?? 'Posisi tidak tersedia',
            'description' => $recruitmentData['deskripsi'] ?? 'Tidak ada deskripsi',
            // ... other fields
        ];
        
        $application = (object) $applicationData;
        
        return view('recruitments.application-status', compact('recruitment', 'application'));
    }
    
    /**
     * Manage applications for a recruitment
     */
    public function manageApplications($id)
    {
        // Debug log - pastikan ID lowongan yang benar diterima
        \Log::info("Managing applications for recruitment ID: {$id}");
        
        // Ambil detail lowongan pekerjaan dari API
        $lowonganResponse = $this->lowonganService->getById($id);
        
        if (!isset($lowonganResponse['status']) || $lowonganResponse['status'] !== 'success') {
            return redirect()->route('recruitments.index')
                ->with('error', 'Lowongan pekerjaan tidak ditemukan: ' . ($lowonganResponse['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        $lowonganData = $lowonganResponse['data'] ?? null;
        
        if (!$lowonganData || !is_array($lowonganData)) {
            return redirect()->route('recruitments.index')
                ->with('error', 'Data lowongan pekerjaan tidak valid');
        }
        
        // Transform data lowongan
        $recruitment = (object) [
            'id' => $lowonganData['id_lowongan_pekerjaan'] ?? null,
            'title' => $lowonganData['judul_pekerjaan'] ?? 'Posisi tidak tersedia',
            'quota' => $lowonganData['jumlah_lowongan'] ?? 0,
            'deadline' => $lowonganData['tanggal_selesai'] ?? null,
        ];
        
        // STEP 1: Ambil data dari API Lamaran Pekerjaan (untuk menu Seleksi Berkas)
        $lamaranResponse = $this->lamaranService->getAll(['id_lowongan_pekerjaan' => $id]);
        $documentApplications = collect();
        
        if (isset($lamaranResponse['status']) && $lamaranResponse['status'] === 'success') {
            $lamaranData = $lamaranResponse['data']['data'] ?? [];
            \Log::info("Found " . count($lamaranData) . " document applications for recruitment {$id}");
            
            $documentApplications = collect($lamaranData)->filter(function($lamaran) use ($id) {
                // FILTER KETAT: Pastikan data lamaran benar-benar untuk lowongan ini
                $lamaranLowonganId = $lamaran['id_lowongan_pekerjaan'] ?? null;
                if ($lamaranLowonganId != $id) {
                    \Log::warning("Lamaran ID {$lamaran['id_lamaran_pekerjaan']} belongs to recruitment {$lamaranLowonganId}, not {$id}");
                    return false;
                }
                return true;
            })->map(function($lamaran) use ($id) {
                $applicationId = $lamaran['id_lamaran_pekerjaan'] ?? null;
                $userId = $lamaran['id_user'] ?? null;
                $lamaranLowonganId = $lamaran['id_lowongan_pekerjaan'] ?? null;
                
                \Log::info("Processing document application", [
                    'applicationId' => $applicationId,
                    'userId' => $userId,
                    'status' => $lamaran['status'] ?? 'unknown',
                    'document_status_mapped' => $this->mapDocumentStatus($lamaran['status'] ?? 'pending')
                ]);
                
                return (object) [
                    'id' => $applicationId,
                    'user_id' => $userId,
                    'recruitment_id' => $lamaranLowonganId,
                    'name' => $lamaran['nama_pelamar'] ?? 'Tidak diketahui',
                    'email' => $lamaran['email_pelamar'] ?? 'Tidak diketahui',
                    'phone' => $lamaran['telepon_pelamar'] ?? 'Tidak diketahui',
                    'nik' => $lamaran['NIK_pelamar'] ?? null,
                    'alamat' => $lamaran['alamat_pelamar'] ?? null,
                    'pendidikan' => $lamaran['pendidikan_terakhir'] ?? null,
                    'cv_path' => $lamaran['CV'] ?? null,
                    'cv_info' => $lamaran['cv_info'] ?? null, // CV info from API
                    'status' => $lamaran['status'] ?? 'pending',
                    'created_at' => isset($lamaran['created_at']) ? \Carbon\Carbon::parse($lamaran['created_at']) : null,
                    'document_status' => $this->mapDocumentStatus($lamaran['status'] ?? 'pending'),
                    'document_notes' => null,
                    'stage' => 'document', // Tahapan seleksi berkas
                    'data_source' => 'lamaran_api',
                ];
            });
        }
        
        // STEP 2: Ambil data dari API Wawancara (untuk menu Interview)
        $wawancaraResponse = $this->wawancaraService->getAll(['id_lowongan_pekerjaan' => $id]);
        $interviewApplications = collect();
        
        if (isset($wawancaraResponse['status']) && $wawancaraResponse['status'] === 'success') {
            $wawancaraData = $wawancaraResponse['data']['data'] ?? [];
            \Log::info("Found " . count($wawancaraData) . " interview applications for recruitment {$id}");
            
            $interviewApplications = collect($wawancaraData)->filter(function($wawancara) use ($id) {
                // FILTER KETAT: Pastikan wawancara benar-benar untuk lowongan ini
                $lamaranData = $wawancara['lamaran_pekerjaan'] ?? null;
                if (!$lamaranData) {
                    \Log::warning("Wawancara ID {$wawancara['id_wawancara']} has no lamaran data");
                    return false;
                }
                
                $lamaranLowonganId = $lamaranData['id_lowongan_pekerjaan'] ?? null;
                if ($lamaranLowonganId != $id) {
                    \Log::warning("Wawancara ID {$wawancara['id_wawancara']} belongs to recruitment {$lamaranLowonganId}, not {$id}");
                    return false;
                }
                return true;
            })->map(function($wawancara) use ($id) {
                // Data dari relasi yang sudah di-include dalam respon API
                $lamaranData = $wawancara['lamaran_pekerjaan'] ?? null;
                $userData = $wawancara['user'] ?? null;
                
                $applicationId = $wawancara['id_lamaran_pekerjaan'] ?? null;
                $userId = $wawancara['id_user'] ?? null;
                
                return (object) [
                    'id' => $applicationId,
                    'user_id' => $userId,
                    'recruitment_id' => $id,
                    'name' => $lamaranData['nama_pelamar'] ?? ($userData['nama_user'] ?? 'Tidak diketahui'),
                    'email' => $lamaranData['email_pelamar'] ?? ($userData['email'] ?? 'Tidak diketahui'),
                    'phone' => $lamaranData['telepon_pelamar'] ?? ($userData['no_telp'] ?? 'Tidak diketahui'),
                    'nik' => $lamaranData['NIK_pelamar'] ?? null,
                    'alamat' => $lamaranData['alamat_pelamar'] ?? null,
                    'pendidikan' => $lamaranData['pendidikan_terakhir'] ?? null,
                    'cv_path' => $lamaranData['CV'] ?? null,
                    'status' => $lamaranData['status'] ?? 'pending',
                    'created_at' => isset($lamaranData['created_at']) ? \Carbon\Carbon::parse($lamaranData['created_at']) : 
                                   (isset($wawancara['created_at']) ? \Carbon\Carbon::parse($wawancara['created_at']) : null),
                    // Interview specific data
                    'interview_status' => $this->mapInterviewStatus($wawancara['status'] ?? 'pending'),
                    'interview_date' => $wawancara['tanggal_wawancara'] ?? null,
                    'interview_location' => $wawancara['lokasi'] ?? null,
                    'interview_notes' => $wawancara['catatan'] ?? null,
                    'interview_id' => $wawancara['id_wawancara'] ?? null,
                    // Document status from lamaran if available
                    'document_status' => isset($lamaranData['status']) ? 
                                       $this->mapDocumentStatus($lamaranData['status']) : 'accepted',
                    'stage' => 'interview', // Tahapan interview
                    'data_source' => 'wawancara_api',
                ];
            });
        }
        
        // STEP 3: Ambil data dari API Hasil Seleksi (untuk menu Hasil Seleksi)
        $hasilSeleksiResponse = $this->hasilSeleksiService->getAll(['id_lowongan_pekerjaan' => $id]);
        $finalApplications = collect();
        
        if (isset($hasilSeleksiResponse['status']) && $hasilSeleksiResponse['status'] === 'success') {
            $hasilSeleksiData = $hasilSeleksiResponse['data']['data'] ?? [];
            \Log::info("Found " . count($hasilSeleksiData) . " final applications for recruitment {$id}");
            
            $finalApplications = collect($hasilSeleksiData)->filter(function($hasilSeleksi) use ($id) {
                // Debug: Log raw data dari API
                \Log::info("Processing hasil seleksi raw data:", [
                    'id_hasil_seleksi' => $hasilSeleksi['id_hasil_seleksi'] ?? 'missing',
                    'id_user' => $hasilSeleksi['id_user'] ?? 'missing',
                    'status' => $hasilSeleksi['status'] ?? 'missing',
                    'lamaran_data_present' => isset($hasilSeleksi['lamaran_pekerjaan']),
                    'lowongan_id_in_lamaran' => $hasilSeleksi['lamaran_pekerjaan']['id_lowongan_pekerjaan'] ?? 'missing'
                ]);
                
                // PERBAIKAN: Ambil lowongan ID dari path yang benar
                // API struktur: hasil_seleksi.lamaran_pekerjaan.lowongan_pekerjaan.id_lowongan_pekerjaan
                $hasilLowonganId = null;
                
                if (isset($hasilSeleksi['lamaran_pekerjaan']['lowongan_pekerjaan']['id_lowongan_pekerjaan'])) {
                    $hasilLowonganId = $hasilSeleksi['lamaran_pekerjaan']['lowongan_pekerjaan']['id_lowongan_pekerjaan'];
                } elseif (isset($hasilSeleksi['lamaran_pekerjaan']['id_lowongan_pekerjaan'])) {
                    // Fallback ke direct field dalam lamaran_pekerjaan
                    $hasilLowonganId = $hasilSeleksi['lamaran_pekerjaan']['id_lowongan_pekerjaan'];
                }
                
                \Log::info("Extracted lowongan ID: {$hasilLowonganId} for hasil seleksi {$hasilSeleksi['id_hasil_seleksi']}");
                
                if (empty($hasilLowonganId) || $hasilLowonganId != $id) {
                    \Log::warning("Hasil seleksi ID {$hasilSeleksi['id_hasil_seleksi']} belongs to recruitment '{$hasilLowonganId}', not {$id} - FILTERED OUT");
                    return false;
                }
                
                // Pastikan ada ID hasil seleksi yang valid
                if (!isset($hasilSeleksi['id_hasil_seleksi']) || !$hasilSeleksi['id_hasil_seleksi']) {
                    \Log::warning("Hasil seleksi tidak memiliki ID yang valid - FILTERED OUT");
                    return false;
                }
                
                \Log::info("Hasil seleksi ID {$hasilSeleksi['id_hasil_seleksi']} PASSED filter for recruitment {$id}");
                return true;
            })->map(function($hasilSeleksi) use ($id) {
                // Data dari relasi yang sudah di-include dalam respon API
                $userData = $hasilSeleksi['user'] ?? null;
                // PERBAIKAN: Ambil lowongan data dari path yang benar
                $lowonganData = $hasilSeleksi['lamaran_pekerjaan']['lowongan_pekerjaan'] ?? null;
                
                $userId = $hasilSeleksi['id_user'] ?? null;
                $hasilSeleksiId = $hasilSeleksi['id_hasil_seleksi'] ?? null;
                
                // Coba ambil data lamaran berdasarkan user_id untuk informasi lengkap
                $lamaranData = null;
                if ($userId) {
                    try {
                        $lamaranResponse = $this->lamaranService->getAll(['id_user' => $userId, 'id_lowongan_pekerjaan' => $id]);
                        if (isset($lamaranResponse['status']) && $lamaranResponse['status'] === 'success') {
                            $lamaranList = $lamaranResponse['data']['data'] ?? [];
                            if (!empty($lamaranList)) {
                                $lamaranData = $lamaranList[0];
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning("Failed to fetch lamaran data for final result {$userId}: " . $e->getMessage());
                    }
                }
                
                \Log::info("Processing final application from API Hasil Seleksi", [
                    'hasil_seleksi_id' => $hasilSeleksiId,
                    'user_id' => $userId,
                    'raw_status_from_api' => $hasilSeleksi['status'] ?? 'unknown',
                    'final_status_mapped' => $this->mapFinalStatus($hasilSeleksi['status'] ?? 'pending'),
                    'data_source' => 'hasil_seleksi_api',
                    'has_lamaran_data' => !empty($lamaranData)
                ]);
                
                return (object) [
                    'id' => $lamaranData['id_lamaran_pekerjaan'] ?? null,
                    'user_id' => $userId,
                    'recruitment_id' => $id,
                    'name' => $lamaranData['nama_pelamar'] ?? ($userData['nama_user'] ?? 'Tidak diketahui'),
                    'email' => $lamaranData['email_pelamar'] ?? ($userData['email'] ?? 'Tidak diketahui'),
                    'phone' => $lamaranData['telepon_pelamar'] ?? ($userData['no_telp'] ?? 'Tidak diketahui'),
                    'nik' => $lamaranData['NIK_pelamar'] ?? null,
                    'alamat' => $lamaranData['alamat_pelamar'] ?? null,
                    'pendidikan' => $lamaranData['pendidikan_terakhir'] ?? null,
                    'cv_path' => $lamaranData['CV'] ?? null,
                    'cv_info' => $lamaranData['cv_info'] ?? null,
                    'status' => $lamaranData['status'] ?? 'pending',
                    'created_at' => isset($lamaranData['created_at']) ? \Carbon\Carbon::parse($lamaranData['created_at']) : 
                                   (isset($hasilSeleksi['created_at']) ? \Carbon\Carbon::parse($hasilSeleksi['created_at']) : null),
                    // Final selection specific data - PRIORITAS UTAMA dari API Hasil Seleksi
                    'final_status' => $this->mapFinalStatus($hasilSeleksi['status'] ?? 'pending'),
                    'final_notes' => $hasilSeleksi['catatan'] ?? null,
                    'start_date' => null, // Tidak ada di respon API, mungkin ditambah di form
                    'hasil_seleksi_id' => $hasilSeleksiId, // ID autentik dari API hasil seleksi
                    // Selection result object untuk referensi lengkap
                    'selection_result' => [
                        'id' => $hasilSeleksiId,
                        'status' => $hasilSeleksi['status'] ?? 'pending',
                        'catatan' => $hasilSeleksi['catatan'] ?? null,
                        'created_at' => $hasilSeleksi['created_at'] ?? null,
                        'updated_at' => $hasilSeleksi['updated_at'] ?? null,
                    ],
                    // Interview status - PERBAIKAN: Cek status interview yang sebenarnya
                    'interview_status' => $this->getActualInterviewStatus($lamaranData['id_lamaran_pekerjaan'] ?? null, $userId),
                    // Document status dari data lamaran yang sebenarnya
                    'document_status' => isset($lamaranData['status']) ? 
                                       $this->mapDocumentStatus($lamaranData['status']) : 'accepted',
                    'stage' => 'final', // Tahapan hasil seleksi
                    'data_source' => 'hasil_seleksi_api', // PENTING: Menandakan sumber data autentik
                ];
            })->filter(); // Filter out null values
        }
        
        // STEP 4: Gabungkan semua data untuk tab "Semua" dengan menghindari duplikasi dan validasi konsistensi
        $allApplications = collect();
        $processedUsers = [];
        
        \Log::info("Merging applications with consistency validation:");
        
        // Prioritas 1: Final applications (data terakhir dari seleksi)
        foreach ($finalApplications as $app) {
            if (!in_array($app->user_id, $processedUsers)) {
                // Validasi konsistensi data final
                $this->validateApplicationConsistency($app, 'final');
                $allApplications->push($app);
                $processedUsers[] = $app->user_id;
                \Log::info("Added final application for user {$app->user_id}", [
                    'interview_status' => $app->interview_status ?? 'undefined',
                    'final_status' => $app->final_status ?? 'undefined',
                    'document_status' => $app->document_status ?? 'undefined',
                    'stage' => $app->stage ?? 'undefined'
                ]);
            }
        }
        
        // Prioritas 2: Interview applications (yang belum ada di final)
        foreach ($interviewApplications as $app) {
            if (!in_array($app->user_id, $processedUsers)) {
                $this->validateApplicationConsistency($app, 'interview');
                $allApplications->push($app);
                $processedUsers[] = $app->user_id;
                \Log::info("Added interview application for user {$app->user_id}", [
                    'interview_status' => $app->interview_status ?? 'undefined',
                    'document_status' => $app->document_status ?? 'undefined',
                    'stage' => $app->stage ?? 'undefined'
                ]);
            }
        }
        
        // Prioritas 3: Document applications (yang belum ada di interview atau final)
        foreach ($documentApplications as $app) {
            if (!in_array($app->user_id, $processedUsers)) {
                $this->validateApplicationConsistency($app, 'document');
                $allApplications->push($app);
                $processedUsers[] = $app->user_id;
                \Log::info("Added document application for user {$app->user_id}", [
                    'document_status' => $app->document_status ?? 'undefined',
                    'stage' => $app->stage ?? 'undefined'
                ]);
            }
        }
        
        // Sort semua aplikasi berdasarkan tanggal created_at terbaru
        $allApplications = $allApplications->sortByDesc(function($app) {
            return $app->created_at;
        });
        
        // STEP 5: Create filtered collections for proper tab statistics
        // Tab counters should only count applications that are actively in that stage
        
        // Get user IDs that have moved to next stages
        $usersInInterview = $interviewApplications->pluck('user_id')->toArray();
        $usersInFinal = $finalApplications->pluck('user_id')->toArray();
        
        // Filter document applications: Only count those NOT in interview or final stage
        $documentStageApplications = $documentApplications->filter(function($app) use ($usersInInterview, $usersInFinal) {
            return !in_array($app->user_id, $usersInInterview) && !in_array($app->user_id, $usersInFinal);
        });
        
        // Filter interview applications: Only count those NOT in final stage
        $interviewStageApplications = $interviewApplications->filter(function($app) use ($usersInFinal) {
            return !in_array($app->user_id, $usersInFinal);
        });
        
        // Final applications remain as is (no filtering needed)
        $finalStageApplications = $finalApplications;
        
        \Log::info("Data Summary for recruitment {$id}:");
        \Log::info("- Document applications (total): " . $documentApplications->count());
        \Log::info("- Document applications (stage active): " . $documentStageApplications->count());
        \Log::info("- Interview applications (total): " . $interviewApplications->count());
        \Log::info("- Interview applications (stage active): " . $interviewStageApplications->count());
        \Log::info("- Final applications: " . $finalApplications->count());
        \Log::info("- All applications (unique): " . $allApplications->count());
        
        // Debug: Log detail struktur final applications
        if ($finalApplications->count() > 0) {
            \Log::info("Final applications detail structure:");
            foreach ($finalApplications as $index => $app) {
                \Log::info("  Final App #{$index}: " . ($app->name ?? 'No name'), [
                    'data_source' => $app->data_source ?? 'undefined',
                    'hasil_seleksi_id' => $app->hasil_seleksi_id ?? 'undefined',
                    'final_status' => $app->final_status ?? 'undefined',
                    'has_selection_result' => isset($app->selection_result) ? 'yes' : 'no'
                ]);
            }
        } else {
            \Log::warning("No final applications found despite API returning data - investigate filtering logic");
        }
        
        return view('recruitments.manage-applications', compact(
            'recruitment', 
            'documentApplications',       // Full data from API Lamaran - for tab content
            'interviewApplications',      // Full data from API Wawancara - for tab content
            'finalApplications',          // Full data from API Hasil Seleksi - for tab content
            'allApplications',            // Combined unique data - for tab content
            'documentStageApplications',  // Filtered for stage statistics (only active in document stage)
            'interviewStageApplications', // Filtered for stage statistics (only active in interview stage) 
            'finalStageApplications'      // Same as finalApplications (for consistency)
        ));
    }
    
    /**
     * Map document status from API lamaran to frontend
     */
    private function mapDocumentStatus($statusLamaran)
    {
        switch($statusLamaran) {
            case 'diterima':
                return 'accepted';
            case 'ditolak':
                return 'rejected';
            case 'pending':
            default:
                return 'pending';
        }
    }

    /**
     * Map interview status from API wawancara to frontend
     */
    private function mapInterviewStatus($statusWawancara)
    {
        switch($statusWawancara) {
            case 'lulus':
                return 'passed';
            case 'tidak_lulus':
                return 'failed';
            case 'terjadwal':
                return 'scheduled'; // Sudah dijadwal, menunggu interview
            case 'pending':
                return 'pending'; // Status pending tetap pending, bukan scheduled
            default:
                return 'not_scheduled'; // Belum ada data wawancara
        }
    }

    /**
     * Map final status from API hasil seleksi to frontend
     */
    private function mapFinalStatus($statusHasilSeleksi)
    {
        switch($statusHasilSeleksi) {
            case 'diterima':
                return 'accepted';
            case 'ditolak':
                return 'rejected';
            case 'pending':
            default:
                return 'pending';
        }
    }

    /**
     * Get actual interview status for an application by checking wawancara API
     * PERBAIKAN: Status interview hanya berdasarkan data wawancara actual, tidak terpengaruh final status
     */
    private function getActualInterviewStatus($applicationId, $userId)
    {
        try {
            // Cek status wawancara berdasarkan data API wawancara
            $wawancaraResponse = $this->wawancaraService->getAll([
                'id_lamaran_pekerjaan' => $applicationId,
                'id_user' => $userId
            ]);
            
            if (isset($wawancaraResponse['status']) && $wawancaraResponse['status'] === 'success') {
                $wawancaraData = $wawancaraResponse['data']['data'] ?? [];
                
                if (!empty($wawancaraData)) {
                    // Ambil wawancara terbaru
                    $latestWawancara = collect($wawancaraData)->sortByDesc('created_at')->first();
                    $actualStatus = $this->mapInterviewStatus($latestWawancara['status'] ?? 'pending');
                    
                    \Log::info("Actual interview status for application {$applicationId}: {$actualStatus} (from API: {$latestWawancara['status']})");
                    return $actualStatus;
                }
            }
            
            \Log::info("No interview data found for application {$applicationId}, defaulting to not_scheduled");
            return 'not_scheduled';
            
        } catch (\Exception $e) {
            \Log::error("Error getting actual interview status for application {$applicationId}: " . $e->getMessage());
            return 'not_scheduled';
        }
    }

    /**
     * Validate application data consistency
     */
    private function validateApplicationConsistency($application, $stage)
    {
        $userId = $application->user_id ?? 'unknown';
        $inconsistencies = [];
        
        // Check logical progression: document -> interview -> final
        if ($stage === 'final') {
            // Jika ada final status, interview harus completed (passed/failed)
            if (isset($application->final_status) && 
                in_array($application->final_status, ['accepted', 'rejected']) &&
                isset($application->interview_status) && 
                !in_array($application->interview_status, ['passed', 'failed'])) {
                
                $inconsistencies[] = "Final status '{$application->final_status}' but interview status is '{$application->interview_status}'";
            }
            
            // Jika final status 'accepted', interview harus 'passed'
            if (isset($application->final_status) && 
                $application->final_status === 'accepted' &&
                isset($application->interview_status) && 
                $application->interview_status !== 'passed') {
                
                $inconsistencies[] = "Final status 'accepted' but interview status is '{$application->interview_status}' (should be 'passed')";
            }
            
            // Jika final status 'rejected' dari interview yang failed
            if (isset($application->final_status) && 
                $application->final_status === 'rejected' &&
                isset($application->interview_status) && 
                $application->interview_status === 'failed') {
                
                // This is actually consistent, no issue
            }
        }
        
        if ($stage === 'interview') {
            // Jika ada interview, document harus accepted
            if (isset($application->interview_status) && 
                in_array($application->interview_status, ['scheduled', 'passed', 'failed']) &&
                isset($application->document_status) && 
                $application->document_status !== 'accepted') {
                
                $inconsistencies[] = "Interview status '{$application->interview_status}' but document status is '{$application->document_status}' (should be 'accepted')";
            }
        }
        
        // Log inconsistencies
        if (!empty($inconsistencies)) {
            \Log::warning("Data inconsistency detected for user {$userId} in {$stage} stage:", $inconsistencies);
            
            // Could implement auto-fix logic here if needed
            // For now, just log the issues
        }
        
        return empty($inconsistencies);
    }

    /**
     * Helper function untuk mendapatkan user ID dengan aman
     * Karena API login menggunakan 'id_user' bukan 'id'
     */
    private function getUserId($user)
    {
        if (!$user) {
            return null;
        }
        
        // Cek apakah menggunakan id_user (dari API) atau id (dari Eloquent)
        return $user->id_user ?? $user->id ?? null;
    }
    
    /**
     * Helper: Buat jadwal wawancara otomatis setelah dokumen diterima
     */
    private function createAutoInterview($applicationId, $lamaranData)
    {
        try {
            $userId = $lamaranData['id_user'] ?? null;
            if (!$userId) {
                \Log::warning("Cannot create auto interview: user ID not found in lamaran data", [
                    'applicationId' => $applicationId,
                    'lamaranData' => $lamaranData
                ]);
                return;
            }
            
            // Cek apakah wawancara sudah ada untuk lamaran ini
            $existingInterviewResponse = $this->wawancaraService->getByLamaran($applicationId);
            if (isset($existingInterviewResponse['status']) && $existingInterviewResponse['status'] === 'success') {
                $existingInterviews = $existingInterviewResponse['data']['data'] ?? [];
                if (!empty($existingInterviews)) {
                    \Log::info("Interview already exists for this application", ['applicationId' => $applicationId]);
                    return;
                }
            }
            
            // Jadwal wawancara 3 hari dari sekarang (jam kerja: 10:00)
            $interviewDate = now()->addDays(3)->setTime(10, 0, 0)->format('Y-m-d H:i:s');
            
            // Data sesuai dengan validasi API WawancaraController
            $wawancaraData = [
                'id_lamaran_pekerjaan' => $applicationId,
                'id_user' => $userId,
                'tanggal_wawancara' => $interviewDate,
                'lokasi' => 'Ruang Meeting Klinik (akan dikonfirmasi)',
                'catatan' => 'Jadwal wawancara otomatis dibuat setelah dokumen diterima. HR akan menghubungi untuk konfirmasi.',
                'status' => 'terjadwal' // sesuai validasi: terjadwal,lulus,tidak_lulus
            ];
            
            \Log::info("Creating auto interview with data", [
                'applicationId' => $applicationId,
                'wawancaraData' => $wawancaraData
            ]);
            
            $response = $this->wawancaraService->store($wawancaraData);
            
            \Log::info("Auto interview API response", [
                'applicationId' => $applicationId,
                'response' => $response,
                'request_data' => $wawancaraData
            ]);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                \Log::info("Auto interview created successfully", [
                    'applicationId' => $applicationId, 
                    'userId' => $userId,
                    'response' => $response
                ]);
            } else {
                \Log::error("Failed to create auto interview", [
                    'response' => $response, 
                    'applicationId' => $applicationId,
                    'wawancaraData' => $wawancaraData
                ]);
            }
        } catch (\Exception $e) {
            \Log::error("Exception in createAutoInterview", [
                'error' => $e->getMessage(),
                'applicationId' => $applicationId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Helper: Buat hasil seleksi otomatis setelah interview lulus
     */
    private function createAutoFinalResult($lamaranData, $interviewNotes = null)
    {
        try {
            $userId = $lamaranData['id_user'] ?? null;
            $lamaranId = $lamaranData['id_lamaran_pekerjaan'] ?? null;
            
            if (!$userId || !$lamaranId) {
                \Log::warning("Cannot create auto final result: missing data", ['userId' => $userId, 'lamaranId' => $lamaranId]);
                return;
            }
            
            // Cek apakah hasil seleksi sudah ada
            $existingResultResponse = $this->hasilSeleksiService->getByLamaran($lamaranId);
            
            \Log::info('Checking existing auto final result', [
                'userId' => $userId,
                'lamaranId' => $lamaranId,
                'response_status' => $existingResultResponse['status'] ?? 'no status'
            ]);
            
            if (isset($existingResultResponse['status']) && $existingResultResponse['status'] === 'success') {
                $existingResults = $existingResultResponse['data']['data'] ?? [];
                
                if (!empty($existingResults)) {
                    \Log::info("Final result already exists, skipping auto creation", ['userId' => $userId, 'lamaranId' => $lamaranId]);
                    return;
                }
            }
            
            $hasilSeleksiData = [
                'id_user' => $userId,
                'id_lamaran_pekerjaan' => $lamaranId,
                'status' => 'pending', // Akan diset manual oleh HR
                'catatan' => 'Otomatis dibuat setelah interview lulus. ' . ($interviewNotes ? 'Catatan interview: ' . $interviewNotes : '')
            ];
            
            \Log::info('Creating auto final result', [
                'userId' => $userId,
                'lamaranId' => $lamaranId,
                'data' => $hasilSeleksiData
            ]);
            
            $response = $this->hasilSeleksiService->store($hasilSeleksiData);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                \Log::info("Auto final result created successfully", ['userId' => $userId, 'lamaranId' => $lamaranId]);
            } else {
                $errorMessage = $response['message'] ?? 'Unknown error';
                if (strpos($errorMessage, 'already exists') !== false || strpos($errorMessage, 'sudah ada') !== false) {
                    \Log::info("Auto final result already exists, this is normal", ['userId' => $userId, 'lamaranId' => $lamaranId]);
                } else {
                    \Log::error("Failed to create auto final result", ['response' => $response, 'userId' => $userId]);
                }
            }
        } catch (\Exception $e) {
            \Log::error("Exception in createAutoFinalResult", ['error' => $e->getMessage(), 'lamaranData' => $lamaranData]);
        }
    }
    
    /**
     * Helper: Buat data pegawai dan update role user setelah diterima
     */
    private function createEmployeeAndUpdateRole($lamaranData, $lowonganId, $startDate = null)
    {
        try {
            $userId = $lamaranData['id_user'] ?? null;
            
            if (!$userId) {
                \Log::warning("Tidak dapat membuat pegawai: ID user tidak ditemukan", ['lamaranData' => $lamaranData]);
                return ['success' => false, 'message' => 'ID user tidak ditemukan'];
            }
            
            // Ambil data lowongan untuk mendapatkan posisi
            $lowonganResponse = $this->lowonganService->getById($lowonganId);
            if (!isset($lowonganResponse['status']) || $lowonganResponse['status'] !== 'success') {
                \Log::error("Tidak dapat mengambil data lowongan untuk pembuatan pegawai", ['lowonganId' => $lowonganId]);
                return ['success' => false, 'message' => 'Tidak dapat mengambil data lowongan'];
            }
            
            $lowonganData = $lowonganResponse['data'];
            $posisiId = $lowonganData['id_posisi'] ?? null;
            
            if (!$posisiId) {
                \Log::error("Tidak dapat mengambil ID posisi dari lowongan", ['lowonganData' => $lowonganData]);
                return ['success' => false, 'message' => 'ID posisi tidak ditemukan'];
            }
            
            // Ambil detail posisi untuk menentukan role yang sesuai
            $posisiResponse = $this->posisiService->getById($posisiId);
            $posisiNama = 'pegawai'; // default
            
            if (isset($posisiResponse['status']) && $posisiResponse['status'] === 'success') {
                $posisiData = $posisiResponse['data'];
                $posisiNama = $posisiData['nama_posisi'] ?? $lowonganData['judul_pekerjaan'] ?? 'pegawai';
            } else {
                // Fallback ke judul pekerjaan dari lowongan jika posisi tidak ditemukan
                $posisiNama = $lowonganData['judul_pekerjaan'] ?? 'pegawai';
            }
            
            // Tentukan role berdasarkan posisi pekerjaan
            $newRole = $this->mapPositionToRole($posisiNama);
            
            \Log::info("Menentukan role berdasarkan posisi", [
                'userId' => $userId,
                'posisiNama' => $posisiNama,
                'newRole' => $newRole
            ]);
            
            // Buat data pegawai sesuai dengan validasi API
            $pegawaiData = [
                'id_user' => $userId, // Hubungkan dengan user yang sudah ada
                'id_posisi' => $posisiId,
                'nama_lengkap' => $lamaranData['nama_pelamar'] ?? 'Unknown',
                'email' => $lamaranData['email_pelamar'] ?? null,
                'telepon' => $lamaranData['telepon_pelamar'] ?? null,
                'alamat' => $lamaranData['alamat_pelamar'] ?? null,
                'tanggal_masuk' => $startDate ?: now()->format('Y-m-d'),
                'NIP' => $this->generateNIP(),
                'create_user' => false  // tidak buat user baru karena sudah ada
            ];
            
            $pegawaiResponse = $this->pegawaiService->store($pegawaiData);
            
            if (isset($pegawaiResponse['status']) && $pegawaiResponse['status'] === 'success') {
                \Log::info("Pegawai berhasil dibuat", ['userId' => $userId, 'posisiId' => $posisiId, 'posisiNama' => $posisiNama]);
                
                // Update role user dari 'pelanggan' ke role yang sesuai dengan posisi
                $roleUpdateResult = $this->updateUserRole($userId, $newRole);
                
                if ($roleUpdateResult['success']) {
                    return [
                        'success' => true, 
                        'message' => 'Pegawai berhasil dibuat dan role user diperbarui',
                        'new_role' => $newRole,
                        'position_name' => $posisiNama,
                        'data' => [
                            'new_role' => $newRole,
                            'position_name' => $posisiNama,
                            'user_id' => $userId,
                            'pegawai_created' => true,
                            'role_updated' => true
                        ]
                    ];
                } else {
                    return [
                        'success' => false, 
                        'message' => 'Pegawai berhasil dibuat tetapi gagal memperbarui role user: ' . $roleUpdateResult['message'],
                        'new_role' => $newRole,
                        'position_name' => $posisiNama,
                        'data' => [
                            'new_role' => $newRole,
                            'position_name' => $posisiNama,
                            'user_id' => $userId,
                            'pegawai_created' => true,
                            'role_updated' => false
                        ]
                    ];
                }
            } else {
                \Log::error("Gagal membuat pegawai", ['response' => $pegawaiResponse, 'userId' => $userId]);
                return ['success' => false, 'message' => 'Gagal membuat data pegawai'];
            }
        } catch (\Exception $e) {
            \Log::error("Exception dalam createEmployeeAndUpdateRole", ['error' => $e->getMessage(), 'lamaranData' => $lamaranData]);
            return ['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()];
        }
    }
    
    /**
     * Helper: Update role user berdasarkan posisi pekerjaan
     */
    private function updateUserRole($userId, $newRole = 'pegawai')
    {
        try {
            \Log::info('Attempting to update user role', [
                'userId' => $userId,
                'newRole' => $newRole,
                'session_token_exists' => \Session::has('api_token'),
                'session_token_length' => \Session::has('api_token') ? strlen(\Session::get('api_token')) : 0
            ]);
            
            $response = $this->userService->update($userId, [
                'role' => $newRole
            ]);
            
            \Log::info('User role update response', [
                'userId' => $userId,
                'newRole' => $newRole,
                'response_status' => $response['status'] ?? 'no status',
                'response_message' => $response['message'] ?? 'no message',
                'full_response' => $response
            ]);
            
            if (isset($response['status']) && $response['status'] === 'success') {
                \Log::info("Role user berhasil diperbarui", [
                    'userId' => $userId, 
                    'newRole' => $newRole,
                    'message' => "User role berhasil diubah dari 'pelanggan' ke '{$newRole}'"
                ]);
                return ['success' => true, 'message' => "Role berhasil diperbarui ke {$newRole}"];
            } else {
                \Log::error("Gagal memperbarui role user", [
                    'response' => $response, 
                    'userId' => $userId,
                    'targetRole' => $newRole
                ]);
                return ['success' => false, 'message' => 'Gagal memperbarui role user: ' . ($response['message'] ?? 'Unknown error')];
            }
        } catch (\Exception $e) {
            \Log::error("Exception dalam updateUserRole", [
                'error' => $e->getMessage(), 
                'userId' => $userId,
                'targetRole' => $newRole,
                'trace' => $e->getTraceAsString()
            ]);
            return ['success' => false, 'message' => 'Terjadi kesalahan saat update role: ' . $e->getMessage()];
        }
    }
    
    /**
     * Helper: Generate NIP untuk pegawai baru
     */
    private function generateNIP()
    {
        // Format: YYYYMMDD + random 4 digit
        return date('Ymd') . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }
    
    /**
     * Get employment type display text
     */
    private function getEmploymentTypeDisplay($employmentType)
    {
        switch($employmentType) {
            case 'full_time':
            case 'full-time':
                return 'Full-time';
            case 'part_time':
            case 'part-time':
                return 'Part-time';
            case 'contract':
                return 'Kontrak';
            case 'internship':
                return 'Magang';
            case 'freelance':
                return 'Freelance';
            default:
                return 'Tidak ditentukan';
        }
    }

    /**
     * Format salary range for display
     */
    private function formatSalaryRange($minSalary, $maxSalary)
    {
        if (!$minSalary && !$maxSalary) {
            return 'Gaji dapat dinegosiasi';
        }

        $formatCurrency = function($amount) {
            if (!$amount) return null;
            
            // Convert to number if it's a string
            $amount = is_string($amount) ? (float) $amount : $amount;
            
            // Format in Indonesian Rupiah
            if ($amount >= 1000000) {
                return 'Rp ' . number_format($amount / 1000000, 1) . ' juta';
            } else {
                return 'Rp ' . number_format($amount, 0, ',', '.');
            }
        };

        $formattedMin = $formatCurrency($minSalary);
        $formattedMax = $formatCurrency($maxSalary);

        if ($formattedMin && $formattedMax) {
            return $formattedMin . ' - ' . $formattedMax;
        } elseif ($formattedMin) {
            return 'Minimal ' . $formattedMin;
        } elseif ($formattedMax) {
            return 'Maksimal ' . $formattedMax;
        }

        return 'Gaji dapat dinegosiasi';
    }

    /**
     * Update document status for application with recruitment context
     */
    public function updateDocumentStatusWithContext(Request $request, $recruitmentId, $applicationId)
    {
        try {
            \Log::info("Updating document status", [
                'recruitment_id' => $recruitmentId,
                'application_id' => $applicationId,
                'data' => $request->all()
            ]);

            // Validasi input
            $request->validate([
                'document_status' => 'required|in:pending,accepted,rejected',
                'document_notes' => 'nullable|string|max:1000',
                'tanggal_wawancara' => 'nullable|date|after:now',
                'lokasi_wawancara' => 'nullable|string|max:255'
            ]);

            $documentStatus = $request->input('document_status');
            $documentNotes = $request->input('document_notes');

            // Map document status untuk API
            $apiStatus = $this->mapDocumentStatusToApi($documentStatus);

            // Update status dokumen melalui API Lamaran Pekerjaan
            $updateData = [
                'status' => $apiStatus,
                'catatan' => $documentNotes
            ];

            $response = $this->lamaranService->update($applicationId, $updateData);

            if (!isset($response['status']) || $response['status'] !== 'success') {
                throw new \Exception('Gagal mengupdate status dokumen: ' . ($response['message'] ?? 'API Error'));
            }

            // Jika dokumen diterima dan ada jadwal wawancara, buat wawancara
            if ($documentStatus === 'accepted' && $request->filled('tanggal_wawancara')) {
                try {
                    // Ambil data lamaran untuk mendapatkan user_id
                    $lamaranResponse = $this->lamaranService->getById($applicationId);
                    if (isset($lamaranResponse['status']) && $lamaranResponse['status'] === 'success') {
                        $lamaranData = $lamaranResponse['data'];
                        $userId = $lamaranData['id_user'] ?? null;

                        if ($userId) {
                            // Buat jadwal wawancara
                            $wawancaraData = [
                                'id_lamaran_pekerjaan' => $applicationId,
                                'id_user' => $userId,
                                'tanggal_wawancara' => $request->input('tanggal_wawancara'),
                                'lokasi' => $request->input('lokasi_wawancara'),
                                'catatan' => 'Wawancara dijadwalkan otomatis setelah dokumen diterima',
                                'status' => 'terjadwal'
                            ];

                            $wawancaraResponse = $this->wawancaraService->store($wawancaraData);
                            
                            if (isset($wawancaraResponse['status']) && $wawancaraResponse['status'] === 'success') {
                                \Log::info("Wawancara berhasil dijadwalkan untuk application {$applicationId}");
                                return redirect()->route('recruitments.manage-applications', $recruitmentId)
                                    ->with('success', 'Status dokumen berhasil diupdate dan wawancara telah dijadwalkan!');
                            } else {
                                \Log::warning("Gagal membuat jadwal wawancara: " . ($wawancaraResponse['message'] ?? 'Unknown error'));
                                return redirect()->route('recruitments.manage-applications', $recruitmentId)
                                    ->with('success', 'Status dokumen berhasil diupdate, tetapi gagal menjadwalkan wawancara. Silakan jadwalkan manual.')
                                    ->with('warning', 'Wawancara belum terjadwal: ' . ($wawancaraResponse['message'] ?? 'Terjadi kesalahan'));
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error("Error creating interview schedule: " . $e->getMessage());
                    return redirect()->route('recruitments.manage-applications', $recruitmentId)
                        ->with('success', 'Status dokumen berhasil diupdate, tetapi gagal menjadwalkan wawancara. Silakan jadwalkan manual.')
                        ->with('warning', 'Error membuat jadwal wawancara: ' . $e->getMessage());
                }
            }

            // Response sukses standar
            $statusText = [
                'accepted' => 'diterima',
                'rejected' => 'ditolak',
                'pending' => 'sedang direview'
            ];

            return redirect()->route('recruitments.manage-applications', $recruitmentId)
                ->with('success', "Status dokumen berhasil diupdate menjadi: {$statusText[$documentStatus]}");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('recruitments.manage-applications', $recruitmentId)
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Data yang dimasukkan tidak valid. Silakan periksa kembali.');

        } catch (\Exception $e) {
            \Log::error("Error updating document status", [
                'recruitment_id' => $recruitmentId,
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('recruitments.manage-applications', $recruitmentId)
                ->with('error', 'Gagal mengupdate status dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Schedule interview with recruitment context
     */
    public function scheduleInterviewWithContext(Request $request, $recruitmentId, $applicationId)
    {
        try {
            \Log::info("Scheduling interview", [
                'recruitment_id' => $recruitmentId,
                'application_id' => $applicationId,
                'data' => $request->all()
            ]);

            // Validasi input
            $request->validate([
                'tanggal_wawancara' => 'required|date|after:now',
                'lokasi' => 'required|string|max:255',
                'catatan' => 'nullable|string|max:1000'
            ]);

            // Ambil data lamaran untuk mendapatkan user_id
            $lamaranResponse = $this->lamaranService->getById($applicationId);
            if (!isset($lamaranResponse['status']) || $lamaranResponse['status'] !== 'success') {
                throw new \Exception('Data lamaran tidak ditemukan');
            }

            $lamaranData = $lamaranResponse['data'];
            $userId = $lamaranData['id_user'] ?? null;

            if (!$userId) {
                throw new \Exception('User ID tidak ditemukan dalam data lamaran');
            }

            // Buat jadwal wawancara
            $wawancaraData = [
                'id_lamaran_pekerjaan' => $applicationId,
                'id_user' => $userId,
                'tanggal_wawancara' => $request->input('tanggal_wawancara'),
                'lokasi' => $request->input('lokasi'),
                'catatan' => $request->input('catatan'),
                'status' => 'terjadwal'
            ];

            $response = $this->wawancaraService->store($wawancaraData);

            if (!isset($response['status']) || $response['status'] !== 'success') {
                throw new \Exception('Gagal menjadwalkan wawancara: ' . ($response['message'] ?? 'API Error'));
            }

            return redirect()->route('recruitments.manage-applications', $recruitmentId)
                ->with('success', 'Wawancara berhasil dijadwalkan!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('recruitments.manage-applications', $recruitmentId)
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Data yang dimasukkan tidak valid. Silakan periksa kembali.');

        } catch (\Exception $e) {
            \Log::error("Error scheduling interview", [
                'recruitment_id' => $recruitmentId,
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('recruitments.manage-applications', $recruitmentId)
                ->with('error', 'Gagal menjadwalkan wawancara: ' . $e->getMessage());
        }
    }

    /**
     * Map document status from form to API format
     */
    private function mapDocumentStatusToApi($documentStatus)
    {
        switch ($documentStatus) {
            case 'accepted':
                return 'diterima';
            case 'rejected':
                return 'ditolak';
            case 'pending':
            default:
                return 'pending';
        }
    }

    /**
     * Update interview result with recruitment context
     */
    public function updateInterviewResultWithContext(Request $request, $recruitmentId, $applicationId)
    {
        try {
            \Log::info("Updating interview result", [
                'recruitment_id' => $recruitmentId,
                'application_id' => $applicationId,
                'data' => $request->all()
            ]);

            // Validasi input
            $request->validate([
                'wawancara_id' => 'required|integer',
                'status' => 'required|in:lulus,tidak_lulus,pending',
                'catatan' => 'nullable|string|max:1000'
            ]);

            $wawancaraId = $request->input('wawancara_id');
            $status = $request->input('status');
            $catatan = $request->input('catatan');

            // Update hasil wawancara melalui API
            $updateData = [
                'status' => $status,
                'catatan' => $catatan
            ];

            $response = $this->wawancaraService->update($wawancaraId, $updateData);

            if (!isset($response['status']) || $response['status'] !== 'success') {
                throw new \Exception('Gagal mengupdate hasil wawancara: ' . ($response['message'] ?? 'API Error'));
            }

            // Jika lulus wawancara, buat hasil seleksi otomatis
            if ($status === 'lulus') {
                try {
                    // Ambil data lamaran untuk mendapatkan user_id
                    $lamaranResponse = $this->lamaranService->getById($applicationId);
                    if (isset($lamaranResponse['status']) && $lamaranResponse['status'] === 'success') {
                        $lamaranData = $lamaranResponse['data'];
                        $userId = $lamaranData['id_user'] ?? null;

                        if ($userId) {
                            // Buat hasil seleksi
                            $hasilSeleksiData = [
                                'id_lamaran_pekerjaan' => $applicationId,
                                'id_user' => $userId,
                                'status' => 'pending',
                                'catatan' => 'Otomatis dibuat setelah lulus wawancara'
                            ];

                            $hasilSeleksiResponse = $this->hasilSeleksiService->store($hasilSeleksiData);
                            
                            if (isset($hasilSeleksiResponse['status']) && $hasilSeleksiResponse['status'] === 'success') {
                                \Log::info("Hasil seleksi berhasil dibuat untuk application {$applicationId}");
                            } else {
                                \Log::warning("Gagal membuat hasil seleksi: " . ($hasilSeleksiResponse['message'] ?? 'Unknown error'));
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error("Error creating hasil seleksi: " . $e->getMessage());
                }
            }

            $statusText = [
                'lulus' => 'lulus',
                'tidak_lulus' => 'tidak lulus',
                'pending' => 'pending'
            ];

            return redirect()->route('recruitments.manage-applications', $recruitmentId)
                ->with('success', "Hasil wawancara berhasil diupdate: {$statusText[$status]}");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('recruitments.manage-applications', $recruitmentId)
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Data yang dimasukkan tidak valid. Silakan periksa kembali.');

        } catch (\Exception $e) {
            \Log::error("Error updating interview result", [
                'recruitment_id' => $recruitmentId,
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('recruitments.manage-applications', $recruitmentId)
                ->with('error', 'Gagal mengupdate hasil wawancara: ' . $e->getMessage());
        }
    }

    /**
     * Update final decision with recruitment context
     */
    public function updateFinalDecisionWithContext(Request $request, $recruitmentId, $applicationId)
    {
        try {
            \Log::info("Updating final decision", [
                'recruitment_id' => $recruitmentId,
                'application_id' => $applicationId,
                'data' => $request->all()
            ]);

            // Validasi input
            $request->validate([
                'final_status' => 'required|in:accepted,rejected,waiting_list',
                'final_notes' => 'nullable|string|max:1000',
                'start_date' => 'nullable|date|after:today'
            ]);

            $finalStatus = $request->input('final_status');
            $finalNotes = $request->input('final_notes');
            $startDate = $request->input('start_date');

            // Map final status untuk API hasil seleksi
            $apiStatus = $this->mapFinalStatusToApi($finalStatus);

            // Ambil data lamaran untuk mendapatkan user_id
            $lamaranResponse = $this->lamaranService->getById($applicationId);
            if (!isset($lamaranResponse['status']) || $lamaranResponse['status'] !== 'success') {
                throw new \Exception('Data lamaran tidak ditemukan');
            }

            $lamaranData = $lamaranResponse['data'];
            $userId = $lamaranData['id_user'] ?? null;

            if (!$userId) {
                throw new \Exception('User ID tidak ditemukan dalam data lamaran');
            }

            // Cek apakah sudah ada hasil seleksi
            $existingHasilResponse = $this->hasilSeleksiService->getAll([
                'id_lamaran_pekerjaan' => $applicationId,
                'id_user' => $userId
            ]);

            $hasilSeleksiData = [
                'id_lamaran_pekerjaan' => $applicationId,
                'id_user' => $userId,
                'status' => $apiStatus,
                'catatan' => $finalNotes ?: "Keputusan final: {$finalStatus}" . ($startDate ? ". Mulai kerja: {$startDate}" : '')
            ];

            $response = null;
            $isUpdate = false;

            // Cek apakah sudah ada hasil seleksi
            if (isset($existingHasilResponse['status']) && $existingHasilResponse['status'] === 'success') {
                $existingData = $existingHasilResponse['data']['data'] ?? [];
                $existingHasil = collect($existingData)->first(function($item) use ($applicationId, $userId) {
                    return ($item['id_lamaran_pekerjaan'] == $applicationId && $item['id_user'] == $userId);
                });

                if ($existingHasil) {
                    // Update existing hasil seleksi
                    $hasilSeleksiId = $existingHasil['id_hasil_seleksi'];
                    $response = $this->hasilSeleksiService->update($hasilSeleksiId, $hasilSeleksiData);
                    $isUpdate = true;
                } else {
                    // Create new hasil seleksi
                    $response = $this->hasilSeleksiService->store($hasilSeleksiData);
                }
            } else {
                // Create new hasil seleksi
                $response = $this->hasilSeleksiService->store($hasilSeleksiData);
            }

            if (!isset($response['status']) || $response['status'] !== 'success') {
                throw new \Exception('Gagal menyimpan keputusan final: ' . ($response['message'] ?? 'API Error'));
            }

            // Jika diterima dan ada tanggal mulai kerja, buat data pegawai
            if ($finalStatus === 'accepted' && $startDate) {
                try {
                    $this->createEmployeeFromApplication($applicationId, $userId, $startDate);
                } catch (\Exception $e) {
                    \Log::error("Error creating employee: " . $e->getMessage());
                    // Continue with success message but note the employee creation failed
                }
            }

            $statusText = [
                'accepted' => 'diterima',
                'rejected' => 'ditolak',
                'waiting_list' => 'waiting list'
            ];

            $actionText = $isUpdate ? 'diperbarui' : 'disimpan';

            return redirect()->route('recruitments.manage-applications', $recruitmentId)
                ->with('success', "Keputusan final berhasil {$actionText}: {$statusText[$finalStatus]}");

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('recruitments.manage-applications', $recruitmentId)
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Data yang dimasukkan tidak valid. Silakan periksa kembali.');

        } catch (\Exception $e) {
            \Log::error("Error updating final decision", [
                'recruitment_id' => $recruitmentId,
                'application_id' => $applicationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('recruitments.manage-applications', $recruitmentId)
                ->with('error', 'Gagal menyimpan keputusan final: ' . $e->getMessage());
        }
    }

    /**
     * Map final status from form to API format
     */
    private function mapFinalStatusToApi($finalStatus)
    {
        switch ($finalStatus) {
            case 'accepted':
                return 'diterima';
            case 'rejected':
                return 'ditolak';
            case 'waiting_list':
                return 'pending';
            default:
                return 'pending';
        }
    }

    /**
     * Create employee from accepted application
     */
    private function createEmployeeFromApplication($applicationId, $userId, $startDate)
    {
        try {
            // Ambil data user
            $userResponse = $this->userService->getById($userId);
            if (!isset($userResponse['status']) || $userResponse['status'] !== 'success') {
                throw new \Exception('Data user tidak ditemukan');
            }

            $userData = $userResponse['data'];

            // Ambil data lamaran untuk posisi
            $lamaranResponse = $this->lamaranService->getById($applicationId);
            if (!isset($lamaranResponse['status']) || $lamaranResponse['status'] !== 'success') {
                throw new \Exception('Data lamaran tidak ditemukan');
            }

            $lamaranData = $lamaranResponse['data'];

            // Data pegawai baru
            $pegawaiData = [
                'id_user' => $userId,
                'id_posisi' => $lamaranData['id_lowongan_pekerjaan'] ?? null, // Adjust based on your structure
                'tanggal_bergabung' => $startDate,
                'status_pegawai' => 'aktif',
                'catatan' => 'Pegawai dari hasil rekrutmen'
            ];

            $response = $this->pegawaiService->store($pegawaiData);

            if (isset($response['status']) && $response['status'] === 'success') {
                \Log::info("Employee created successfully for user {$userId}");
                
                // Update user role to pegawai
                $this->updateUserRole($userId, 'pegawai');
                
                return true;
            } else {
                throw new \Exception('Gagal membuat data pegawai: ' . ($response['message'] ?? 'API Error'));
            }

        } catch (\Exception $e) {
            \Log::error("Error creating employee from application", [
                'application_id' => $applicationId,
                'user_id' => $userId,
                'start_date' => $startDate,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
