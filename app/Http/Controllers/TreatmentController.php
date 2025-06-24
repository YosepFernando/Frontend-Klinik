<?php

namespace App\Http\Controllers;

use App\Models\Treatment;
use Illuminate\Http\Request;

class TreatmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Treatment::query();
        
        // Filter by year and month (SQLite compatible)
        if ($request->filled('year') && $request->filled('month')) {
            $query->whereRaw("strftime('%Y', created_at) = ?", [$request->year])
                  ->whereRaw("strftime('%m', created_at) = ?", [sprintf('%02d', $request->month)]);
        } elseif ($request->filled('year')) {
            $query->whereRaw("strftime('%Y', created_at) = ?", [$request->year]);
        } elseif ($request->filled('month')) {
            $query->whereRaw("strftime('%m', created_at) = ?", [sprintf('%02d', $request->month)]);
        }
        
        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $treatments = $query->latest()->paginate(12);
        
        // Get available years and months for filter (SQLite compatible)
        $years = Treatment::selectRaw("strftime('%Y', created_at) as year")
                         ->distinct()
                         ->orderBy('year', 'desc')
                         ->pluck('year');
        
        $categories = Treatment::select('category')
                              ->distinct()
                              ->whereNotNull('category')
                              ->pluck('category');
        
        return view('treatments.index', compact('treatments', 'years', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('treatments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'category' => 'required|in:medical,beauty,wellness',
        ]);

        Treatment::create($request->all());

        return redirect()->route('treatments.index')
            ->with('success', 'Treatment berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Treatment $treatment)
    {
        return view('treatments.show', compact('treatment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Treatment $treatment)
    {
        return view('treatments.edit', compact('treatment'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Treatment $treatment)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'category' => 'required|in:medical,beauty,wellness',
        ]);

        $treatment->update($request->all());

        return redirect()->route('treatments.index')
            ->with('success', 'Treatment berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Treatment $treatment)
    {
        $treatment->delete();

        return redirect()->route('treatments.index')
            ->with('success', 'Treatment berhasil dihapus.');
    }
}
