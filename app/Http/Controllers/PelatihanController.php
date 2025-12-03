<?php

namespace App\Http\Controllers;

use App\Services\PelatihanService;
use App\Services\PegawaiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PelatihanController extends Controller
{
    protected $pelatihanService;
    protected $pegawaiService;

    public function __construct(PelatihanService $pelatihanService, PegawaiService $pegawaiService)
    {
        $this->pelatihanService = $pelatihanService;
        $this->pegawaiService = $pegawaiService;
    }

    /**
     * Display a listing of pelatihan for admin/HRD
     */
    public function index(Request $request)
    {
        try {
            $params = $request->only(['judul', 'jenis_pelatihan', 'per_page']);
            $response = $this->pelatihanService->getAll($params);

            if (isset($response['status']) && $response['status'] === 'success') {
                $pelatihan = $response['data'];
                return view('pelatihan.index', compact('pelatihan'));
            }

            return view('pelatihan.index', ['pelatihan' => collect(), 'error' => $response['message'] ?? 'Gagal mengambil data pelatihan']);
        } catch (\Exception $e) {
            Log::error('PelatihanController::index - ' . $e->getMessage());
            return view('pelatihan.index', ['pelatihan' => collect(), 'error' => 'Terjadi kesalahan sistem']);
        }
    }

    /**
     * Show the form for creating a new pelatihan
     */
    public function create()
    {
        return view('pelatihan.create');
    }

    /**
     * Store a newly created pelatihan
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'judul' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'jenis_pelatihan' => 'required|in:video,document,offline',
                'jadwal_pelatihan' => 'required|date',
                'link_url' => 'nullable|url',
                'durasi' => 'required|integer|min:1',
                'is_selective' => 'boolean',
                'max_participants' => 'nullable|integer|min:1',
            ]);

            $response = $this->pelatihanService->store($validated);

            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->route('pelatihan.index')->with('success', 'Pelatihan berhasil dibuat');
            }

            return redirect()->back()->with('error', $response['message'] ?? 'Gagal membuat pelatihan')->withInput();
        } catch (\Exception $e) {
            Log::error('PelatihanController::store - ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat membuat pelatihan')->withInput();
        }
    }

    /**
     * Display the specified pelatihan
     */
    public function show($id)
    {
        try {
            $response = $this->pelatihanService->getById($id);

            if (isset($response['status']) && $response['status'] === 'success') {
                $pelatihan = $response['data'];
                
                // Get participants
                $participantsResponse = $this->pelatihanService->getParticipants($id);
                $participants = [];
                if (isset($participantsResponse['status']) && $participantsResponse['status'] === 'success') {
                    $participants = $participantsResponse['data']['participants'] ?? [];
                }

                return view('pelatihan.show', compact('pelatihan', 'participants'));
            }

            return redirect()->route('pelatihan.index')->with('error', $response['message'] ?? 'Pelatihan tidak ditemukan');
        } catch (\Exception $e) {
            Log::error('PelatihanController::show - ' . $e->getMessage());
            return redirect()->route('pelatihan.index')->with('error', 'Terjadi kesalahan saat mengambil data pelatihan');
        }
    }

    /**
     * Show the form for editing the specified pelatihan
     */
    public function edit($id)
    {
        try {
            $response = $this->pelatihanService->getById($id);

            if (isset($response['status']) && $response['status'] === 'success') {
                $pelatihan = $response['data'];
                return view('pelatihan.edit', compact('pelatihan'));
            }

            return redirect()->route('pelatihan.index')->with('error', $response['message'] ?? 'Pelatihan tidak ditemukan');
        } catch (\Exception $e) {
            Log::error('PelatihanController::edit - ' . $e->getMessage());
            return redirect()->route('pelatihan.index')->with('error', 'Terjadi kesalahan saat mengambil data pelatihan');
        }
    }

    /**
     * Update the specified pelatihan
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'judul' => 'required|string|max:255',
                'deskripsi' => 'required|string',
                'jenis_pelatihan' => 'required|in:video,document,offline',
                'jadwal_pelatihan' => 'required|date',
                'link_url' => 'nullable|url',
                'durasi' => 'required|integer|min:1',
                'is_selective' => 'boolean',
                'max_participants' => 'nullable|integer|min:1',
            ]);

            $response = $this->pelatihanService->update($id, $validated);

            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->route('pelatihan.show', $id)->with('success', 'Pelatihan berhasil diupdate');
            }

            return redirect()->back()->with('error', $response['message'] ?? 'Gagal mengupdate pelatihan')->withInput();
        } catch (\Exception $e) {
            Log::error('PelatihanController::update - ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupdate pelatihan')->withInput();
        }
    }

    /**
     * Remove the specified pelatihan
     */
    public function destroy($id)
    {
        try {
            $response = $this->pelatihanService->delete($id);

            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->route('pelatihan.index')->with('success', 'Pelatihan berhasil dihapus');
            }

            return redirect()->back()->with('error', $response['message'] ?? 'Gagal menghapus pelatihan');
        } catch (\Exception $e) {
            Log::error('PelatihanController::destroy - ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus pelatihan');
        }
    }

    /**
     * Show participant management page
     */
    public function manageParticipants($id)
    {
        try {
            // Get pelatihan details
            $pelatihanResponse = $this->pelatihanService->getById($id);
            if (!isset($pelatihanResponse['status']) || $pelatihanResponse['status'] !== 'success') {
                return redirect()->route('pelatihan.index')->with('error', 'Pelatihan tidak ditemukan');
            }
            $pelatihan = $pelatihanResponse['data'];

            // Get current participants
            $participantsResponse = $this->pelatihanService->getParticipants($id);
            $participants = [];
            if (isset($participantsResponse['status']) && $participantsResponse['status'] === 'success') {
                $participants = $participantsResponse['data']['participants'] ?? [];
            }

            // Get all employees for selection
            $employeesResponse = $this->pegawaiService->getAllPegawai(['status' => 'active']);
            $employees = [];
            if (isset($employeesResponse['status']) && $employeesResponse['status'] === 'success') {
                $employees = $employeesResponse['data']['data'] ?? $employeesResponse['data'] ?? [];
            }

            return view('pelatihan.manage-participants', compact('pelatihan', 'participants', 'employees'));
        } catch (\Exception $e) {
            Log::error('PelatihanController::manageParticipants - ' . $e->getMessage());
            return redirect()->route('pelatihan.index')->with('error', 'Terjadi kesalahan saat mengambil data');
        }
    }

    /**
     * Assign participants to pelatihan
     */
    public function assignParticipants(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'pegawai_ids' => 'required|array',
                'pegawai_ids.*' => 'integer',
            ]);

            $response = $this->pelatihanService->assignParticipants($id, $validated['pegawai_ids']);

            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->route('pelatihan.manage-participants', $id)->with('success', 'Peserta berhasil ditambahkan');
            }

            return redirect()->back()->with('error', $response['message'] ?? 'Gagal menambahkan peserta');
        } catch (\Exception $e) {
            Log::error('PelatihanController::assignParticipants - ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menambahkan peserta');
        }
    }

    /**
     * Remove participant from pelatihan
     */
    public function removeParticipant($id, $pegawaiId)
    {
        try {
            $response = $this->pelatihanService->removeParticipant($id, $pegawaiId);

            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->route('pelatihan.manage-participants', $id)->with('success', 'Peserta berhasil dihapus');
            }

            return redirect()->back()->with('error', $response['message'] ?? 'Gagal menghapus peserta');
        } catch (\Exception $e) {
            Log::error('PelatihanController::removeParticipant - ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus peserta');
        }
    }

    /**
     * Update participant progress
     */
    public function updateParticipantProgress(Request $request, $id, $pegawaiId)
    {
        try {
            $validated = $request->validate([
                'status_partisipasi' => 'required|in:assigned,started,completed,cancelled',
                'progress' => 'nullable|integer|min:0|max:100',
                'catatan' => 'nullable|string',
            ]);

            $response = $this->pelatihanService->updateParticipantProgress($id, $pegawaiId, $validated);

            if (isset($response['status']) && $response['status'] === 'success') {
                return redirect()->back()->with('success', 'Progress peserta berhasil diupdate');
            }

            return redirect()->back()->with('error', $response['message'] ?? 'Gagal mengupdate progress peserta');
        } catch (\Exception $e) {
            Log::error('PelatihanController::updateParticipantProgress - ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupdate progress');
        }
    }

    /**
     * Show my trainings for employees
     */
    public function myTrainings()
    {
        try {
            $response = $this->pelatihanService->getMyTrainings();

            if (isset($response['status']) && $response['status'] === 'success') {
                $trainings = $response['data']['data'] ?? $response['data'] ?? [];
                return view('pelatihan.my-trainings', compact('trainings'));
            }

            return view('pelatihan.my-trainings', ['trainings' => collect(), 'error' => $response['message'] ?? 'Gagal mengambil data pelatihan']);
        } catch (\Exception $e) {
            Log::error('PelatihanController::myTrainings - ' . $e->getMessage());
            return view('pelatihan.my-trainings', ['trainings' => collect(), 'error' => 'Terjadi kesalahan sistem']);
        }
    }
}
