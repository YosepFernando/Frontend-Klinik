<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Training::query();
        
        // Search by title
        if ($request->filled('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        // Filter by training type
        if ($request->filled('jenis_pelatihan')) {
            $query->where('jenis_pelatihan', $request->jenis_pelatihan);
        }
        
        $trainings = $query->latest()->paginate(12);
        
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

        Training::create([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'jenis_pelatihan' => $request->jenis_pelatihan,
            'konten' => $request->konten,
            'link_url' => $request->link_url,
            'durasi' => $request->durasi,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('trainings.index')
            ->with('success', 'Pelatihan berhasil dibuat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Training $training)
    {
        return view('trainings.show', compact('training'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Training $training)
    {
        return view('trainings.edit', compact('training'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Training $training)
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

        $training->update([
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'jenis_pelatihan' => $request->jenis_pelatihan,
            'konten' => $request->konten,
            'link_url' => $request->link_url,
            'durasi' => $request->durasi,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('trainings.index')
            ->with('success', 'Pelatihan berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Training $training)
    {
        $training->delete();
        
        return redirect()->route('trainings.index')
            ->with('success', 'Pelatihan berhasil dihapus.');
    }
}
