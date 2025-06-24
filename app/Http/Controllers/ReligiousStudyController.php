<?php

namespace App\Http\Controllers;

use App\Models\ReligiousStudy;
use App\Models\ReligiousStudyParticipant;
use App\Models\User;
use Illuminate\Http\Request;

class ReligiousStudyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ReligiousStudy::with(['leader', 'participants']);
        
        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by leader
        if ($request->filled('leader_id')) {
            $query->where('leader_id', $request->leader_id);
        }
        
        $religiousStudies = $query->latest()->paginate(12);
        
        // Get leaders for filter
        $leaders = User::whereIn('role', ['admin', 'hrd'])
                      ->where('is_active', true)
                      ->orderBy('name')
                      ->get();
        
        return view('religious-studies.index', compact('religiousStudies', 'leaders'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ReligiousStudy $religiousStudy)
    {
        $religiousStudy->load(['leader', 'participants.user']);
        return view('religious-studies.show', compact('religiousStudy'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReligiousStudy $religiousStudy)
    {
        $leaders = User::whereIn('role', ['admin', 'hrd'])
                      ->where('is_active', true)
                      ->orderBy('name')
                      ->get();
        
        return view('religious-studies.edit', compact('religiousStudy', 'leaders'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReligiousStudy $religiousStudy)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'leader_id' => 'required|exists:users,id',
            'study_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'max_participants' => 'required|integer|min:' . $religiousStudy->participants->count() . '|max:100',
            'materials' => 'nullable|string',
            'status' => 'required|in:scheduled,ongoing,completed,cancelled',
        ]);

        $religiousStudy->update($request->all());

        return redirect()->route('religious-studies.show', $religiousStudy)
                        ->with('success', 'Pengajian berhasil diperbarui.');
    }

    /**
     * Join religious study
     */
    public function join(ReligiousStudy $religiousStudy)
    {
        if ($religiousStudy->status !== 'scheduled') {
            return back()->with('error', 'Tidak dapat mendaftar pada pengajian yang tidak terjadwal.');
        }

        if ($religiousStudy->participants->count() >= $religiousStudy->max_participants) {
            return back()->with('error', 'Pengajian sudah penuh.');
        }

        if ($religiousStudy->participants->contains('user_id', auth()->id())) {
            return back()->with('error', 'Anda sudah terdaftar pada pengajian ini.');
        }

        ReligiousStudyParticipant::create([
            'religious_study_id' => $religiousStudy->id,
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Berhasil mendaftar pengajian.');
    }

    /**
     * Leave religious study
     */
    public function leave(ReligiousStudy $religiousStudy)
    {
        $participant = $religiousStudy->participants()->where('user_id', auth()->id())->first();
        
        if (!$participant) {
            return back()->with('error', 'Anda tidak terdaftar pada pengajian ini.');
        }

        $participant->delete();

        return back()->with('success', 'Berhasil keluar dari pengajian.');
    }
}
