<?php

namespace App\Http\Controllers;

use App\Models\Recruitment;
use App\Models\RecruitmentApplication;
use App\Models\Posisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecruitmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recruitments = Recruitment::latest()->paginate(10);
        return view('recruitments.index', compact('recruitments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $posisi = Posisi::where('nama_posisi', '!=', 'Admin')->orderBy('nama_posisi')->get();
        return view('recruitments.create', compact('posisi'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_posisi' => 'required|exists:tb_posisi,id_posisi',
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

        // Get position name from selected posisi
        $posisi = Posisi::find($request->id_posisi);

        Recruitment::create([
            'position' => $posisi->nama_posisi,
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
        ]);

        return redirect()->route('recruitments.index')
            ->with('success', 'Lowongan kerja berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Recruitment $recruitment)
    {
        return view('recruitments.show', compact('recruitment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Recruitment $recruitment)
    {
        $posisi = Posisi::where('nama_posisi', '!=', 'Admin')->orderBy('nama_posisi')->get();
        return view('recruitments.edit', compact('recruitment', 'posisi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Recruitment $recruitment)
    {
        $request->validate([
            'id_posisi' => 'required|exists:tb_posisi,id_posisi',
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

        // Get position name from selected posisi
        $posisi = Posisi::find($request->id_posisi);

        $recruitment->update([
            'position' => $posisi->nama_posisi,
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
        ]);

        return redirect()->route('recruitments.index')
            ->with('success', 'Lowongan kerja berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Recruitment $recruitment)
    {
        $recruitment->delete();
        
        return redirect()->route('recruitments.index')
            ->with('success', 'Lowongan kerja berhasil dihapus.');
    }

    /**
     * Apply for recruitment (for customers/pelanggan)
     */
    public function apply(Request $request, Recruitment $recruitment)
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
            return redirect()->route('recruitments.show', $recruitment)
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

        // Store CV file
        $cvPath = $request->file('cv')->store('recruitment-applications/cv', 'public');
        
        // Store cover letter file if provided
        $coverLetterPath = null;
        if ($request->hasFile('cover_letter_file')) {
            $coverLetterPath = $request->file('cover_letter_file')->store('recruitment-applications/cover-letters', 'public');
        }
        
        // Store additional documents
        $additionalDocsPath = null;
        if ($request->hasFile('additional_documents')) {
            $additionalDocsPath = $request->file('additional_documents')->store('recruitment-applications/additional', 'public');
        }
        
        // Create application
        RecruitmentApplication::create([
            'recruitment_id' => $recruitment->id,
            'user_id' => $user->id,
            'full_name' => $request->full_name,
            'nik' => $request->nik,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'education' => $request->education,
            'cover_letter' => $request->cover_letter,
            'cv_path' => $cvPath,
            'cover_letter_path' => $coverLetterPath,
            'additional_documents_path' => $additionalDocsPath,
            'document_status' => 'pending',
            'interview_status' => 'pending',
            'final_status' => 'pending',
        ]);
        
        return redirect()->route('recruitments.show', $recruitment)
            ->with('success', 'Lamaran Anda berhasil dikirim! Tim HRD akan meninjau dokumen Anda.');
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
