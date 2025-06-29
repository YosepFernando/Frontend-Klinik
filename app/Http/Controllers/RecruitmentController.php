<?php

namespace App\Http\Controllers;

use App\Services\LowonganPekerjaanService;
use App\Services\LamaranPekerjaanService;
use App\Services\PosisiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecruitmentController extends Controller
{
    protected $lowonganService;
    protected $lamaranService;
    protected $posisiService;
    
    /**
     * Constructor untuk menginisialisasi service
     */
    public function __construct(
        LowonganPekerjaanService $lowonganService,
        LamaranPekerjaanService $lamaranService,
        PosisiService $posisiService
    ) {
        $this->lowonganService = $lowonganService;
        $this->lamaranService = $lamaranService;
        $this->posisiService = $posisiService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Ambil data lowongan pekerjaan dari API
        $response = $this->lowonganService->getAll(['page' => 1, 'per_page' => 10]);
        
        // Periksa respons dari API
        if (!isset($response['status']) || $response['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data lowongan pekerjaan: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        // Siapkan data untuk view
        $recruitments = $response['data'] ?? [];
        
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
        
        // Filter posisi selain Admin
        $posisi = collect($posisiResponse['data'] ?? [])->filter(function($item) {
            return $item['nama_posisi'] !== 'Admin';
        })->values();
        
        return view('recruitments.create', compact('posisi'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_posisi' => 'required',
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
        ]);

        // Ambil data posisi dari API
        $posisiResponse = $this->posisiService->getById($request->id_posisi);
        
        // Periksa respons dari API
        if (!isset($posisiResponse['status']) || $posisiResponse['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data posisi: ' . ($posisiResponse['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        $posisi = $posisiResponse['data'] ?? null;
        
        if (!$posisi) {
            return back()->with('error', 'Data posisi tidak ditemukan');
        }

        // Persiapkan data untuk dikirim ke API
        $data = [
            'position' => $posisi['nama_posisi'],
            'id_posisi' => $request->id_posisi,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'application_deadline' => $request->application_deadline,
            'slots' => $request->slots,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'employment_type' => $request->employment_type,
            'status' => $request->status,
            'age_min' => $request->age_min,
            'age_max' => $request->age_max,
        ];

        // Kirim data ke API
        $response = $this->lowonganService->store($data);
        
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
        
        $recruitment = $response['data'] ?? null;
        
        if (!$recruitment) {
            return back()->with('error', 'Data lowongan pekerjaan tidak ditemukan');
        }
        
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
        
        $recruitment = $response['data'] ?? null;
        
        if (!$recruitment) {
            return back()->with('error', 'Data lowongan pekerjaan tidak ditemukan');
        }
        
        // Ambil data posisi dari API
        $posisiResponse = $this->posisiService->getAll();
        
        // Periksa respons dari API
        if (!isset($posisiResponse['status']) || $posisiResponse['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data posisi: ' . ($posisiResponse['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        // Filter posisi selain Admin
        $posisi = collect($posisiResponse['data'] ?? [])->filter(function($item) {
            return $item['nama_posisi'] !== 'Admin';
        })->values();
        
        return view('recruitments.edit', compact('recruitment', 'posisi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_posisi' => 'required',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'application_deadline' => 'required|date',
            'slots' => 'required|integer|min:1',
            'salary_min' => 'nullable|numeric|min:0',
            'salary_max' => 'nullable|numeric|min:0|gte:salary_min',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'status' => 'required|in:open,closed',
            'age_min' => 'nullable|integer|min:16|max:100',
            'age_max' => 'nullable|integer|min:16|max:100|gte:age_min',
        ]);

        // Ambil data posisi dari API
        $posisiResponse = $this->posisiService->getById($request->id_posisi);
        
        // Periksa respons dari API
        if (!isset($posisiResponse['status']) || $posisiResponse['status'] !== 'success') {
            return back()->with('error', 'Gagal memuat data posisi: ' . ($posisiResponse['message'] ?? 'Terjadi kesalahan pada server'));
        }
        
        $posisi = $posisiResponse['data'] ?? null;
        
        if (!$posisi) {
            return back()->with('error', 'Data posisi tidak ditemukan');
        }

        // Persiapkan data untuk dikirim ke API
        $data = [
            'position' => $posisi['nama_posisi'],
            'id_posisi' => $request->id_posisi,
            'description' => $request->description,
            'requirements' => $request->requirements,
            'application_deadline' => $request->application_deadline,
            'slots' => $request->slots,
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'employment_type' => $request->employment_type,
            'status' => $request->status,
            'age_min' => $request->age_min,
            'age_max' => $request->age_max,
        ];

        // Kirim data ke API
        $response = $this->lowonganService->update($id, $data);
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('recruitments.index')
                ->with('success', 'Lowongan kerja berhasil diperbarui.');
        } else {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui lowongan kerja: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Kirim permintaan hapus ke API
        $response = $this->lowonganService->delete($id);
        
        // Periksa respons dari API
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('recruitments.index')
                ->with('success', 'Lowongan kerja berhasil dihapus.');
        } else {
            return back()->with('error', 'Gagal menghapus lowongan kerja: ' . ($response['message'] ?? 'Terjadi kesalahan pada server'));
        }
    }

    /**
     * Apply for recruitment (for customers/pelanggan)
     */
    public function apply(Request $request, $id)
    {
        $user = auth()->user();
        
        if (!$user->isPelanggan()) {
            abort(403, 'Hanya pelanggan yang dapat melamar pekerjaan.');
        }
        
        // Ambil detail lowongan pekerjaan dari API
        $response = $this->lowonganService->getById($id);
        
        // Periksa respons dari API dan cek status lowongan
        if (!isset($response['status']) || $response['status'] !== 'success' || 
            !isset($response['data']) || $response['data']['status'] !== 'open') {
            return redirect()->route('recruitments.show', $id)
                ->with('error', 'Lowongan ini sudah ditutup atau sudah lewat deadline.');
        }
        
        // Periksa apakah user sudah melamar
        $lamaranResponse = $this->lamaranService->getAll(['user_id' => $user->id, 'recruitment_id' => $id]);
        
        if (isset($lamaranResponse['status']) && $lamaranResponse['status'] === 'success' && 
            isset($lamaranResponse['data']) && count($lamaranResponse['data']) > 0) {
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
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120', // 5MB max
            'cover_letter_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'additional_documents' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        // Persiapkan data untuk API
        $formData = new \GuzzleHttp\Psr7\MultipartStream([
            [
                'name' => 'recruitment_id',
                'contents' => $id
            ],
            [
                'name' => 'user_id',
                'contents' => $user->id
            ],
            [
                'name' => 'full_name',
                'contents' => $request->full_name
            ],
            [
                'name' => 'nik',
                'contents' => $request->nik
            ],
            [
                'name' => 'email',
                'contents' => $request->email
            ],
            [
                'name' => 'phone',
                'contents' => $request->phone
            ],
            [
                'name' => 'address',
                'contents' => $request->address
            ],
            [
                'name' => 'education',
                'contents' => $request->education
            ],
            [
                'name' => 'cover_letter',
                'contents' => $request->cover_letter
            ],
            [
                'name' => 'cv',
                'contents' => fopen($request->file('cv')->getPathname(), 'r'),
                'filename' => $request->file('cv')->getClientOriginalName()
            ]
        ]);

        // Tambahkan cover letter file jika ada
        if ($request->hasFile('cover_letter_file')) {
            $formData = new \GuzzleHttp\Psr7\MultipartStream(array_merge(
                $formData->getBoundary(),
                [
                    [
                        'name' => 'cover_letter_file',
                        'contents' => fopen($request->file('cover_letter_file')->getPathname(), 'r'),
                        'filename' => $request->file('cover_letter_file')->getClientOriginalName()
                    ]
                ]
            ));
        }

        // Tambahkan additional documents jika ada
        if ($request->hasFile('additional_documents')) {
            $formData = new \GuzzleHttp\Psr7\MultipartStream(array_merge(
                $formData->getBoundary(),
                [
                    [
                        'name' => 'additional_documents',
                        'contents' => fopen($request->file('additional_documents')->getPathname(), 'r'),
                        'filename' => $request->file('additional_documents')->getClientOriginalName()
                    ]
                ]
            ));
        }

        // Kirim data ke API
        $response = $this->lamaranService->apply($formData);
        
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
    public function applicationStatus(Recruitment $recruitment)
    {
        $user = auth()->user();
        $application = $recruitment->getUserApplication($user->id);
        
        if (!$application) {
            return redirect()->route('recruitments.show', $recruitment)
                ->with('error', 'Anda belum melamar untuk posisi ini.');
        }
        
        return view('recruitments.application-status', compact('recruitment', 'application'));
    }
    
    /**
     * Manage applications for a recruitment
     */
    public function manageApplications(Recruitment $recruitment)
    {
        $applications = $recruitment->applications()
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('recruitments.manage-applications', compact('recruitment', 'applications'));
    }

    /**
     * Update document review status
     */
    public function updateDocumentStatus(Request $request, RecruitmentApplication $application)
    {
        $request->validate([
            'document_status' => 'required|in:accepted,rejected',
            'document_notes' => 'nullable|string|max:1000',
        ]);
        
        $application->update([
            'document_status' => $request->document_status,
            'document_notes' => $request->document_notes,
            'document_reviewed_at' => now(),
            'document_reviewed_by' => auth()->id(),
            'overall_status' => $request->document_status === 'accepted' ? 'interview_stage' : 'rejected',
        ]);
        
        return redirect()->back()
            ->with('success', 'Status seleksi berkas berhasil diperbarui.');
    }
    
    /**
     * Schedule interview
     */
    public function scheduleInterview(Request $request, RecruitmentApplication $application)
    {
        $request->validate([
            'interview_date' => 'required|date|after:now',
            'interview_location' => 'required|string|max:255',
            'interview_notes' => 'nullable|string|max:1000',
        ]);
        
        $application->update([
            'interview_status' => 'scheduled',
            'interview_date' => $request->interview_date,
            'interview_location' => $request->interview_location,
            'interview_notes' => $request->interview_notes,
            'interview_scheduled_by' => auth()->id(),
        ]);
        
        return redirect()->back()
            ->with('success', 'Jadwal wawancara berhasil ditentukan.');
    }
    
    /**
     * Update interview result
     */
    public function updateInterviewResult(Request $request, RecruitmentApplication $application)
    {
        $request->validate([
            'interview_status' => 'required|in:passed,failed',
            'interview_score' => 'nullable|integer|min:0|max:100',
            'interview_notes' => 'nullable|string|max:1000',
        ]);
        
        $application->update([
            'interview_status' => $request->interview_status,
            'interview_score' => $request->interview_score,
            'interview_notes' => $request->interview_notes,
            'interview_completed_at' => now(),
            'interview_conducted_by' => auth()->id(),
        ]);
        
        return redirect()->back()
            ->with('success', 'Hasil wawancara berhasil diperbarui.');
    }
    
    /**
     * Update final decision
     */
    public function updateFinalDecision(Request $request, RecruitmentApplication $application)
    {
        $request->validate([
            'final_status' => 'required|in:accepted,rejected,waiting_list',
            'start_date' => 'nullable|date|after:today',
            'final_notes' => 'nullable|string|max:1000',
        ]);
        
        $updateData = [
            'final_status' => $request->final_status,
            'final_notes' => $request->final_notes,
            'final_decided_at' => now(),
            'final_decided_by' => auth()->id(),
        ];
        
        if ($request->final_status === 'accepted' && $request->start_date) {
            $updateData['start_date'] = $request->start_date;
        }
        
        $application->update($updateData);
        
        return redirect()->back()
            ->with('success', 'Keputusan akhir berhasil disimpan.');
    }
    
    /**
     * Show apply form for recruitment
     */
    public function showApplyForm(Recruitment $recruitment)
    {
        $user = auth()->user();
        
        if (!$user->isPelanggan()) {
            abort(403, 'Hanya pelanggan yang dapat melamar pekerjaan.');
        }
        
        if (!$recruitment->isOpen()) {
            return redirect()->route('recruitments.show', $recruitment)
                ->with('error', 'Lowongan ini sudah ditutup atau sudah lewat deadline.');
        }
        
        // Check if user already applied
        if ($recruitment->hasUserApplied($user->id)) {
            return redirect()->route('recruitments.application-status', $recruitment);
        }
        
        return view('recruitments.apply', compact('recruitment'));
    }
    
    /**
     * Show user's job applications
     */
    public function myApplications()
    {
        $user = auth()->user();
        
        $applications = RecruitmentApplication::where('user_id', $user->id)
            ->with(['recruitment', 'recruitment.posisi'])
            ->latest()
            ->paginate(10);
            
        return view('recruitments.my-applications', compact('applications'));
    }
}
