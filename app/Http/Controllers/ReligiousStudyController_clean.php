<?php

namespace App\Http\Controllers;

use App\Services\ReligiousStudyService;
use App\Services\UserService;
use Illuminate\Http\Request;

class ReligiousStudyController extends Controller
{
    protected $religiousStudyService;
    protected $userService;
    
    public function __construct(ReligiousStudyService $religiousStudyService, UserService $userService)
    {
        $this->religiousStudyService = $religiousStudyService;
        $this->userService = $userService;
    }
    
    /**
     * Tampilkan daftar kajian keagamaan
     */
    public function index(Request $request)
    {
        $params = [];
        
        // Search by title
        if ($request->filled('search')) {
            $params['search'] = $request->search;
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $params['status'] = $request->status;
        }
        
        // Filter by leader
        if ($request->filled('leader_id')) {
            $params['leader_id'] = $request->leader_id;
        }
        
        // Ambil data kajian keagamaan dari API
        $response = $this->religiousStudyService->getAll($params);
        $religiousStudies = collect($response['data'] ?? []);
        
        // Ambil daftar pimpinan untuk filter
        $leadersResponse = $this->userService->getAll(['role' => 'admin,hrd']);
        $leaders = collect($leadersResponse['data'] ?? []);
        
        return view('religious-studies.index', compact('religiousStudies', 'leaders'));
    }

    /**
     * Tampilkan form untuk kajian keagamaan baru
     */
    public function create()
    {
        // Ambil daftar pimpinan
        $leadersResponse = $this->userService->getAll(['role' => 'admin,hrd']);
        $leaders = collect($leadersResponse['data'] ?? []);
        
        return view('religious-studies.create', compact('leaders'));
    }

    /**
     * Simpan kajian keagamaan baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'leader_id' => 'required|integer',
            'scheduled_date' => 'required|date|after:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'is_mandatory' => 'boolean'
        ]);
        
        $validated['is_mandatory'] = $request->has('is_mandatory');
        
        // Kirim ke API
        $response = $this->religiousStudyService->store($validated);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('religious-studies.index')
                            ->with('success', 'Kajian keagamaan berhasil ditambahkan.');
        }
        
        return redirect()->route('religious-studies.create')
                        ->with('error', 'Gagal menambahkan kajian keagamaan: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Tampilkan detail kajian keagamaan
     */
    public function show($id)
    {
        $response = $this->religiousStudyService->getById($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            $religiousStudy = $response['data'];
            
            // Ambil daftar peserta
            $participantsResponse = $this->religiousStudyService->getParticipants($id);
            $participants = collect($participantsResponse['data'] ?? []);
            
            return view('religious-studies.show', compact('religiousStudy', 'participants'));
        }
        
        return redirect()->route('religious-studies.index')
                        ->with('error', 'Kajian keagamaan tidak ditemukan.');
    }

    /**
     * Tampilkan form edit kajian keagamaan
     */
    public function edit($id)
    {
        $response = $this->religiousStudyService->getById($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            $religiousStudy = $response['data'];
            
            // Ambil daftar pimpinan
            $leadersResponse = $this->userService->getAll(['role' => 'admin,hrd']);
            $leaders = collect($leadersResponse['data'] ?? []);
            
            return view('religious-studies.edit', compact('religiousStudy', 'leaders'));
        }
        
        return redirect()->route('religious-studies.index')
                        ->with('error', 'Kajian keagamaan tidak ditemukan.');
    }

    /**
     * Update kajian keagamaan
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'leader_id' => 'required|integer',
            'scheduled_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'is_mandatory' => 'boolean',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled'
        ]);
        
        $validated['is_mandatory'] = $request->has('is_mandatory');
        
        // Kirim ke API
        $response = $this->religiousStudyService->update($id, $validated);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('religious-studies.index')
                            ->with('success', 'Kajian keagamaan berhasil diperbarui.');
        }
        
        return redirect()->route('religious-studies.edit', $id)
                        ->with('error', 'Gagal memperbarui kajian keagamaan: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Hapus kajian keagamaan
     */
    public function destroy($id)
    {
        $response = $this->religiousStudyService->delete($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('religious-studies.index')
                            ->with('success', 'Kajian keagamaan berhasil dihapus.');
        }
        
        return redirect()->route('religious-studies.index')
                        ->with('error', 'Gagal menghapus kajian keagamaan: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Daftar ke kajian keagamaan
     */
    public function join(Request $request, $id)
    {
        $response = $this->religiousStudyService->joinStudy($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('religious-studies.show', $id)
                            ->with('success', 'Anda berhasil mendaftar ke kajian keagamaan ini.');
        }
        
        return redirect()->route('religious-studies.show', $id)
                        ->with('error', 'Gagal mendaftar: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }

    /**
     * Batal daftar dari kajian keagamaan
     */
    public function leave(Request $request, $id)
    {
        $response = $this->religiousStudyService->leaveStudy($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('religious-studies.show', $id)
                            ->with('success', 'Anda berhasil membatalkan pendaftaran.');
        }
        
        return redirect()->route('religious-studies.show', $id)
                        ->with('error', 'Gagal membatalkan pendaftaran: ' . ($response['message'] ?? 'Terjadi kesalahan.'));
    }
}
