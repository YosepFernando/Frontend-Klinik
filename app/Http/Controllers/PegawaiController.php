<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Posisi;
use App\Models\User;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Pegawai::with(['user', 'posisi']);
        
        // Filter by position
        if ($request->filled('posisi_id')) {
            $query->where('id_posisi', $request->posisi_id);
        }
        
        // Filter by gender
        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }
        
        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $pegawai = $query->orderBy('nama_lengkap')->paginate(15);
        $posisi = Posisi::all();
        
        return view('pegawai.index', compact('pegawai', 'posisi'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $posisi = Posisi::all();
        $users = User::whereIn('role', ['admin', 'hrd', 'front_office', 'kasir', 'dokter', 'beautician'])
                    ->doesntHave('pegawai')
                    ->get();
        
        return view('pegawai.create', compact('posisi', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_user' => 'nullable|exists:users,id',
            'nama_lengkap' => 'required|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'NIK' => 'nullable|string|max:16|unique:tb_pegawai,NIK',
            'id_posisi' => 'required|exists:tb_posisi,id_posisi',
            'agama' => 'nullable|string|max:20',
            'tanggal_masuk' => 'required|date',
        ]);

        Pegawai::create($request->all());

        return redirect()->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Pegawai $pegawai)
    {
        $pegawai->load(['user', 'posisi', 'absensi', 'gaji']);
        return view('pegawai.show', compact('pegawai'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pegawai $pegawai)
    {
        $posisi = Posisi::all();
        $users = User::whereIn('role', ['admin', 'hrd', 'front_office', 'kasir', 'dokter', 'beautician'])
                    ->where(function($query) use ($pegawai) {
                        $query->doesntHave('pegawai')
                              ->orWhere('id', $pegawai->id_user);
                    })
                    ->get();
        
        return view('pegawai.edit', compact('pegawai', 'posisi', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pegawai $pegawai)
    {
        $request->validate([
            'id_user' => 'nullable|exists:users,id',
            'nama_lengkap' => 'required|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:laki-laki,perempuan',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'NIK' => 'nullable|string|max:16|unique:tb_pegawai,NIK,' . $pegawai->id_pegawai . ',id_pegawai',
            'id_posisi' => 'required|exists:tb_posisi,id_posisi',
            'agama' => 'nullable|string|max:20',
            'tanggal_masuk' => 'required|date',
            'tanggal_keluar' => 'nullable|date|after_or_equal:tanggal_masuk',
        ]);

        $pegawai->update($request->all());

        return redirect()->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pegawai $pegawai)
    {
        $pegawai->delete();
        
        return redirect()->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil dihapus.');
    }
}
