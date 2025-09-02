<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TreatmentService;

class TreatmentController extends Controller
{
    protected $treatmentService;

    public function __construct(TreatmentService $treatmentService)
    {
        $this->middleware('auth');
        $this->treatmentService = $treatmentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $params = [];
        
        // Filter parameters
        if ($request->filled('year')) {
            $params['year'] = $request->year;
        }
        if ($request->filled('month')) {
            $params['month'] = $request->month;
        }
        if ($request->filled('search')) {
            $params['search'] = $request->search;
        }
        if ($request->filled('category')) {
            $params['category'] = $request->category;
        }
        if ($request->filled('status')) {
            $params['status'] = $request->status;
        }
        
        $response = $this->treatmentService->getAll($params);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            $treatmentsData = $response['data']['treatments'] ?? [];
            $pagination = $response['data']['pagination'] ?? null;
            $years = $response['data']['years'] ?? [];
            $categories = $response['data']['categories'] ?? [];
            
            // Convert to LengthAwarePaginator for consistency with views
            $treatments = new \Illuminate\Pagination\LengthAwarePaginator(
                $treatmentsData,
                $pagination['total'] ?? count($treatmentsData),
                $pagination['per_page'] ?? 15,
                $pagination['current_page'] ?? 1,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
        } else {
            $treatments = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                15,
                1,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
            $pagination = null;
            $years = [];
            $categories = [];
            session()->flash('error', $response['message'] ?? 'Gagal mengambil data treatment');
        }

        return view('treatments.index', compact('treatments', 'pagination', 'years', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $response = $this->treatmentService->getCategories();
        $categories = [];
        
        if (isset($response['status']) && $response['status'] === 'success') {
            $categories = $response['data'] ?? [];
        }
        
        return view('treatments.create', compact('categories'));
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

        $response = $this->treatmentService->store($request->all());
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('treatments.index')
                ->with('success', 'Treatment berhasil ditambahkan.');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', $response['message'] ?? 'Gagal menambahkan treatment.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $response = $this->treatmentService->getById($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            $treatment = $response['data'];
            return view('treatments.show', compact('treatment'));
        } else {
            return redirect()->route('treatments.index')
                ->with('error', $response['message'] ?? 'Treatment tidak ditemukan.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $response = $this->treatmentService->getById($id);
        $categoriesResponse = $this->treatmentService->getCategories();
        
        $treatment = null;
        $categories = [];
        
        if (isset($response['status']) && $response['status'] === 'success') {
            $treatment = $response['data'];
        }
        
        if (isset($categoriesResponse['status']) && $categoriesResponse['status'] === 'success') {
            $categories = $categoriesResponse['data'] ?? [];
        }
        
        if (!$treatment) {
            return redirect()->route('treatments.index')
                ->with('error', 'Treatment tidak ditemukan.');
        }
        
        return view('treatments.edit', compact('treatment', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'category' => 'required|in:medical,beauty,wellness',
        ]);

        $response = $this->treatmentService->update($id, $request->all());
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('treatments.index')
                ->with('success', 'Treatment berhasil diperbarui.');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', $response['message'] ?? 'Gagal memperbarui treatment.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $response = $this->treatmentService->delete($id);
        
        if (isset($response['status']) && $response['status'] === 'success') {
            return redirect()->route('treatments.index')
                ->with('success', 'Treatment berhasil dihapus.');
        } else {
            return redirect()->route('treatments.index')
                ->with('error', $response['message'] ?? 'Gagal menghapus treatment.');
        }
    }
}
